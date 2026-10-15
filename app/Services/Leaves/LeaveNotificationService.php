<?php

namespace App\Services\Leaves;

use App\Jobs\SendGraphMailJob;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\NotificationLog;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Log;

class LeaveNotificationService
{
    public function __construct(private LeaveValidatorService $validators) {}

    public function requestSubmitted(LeaveRequest $request): void
    {
        $this->notifyCurrentStepValidators($request);
    }

    public function requestDecided(LeaveRequest $request): void
    {
        $request->status === 'approved'
            ? $this->notifyFinalApprovalRecipients($request)
            : $this->notifyRejectionRecipients($request);
    }

    /** @return array<int, array{email: string, name: string}> */
    public function currentStepRecipients(LeaveRequest $request): array
    {
        $approval = $request->currentApproval;
        if (! $approval) {
            return [];
        }

        $emails = $this->stepRecipientEmails($request, $approval->step_key);
        $names = User::query()->whereIn('email', $emails)->pluck('name', 'email');
        $employees = Employee::query()->whereIn('email', $emails)->get()->keyBy('email');

        return array_map(fn (string $email): array => [
            'email' => $email,
            'name' => $employees->get($email)?->name() ?? $names->get($email) ?? $email,
        ], $emails);
    }

    public function notifyCurrentStepValidators(LeaveRequest $request, bool $reminder = false, ?string $recipient = null): void
    {
        $request->loadMissing(['employee', 'leaveType']);
        $approval = $request->approvals()
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first();

        if (! $approval) {
            return;
        }

        $recipients = $this->stepRecipientEmails($request, $approval->step_key);
        if ($recipient !== null) {
            if (! in_array($recipient, $recipients, true)) {
                throw new DomainException('Ce signataire ne peut pas être relancé à cette étape.');
            }
            $recipients = [$recipient];
        }

        $this->queue(
            action: 'leave.'.($reminder ? 'reminder_' : '').'pending_'.$approval->step_key,
            request: $request,
            recipients: $recipients,
            subject: ($reminder ? 'Relance - ' : '').'Demande de conge a valider - '.$approval->step_label,
            html: view('emails.conges.request-submitted', [
                'leaveRequest' => $request,
                'approval' => $approval,
                'reminder' => $reminder,
                'actionUrl' => route('leaves.validations.show', $request->uuid),
            ])->render(),
            text: ($reminder ? 'Relance : ' : '')."Une demande de conge attend l'etape {$approval->step_label}. Voir: ".route('leaves.validations.show', $request->uuid),
        );
    }

    public function notifyRejectionRecipients(LeaveRequest $request): void
    {
        $request->loadMissing(['employee', 'leaveType', 'approvals.validatorUser.employee']);
        $approval = $request->approvals->firstWhere('status', 'rejected');
        $recipients = collect([$request->employee?->email])
            ->merge($request->approvals->where('status', 'approved')->pluck('validatorUser.email'))
            ->merge($request->approvals->where('status', 'approved')->pluck('validatorEmployee.email'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->queue(
            action: 'leave.rejected',
            request: $request,
            recipients: $recipients,
            subject: 'Demande de conge rejetee',
            html: view('emails.conges.request-rejected', [
                'leaveRequest' => $request,
                'approval' => $approval,
                'actionUrl' => route('leaves.show', $request->uuid),
            ])->render(),
            text: "Demande rejetee par {$approval?->validatorUser?->name} ({$approval?->step_label}): {$approval?->comment}. Voir: ".route('leaves.show', $request->uuid),
        );
    }

    public function notifyFinalApprovalRecipients(LeaveRequest $request): void
    {
        $request->loadMissing(['employee', 'leaveType', 'document', 'approvals.validatorUser.employee']);
        $recipients = collect([$request->employee?->email])
            ->merge($request->approvals->pluck('validatorUser.email'))
            ->merge($request->approvals->pluck('validatorEmployee.email'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->queue(
            action: 'leave.approved',
            request: $request,
            recipients: $recipients,
            subject: 'Demande de conge approuvee',
            html: view('emails.conges.request-approved', [
                'leaveRequest' => $request,
                'actionUrl' => route('leaves.show', $request->uuid),
                'pdfUrl' => $request->document ? route('leaves.documents.download', $request->document) : null,
            ])->render(),
            text: 'Demande approuvee. PDF: '.($request->document ? route('leaves.documents.download', $request->document) : 'indisponible').'. Voir: '.route('leaves.show', $request->uuid),
        );
    }

    /**
     * @return array<int, string>
     */
    private function stepRecipientEmails(LeaveRequest $request, string $stepKey): array
    {
        $validatorEmails = $this->validators->validatorsForStep($request, $stepKey)
            ->where('notify_by_email', true)
            ->pluck('employee.email');

        $roleEmails = $this->validators->usersForStep($request, $stepKey)->pluck('email');

        return $validatorEmails
            ->merge($roleEmails)
            ->filter()
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $recipients
     */
    private function queue(
        string $action,
        LeaveRequest $request,
        array $recipients,
        string $subject,
        string $html,
        string $text,
    ): void {
        if ($recipients === [] || ! config('services.graph_mail.enabled')) {
            $this->logNotification($action, $request, $subject, $recipients, 'skipped');
        } else {
            $notification = $this->logNotification($action, $request, $subject, $recipients, 'queued');

            SendGraphMailJob::dispatch($recipients, $subject, $html, [], [], $text, $action, LeaveRequest::class, $request->id, $notification->id);
        }

        Log::info($action, ['leave_request_id' => $request->id, 'recipients' => $recipients]);

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => LeaveRequest::class,
            'auditable_id' => $request->id,
            'metadata' => ['recipients' => $recipients],
        ]);
    }

    /**
     * @param  array<int, string>  $recipients
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
