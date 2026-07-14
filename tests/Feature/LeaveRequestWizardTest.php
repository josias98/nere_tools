<?php

namespace Tests\Feature;

use App\Livewire\Leaves\RequestWizard;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveHoliday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveRequestWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_progresses_back_and_restores_server_draft(): void
    {
        [$user, $employee] = $this->userWithEmployee();
        $type = $this->leaveType();
        LeaveBalance::query()->create(['employee_id' => $employee->id, 'reference_date' => '2026-01-01', 'initial_remaining_days' => 30]);

        $this->actingAs($user);
        $component = Livewire::test(RequestWizard::class)
            ->call('selectType', $type->id)
            ->assertSet('step', 2)
            ->set('form.start_date', '2026-08-03')
            ->set('form.end_date', '2026-08-07')
            ->call('next')
            ->assertSet('step', 3)
            ->call('previous')
            ->assertSet('step', 2);

        $draftUuid = $component->get('draftUuid');
        $this->assertDatabaseHas('leave_request_drafts', ['uuid' => $draftUuid, 'user_id' => $user->id]);

        $this->get(route('leaves.create', ['draft' => $draftUuid]))
            ->assertOk()
            ->assertSee('2026-08-03');
    }

    public function test_period_preview_uses_server_holidays_and_blocks_overlap(): void
    {
        [$user, $employee] = $this->userWithEmployee();
        $type = $this->leaveType(['unit' => 'working_day']);
        LeaveBalance::query()->create(['employee_id' => $employee->id, 'reference_date' => '2026-01-01', 'initial_remaining_days' => 30]);
        LeaveHoliday::query()->create(['date' => '2026-08-04', 'name' => 'Ferie test']);
        LeaveRequest::query()->create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'requested_days' => 3,
            'status' => 'pending_supervisor',
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($user);
        Livewire::test(RequestWizard::class)
            ->call('selectType', $type->id)
            ->set('form.start_date', '2026-08-03')
            ->set('form.end_date', '2026-08-07')
            ->assertSee('Ferie test')
            ->set('form.start_date', '2026-08-11')
            ->set('form.end_date', '2026-08-13')
            ->call('next')
            ->assertHasErrors('form.start_date');
    }

    public function test_replacement_search_only_returns_active_colleagues(): void
    {
        [$user] = $this->userWithEmployee('requester@nere.test', 'Requester');
        $type = $this->leaveType();
        $replacement = $this->employee('active-replacement@nere.test', 'Active Replacement');
        $this->employee('inactive-replacement@nere.test', 'Inactive Replacement', false);

        $this->actingAs($user);
        Livewire::test(RequestWizard::class)
            ->call('selectType', $type->id)
            ->set('form.start_date', '2026-08-03')
            ->set('form.end_date', '2026-08-07')
            ->call('next')
            ->set('form.replacement_needed', true)
            ->set('replacementSearch', 'Replacement')
            ->assertSee($replacement->name())
            ->assertDontSee('Inactive Replacement');
    }

    public function test_submission_is_idempotent_and_reaches_confirmation(): void
    {
        [$user, $employee] = $this->userWithEmployee();
        $type = $this->leaveType();
        LeaveBalance::query()->create(['employee_id' => $employee->id, 'reference_date' => '2026-01-01', 'initial_remaining_days' => 30]);

        $this->actingAs($user);
        $component = Livewire::test(RequestWizard::class)
            ->call('selectType', $type->id)
            ->set('form.start_date', '2026-08-03')
            ->set('form.end_date', '2026-08-07')
            ->call('next')
            ->call('next')
            ->set('confirmed', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('step', 5)
            ->assertSee('Demande soumise avec succes');

        $component->call('submit')->assertSet('step', 5);

        $this->assertDatabaseCount('leave_requests', 1);
        $request = LeaveRequest::query()->firstOrFail();
        $this->assertSame('pending_supervisor', $request->status);
        $this->assertCount(3, $request->approvals);
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function userWithEmployee(string $email = 'user@nere.test', string $name = 'User'): array
    {
        $user = User::factory()->create(['name' => $name, 'email' => $email, 'role' => User::ROLE_USER, 'is_active' => true]);
        $employee = $this->employee($email, $name);

        return [$user, $employee];
    }

    private function employee(string $email, string $name, bool $active = true): Employee
    {
        return Employee::query()->create([
            'first_name' => $name,
            'last_name' => 'Test',
            'display_name' => $name.' Test',
            'email' => $email,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => strtoupper(Str::substr(Str::slug($name, ''), 0, 6) ?: 'TEST'),
            'is_active' => $active,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function leaveType(array $overrides = []): LeaveType
    {
        return LeaveType::query()->create(array_merge([
            'name' => 'Conge annuel',
            'slug' => 'annual-'.Str::random(6),
            'unit' => 'calendar_day',
            'is_active' => true,
            'counts_against_balance' => true,
        ], $overrides));
    }
}
