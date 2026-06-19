<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MicrosoftAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_authorized_microsoft_user_is_logged_in(): void
    {
        config()->set('services.microsoft.tenant_id', 'tenant-id');
        config()->set('services.microsoft.client_id', 'client-id');
        config()->set('services.microsoft.client_secret', 'client-secret');
        config()->set('services.microsoft.redirect_uri', 'http://localhost/auth/microsoft/callback');

        User::factory()->create([
            'email' => 'finance@nerecapital.com',
            'name' => 'Ancien nom',
            'role' => User::ROLE_FINANCE,
            'is_active' => true,
        ]);

        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'test-access-token',
                'token_type' => 'Bearer',
            ]),
            'graph.microsoft.com/v1.0/me*' => Http::response([
                'id' => 'microsoft-user-id',
                'displayName' => 'Finance Nere',
                'mail' => 'finance@nerecapital.com',
                'userPrincipalName' => 'finance@nerecapital.com',
            ]),
        ]);

        $this
            ->withSession(['microsoft_oauth_state' => 'state-value'])
            ->get('/auth/microsoft/callback?code=valid-code&state=state-value')
            ->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'finance@nerecapital.com',
            'name' => 'Finance Nere',
            'microsoft_id' => 'microsoft-user-id',
        ]);
    }

    public function test_microsoft_user_absent_from_local_database_is_denied(): void
    {
        config()->set('services.microsoft.tenant_id', 'tenant-id');
        config()->set('services.microsoft.client_id', 'client-id');
        config()->set('services.microsoft.client_secret', 'client-secret');
        config()->set('services.microsoft.redirect_uri', 'http://localhost/auth/microsoft/callback');

        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'test-access-token',
                'token_type' => 'Bearer',
            ]),
            'graph.microsoft.com/v1.0/me*' => Http::response([
                'id' => 'microsoft-user-id',
                'displayName' => 'External User',
                'mail' => 'external@example.com',
                'userPrincipalName' => 'external@example.com',
            ]),
        ]);

        $this
            ->withSession(['microsoft_oauth_state' => 'state-value'])
            ->get('/auth/microsoft/callback?code=valid-code&state=state-value')
            ->assertForbidden()
            ->assertSee('Compte non autorise');

        $this->assertGuest();
    }

    public function test_timesheets_route_requires_expected_roles(): void
    {
        $standardUser = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $financeUser = User::factory()->create([
            'role' => User::ROLE_FINANCE,
        ]);

        $this->actingAs($standardUser)
            ->get('/timesheets')
            ->assertForbidden();

        $this->actingAs($financeUser)
            ->get('/timesheets')
            ->assertOk()
            ->assertSee('Feuilles de temps');
    }
}
