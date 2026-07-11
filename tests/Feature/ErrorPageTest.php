<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbidden_pages_use_custom_view_and_are_audited(): void
    {
        Route::get('/_test-forbidden-page', fn () => abort(403));

        $this->actingAs(User::factory()->create())
            ->get('/_test-forbidden-page')
            ->assertForbidden()
            ->assertSee('Acces refuse')
            ->assertDontSee('Whoops');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'http_error.rendered',
        ]);
    }

    public function test_server_errors_use_custom_view_and_are_audited(): void
    {
        Route::get('/_test-server-error-page', fn () => throw new RuntimeException('boom'));

        $this->get('/_test-server-error-page')
            ->assertStatus(500)
            ->assertSee('Incident enregistre')
            ->assertDontSee('Whoops');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'exception.reported',
        ]);
    }

    public function test_not_found_and_expired_session_pages_are_branded(): void
    {
        $this->get('/_missing-page')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertSee('Contacter l’administrateur');

        Route::get('/_test-expired-session', fn () => abort(419));

        $this->get('/_test-expired-session')
            ->assertStatus(419)
            ->assertSee('Session expiree')
            ->assertDontSee('Whoops');
    }

    public function test_validation_throttle_and_maintenance_pages_are_branded(): void
    {
        foreach ([422 => 'Informations à corriger', 429 => 'Trop de tentatives', 503 => 'Service temporairement indisponible'] as $status => $text) {
            Route::get('/_test-error-'.$status, fn () => abort($status));

            $this->get('/_test-error-'.$status)
                ->assertStatus($status)
                ->assertSee($text);
        }
    }
}
