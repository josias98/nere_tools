<?php

namespace App\Services\Signatures;

use App\Exceptions\PdfSignatureException;
use App\Models\AuditLog;
use App\Models\SignatureDocument;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PdfSigningService
{
    public const CONSENT_VERSION = 'nere-pdf-v1';

    public function __construct(private PdfSignatureEngine $engine, private SigningCertificate $certificates) {}

    public function import(UploadedFile $file, User $user): SignatureDocument
    {
        $pdf = $file->getContent();
        $pages = $this->engine->inspect($pdf);
        $uuid = (string) Str::uuid();
        $path = 'signatures/'.$uuid.'/original.pdf';
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = Str::limit(preg_replace('/[^\pL\pN ._()\-]/u', '', $name) ?: 'document.pdf', 180, '');
        if (! str_ends_with(strtolower($name), '.pdf')) {
            $name .= '.pdf';
        }

        try {
            if (! Storage::disk('local')->put($path, $pdf, 'private')) {
                throw new PdfSignatureException('Le PDF n’a pas pu être enregistré. Réessayez.');
            }

            return DB::transaction(function () use ($uuid, $user, $name, $path, $pdf, $pages): SignatureDocument {
                $document = SignatureDocument::query()->create([
                    'uuid' => $uuid, 'user_id' => $user->id, 'file_name' => $name,
                    'original_path' => $path, 'original_sha256' => hash('sha256', $pdf),
                    'original_size' => strlen($pdf), 'page_count' => $pages,
                ]);
                $this->audit($document, $user, 'pdf_signature.imported', ['original_sha256' => $document->original_sha256]);

                return $document;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    public function sign(SignatureDocument $document, User $user, string $hash, string $certificateHash, string $reason, array $context): SignatureDocument
    {
        $path = null;
        try {
            return DB::transaction(function () use ($document, $user, $hash, $certificateHash, $reason, $context, &$path): SignatureDocument {
                $document = SignatureDocument::query()->lockForUpdate()->findOrFail($document->id);
                if ($document->user_id !== $user->id) {
                    throw new AuthorizationException;
                }
                if ($document->signed_at) {
                    return $document;
                }
                $pdf = $this->original($document);
                if (! hash_equals($document->original_sha256, $hash)) {
                    throw new PdfSignatureException('Le document a changé. Rechargez la page et relisez le PDF avant de signer.');
                }
                $certificate = $this->certificates->load();
                if (! hash_equals($certificate['fingerprint'], $certificateHash)) {
                    throw new PdfSignatureException('Le certificat a changé. Rechargez la page pour vérifier le certificat utilisé.');
                }
                $signed = $this->engine->sign($pdf, $certificate, $user->name, $user->email, $reason);
                $path = 'signatures/'.$document->uuid.'/'.Str::uuid().'-signe.pdf';
                if (! Storage::disk('local')->put($path, $signed, 'private')) {
                    throw new PdfSignatureException('Le PDF signé n’a pas pu être enregistré. L’original reste disponible.');
                }
                $document->forceFill([
                    'signed_path' => $path, 'signed_sha256' => hash('sha256', $signed),
                    'signer_name' => $user->name, 'signer_email' => $user->email,
                    'signature_mode' => $certificate['mode'], 'certificate_subject' => $certificate['subject'],
                    'certificate_issuer' => $certificate['issuer'], 'certificate_sha256' => $certificate['fingerprint'],
                    'certificate_valid_until' => $certificate['valid_until'], 'reason' => $reason,
                    'consent_version' => self::CONSENT_VERSION, 'signed_at' => now(),
                ])->save();
                $this->audit($document, $user, 'pdf_signature.signed', [
                    'original_sha256' => $document->original_sha256, 'signed_sha256' => $document->signed_sha256,
                    'certificate_sha256' => $document->certificate_sha256, 'mode' => $document->signature_mode,
                    'signer_name' => $document->signer_name, 'signer_email' => $document->signer_email,
                    'consent_version' => self::CONSENT_VERSION, 'reason' => $reason,
                    'ip' => $context['ip'] ?? null, 'user_agent' => Str::limit($context['user_agent'] ?? '', 500),
                ]);

                return $document;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function original(SignatureDocument $document): string
    {
        $pdf = Storage::disk('local')->get($document->original_path);
        if (! $pdf || ! hash_equals($document->original_sha256, hash('sha256', $pdf))) {
            throw new PdfSignatureException('Le fichier original est indisponible ou a été modifié. La signature est bloquée.');
        }

        return $pdf;
    }

    public function signed(SignatureDocument $document): string
    {
        $pdf = $document->signed_path ? Storage::disk('local')->get($document->signed_path) : null;
        if (! $pdf || ! $document->signed_sha256 || ! hash_equals($document->signed_sha256, hash('sha256', $pdf))
            || ! $this->engine->verify($pdf, $document->certificate_sha256)) {
            throw new PdfSignatureException('Le PDF signé est indisponible ou son intégrité ne peut pas être confirmée. Contactez un administrateur.');
        }

        return $pdf;
    }

    private function audit(SignatureDocument $document, User $user, string $action, array $metadata): void
    {
        AuditLog::query()->create([
            'user_id' => $user->id, 'action' => $action, 'auditable_type' => SignatureDocument::class,
            'auditable_id' => $document->id, 'metadata' => $metadata,
        ]);
    }
}
