<?php

namespace App\Services\Leaves;

use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use App\Models\NotificationLog;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class LeaveApprovalReminderService
{
    public function __construct(private LeaveNotificationService $notifications) {}

    public function sendDue(): int
    {
        $sent = Cache::lock('leave-approval-reminders', 300)->get(fn (): int => $this->sendDueUnlocked());

        return is_int($sent) ? $sent : 0;
    }

    public function sendManual(LeaveRequest $request, int $approvalId, string $recipient): string
    {
        return Cache::lock('leave-approval-employee:'.$request->employee_id, 120)
            ->block(10, function () use ($request, $approvalId, $recipient): string {
                $request = $request->fresh(['employee', 'leaveType', 'currentApproval']);
                $approval = $request->currentApproval;
                if (! $approval || $approval->id !== $approvalId || $request->status !== 'pending_'.$approval->step_key) {
                    throw new DomainException('Cette étape n’attend plus de validation. Rechargez la demande.');
                }

                if (! config('services.graph_mail.enabled')) {
                    throw new DomainException('L’envoi des emails est désactivé. Contactez l’administration.');
                }

                $recipient = strtolower(trim($recipient));
                $signatory = collect($this->notifications->currentStepRecipients($request))->firstWhere('email', $recipient);
                if (! $signatory) {
                    throw new DomainException('Ce signataire ne peut pas être relancé à cette étape.');
                }

                $key = 'leave-manual-reminder:'.$approval->id.':'.hash('sha256', $recipient);
                if (RateLimiter::tooManyAttempts($key, 1)) {
                    $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
                    throw new DomainException("Ce signataire a déjà été relancé. Réessayez dans {$minutes} min.");
                }

                $this->notifications->notifyCurrentStepValidators($request, true, $recipient);
                RateLimiter::hit($key, 3600);

                return $signatory['name'];
            });
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
