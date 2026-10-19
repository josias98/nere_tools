<?php

namespace App\Http\Controllers\Signatures;

use App\Exceptions\PdfSignatureException;
use App\Http\Controllers\Controller;
use App\Models\SignatureDocument;
use App\Services\Signatures\PdfSigningService;
use App\Services\Signatures\SigningCertificate;
use Illuminate\Http\Request;
use SignerPHP\PdfSigner\Domain\Exception\SignerException;

class SignatureDocumentController extends Controller
{
    public function index(Request $request, SigningCertificate $certificates)
    {
        return view('signatures.index', [
            'documents' => SignatureDocument::query()->where('user_id', $request->user()->id)->latest('id')->paginate(12),
            'certificate' => $certificates->status(),
        ]);
    }

    public function store(Request $request, PdfSigningService $signing)
    {
        $request->validate(['pdf' => ['required', 'file', 'mimes:pdf', 'extensions:pdf', 'max:'.config('pdf_signatures.max_upload_kb')]], [
            'pdf.required' => 'Sélectionnez le PDF à signer.',
            'pdf.mimes' => 'Le fichier doit être un PDF.', 'pdf.extensions' => 'Le fichier doit porter l’extension .pdf.',
            'pdf.max' => 'Le PDF ne doit pas dépasser 10 Mo.',
        ]);
        try {
            $document = $signing->import($request->file('pdf'), $request->user());
        } catch (PdfSignatureException $exception) {
            return back()->withErrors(['pdf' => $exception->getMessage()]);
        }

        return redirect()->route('signatures.show', $document->uuid);
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

        return view('signatures.show', [
            'document' => $document, 'certificate' => $certificates->status(), 'integrityError' => $integrityError,
        ]);
    }

    public function sign(Request $request, SignatureDocument $document, PdfSigningService $signing)
    {
        $this->authorizeOwner($request, $document);
        $data = $request->validate([
            'consent' => ['accepted'], 'original_sha256' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'certificate_sha256' => ['required', 'regex:/\A[a-f0-9]{64}\z/'], 'reason' => ['required', 'string', 'max:500'],
        ], ['consent.accepted' => 'Confirmez votre accord avant de signer.', 'reason.required' => 'Précisez le motif de votre signature.']);
        try {
            $signing->sign($document, $request->user(), $data['original_sha256'], $data['certificate_sha256'], trim($data['reason']), [
                'ip' => $request->ip(), 'user_agent' => $request->userAgent(),
            ]);
        } catch (SignerException) {
            return back()->withErrors(['signature' => 'La signature a échoué. Le PDF original est conservé. Contactez un administrateur.'])->withInput();
        } catch (PdfSignatureException $exception) {
            return back()->withErrors(['signature' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('signatures.show', $document->uuid)->with('success', 'Le PDF a été signé. Vous pouvez le télécharger.');
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
