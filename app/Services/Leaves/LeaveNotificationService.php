<?php

namespace App\Services\Leaves;

use App\Jobs\SendGraphMailJob;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class LeaveNotificationService
{
    public function __construct(private LeaveValidatorService $validators)
    {
    }

    public function requestSubmitted(LeaveRequest $request): void
    {
        $recipients = $this->validators->validatorsFor($request)
            ->where('notify_by_email', true)
            ->pluck('employee.email')
            ->filter()
            ->values()
            ->all();

        $this->queue(
            action: 'leave.submitted',
            request: $request,
            recipients: $recipients,
            subject: 'Nouvelle demande de conge a valider',
            html: view('emails.conges.request-submitted', [
                'leaveRequest' => $request,
                'actionUrl' => route('leaves.validations.show', $request->uuid),
            ])->render(),
            text: "Une demande de conge a ete soumise par {$request->employee?->name()}. Voir la demande: ".route('leaves.validations.show', $request->uuid),
        );
    }

    public function requestDecided(LeaveRequest $request): void
    {
        $status = $request->status === 'approved' ? 'approved' : 'rejected';

        $this->queue(
            action: 'leave.'.$request->status,
            request: $request,
            recipients: array_values(array_filter([$request->employee?->email])),
            subject: $status === 'approved'
                ? 'Votre demande de conge a ete approuvee'
                : 'Votre demande de conge a ete rejetee',
            html: view("emails.conges.request-{$status}", [
                'leaveRequest' => $request,
                'actionUrl' => route('leaves.show', $request->uuid),
            ])->render(),
            text: "Votre demande de conge est maintenant {$request->status}. Voir la demande: ".route('leaves.show', $request->uuid),
        );
    }

    /**
     * @param array<int, string> $recipients
     */
    private function queue(
        string $action,
        LeaveRequest $request,
        array $recipients,
        string $subject,
        string $html,
        string $text,
    ): void {
        if ($recipients === []) {
            $this->logNotification($action, $request, $subject, $recipients, 'skipped');
        } elseif (! config('services.graph_mail.enabled')) {
            $this->logNotification($action, $request, $subject, $recipients, 'skipped');
        } else {
            $notification = $this->logNotification($action, $request, $subject, $recipients, 'queued');

            app()->environment('local')
                ? SendGraphMailJob::dispatchAfterResponse(
                    to: $recipients,
                    subject: $subject,
                    html: $html,
                    cc: [],
                    bcc: [],
                    text: $text,
                    event: $action,
                    relatedType: LeaveRequest::class,
                    relatedId: $request->id,
                    notificationLogId: $notification->id,
                )
                : SendGraphMailJob::dispatch(
                to: $recipients,
                subject: $subject,
                html: $html,
                cc: [],
                bcc: [],
                text: $text,
                event: $action,
                relatedType: LeaveRequest::class,
                relatedId: $request->id,
                notificationLogId: $notification->id,
            );
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

    /**
     * @param array<int, string> $recipients
     */
    private function logNotification(
        string $action,
        LeaveRequest $request,
        string $subject,
        array $recipients,
        string $status,
    ): NotificationLog {
        return NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => $action,
            'related_type' => LeaveRequest::class,
            'related_id' => $request->id,
            'to_recipients' => array_values($recipients),
            'subject' => $subject,
            'status' => $status,
            'queued_at' => in_array($status, ['queued', 'skipped'], true) ? now() : null,
        ]);
    }
}
