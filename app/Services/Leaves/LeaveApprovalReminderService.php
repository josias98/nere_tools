<?php

namespace App\Services\Leaves;

use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeaveApprovalReminderService
{
    public function __construct(private LeaveNotificationService $notifications) {}

    public function sendDue(): int
    {
        $sent = Cache::lock('leave-approval-reminders', 300)->get(fn (): int => $this->sendDueUnlocked());

        return is_int($sent) ? $sent : 0;
    }

    private function sendDueUnlocked(): int
    {
        $hours = max(1, (int) (LeaveSetting::query()
            ->where('key', 'approval_reminder_hours')
            ->value('value') ?? config('leaves.approval_reminder_hours')));
        $cutoff = now()->subHours($hours);
        $sent = 0;

        LeaveRequest::query()
            ->whereIn('status', ['pending_supervisor', 'pending_hr', 'pending_dg'])
            ->with('currentApproval')
            ->each(function (LeaveRequest $request) use ($cutoff, &$sent): void {
                $step = $request->currentApproval?->step_key;

                if (! $step) {
                    return;
                }

                $lastNotification = NotificationLog::query()
                    ->where('related_type', LeaveRequest::class)
                    ->where('related_id', $request->id)
                    ->whereIn('event', ['leave.pending_'.$step, 'leave.reminder_pending_'.$step])
                    ->latest('id')
                    ->first();
                $lastAttention = $lastNotification?->queued_at ?? $lastNotification?->created_at ?? $request->updated_at;

                if ($lastAttention->greaterThan($cutoff)) {
                    return;
                }

                try {
                    $this->notifications->notifyCurrentStepValidators($request, true);
                    $sent++;
                } catch (Throwable $exception) {
                    Log::error('Leave approval reminder failed.', [
                        'exception' => $exception,
                        'leave_request_id' => $request->id,
                    ]);
                }
            });

        return $sent;
    }
}
