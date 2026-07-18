<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveSetting;
use App\Models\LeaveValidator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConfigurationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_configuration_health_and_recommendations(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Santé de la configuration')
            ->assertSee('Congés')
            ->assertSee('Feuilles de temps')
            ->assertSee('Recommandations prioritaires');
    }

    public function test_non_admin_cannot_open_configuration_center_directly(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_pages_share_the_pilotage_navigation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        foreach (['/admin', '/admin/users', '/admin/conges', '/admin/conges/notifications'] as $path) {
            $this->actingAs($admin)->get($path)
                ->assertOk()
                ->assertSee('Centre de pilotage');
        }
    }

    public function test_admin_sidebar_links_point_to_available_admin_destinations(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get('/admin/conges');

        foreach ([
            route('admin.index'),
            route('admin.users.index'),
            route('admin.leaves.index'),
            route('timesheets.index'),
            route('admin.leaves.index', ['section' => 'overview']),
            route('admin.leaves.index', ['section' => 'requests']),
            route('admin.leaves.index', ['section' => 'people']),
            route('admin.leaves.index', ['section' => 'workflow']),
            route('admin.leaves.index', ['section' => 'settings']),
            route('admin.leaves.index', ['section' => 'rules']),
            route('admin.leaves.index', ['section' => 'calendar']),
            route('admin.leaves.index', ['section' => 'data']),
            route('admin.leaves.notifications.index'),
        ] as $url) {
            $response->assertSee('href="'.$url.'"', false);
        }

        $response->assertSee('Organisation')
            ->assertSee('Configuration')
            ->assertDontSee('Workflows et validations');
    }

    public function test_leave_admin_only_renders_the_selected_section(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin/conges')
            ->assertOk()
            ->assertSee('Situation opérationnelle à cet instant.')
            ->assertDontSee('Suivi des demandes')
            ->assertDontSee('Paramètres de calcul et de documents');

        $this->actingAs($admin)->get('/admin/conges?section=requests')
            ->assertOk()
            ->assertSee('Suivi des demandes')
            ->assertDontSee('Personnel et soldes');

        $this->actingAs($admin)->get('/admin/conges?section=unknown')
            ->assertOk()
            ->assertSee('Situation opérationnelle à cet instant.');
    }

    public function test_admin_can_access_initial_leave_balance_upload(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin/conges?section=data')
            ->assertOk()
            ->assertSee('Importer les soldes initiaux')
            ->assertSee('action="'.route('admin.leaves.import').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="reference_date"', false)
            ->assertSee('name="file"', false);
    }

    public function test_admin_sees_the_leave_balance_import_report(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->withSession(['import_report' => [
            'rows_read' => 3,
            'rows_imported' => 2,
            'rows_updated' => 0,
            'rows_skipped' => 1,
            'errors' => ['Ligne 3 : collaborateur introuvable.'],
        ]])->get('/admin/conges?section=data')
            ->assertOk()
            ->assertSee('Résultat du dernier import')
            ->assertSee('3 lignes lues')
            ->assertSee('2 créées')
            ->assertSee('1 ignorée')
            ->assertSee('Ligne 3 : collaborateur introuvable.');
    }

    public function test_admin_can_configure_operational_leave_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin/conges?section=settings')
            ->assertOk()
            ->assertSee('Paramètres de calcul et de documents')
            ->assertSee('action="'.route('admin.leaves.settings.update').'"', false)
            ->assertSee('name="monthly_accrual_days"', false)
            ->assertSee('name="accrual_policy"', false)
            ->assertSee('name="LEAVE_CERTIFICATE_SIGNATORY_NAME"', false)
            ->assertSee('name="LEAVE_CERTIFICATE_SIGNATORY_TITLE"', false)
            ->assertSee('name="settings_updated_at"', false)
            ->assertSee('name="change_reason"', false);
    }

    public function test_admin_can_configure_validation_responsibilities(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $department = Department::query()->create(['name' => 'Finance', 'slug' => 'finance']);
        $validator = $this->employee('Responsable Finance', $department);
        $responsibility = LeaveValidator::query()->create([
            'employee_id' => $validator->id,
            'step_key' => 'supervisor',
            'scope' => 'department',
            'department_id' => $department->id,
            'notify_by_email' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin/conges?section=workflow')
            ->assertOk()
            ->assertSee('Responsabilités de validation')
            ->assertSee('action="'.route('admin.leaves.validators.store').'"', false)
            ->assertSee('name="step_key"', false)
            ->assertSee('name="scope"', false)
            ->assertSee('name="department_id"', false)
            ->assertSee('name="target_employee_id"', false)
            ->assertSee('Responsable hiérarchique')
            ->assertSee('action="'.route('admin.leaves.validators.update', $responsibility).'"', false)
            ->assertSee('action="'.route('admin.leaves.validators.destroy', $responsibility).'"', false);
    }

    public function test_leave_workflow_actions_explain_their_effects(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get(route('admin.leaves.index', ['section' => 'workflow']))
            ->assertOk()
            ->assertSee('data-tooltip-title="Ajouter au circuit"', false)
            ->assertSee('Le validateur recevra les demandes correspondant à l’étape et à la portée choisies.', false);
    }

    public function test_validation_responsibility_scope_requires_its_target(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $validator = $this->employee('Responsable Test');

        $this->actingAs($admin)->from('/admin/conges?section=workflow')->post(route('admin.leaves.validators.store'), [
            'employee_id' => $validator->id,
            'step_key' => 'supervisor',
            'scope' => 'department',
        ])->assertSessionHasErrors('department_id');

        $this->actingAs($admin)->from('/admin/conges?section=workflow')->post(route('admin.leaves.validators.store'), [
            'employee_id' => $validator->id,
            'step_key' => 'supervisor',
            'scope' => 'employee',
        ])->assertSessionHasErrors('target_employee_id');
    }

    public function test_validation_responsibility_can_disable_email_notifications(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $validator = $this->employee('Validateur Sans Email');

        $this->actingAs($admin)->post(route('admin.leaves.validators.store'), [
            'employee_id' => $validator->id,
            'step_key' => 'supervisor',
            'scope' => 'global',
        ])->assertRedirect();

        $this->assertDatabaseHas('leave_validators', [
            'employee_id' => $validator->id,
            'step_key' => 'supervisor',
            'scope' => 'global',
            'notify_by_email' => false,
        ]);
    }

    public function test_admin_can_manage_leave_profiles_and_filter_the_validation_step(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employee = $this->employee('Profil Congés');

        $this->actingAs($admin)->get('/admin/conges?section=requests&step=supervisor')
            ->assertOk()
            ->assertSee('name="step"', false)
            ->assertSee('<option value="supervisor" selected', false);

        $this->actingAs($admin)->get('/admin/conges?section=people')
            ->assertOk()
            ->assertSee('Profils RH des collaborateurs')
            ->assertSee('action="'.route('admin.leaves.employees.update', $employee).'"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="hire_date"', false)
            ->assertSee('name="leave_eligible"', false);
    }

    public function test_missing_leave_workflow_is_reported_as_actionable(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        LeaveSetting::query()->create(['key' => 'workflow_mode', 'value' => 'sequential', 'value_type' => 'string']);

        $this->actingAs($admin)->get('/admin?module=conges&status=error')
            ->assertOk()
            ->assertSee('Aucun validateur actif')
            ->assertSee('Corriger');
    }

    public function test_setting_change_is_versioned_and_audited(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $setting = LeaveSetting::query()->create(['key' => 'monthly_accrual_days', 'value' => '2.5', 'value_type' => 'decimal']);

        $this->actingAs($admin)->put('/admin/conges/parametres', [
            'monthly_accrual_days' => '2.75',
            'accrual_policy' => 'end_of_month',
            'settings_updated_at' => $setting->updated_at->toISOString(),
            'change_reason' => 'Alignement avec la politique RH 2026',
        ])->assertRedirect();

        $this->assertDatabaseHas('setting_versions', [
            'module' => 'conges', 'key' => 'monthly_accrual_days', 'old_value' => '2.5', 'new_value' => '2.75',
        ]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'settings.updated']);
    }

    public function test_stale_setting_update_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        LeaveSetting::query()->create(['key' => 'monthly_accrual_days', 'value' => '2.5', 'value_type' => 'decimal']);

        $this->actingAs($admin)->from('/admin/conges')->put('/admin/conges/parametres', [
            'monthly_accrual_days' => '3',
            'accrual_policy' => 'end_of_month',
            'settings_updated_at' => now()->subDay()->toISOString(),
        ])->assertRedirect('/admin/conges')->assertSessionHasErrors('settings');

        $this->assertSame('2.5', LeaveSetting::query()->where('key', 'monthly_accrual_days')->value('value'));
    }

    private function employee(string $name, ?Department $department = null): Employee
    {
        return Employee::query()->create([
            'first_name' => strtok($name, ' '),
            'last_name' => trim(strstr($name, ' ') ?: 'Test'),
            'display_name' => $name,
            'email' => str($name)->slug().'-'.fake()->uuid().'@nere.test',
            'department_id' => $department?->id,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => 'TEST',
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);
    }
}
