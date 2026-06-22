<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

    public function test_timesheets_csv_upload_prepares_editable_rows(): void
    {
        $financeUser = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $employee = Employee::query()->create([
            'first_name' => 'Job',
            'last_name' => 'ZONGO',
            'display_name' => 'Job ZONGO',
            'entity' => 'NERE CAPITAL PARTNERS',
            'location' => 'Ouagadougou',
            'job_title' => 'DG Fonds',
            'analytic_code' => '1.1.1 Personnel technique',
            'ipas_rate' => 0,
            'catal_rate' => 12,
            'ipde_rate' => 0,
            'requires_other_projects' => true,
            'signature_title' => 'Signature du DAF',
            'signatory_name' => 'BAKO/NAGALO A Germaine',
            'is_active' => true,
        ]);

        $csv = UploadedFile::fake()->createWithContent(
            'timesheets.csv',
            "collaborateur;year;start_month;end_month;signatory_name\nJob ZONGO;2026;1;1;Responsable CSV\n"
        );

        $this->actingAs($financeUser)
            ->post('/timesheets/csv', ['csv_file' => $csv])
            ->assertRedirect('/timesheets')
            ->assertSessionHas('timesheet_csv_rows.0.employee_id', $employee->id)
            ->assertSessionHas('timesheet_csv_rows.0.signatory_name', 'Responsable CSV');

        Storage::fake('local');

        $response = $this->actingAs($financeUser)->post('/timesheets/generate', [
            'download_zip' => 1,
            'rows' => [[
                'employee_id' => $employee->id,
                'year' => 2026,
                'start_month' => 1,
                'end_month' => 1,
                'entity_label' => 'Nere Capital',
                'signature_date' => '2026-02-02',
                'signatory_name' => 'Responsable CSV',
                'comments_label' => 'Commentaires / Details',
                'include_comments' => 1,
            ]],
        ]);

        $response->assertOk();
        $this->assertStringContainsString('Feuilles_de_temps_CSV_', $response->headers->get('content-disposition'));
    }
}
