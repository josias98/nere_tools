<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\TimesheetGeneration;
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

    public function test_deactivating_a_user_preserves_business_history(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['is_active' => true]);
        $employee = Employee::query()->create([
            'first_name' => 'History',
            'last_name' => 'Owner',
            'display_name' => 'History Owner',
            'email' => $user->email,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => 'HIST',
            'is_active' => true,
        ]);
        $type = LeaveType::query()->create(['name' => 'Congé', 'slug' => 'history-test']);
        $leaveRequest = LeaveRequest::query()->create([
            'uuid' => fake()->uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
            'requested_days' => 2,
            'status' => 'approved',
            'created_by_user_id' => $user->id,
        ]);
        $generation = TimesheetGeneration::query()->create([
            'uuid' => fake()->uuid(),
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'year' => 2026,
            'period_label' => 'Janvier 2026',
            'generated_by_user_id' => $user->id,
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users.index'));

        $this->assertFalse($user->fresh()->is_active);
        $this->assertTrue(LeaveRequest::query()->whereKey($leaveRequest->id)->exists());
        $this->assertTrue(TimesheetGeneration::query()->whereKey($generation->id)->exists());
    }

    public function test_inactive_admin_loses_admin_access_immediately(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => false]);

        $this->actingAs($admin)->get(route('admin.index'))->assertForbidden();
    }
}
