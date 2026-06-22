<?php

namespace App\Services\Leaves;

use App\Models\AuditLog;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        $this->send(
            'leave.submitted',
            $request,
            $recipients,
            'Nouvelle demande de congé à valider',
            "Une demande de congé a été soumise par {$request->employee?->name()}.\n\n".
            'Voir la demande: '.route('leaves.validations.show', $request->uuid),
        );
    }

    public function requestDecided(LeaveRequest $request): void
    {
        $this->send(
            'leave.'.$request->status,
            $request,
            array_filter([$request->employee?->email]),
            'Votre demande de congé a été traitée',
            "Votre demande de congé est maintenant: {$request->status}.\n\n".
            'Voir la demande: '.route('leaves.show', $request->uuid),
        );
    }

    /**
     * @param array<int, string> $recipients
     */
    private function send(string $action, LeaveRequest $request, array $recipients, string $subject, string $body): void
    {
        if ($recipients !== []) {
            Mail::raw($body, fn ($message) => $message->to($recipients)->subject($subject));
        }

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
