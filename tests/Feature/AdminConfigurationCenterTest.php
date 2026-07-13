<?php

namespace Tests\Feature;

use App\Models\LeaveSetting;
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
}
