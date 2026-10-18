<?php

namespace Tests\Feature;

use App\Models\PdfCraftFile;
use App\Models\Tool;
use App\Models\User;
use App\Services\PdfCraft\PdfCraftPruner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PdfCraftIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdfcraft_pages_and_history_require_access(): void
    {
        $this->get('/pdfcraft/history')->assertRedirect();

        $user = User::factory()->create(['is_active' => true, 'role' => User::ROLE_USER]);
        $this->actingAs($user)->get('/pdfcraft/history')->assertOk();

        Tool::query()->where('slug', 'pdfcraft')->update(['status' => Tool::STATUS_DISABLED]);
        $this->actingAs($user)->get('/pdfcraft/history')->assertForbidden();
    }

    public function test_user_can_archive_and_download_only_their_own_file(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['is_active' => true, 'role' => User::ROLE_USER]);
        $other = User::factory()->create(['is_active' => true, 'role' => User::ROLE_USER]);

        $response = $this->actingAs($owner)->post('/pdfcraft/api/files', [
            'file' => UploadedFile::fake()->create('source.pdf', 10, 'application/pdf'),
            'operation_id' => (string) Str::uuid(),
            'tool_slug' => 'merge-pdf',
            'kind' => 'source',
        ])->assertCreated();

        $saved = PdfCraftFile::query()->sole();
        $this->assertSame($response->json('id'), $saved->uuid);
        $this->assertSame($owner->id, $saved->user_id);
        $this->assertSame('source.pdf', $saved->original_name);
        $this->assertTrue($saved->expires_at->between(now()->addDays(89), now()->addDays(91)));
        Storage::disk('local')->assertExists($saved->file_path);

        $this->actingAs($other)->get('/pdfcraft/files/'.$saved->uuid)->assertForbidden();
        $this->actingAs($owner)->get('/pdfcraft/files/'.$saved->uuid)->assertDownload('source.pdf');
    }

    public function test_expired_files_are_removed_from_private_storage_and_history(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['is_active' => true, 'role' => User::ROLE_USER]);
        $path = 'pdfcraft/'.$user->id.'/expired-file';
        Storage::disk('local')->put($path, 'old result');

        $file = PdfCraftFile::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'operation_id' => (string) Str::uuid(),
            'tool_slug' => 'merge-pdf',
            'kind' => 'result',
            'original_name' => 'result.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10,
            'sha256' => hash('sha256', 'old result'),
            'file_path' => $path,
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)->get('/pdfcraft/files/'.$file->uuid)->assertNotFound();
        $this->assertSame(1, app(PdfCraftPruner::class)->prune());
        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('pdfcraft_files', ['id' => $file->id]);
    }
}
