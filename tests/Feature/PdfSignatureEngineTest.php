<?php

namespace Tests\Feature;

use App\Services\Signatures\PdfSignatureEngine;
use App\Services\Signatures\SigningCertificate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Support\PdfSigningFixture;
use Tests\TestCase;

class PdfSignatureEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::disk('local')->put('test-certificate.p12', PdfSigningFixture::certificate());
        config([
            'pdf_signatures.mode' => 'test',
            'pdf_signatures.certificate_path' => Storage::disk('local')->path('test-certificate.p12'),
            'pdf_signatures.password' => 'test-password',
        ]);
    }

    public function test_real_signature_preserves_the_original_and_detects_tampering(): void
    {
        $engine = app(PdfSignatureEngine::class);
        $cert = app(SigningCertificate::class)->load();
        $original = PdfSigningFixture::pdf();
        $signed = $engine->sign($original, $cert, 'Zoé Kaboré', 'zoe@example.test', 'Approbation');

        $this->assertStringStartsWith($original, $signed);
        $this->assertStringContainsString('/ETSI.CAdES.detached', $signed);
        $this->assertTrue($engine->verify($signed, $cert['fingerprint']));
        $this->assertFalse($engine->verify(str_replace('1500 EUR', '9500 EUR', $signed), $cert['fingerprint']));
        $this->assertFalse($engine->verify($signed."\n% modification", $cert['fingerprint']));
        $this->assertFalse($engine->verify($signed, str_repeat('0', 64)));
    }

    public function test_signed_pdfs_cannot_be_imported_again(): void
    {
        $engine = app(PdfSignatureEngine::class);
        $signed = $engine->sign(PdfSigningFixture::pdf(), app(SigningCertificate::class)->load(), 'User', 'user@example.test', 'Approbation');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('contient déjà');
        $engine->inspect($signed);
    }

    public function test_forms_and_active_content_are_rejected_without_flattening(): void
    {
        $this->expectException(RuntimeException::class);
        app(PdfSignatureEngine::class)->inspect(PdfSigningFixture::pdf('/AcroForm << /Fields [] >>'));
    }

    public function test_javascript_is_rejected_even_with_escaped_pdf_names(): void
    {
        $this->expectException(RuntimeException::class);
        app(PdfSignatureEngine::class)->inspect(PdfSigningFixture::pdf('/Open#41ction << /S /JavaScript /JS (app.alert) >>'));
    }

    public function test_encrypted_pdfs_are_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        app(PdfSignatureEngine::class)->inspect(PdfSigningFixture::pdf('', '/Encrypt 5 0 R'));
    }

    public function test_invalid_password_disables_signing_without_exposing_secrets(): void
    {
        config(['pdf_signatures.password' => 'wrong']);
        $status = app(SigningCertificate::class)->status();
        $this->assertFalse($status['ready']);
        $this->assertArrayNotHasKey('pkcs12', $status);
        $this->assertStringNotContainsString('wrong', $status['message']);
    }

    public function test_expired_certificates_are_rejected(): void
    {
        Storage::disk('local')->put('test-certificate.p12', PdfSigningFixture::certificate(days: 0));
        $this->assertFalse(app(SigningCertificate::class)->status()['ready']);
    }

    public function test_test_mode_is_rejected_in_production(): void
    {
        app()->instance('env', 'production');
        $this->assertFalse(app(SigningCertificate::class)->status()['ready']);
    }

    public function test_self_signed_certificate_is_rejected_in_production_mode(): void
    {
        config(['pdf_signatures.mode' => 'production']);
        $this->assertFalse(app(SigningCertificate::class)->status()['ready']);
    }
}
