<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaveDocument;
use App\Services\Leaves\LeaveValidatorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LeaveDocumentVerificationController extends Controller
{
    public function index(): View
    {
        return $this->page();
    }

    public function show(Request $request, string $token, LeaveValidatorService $validators): View
    {
        $document = LeaveDocument::query()
            ->with(['leaveRequest.employee.department', 'leaveRequest.leaveType', 'signedBy.employee'])
            ->where('verification_token', $token)
            ->first();

        $result = $document
            ? $this->tokenResult($document, $request, $validators)
            : [
                'status' => 'unknown_token',
                'title' => 'Document introuvable',
                'message' => "Aucun document correspondant n'a ete trouve dans Nere Tools.",
            ];

        $this->audit(
            action: 'leave_document.verified_by_token',
            request: $request,
            document: $document,
            result: $result['status'],
        );

        return $this->page(document: $document, tokenResult: $result, validators: $validators, request: $request);
    }

    public function upload(Request $request, LeaveValidatorService $validators): View|Response
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimetypes:application/pdf', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->view('leaves.verify', $this->pageData(uploadResult: [
                'status' => 'invalid_format',
                'title' => 'Fichier invalide',
                'message' => "Le fichier transmis n'est pas un PDF valide ou depasse la taille autorisee.",
            ]) + [
                'uploadErrors' => $validator->errors(),
            ], 422);
        }

        $uploaded = $request->file('file');
        $path = $uploaded?->getRealPath();
        $document = null;
        $hash = null;

        try {
            $hash = $path ? hash_file('sha256', $path) : null;
            $document = $hash
                ? LeaveDocument::query()
                    ->with(['leaveRequest.employee.department', 'leaveRequest.leaveType', 'signedBy.employee'])
                    ->where('sha256_hash', $hash)
                    ->first()
                : null;

            $result = $document
                ? $this->uploadResultFromDocument($document)
                : [
                    'status' => 'unknown',
                    'title' => 'Correspondance inconnue',
                    'message' => 'Ce fichier ne correspond a aucun document actif enregistre dans Nere Tools. Il peut etre inconnu, modifie, recompresse ou altere.',
                ];

            $this->audit(
                action: 'leave_document.verified_by_upload',
                request: $request,
                document: $document,
                result: $result['status'],
                extra: ['sha256_hash' => $hash],
            );

            return $this->page(
                document: $document,
                uploadResult: $result,
                validators: $validators,
                request: $request,
            );
        } finally {
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function page(
        ?LeaveDocument $document = null,
        ?array $tokenResult = null,
        ?array $uploadResult = null,
        ?LeaveValidatorService $validators = null,
        ?Request $request = null,
    ): View {
        return view('leaves.verify', $this->pageData($document, $tokenResult, $uploadResult, $validators, $request));
    }

    /**
     * @return array{status: string, title: string, message: string}
     */
    private function tokenResult(LeaveDocument $document, Request $request, LeaveValidatorService $validators): array
    {
        $canSeePrivateDetails = $request->user() && $validators->userCanAccessDocument($request->user(), $document->leaveRequest);

        return match ($document->status) {
            LeaveDocument::STATUS_ACTIVE => [
                'status' => 'active',
                'title' => 'Document authentique',
                'message' => 'Ce document correspond a une demande de conge authentique, generee et validee via Nere Tools.'.($canSeePrivateDetails ? '' : ''),
            ],
            LeaveDocument::STATUS_REPLACED => [
                'status' => 'replaced',
                'title' => 'Version remplacee',
                'message' => 'Ce document correspond a une version anterieure remplacee. Veuillez vous referer a la derniere version disponible dans Nere Tools.',
            ],
            LeaveDocument::STATUS_REVOKED => [
                'status' => 'revoked',
                'title' => 'Document revoque',
                'message' => 'Ce document a bien ete genere par Nere Tools, mais il a ete revoque. Il ne doit plus etre considere comme actif.',
            ],
            default => [
                'status' => 'unknown_token',
                'title' => 'Statut inconnu',
                'message' => "Aucun document correspondant n'a ete trouve dans Nere Tools.",
            ],
        };
    }

    /**
     * @return array{status: string, title: string, message: string}
     */
    private function uploadResultFromDocument(LeaveDocument $document): array
    {
        return match ($document->status) {
            LeaveDocument::STATUS_ACTIVE => [
                'status' => 'valid_active',
                'title' => 'Correspondance exacte',
                'message' => 'Ce fichier correspond exactement a un document actif genere par Nere Tools.',
            ],
            LeaveDocument::STATUS_REPLACED => [
                'status' => 'valid_replaced',
                'title' => 'Ancienne version reconnue',
                'message' => "Ce fichier correspond a une ancienne version d'un document genere par Nere Tools. Cette version a ete remplacee.",
            ],
            LeaveDocument::STATUS_REVOKED => [
                'status' => 'valid_revoked',
                'title' => 'Document revoque reconnu',
                'message' => 'Ce fichier correspond a un document genere par Nere Tools, mais il a ete revoque.',
            ],
            default => [
                'status' => 'unknown',
                'title' => 'Correspondance inconnue',
                'message' => 'Ce fichier ne correspond a aucun document actif enregistre dans Nere Tools. Il peut etre inconnu, modifie, recompresse ou altere.',
            ],
        };
    }

    private function audit(
        string $action,
        Request $request,
        ?LeaveDocument $document,
        string $result,
        array $extra = [],
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => LeaveDocument::class,
            'auditable_id' => $document?->id,
            'metadata' => array_merge([
                'document_reference' => $document?->document_reference,
                'result' => $result,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], $extra),
        ]);
    }

    private function pageData(
        ?LeaveDocument $document = null,
        ?array $tokenResult = null,
        ?array $uploadResult = null,
        ?LeaveValidatorService $validators = null,
        ?Request $request = null,
    ): array {
        $canSeePrivateDetails = false;

        if ($document && $validators && $request?->user()) {
            $canSeePrivateDetails = $validators->userCanAccessDocument($request->user(), $document->leaveRequest);
        }

        return [
            'document' => $document,
            'tokenResult' => $tokenResult,
            'uploadResult' => $uploadResult,
            'canSeePrivateDetails' => $canSeePrivateDetails,
            'uploadErrors' => null,
        ];
    }
}
