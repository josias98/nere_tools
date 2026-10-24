<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\SignatureDocument;
use App\Models\Tool;
use App\Models\ToolUserAccess;
use App\Models\User;
use App\Services\Signatures\PdfSignatureEngine;
use App\Services\Signatures\PdfSigningService;
use App\Services\Signatures\SigningCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\Support\PdfSigningFixture;
use Tests\TestCase;

class PdfSigningWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create(['is_active' => true, 'name' => 'Zoé Kaboré', 'email' => 'zoe@example.test']);
        Storage::disk('local')->put('certificate.p12', PdfSigningFixture::certificate());
        config([
            'pdf_signatures.mode' => 'test', 'pdf_signatures.password' => 'test-password',
            'pdf_signatures.certificate_path' => Storage::disk('local')->path('certificate.p12'),
        ]);
    }

    private function document(): SignatureDocument
    {
        return app(PdfSigningService::class)->import(UploadedFile::fake()->createWithContent('Contrat.pdf', PdfSigningFixture::pdf()), $this->user);
    }

    private function consent(SignatureDocument $document): array
    {
        return [
            'consent' => '1', 'reason' => 'Bon pour accord', 'original_sha256' => $document->original_sha256,
            'certificate_sha256' => app(SigningCertificate::class)->load()['fingerprint'],
        ];
    }

    public function test_module_is_available_to_active_staff_and_dashboard_links_to_it(): void
    {
        $this->actingAs($this->user)->get(route('dashboard'))->assertOk()->assertSee('Signature PDF');
        $this->get(route('signatures.index'))->assertOk()->assertSee('Environnement de test')->assertSee('Quel PDF souhaitez-vous signer');
        $this->assertTrue($this->user->canAccessTool('signatures'));
    }

    public function test_async_import_and_sign_return_confirmed_document_urls(): void
    {
        $this->actingAs($this->user)->postJson(route('signatures.store'), [
            'pdf' => UploadedFile::fake()->createWithContent('Contrat.pdf', PdfSigningFixture::pdf()),
        ])->assertCreated()->assertJsonPath('redirect_url', route('signatures.show', SignatureDocument::query()->sole()->uuid, false));
        $document = SignatureDocument::query()->sole();
        $this->postJson(route('signatures.sign', $document->uuid), $this->consent($document))->assertOk()
            ->assertExactJson(['redirect_url' => route('signatures.show', $document->uuid, false)]);
        $this->assertNotNull($document->fresh()->signed_at);
    }

    public function test_async_destinations_stay_on_the_browser_origin_behind_an_https_proxy(): void
    {
        URL::forceRootUrl('http://internal.example.test');
        $this->actingAs($this->user)->postJson('/signatures', [
            'pdf' => UploadedFile::fake()->createWithContent('Test.pdf', PdfSigningFixture::pdf()),
        ])->assertCreated()->assertJsonPath('redirect_url', fn ($url) => str_starts_with($url, '/signatures/') && ! str_starts_with($url, '//'));
    }

    public function test_async_validation_reports_fields_without_redirect_or_technical_details(): void
    {
        $this->actingAs($this->user)->postJson(route('signatures.store'), [
            'pdf' => UploadedFile::fake()->createWithContent('invalid.pdf', "%PDF-1.7\ninvalid"),
        ])->assertUnprocessable()->assertJsonValidationErrors('pdf');
        $document = $this->document();
        $this->postJson(route('signatures.sign', $document->uuid), array_replace($this->consent($document), ['consent' => '0', 'reason' => '']))
            ->assertUnprocessable()->assertJsonValidationErrors(['consent', 'reason']);
        $this->assertNull($document->fresh()->signed_at);
    }

    public function test_expired_async_session_returns_unauthorized_and_does_not_start_a_document(): void
    {
        $this->postJson(route('signatures.store'), [])->assertUnauthorized();
        $this->assertDatabaseCount('signature_documents', 0);
    }

    public function test_async_server_failure_is_safe_and_does_not_claim_a_signature_succeeded(): void
    {
        $document = $this->document();
        $signing = Mockery::mock(PdfSigningService::class);
        $signing->shouldReceive('sign')->once()->andThrow(new \RuntimeException('private database connection details'));
        $this->app->instance(PdfSigningService::class, $signing);
        $response = $this->actingAs($this->user)->postJson(route('signatures.sign', $document->uuid), $this->consent($document))
            ->assertStatus(500)->assertJsonStructure(['message']);
        $this->assertStringNotContainsString('private database', $response->getContent());
        $this->assertNull($document->fresh()->signed_at);
    }

    public function test_status_check_can_confirm_a_lost_response_without_signing_again(): void
    {
        $document = $this->document();
        $this->actingAs($this->user)->getJson(route('signatures.show', $document->uuid))->assertOk()
            ->assertJsonPath('signed', false)->assertJsonPath('integrity_ok', true);
        $this->postJson(route('signatures.sign', $document->uuid), $this->consent($document))->assertOk();
        $this->getJson(route('signatures.show', $document->uuid))->assertOk()
            ->assertJsonPath('signed', true)->assertJsonPath('integrity_ok', true);
        $this->assertSame(1, AuditLog::query()->where('action', 'pdf_signature.signed')->count());
        $this->actingAs(User::factory()->create(['is_active' => true]))->getJson(route('signatures.show', $document->uuid))->assertForbidden();
    }

    public function test_guest_inactive_and_explicitly_denied_accounts_cannot_use_the_tool(): void
    {
        $this->get(route('signatures.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['is_active' => false]))->get(route('signatures.index'))->assertForbidden();
        ToolUserAccess::query()->create(['user_id' => $this->user->id, 'tool_id' => Tool::query()->where('slug', 'signatures')->value('id'), 'can_access' => false]);
        $this->actingAs($this->user)->get(route('signatures.index'))->assertForbidden();
    }

    public function test_import_review_consent_signature_and_private_download(): void
    {
        $original = PdfSigningFixture::pdf();
        $response = $this->actingAs($this->user)->post(route('signatures.store'), [
            'pdf' => UploadedFile::fake()->createWithContent('Contrat.pdf', $original),
        ]);
        $document = SignatureDocument::query()->sole();
        $response->assertRedirect(route('signatures.show', $document->uuid));
        $this->get(route('signatures.show', $document->uuid))->assertOk()->assertSee('Confirmer ma signature')
            ->assertSee('Zoé Kaboré')->assertSee('Certificat de test')->assertDontSee('test-password');
        $this->post(route('signatures.sign', $document->uuid), $this->consent($document))->assertSessionHasNoErrors()->assertRedirect();
        $document->refresh();
        $this->assertNotNull($document->signed_at);
        $this->assertSame(PdfSigningService::CONSENT_VERSION, $document->consent_version);
        $this->assertSame('test', $document->signature_mode);
        $this->assertSame($original, Storage::disk('local')->get($document->original_path));
        $this->assertTrue(app(PdfSignatureEngine::class)->verify(Storage::disk('local')->get($document->signed_path), $document->certificate_sha256));
        $this->get(route('signatures.show', $document->uuid))->assertOk()->assertSee('Intégrité cryptographique vérifiée')
            ->assertSee('Télécharger le PDF signé')->assertDontSee('Signer ce PDF');
        $download = $this->get(route('signatures.file', [$document->uuid, 'signe']))->assertOk()->assertDownload('Contrat-signe.pdf');
        $this->assertStringContainsString('no-store', $download->headers->get('Cache-Control'));
        $this->assertSame(Storage::disk('local')->get($document->signed_path), $download->streamedContent());
        $proof = $this->get(route('signatures.evidence', $document->uuid))->assertOk();
        $data = json_decode($proof->streamedContent(), true);
        $this->assertSame($document->signed_sha256, $data['signed_sha256']);
        $this->assertSame('zoe@example.test', $data['signer_email']);
        $this->assertStringNotContainsString('test-password', json_encode($data));
        $audit = AuditLog::query()->where('action', 'pdf_signature.signed')->sole();
        $this->assertSame($this->user->id, $audit->user_id);
        $this->assertSame($document->certificate_sha256, $audit->metadata['certificate_sha256']);
        $this->assertArrayHasKey('ip', $audit->metadata);
    }

    public function test_other_staff_and_admin_cannot_read_or_sign_another_users_document(): void
    {
        $document = $this->document();
        foreach ([User::ROLE_USER, User::ROLE_ADMIN] as $role) {
            $other = User::factory()->create(['role' => $role, 'is_active' => true]);
            $this->actingAs($other)->get(route('signatures.index'))->assertDontSee('Contrat.pdf');
            $this->get(route('signatures.show', $document->uuid))->assertForbidden();
            $this->get(route('signatures.file', [$document->uuid, 'original']))->assertForbidden();
            $this->get(route('signatures.evidence', $document->uuid))->assertForbidden();
            $this->post(route('signatures.sign', $document->uuid), $this->consent($document))->assertForbidden();
        }
    }

    public function test_consent_and_matching_document_and_certificate_are_required(): void
    {
        $document = $this->document();
        $this->actingAs($this->user);
        $payload = $this->consent($document);
        $this->post(route('signatures.sign', $document->uuid), array_replace($payload, ['consent' => '0']))->assertSessionHasErrors('consent');
        $this->post(route('signatures.sign', $document->uuid), array_replace($payload, ['original_sha256' => str_repeat('0', 64)]))->assertSessionHasErrors('signature');
        $this->post(route('signatures.sign', $document->uuid), array_replace($payload, ['certificate_sha256' => str_repeat('0', 64)]))->assertSessionHasErrors('signature');
        $this->assertNull($document->fresh()->signed_at);
    }

    public function test_repeated_submission_does_not_create_another_signature_or_audit(): void
    {
        $document = $this->document();
        $payload = $this->consent($document);
        $this->actingAs($this->user)->post(route('signatures.sign', $document->uuid), $payload)->assertSessionHasNoErrors();
        $path = $document->fresh()->signed_path;
        $this->post(route('signatures.sign', $document->uuid), $payload)->assertSessionHasNoErrors();
        $this->assertSame($path, $document->fresh()->signed_path);
        $this->assertSame(1, AuditLog::query()->where('action', 'pdf_signature.signed')->count());
    }

    public function test_modified_source_blocks_signing(): void
    {
        $document = $this->document();
        Storage::disk('local')->put($document->original_path, str_replace('1500', '9500', PdfSigningFixture::pdf()));
        $this->actingAs($this->user)->post(route('signatures.sign', $document->uuid), $this->consent($document))->assertSessionHasErrors('signature');
        $this->assertNull($document->fresh()->signed_path);
    }

    public function test_modified_signed_file_is_not_downloaded_or_presented_as_valid(): void
    {
        $document = $this->document();
        $this->actingAs($this->user)->post(route('signatures.sign', $document->uuid), $this->consent($document))->assertSessionHasNoErrors();
        $document->refresh();
        Storage::disk('local')->put($document->signed_path, Storage::disk('local')->get($document->signed_path)."\n% tampered");
        $this->get(route('signatures.show', $document->uuid))->assertOk()->assertSee('Ce document demande une vérification')
            ->assertDontSee('Intégrité cryptographique vérifiée')->assertDontSee('data-signature-flash', false);
        $this->get(route('signatures.file', [$document->uuid, 'signe']))->assertStatus(409);
        $this->get(route('signatures.evidence', $document->uuid))->assertStatus(409);
    }

    public function test_signing_is_blocked_without_a_certificate(): void
    {
        $document = $this->document();
        $payload = $this->consent($document);
        config(['pdf_signatures.mode' => 'disabled']);
        $this->actingAs($this->user)->get(route('signatures.show', $document->uuid))->assertOk()->assertSee('Certificat indisponible')->assertDontSee('Signer ce PDF');
        $this->post(route('signatures.sign', $document->uuid), $payload)->assertSessionHasErrors('signature');
    }

    public function test_invalid_pdf_upload_has_an_actionable_error_and_creates_no_document(): void
    {
        $this->actingAs($this->user)->from(route('signatures.index'))->post(route('signatures.store'), [
            'pdf' => UploadedFile::fake()->createWithContent('bad.pdf', "%PDF-1.7\nnot a real PDF"),
        ])->assertRedirect(route('signatures.index'))->assertSessionHasErrors('pdf');
        $this->assertSame(0, SignatureDocument::query()->count());
    }

    public function test_database_failure_does_not_leave_a_signed_file_or_success_state(): void
    {
        $document = $this->document();
        $payload = $this->consent($document);
        $engine = Mockery::mock(PdfSignatureEngine::class);
        $engine->shouldReceive('sign')->once()->andReturn('signed output');
        $this->app->instance(PdfSignatureEngine::class, $engine);
        SignatureDocument::saving(function (SignatureDocument $record): void {
            if ($record->signed_path) {
                throw new \RuntimeException('Database failure');
            }
        });
        try {
            $this->actingAs($this->user)->post(route('signatures.sign', $document->uuid), $payload)->assertStatus(500);
            $this->assertNull($document->fresh()->signed_path);
            $this->assertSame([$document->original_path], Storage::disk('local')->allFiles('signatures'));
        } finally {
            SignatureDocument::flushEventListeners();
        }
    }
}
