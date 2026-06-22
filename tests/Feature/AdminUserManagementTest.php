<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_finance_department_user_can_open_admin(): void
    {
        $department = Department::query()->create([
            'name' => 'Administratif et Finance',
            'slug' => 'administratif-finance',
        ]);
        $user = User::factory()->create([
            'email' => 'finance-admin@nere.test',
            'role' => User::ROLE_USER,
        ]);
        Employee::query()->create([
            'first_name' => 'Finance',
            'last_name' => 'Admin',
            'display_name' => 'Finance Admin',
            'email' => $user->email,
            'department_id' => $department->id,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Finance',
            'analytic_code' => 'TEST',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Administration');
    }

    public function test_admin_can_create_user_with_tool_access(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $timesheets = Tool::query()->create([
            'name' => 'Feuilles de temps',
            'slug' => 'timesheets',
            'route' => '/timesheets',
            'status' => Tool::STATUS_ACTIVE,
            'required_role' => User::ROLE_FINANCE,
            'display_order' => 10,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Collaborateur Test',
                'email' => 'collab@nere.test',
                'role' => User::ROLE_USER,
                'is_active' => 1,
                'tool_ids' => [$timesheets->id],
            ])
            ->assertRedirect();

        $user = User::query()->where('email', 'collab@nere.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('timesheets.index'))
            ->assertOk()
            ->assertSee('Feuilles de temps');
    }
}
