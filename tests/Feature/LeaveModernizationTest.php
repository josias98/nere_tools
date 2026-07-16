<?php

namespace Tests\Feature;

use App\Enums\LeaveUnit;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveHoliday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\Leaves\LeaveAccrualService;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveDayCountService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveModernizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_working_days_exclude_weekends_and_holidays_and_compute_return(): void
    {
        LeaveHoliday::query()->create(['date' => '2026-08-10', 'name' => 'Férié test']);
        $service = app(LeaveDayCountService::class);

        $this->assertSame(1.0, $service->calculate(now()->parse('2026-08-07'), now()->parse('2026-08-10'), LeaveUnit::WorkingDay));
        $this->assertSame('2026-08-11', $service->effectiveReturn(now()->parse('2026-08-07'), LeaveUnit::WorkingDay)->toDateString());
    }

    public function test_independent_quota_does_not_reduce_annual_balance(): void
    {
        $employee = $this->employee();
        $user = User::factory()->create();
        LeaveBalance::query()->create(['employee_id' => $employee->id, 'reference_date' => '2026-01-01', 'initial_remaining_days' => 30]);
        $type = LeaveType::query()->create(['name' => 'Permission', 'slug' => 'permission-test', 'counts_against_balance' => false]);
        LeaveRequest::query()->create(['uuid' => fake()->uuid(), 'employee_id' => $employee->id, 'leave_type_id' => $type->id, 'start_date' => '2026-02-01', 'end_date' => '2026-02-05', 'requested_days' => 5, 'status' => 'approved', 'created_by_user_id' => $user->id]);
        $accrual = $this->createMock(LeaveAccrualService::class);
        $accrual->method('calculateAccruedDays')->willReturn(0.0);

        $this->assertSame(30.0, (new LeaveBalanceService($accrual))->getBalance($employee)['available']);
    }

    public function test_submission_keeps_rule_snapshot_and_private_attachment(): void
    {
        Storage::fake('local');
        $employee = $this->employee();
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Maladie', 'slug' => 'illness-test', 'unit' => 'calendar_day', 'requires_attachment' => true]);
        $type->rules()->create(['version' => 1, 'effective_from' => '2026-01-01', 'configuration' => ['maximum' => 30]]);

        $this->actingAs($user)->post(route('leaves.store'), ['leave_type_id' => $type->id, 'start_date' => '2026-08-01', 'end_date' => '2026-08-02', 'attachments' => [UploadedFile::fake()->create('certificat.pdf', 20, 'application/pdf')]])->assertRedirect();

        $request = LeaveRequest::query()->firstOrFail();
        $this->assertSame(1, $request->rule_snapshot['rule_version']);
        $this->assertCount(1, $request->attachments);
        Storage::disk('local')->assertExists($request->attachments->first()->file_path);
    }

    public function test_missing_required_attachment_is_explained_on_the_form(): void
    {
        $employee = $this->employee();
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Maladie', 'slug' => 'illness-required', 'unit' => 'calendar_day', 'requires_attachment' => true]);

        $this->actingAs($user)
            ->followingRedirects()
            ->from(route('leaves.create'))
            ->post(route('leaves.store'), ['leave_type_id' => $type->id, 'start_date' => '2026-08-01', 'end_date' => '2026-08-02'])
            ->assertOk()
            ->assertSee('Le justificatif est obligatoire pour ce type de congé.');
    }

    public function test_ineligible_employee_cannot_submit_a_request(): void
    {
        $employee = $this->employee();
        $employee->update(['leave_eligible' => false]);
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Congé', 'slug' => 'ineligible-test']);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Ce collaborateur n'est pas éligible");

        app(LeaveRequestWorkflowService::class)->submitRequest($employee->fresh(), [
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
        ], $user->id);
    }

    public function test_maximum_duration_is_enforced(): void
    {
        $employee = $this->employee();
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Permission', 'slug' => 'maximum-test', 'maximum_duration' => 2]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('dépasse le maximum');

        app(LeaveRequestWorkflowService::class)->submitRequest($employee, [
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
        ], $user->id);
    }

    public function test_notice_period_is_enforced(): void
    {
        $this->travelTo('2026-07-01 12:00:00');
        $employee = $this->employee();
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Autorisation', 'slug' => 'notice-test', 'notice_hours' => 72]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('préavis');

        app(LeaveRequestWorkflowService::class)->submitRequest($employee, [
            'leave_type_id' => $type->id,
            'start_date' => '2026-07-03',
            'end_date' => '2026-07-03',
        ], $user->id);
    }

    public function test_annual_quota_includes_pending_requests(): void
    {
        $employee = $this->employee();
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Permission', 'slug' => 'quota-test', 'quota' => 3]);
        LeaveRequest::query()->create([
            'uuid' => fake()->uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
            'requested_days' => 2,
            'requested_duration' => 2,
            'duration_unit' => 'calendar_day',
            'status' => 'pending_supervisor',
            'created_by_user_id' => $user->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('quota annuel');

        app(LeaveRequestWorkflowService::class)->submitRequest($employee, [
            'leave_type_id' => $type->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
        ], $user->id);
    }

    public function test_hourly_request_requires_times(): void
    {
        $employee = $this->employee();
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Autorisation horaire', 'slug' => 'hour-required', 'unit' => LeaveUnit::Hour]);

        $this->actingAs($user)->post(route('leaves.store'), [
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
        ])->assertSessionHasErrors(['start_time', 'end_time']);
    }

    public function test_disjoint_hourly_requests_store_exact_times_and_duration(): void
    {
        $employee = $this->employee();
        $user = User::factory()->create(['email' => $employee->email]);
        $type = LeaveType::query()->create(['name' => 'Autorisation horaire', 'slug' => 'hour-duration', 'unit' => LeaveUnit::Hour]);

        $this->actingAs($user)->post(route('leaves.store'), [
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'start_time' => '09:00',
            'end_date' => '2026-08-01',
            'end_time' => '13:30',
        ])->assertSessionHas('success');
        $this->actingAs($user)->post(route('leaves.store'), [
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'start_time' => '13:30',
            'end_date' => '2026-08-01',
            'end_time' => '15:00',
        ])->assertSessionHas('success');

        $requests = LeaveRequest::query()->orderBy('id')->get();
        $this->assertCount(2, $requests);
        $this->assertSame(4.5, $requests->first()->requested_duration);
        $this->assertSame('2026-08-01 09:00', $requests->first()->start_at->format('Y-m-d H:i'));
        $this->assertSame('2026-08-01 13:30', $requests->first()->effective_return_at->format('Y-m-d H:i'));
    }

    public function test_admin_export_is_an_xlsx_workbook(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $response = $this->actingAs($admin)->get(route('admin.leaves.export'));
        $response->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function employee(): Employee
    {
        return Employee::query()->create(['first_name' => 'Awa', 'last_name' => 'Test', 'display_name' => 'Awa Test', 'email' => 'awa-'.fake()->uuid().'@nere.test', 'entity' => 'NERE', 'location' => 'Ouagadougou', 'job_title' => 'Test', 'analytic_code' => 'TEST', 'is_active' => true, 'leave_eligible' => true, 'hire_date' => '2025-01-01']);
    }
}
