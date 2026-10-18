<?php

namespace App\Http\Controllers\PdfCraft;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PdfCraftFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PdfCraftController extends Controller
{
    public function session(Request $request): JsonResponse
    {
        return response()->json([
            'name' => $request->user()->name,
            'csrfToken' => csrf_token(),
            'historyUrl' => route('pdfcraft.history', absolute: false),
            'dashboardUrl' => route('dashboard', absolute: false),
        ])->header('Cache-Control', 'no-store');
    }

    public function history(Request $request): View
    {
        return view('pdfcraft.history', [
            'files' => PdfCraftFile::query()
                ->where('user_id', $request->user()->id)
                ->where('expires_at', '>', now())
                ->latest()
                ->paginate(30),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.config('pdfcraft.max_file_kib')],
            'operation_id' => ['required', 'uuid'],
            'tool_slug' => ['required', 'string', 'max:100', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'],
            'kind' => ['required', 'in:source,result'],
        ]);

        $uploaded = $data['file'];
        $uuid = (string) Str::uuid();
        $path = 'pdfcraft/'.$request->user()->id.'/'.now()->format('Y/m').'/'.$uuid;
        $stored = Storage::disk('local')->putFileAs(dirname($path), $uploaded, basename($path));

        if ($stored === false) {
            throw new RuntimeException('Le stockage du fichier PDFCraft a échoué.');
        }

        try {
            $file = PdfCraftFile::query()->create([
                'uuid' => $uuid,
                'user_id' => $request->user()->id,
                'operation_id' => $data['operation_id'],
                'tool_slug' => $data['tool_slug'],
                'kind' => $data['kind'],
                'original_name' => Str::limit(basename(str_replace('\\', '/', $uploaded->getClientOriginalName())), 255, ''),
                'mime_type' => $uploaded->getMimeType(),
                'size_bytes' => $uploaded->getSize(),
                'sha256' => hash_file('sha256', $uploaded->getRealPath()),
                'file_path' => $stored,
                'expires_at' => now()->addDays(config('pdfcraft.retention_days')),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($stored);
            throw $exception;
        }

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => 'pdfcraft.file_saved',
            'auditable_type' => PdfCraftFile::class,
            'auditable_id' => $file->id,
            'metadata' => ['kind' => $file->kind, 'tool' => $file->tool_slug, 'size_bytes' => $file->size_bytes],
        ]);

        return response()->json(['id' => $file->uuid, 'expiresAt' => $file->expires_at->toIso8601String()], 201);
    }

    public function download(Request $request, PdfCraftFile $pdfCraftFile): StreamedResponse
    {
        abort_unless($pdfCraftFile->user_id === $request->user()->id, 403);
        abort_if($pdfCraftFile->expires_at->isPast(), 404);
        abort_unless(Storage::disk('local')->exists($pdfCraftFile->file_path), 404);

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => 'pdfcraft.file_downloaded',
            'auditable_type' => PdfCraftFile::class,
            'auditable_id' => $pdfCraftFile->id,
        ]);

        return Storage::disk('local')->download($pdfCraftFile->file_path, $pdfCraftFile->original_name);
    }

    public function page(?string $path = null): BinaryFileResponse
    {
        $path = trim($path ?? '', '/');
        abort_unless($path === '' || preg_match('/\A[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*\z/D', $path), 404);

        $file = rtrim(config('pdfcraft.pages_path'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.($path === '' ? 'index.html' : str_replace('/', DIRECTORY_SEPARATOR, $path).DIRECTORY_SEPARATOR.'index.html');
        abort_unless(is_file($file), 404);

        return response()->file($file, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Resource-Policy' => 'same-origin',
        ]);
    }
}
