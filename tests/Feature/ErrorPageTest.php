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
}
