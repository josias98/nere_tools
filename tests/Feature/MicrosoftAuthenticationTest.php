<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\QueryException;
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
            'microsoft_id' => null,
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

    public function test_reassigned_email_cannot_take_over_a_linked_account(): void
    {
        config()->set('services.microsoft.tenant_id', 'tenant-id');
        config()->set('services.microsoft.client_id', 'client-id');
        config()->set('services.microsoft.client_secret', 'client-secret');
        config()->set('services.microsoft.redirect_uri', 'http://localhost/auth/microsoft/callback');
        $user = User::factory()->create([
            'email' => 'linked@nerecapital.com',
            'microsoft_id' => 'original-microsoft-id',
            'role' => User::ROLE_ADMIN,
        ]);

        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response(['access_token' => 'test-access-token']),
            'graph.microsoft.com/v1.0/me*' => Http::response([
                'id' => 'different-microsoft-id',
                'displayName' => 'Different User',
                'mail' => $user->email,
                'userPrincipalName' => $user->email,
            ]),
        ]);

        $this->withSession(['microsoft_oauth_state' => 'state-value'])
            ->get('/auth/microsoft/callback?code=valid-code&state=state-value')
            ->assertForbidden();

        $this->assertGuest();
        $this->assertSame('original-microsoft-id', $user->fresh()->microsoft_id);
    }

    public function test_linked_microsoft_id_survives_an_email_change(): void
    {
        config()->set('services.microsoft.tenant_id', 'tenant-id');
        config()->set('services.microsoft.client_id', 'client-id');
        config()->set('services.microsoft.client_secret', 'client-secret');
        config()->set('services.microsoft.redirect_uri', 'http://localhost/auth/microsoft/callback');
        $user = User::factory()->create([
            'email' => 'old-address@nerecapital.com',
            'microsoft_id' => 'stable-microsoft-id',
        ]);

        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response(['access_token' => 'test-access-token']),
            'graph.microsoft.com/v1.0/me*' => Http::response([
                'id' => 'stable-microsoft-id',
                'displayName' => 'Linked User',
                'mail' => 'new-address@nerecapital.com',
                'userPrincipalName' => 'new-address@nerecapital.com',
            ]),
        ]);

        $this->withSession(['microsoft_oauth_state' => 'state-value'])
            ->get('/auth/microsoft/callback?code=valid-code&state=state-value')
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('new-address@nerecapital.com', $user->fresh()->email);
    }

    public function test_microsoft_id_is_unique(): void
    {
        User::factory()->create(['microsoft_id' => 'unique-microsoft-id']);

        $this->expectException(QueryException::class);
        User::factory()->create(['microsoft_id' => 'unique-microsoft-id']);
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

    public function test_dashboard_hides_coming_soon_tools(): void
    {
        $financeUser = User::factory()->create(['role' => User::ROLE_FINANCE]);

        Tool::query()->create([
            'name' => 'Feuilles de temps',
            'slug' => 'timesheets',
            'description' => 'Generation automatique des feuilles mensuelles en PDF.',
            'route' => '/timesheets',
            'status' => Tool::STATUS_ACTIVE,
            'required_role' => User::ROLE_FINANCE,
            'display_order' => 10,
        ]);
        Tool::query()->create([
            'name' => 'Reporting portefeuille',
            'slug' => 'reporting',
            'description' => 'Suivi portefeuille.',
            'route' => '/reporting',
            'status' => Tool::STATUS_COMING_SOON,
            'display_order' => 20,
        ]);

        $this->actingAs($financeUser)
            ->get('/')
            ->assertOk()
            ->assertSee('Feuilles de temps')
            ->assertDontSee('Reporting portefeuille')
            ->assertDontSee('Bientot disponible');
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
            "prenom,nom,entite,pays,fonction,role,fonds,ipde,catal,autre_projets,code_analytique,lieu,nom_signature,responsable_hierarchique,signature_droite_titre\nJob,ZONGO,Nere Capital,Burkina Faso,DG Fonds,DG Fonds,88%,0%,12%,88%,1.1.1 Personnel technique,Ouagadougou,ZONGO P. Job,Responsable CSV,DAF\n"
        );

        $this->actingAs($financeUser)
            ->post('/timesheets/csv', ['csv_file' => $csv])
            ->assertRedirect('/timesheets')
            ->assertSessionHas('timesheet_csv_rows.0.employee_id', $employee->id)
            ->assertSessionHas('timesheet_csv_rows.0.first_name', 'Job')
            ->assertSessionHas('timesheet_csv_rows.0.signatory_name', 'Responsable CSV')
            ->assertSessionHas('timesheet_csv_rows.0.signature_title', 'DAF');

        Storage::fake('local');

        $response = $this->actingAs($financeUser)->post('/timesheets/generate', [
            'download_zip' => 1,
            'period_start' => '2026-01-01',
            'period_end' => '2026-06-30',
            'zip_label' => 'Feuilles_de_temps_S1_2026',
            'excluded_signature_dates' => "2026-02-02\n",
            'rows' => [[
                'selected' => 1,
                'employee_id' => $employee->id,
                'first_name' => 'Job',
                'last_name' => 'ZONGO',
                'entity_name' => 'Nere Capital',
                'country' => 'Burkina Faso',
                'function_title' => 'DG Fonds',
                'role' => 'DG Fonds',
                'funds' => '88%',
                'ipde_rate' => 0,
                'catal_rate' => 12,
                'other_projects_rate' => 88,
                'analytic_code' => '1.1.1 Personnel technique',
                'location' => 'Ouagadougou',
                'employee_signature_name' => 'ZONGO P. Job',
                'signatory_name' => 'Responsable CSV',
                'signature_title' => 'DAF',
                'comments_label' => 'Commentaires / Details',
                'include_comments' => 1,
            ]],
        ]);

        $response->assertOk();
        $this->assertStringContainsString('Feuilles_de_temps_S1_2026', $response->headers->get('content-disposition'));
    }

    public function test_timesheets_csv_upload_matches_partial_last_name_without_error(): void
    {
        $financeUser = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $employee = Employee::query()->create([
            'first_name' => 'Germaine',
            'last_name' => 'BAKO/NAGALO',
            'display_name' => 'Germaine BAKO/NAGALO',
            'entity' => 'NERE CAPITAL PARTNERS',
            'location' => 'Ouagadougou',
            'job_title' => 'Assistant(e) administratif(ve) et financier(e)',
            'analytic_code' => '1.1.1 Personnel technique',
            'ipas_rate' => 80,
            'catal_rate' => 20,
            'ipde_rate' => 0,
            'requires_other_projects' => true,
            'signature_title' => 'DAF',
            'signatory_name' => 'BAKO/NAGALO A Germaine',
            'is_active' => true,
        ]);

        $csv = UploadedFile::fake()->createWithContent(
            'timesheets.csv',
            "prenom,nom,entite,pays,fonction,role,fonds,ipde,catal,autre_projets,code_analytique,lieu,nom_signature\nGermaine,BAKO,Nere Capital,Burkina Faso,Assistant(e) administratif(ve) et financier(e),AAF,80%,0%,20%,80%,1.1.1 Personnel technique,Ouagadougou,BAKO/NAGALO A Germaine\n"
        );

        $this->actingAs($financeUser)
            ->post('/timesheets/csv', ['csv_file' => $csv])
            ->assertRedirect('/timesheets')
            ->assertSessionHas('timesheet_csv_rows.0.employee_id', $employee->id)
            ->assertSessionHas('timesheet_csv_rows.0.first_name', 'Germaine')
            ->assertSessionHas('timesheet_csv_rows.0.last_name', 'BAKO');
    }
}
