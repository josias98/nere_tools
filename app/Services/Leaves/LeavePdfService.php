<?php

namespace App\Services\Leaves;

use App\Models\AuditLog;
use App\Models\LeaveDocument;
use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeavePdfService
{
    public function __construct(
        private LeaveDocumentReferenceService $references,
        private LeavePdfRenderer $renderer,
    ) {}

    public function generate(LeaveRequest $request, ?User $actor = null): LeaveDocument
    {
        return $this->buildDocument($request, $actor, false);
    }

    public function regenerate(LeaveRequest $request, User $actor, ?string $reason = null): LeaveDocument
    {
        return $this->buildDocument($request, $actor, true, $reason);
    }

    public function revoke(LeaveDocument $document, User $actor, ?string $reason = null): LeaveDocument
    {
        if ($document->status !== LeaveDocument::STATUS_ACTIVE) {
            throw new \RuntimeException('Seul un document actif peut etre revoque.');
        }

        $document->forceFill([
            'status' => LeaveDocument::STATUS_REVOKED,
            'status_reason' => $reason,
        ])->save();

        $this->audit('leave_document.revoked', $document, $actor->id, [
            'reason' => $reason,
        ]);

        return $document->refresh();
    }

    private function buildDocument(
        LeaveRequest $request,
        ?User $actor,
        bool $replaceExisting,
        ?string $reason = null,
    ): LeaveDocument {
        $request->loadMissing(['employee.department', 'leaveType', 'reviewer.employee', 'documents']);

        if ($request->status !== 'approved') {
            throw new \RuntimeException('Le PDF final ne peut etre genere que pour une demande approuvee.');
        }

        $existing = $request->document()->first();

        if ($existing && ! $replaceExisting) {
            return $existing;
        }

        $attempt = 0;

        while ($attempt < 3) {
            $attempt++;

            try {
                return DB::transaction(function () use ($request, $actor, $replaceExisting, $reason): LeaveDocument {
                    $request = LeaveRequest::query()
                        ->with(['employee.department', 'leaveType', 'reviewer.employee'])
                        ->lockForUpdate()
                        ->findOrFail($request->id);

                    $existing = $request->document()->first();

                    if ($existing && ! $replaceExisting) {
                        return $existing;
                    }

                    $referenceYear = (int) ($request->reviewed_at?->format('Y') ?? now()->format('Y'));
                    $reference = $this->references->next($referenceYear);
                    $token = $this->uniqueToken();
                    $verificationUrl = route('leaves.verify.show', $token);
                    $actorId = $actor?->id ?? $request->reviewed_by_user_id;
                    $signedBy = $request->reviewed_by_user_id ?? $actorId;
                    $signedAt = $request->reviewed_at ?? now();
                    $fileName = $reference.'.pdf';
                    $filePath = 'leave-documents/'.$referenceYear.'/'.$fileName;

                    [$signatoryName, $signatoryTitle] = $this->signatory($request);

                    $output = $this->renderer->render($request, [
                        'document_reference' => $reference,
                        'verification_url' => $verificationUrl,
                        'verification_path' => '/conges/verify/'.$token,
                        'signed_by_label' => $signatoryName,
                        'signed_by_role' => $signatoryTitle,
                    ]);
                    $sha256 = hash('sha256', $output);

                    Storage::disk('local')->put($filePath, $output);

                    $document = LeaveDocument::query()->create([
                        'leave_request_id' => $request->id,
                        'document_reference' => $reference,
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'local_path' => $filePath,
                        'disk' => 'local',
                        'sha256_hash' => $sha256,
                        'verification_token' => $token,
                        'verification_url' => $verificationUrl,
                        'status' => LeaveDocument::STATUS_ACTIVE,
                        'generated_at' => now(),
                        'generated_by' => $actorId,
                        'signed_by' => $signedBy,
                        'signed_at' => $signedAt,
                    ]);

                    $this->audit('leave_document.generated', $document, $actorId, [
                        'leave_request_id' => $request->id,
                        'sha256_hash' => $sha256,
                    ]);

                    if ($existing && $replaceExisting) {
                        $existing->forceFill([
                            'status' => LeaveDocument::STATUS_REPLACED,
                            'status_reason' => $reason,
                            'replaced_by_document_id' => $document->id,
                        ])->save();

                        $this->audit('leave_document.replaced', $existing, $actorId, [
                            'reason' => $reason,
                            'replaced_by_document_id' => $document->id,
                        ]);

                        $this->audit('leave_document.regenerated', $document, $actorId, [
                            'reason' => $reason,
                            'replaces_document_id' => $existing->id,
                        ]);
                    }

                    return $document->fresh(['leaveRequest', 'signedBy', 'generatedBy']);
                });
            } catch (QueryException $exception) {
                if ($attempt < 3 && $this->isDuplicateKey($exception)) {
                    continue;
                }

                throw $exception;
            }
        }

        throw new \RuntimeException('Impossible de generer le document de conge.');
    }

    /**
     * @return array<int, array{label: string, checked: bool, note: ?string}>
     */
    private function typeChoices(LeaveRequest $request): array
    {
        $label = Str::lower((string) ($request->leaveType?->name ?? ''));

        $choices = [
            ['label' => 'Congé administratif', 'match' => ['administratif', 'annuel']],
            ['label' => 'Congé de maternité', 'match' => ['maternite']],
            ['label' => 'Congé de paternité', 'match' => ['paternite']],
            ['label' => 'Décès (préciser le lien de parenté)', 'match' => ['deces']],
            ['label' => 'Congé de maladie', 'match' => ['maladie']],
            ['label' => 'Accident de travail', 'match' => ['accident']],
            ['label' => 'Autre (motif à préciser)', 'match' => []],
        ];

        $matched = false;

        return array_map(function (array $choice) use ($label, $request, &$matched): array {
            $checked = false;
            $note = null;

            foreach ($choice['match'] as $needle) {
                if (Str::contains($label, $needle)) {
                    $checked = true;
                    $matched = true;
                    break;
                }
            }

            if ($choice['label'] === 'Autre (motif à préciser)' && ! $matched) {
                $checked = true;
                $note = $request->leaveType?->name;
            }

            return [
                'label' => $choice['label'],
                'checked' => $checked,
                'note' => $note,
            ];
        }, $choices);
    }

    private function uniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (LeaveDocument::query()->where('verification_token', $token)->exists());

        return $token;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function signatory(LeaveRequest $request): array
    {
        return [
            $this->setting('LEAVE_CERTIFICATE_SIGNATORY_NAME', config('leaves.certificate_signatory_name')),
            $this->setting('LEAVE_CERTIFICATE_SIGNATORY_TITLE', config('leaves.certificate_signatory_title')),
        ];
    }

    private function setting(string $key, ?string $fallback): string
    {
        return (string) (LeaveSetting::query()->where('key', $key)->value('value') ?: $fallback);
    }

    private function roleLabel(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return match ($user->role) {
            User::ROLE_ADMIN => 'Super admin',
            User::ROLE_FINANCE => 'Finance',
            User::ROLE_DIRECTION => 'Direction',
            User::ROLE_MANAGER => 'Manager',
            default => 'Validateur',
        };
    }

    private function audit(string $action, LeaveDocument $document, ?int $userId, array $metadata = []): void
    {
        AuditLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => LeaveDocument::class,
            'auditable_id' => $document->id,
            'metadata' => array_merge([
                'leave_request_id' => $document->leave_request_id,
                'document_reference' => $document->document_reference,
                'status' => $document->status,
            ], $metadata),
        ]);
    }

    private function isDuplicateKey(QueryException $exception): bool
    {
        $message = Str::lower($exception->getMessage());

        return Str::contains($message, ['unique', 'duplicate', 'integrity constraint']);
    }
}
