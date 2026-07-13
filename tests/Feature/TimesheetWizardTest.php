<?php

namespace Tests\Feature;

use App\Livewire\Timesheets\Wizard;
use App\Models\Employee;
use App\Models\TimesheetGeneration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TimesheetWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_wizard_preserves_rows_and_validates_allocation_before_generation(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $employee = $this->employee();

        $this->actingAs($user);
        $component = Livewire::test(Wizard::class)
            ->call('chooseMethod', 'manual')
            ->assertSet('step', 2)
            ->call('next')
            ->assertSet('step', 3)
            ->call('addEmployee', $employee->id)
            ->assertCount('rows', 1)
            ->call('next')
            ->assertSet('step', 4)
            ->set('rows.0.other_projects_rate', 70)
            ->call('next')
            ->assertHasErrors('rows.0.other_projects_rate')
            ->assertSet('step', 4);
    }

    public function test_confirmed_wizard_generates_one_private_lot(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $employee = $this->employee();

        $this->actingAs($user);
        $component = Livewire::test(Wizard::class)
            ->set('method', 'manual')
            ->set('step', 3)
            ->call('addEmployee', $employee->id)
            ->call('next')
            ->call('next')
            ->set('confirmed', true)
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('step', 6);

        $component->call('generate')->assertHasNoErrors()->assertSet('step', 6);

        $this->assertDatabaseCount('timesheet_generations', 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'timesheet.generated', 'user_id' => $user->id]);
    }

    public function test_user_cannot_open_another_users_generation(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $other = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $generation = TimesheetGeneration::query()->create([
            'uuid' => fake()->uuid(), 'period_start' => '2026-01-01', 'period_end' => '2026-01-31',
            'year' => 2026, 'period_label' => 'Janvier', 'generated_by_user_id' => $owner->id,
            'employee_count' => 1, 'pdf_count' => 1, 'status' => 'completed',
        ]);

        $this->actingAs($other)->get(route('timesheets.result', $generation))->assertForbidden();
        $this->actingAs($owner)->get(route('timesheets.result', $generation))->assertOk();
    }

    private function employee(): Employee
    {
        return Employee::query()->create([
            'first_name' => 'Awa', 'last_name' => 'Kaboré', 'display_name' => 'Awa Kaboré',
            'entity' => 'Néré Capital', 'location' => 'Ouagadougou', 'job_title' => 'Analyste',
            'analytic_code' => '1.1.1', 'ipas_rate' => 20, 'catal_rate' => 30, 'ipde_rate' => 10,
            'requires_other_projects' => true, 'is_active' => true,
        ]);
    }
}
