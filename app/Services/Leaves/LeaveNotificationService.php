<?php

namespace App\Services\Leaves;

use App\Models\AuditLog;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;

class LeaveNotificationService
{
    public function __construct(private LeaveValidatorService $validators)
    {
    }

    public function requestSubmitted(LeaveRequest $request): void
    {
        $recipients = $this->validators->validatorsFor($request)
            ->pluck('employee.email')
            ->filter()
            ->values()
            ->all();

        $this->log('leave.submitted', $request, $recipients);
    }

    public function requestDecided(LeaveRequest $request): void
    {
        $this->log('leave.'.$request->status, $request, array_filter([$request->employee?->email]));
    }

    /**
     * @param array<int, string> $recipients
     */
    private function log(string $action, LeaveRequest $request, array $recipients): void
    {
        // ponytail: local notification log; swap for Graph when production credentials are ready.
        Log::info($action, [
            'leave_request_id' => $request->id,
            'recipients' => $recipients,
        ]);

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => LeaveRequest::class,
            'auditable_id' => $request->id,
            'metadata' => ['recipients' => $recipients],
        ]);
    }
}
