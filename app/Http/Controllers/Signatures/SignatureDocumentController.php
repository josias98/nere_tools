<?php

namespace App\Http\Controllers\Signatures;

use App\Exceptions\PdfSignatureException;
use App\Http\Controllers\Controller;
use App\Models\SignatureDocument;
use App\Services\Signatures\PdfSigningService;
use App\Services\Signatures\SigningCertificate;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use SignerPHP\PdfSigner\Domain\Exception\SignerException;
use Throwable;

class SignatureDocumentController extends Controller
{
    public function index(Request $request, SigningCertificate $certificates)
    {
        return response()->view('signatures.index', [
            'documents' => SignatureDocument::query()->where('user_id', $request->user()->id)->latest('id')->paginate(12),
            'certificate' => $certificates->status(),
        ])->withHeaders(['Cache-Control' => 'private, no-store']);
    }

    public function store(Request $request, PdfSigningService $signing)
    {
        $request->validate(['pdf' => ['required', 'file', 'mimes:pdf', 'extensions:pdf', 'max:'.config('pdf_signatures.max_upload_kb')]], [
            'pdf.required' => 'Sélectionnez le PDF à signer.',
            'pdf.mimes' => 'Le fichier doit être un PDF.', 'pdf.extensions' => 'Le fichier doit porter l’extension .pdf.',
            'pdf.max' => 'Ce PDF dépasse 10 Mo. Exportez une version plus légère, puis réessayez.',
            'pdf.uploaded' => 'Le transfert du PDF a été interrompu. Sélectionnez à nouveau le fichier et réessayez.',
        ]);
        try {
            $document = $signing->import($request->file('pdf'), $request->user());
        } catch (PdfSignatureException $exception) {
            throw ValidationException::withMessages(['pdf' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            if (! $request->expectsJson()) {
                throw $exception;
            }
            report($exception);

            return response()->json(['message' => 'L’import est momentanément indisponible. Votre fichier reste sélectionné ; réessayez dans un instant.'], 500);
        }

        $request->session()->flash('success', 'Votre PDF est prêt. Relisez-le avant de donner votre accord.');

        return $request->expectsJson()
            ? response()->json(['redirect_url' => route('signatures.show', $document->uuid)], 201)
            : redirect()->route('signatures.show', $document->uuid);
    }

    public function show(Request $request, SignatureDocument $document, SigningCertificate $certificates, PdfSigningService $signing)
    {
        $this->authorizeOwner($request, $document);
        $integrityError = null;
        try {
            $document->signed_at ? $signing->signed($document) : $signing->original($document);
        } catch (PdfSignatureException $exception) {
            $integrityError = $exception->getMessage();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'signed' => $document->signed_at !== null,
                'integrity_ok' => $integrityError === null,
                'redirect_url' => route('signatures.show', $document->uuid),
            ])->withHeaders(['Cache-Control' => 'private, no-store']);
        }

        return response()->view('signatures.show', [
            'document' => $document, 'certificate' => $certificates->status(), 'integrityError' => $integrityError,
        ])->withHeaders(['Cache-Control' => 'private, no-store']);
    }

    public function sign(Request $request, SignatureDocument $document, PdfSigningService $signing)
    {
        $this->authorizeOwner($request, $document);
        $data = $request->validate([
            'consent' => ['accepted'], 'original_sha256' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'certificate_sha256' => ['required', 'regex:/\A[a-f0-9]{64}\z/'], 'reason' => ['required', 'string', 'max:500'],
        ], [
            'consent.accepted' => 'Cochez la confirmation pour autoriser la signature de ce PDF.',
            'reason.required' => 'Indiquez le motif de votre signature, par exemple « Bon pour accord ».',
            'reason.max' => 'Le motif est trop long. Limitez-le à 500 caractères.',
            'original_sha256.*' => 'La relecture du document a expiré. Actualisez la page avant de signer.',
            'certificate_sha256.*' => 'Le certificat doit être vérifié à nouveau. Actualisez la page avant de signer.',
        ]);
        try {
            $signing->sign($document, $request->user(), $data['original_sha256'], $data['certificate_sha256'], trim($data['reason']), [
                'ip' => $request->ip(), 'user_agent' => $request->userAgent(),
            ]);
        } catch (SignerException) {
            throw ValidationException::withMessages(['signature' => 'La signature n’a pas abouti. Votre PDF original est conservé. Réessayez ; si cela persiste, contactez un administrateur.']);
        } catch (PdfSignatureException $exception) {
            throw ValidationException::withMessages(['signature' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            if (! $request->expectsJson()) {
                throw $exception;
            }
            report($exception);

            return response()->json(['message' => 'Le résultat de la signature ne peut pas être confirmé. Vérifiez l’état du document avant de réessayer.'], 500);
        }

        $request->session()->flash('success', 'Votre PDF est signé. Il est prêt à être téléchargé.');

        return $request->expectsJson()
            ? response()->json(['redirect_url' => route('signatures.show', $document->uuid)])
            : redirect()->route('signatures.show', $document->uuid);
    }

    public function file(Request $request, SignatureDocument $document, string $version, PdfSigningService $signing)
    {
        $this->authorizeOwner($request, $document);
        abort_unless(in_array($version, ['original', 'signe'], true), 404);
        abort_if($version === 'signe' && ! $document->signed_at, 404);
        try {
            $pdf = $version === 'signe' ? $signing->signed($document) : $signing->original($document);
        } catch (PdfSignatureException $exception) {
            abort(409, $exception->getMessage());
        }
        $name = $version === 'signe' ? $document->signedFileName() : $document->file_name;

        return response()->streamDownload(fn () => print ($pdf), $name, [
            'Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function evidence(Request $request, SignatureDocument $document, PdfSigningService $signing)
    {
        $this->authorizeOwner($request, $document);
        abort_unless($document->signed_at, 404);
        try {
            $signing->signed($document);
        } catch (PdfSignatureException $exception) {
            abort(409, $exception->getMessage());
        }
        $evidence = [
            'document_id' => $document->uuid, 'file_name' => $document->file_name,
            'signer_name' => $document->signer_name, 'signer_email' => $document->signer_email,
            'signed_at' => $document->signed_at->toIso8601String(), 'time_source' => 'server',
            'reason' => $document->reason, 'consent_version' => $document->consent_version,
            'mode' => $document->signature_mode, 'profile' => 'PAdES Baseline B',
            'original_sha256' => $document->original_sha256, 'signed_sha256' => $document->signed_sha256,
            'certificate_subject' => $document->certificate_subject, 'certificate_issuer' => $document->certificate_issuer,
            'certificate_sha256' => $document->certificate_sha256,
            'certificate_valid_until' => $document->certificate_valid_until?->toDateString(),
            'verification' => 'Cryptographic integrity verified; certificate trust and revocation not checked.',
        ];

        return response()->streamDownload(fn () => print (json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
            'signature-'.$document->uuid.'.json', ['Content-Type' => 'application/json', 'Cache-Control' => 'private, no-store']);
    }

    private function authorizeOwner(Request $request, SignatureDocument $document): void
    {
        abort_unless($document->user_id === $request->user()->id, 403);
    }
}
