<?php

namespace Tests\Unit\Leaves;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\Leaves\LeaveAccrualService;
use App\Services\Leaves\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeaveBalanceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Use a mock for accrual service to isolate tests
        $accrualMock = $this->createMock(LeaveAccrualService::class);
        $accrualMock->method('calculateAccruedDays')->willReturn(5.0);

        $this->service = new LeaveBalanceService($accrualMock);
    }

    public function test_calculates_available_pending_projected_balance()
    {
        $employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'display_name' => 'John Doe',
            'job_title' => 'Test',
            'entity' => 'NERE',
            'location' => 'Test',
            'analytic_code' => 'TEST',
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2025-01-01',
        ]);

        LeaveBalance::create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 10.0,
        ]);

        // Mock 1 approved request (should be deducted from available)
        $leaveType = LeaveType::create([
            'name' => 'Test',
            'slug' => 'test',
        ]);

        $user = User::create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        LeaveRequest::create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-03',
            'requested_days' => 3.0,
            'status' => 'approved',
            'created_by_user_id' => $user->id,
        ]);

        foreach (['submitted', 'pending_supervisor', 'pending_hr', 'pending_dg'] as $index => $status) {
            LeaveRequest::create([
                'uuid' => Str::uuid(),
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => '2026-03-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'end_date' => '2026-03-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'requested_days' => 2.0,
                'status' => $status,
                'created_by_user_id' => $user->id,
            ]);
        }

        $balance = $this->service->getBalance($employee, Carbon::parse('2026-03-15'));

        // Expected available = 10 (initial) + 5 (mock accrued) - 3 (approved) = 12
        $this->assertEquals(12.0, $balance['available_balance']);

        $this->assertEquals(8.0, $balance['pending_days']);

        $this->assertEquals(4.0, $balance['projected_balance']);
    }
}
