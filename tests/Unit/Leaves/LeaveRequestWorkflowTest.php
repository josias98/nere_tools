<?php

namespace Tests\Unit\Leaves;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveDayCountService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private LeaveRequestWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $balanceMock = $this->createMock(LeaveBalanceService::class);
        $dayCountMock = new LeaveDayCountService;

        $this->service = new LeaveRequestWorkflowService($balanceMock, $dayCountMock);
    }

    public function test_employee_can_submit_request()
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
        ]);

        $leaveType = LeaveType::create(['name' => 'Test', 'slug' => 'test']);
        $user = User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => 'password']);

        $data = [
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
        ];

        $request = $this->service->submitRequest($employee, $data, $user->id);

        $this->assertEquals('pending_supervisor', $request->status);
        $this->assertEquals(5, $request->requested_days);
        $this->assertCount(3, $request->approvals);
    }

    public function test_overlapping_request_is_blocked()
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
        ]);

        $leaveType = LeaveType::create(['name' => 'Test', 'slug' => 'test']);
        $user = User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => 'password']);

        // First request
        LeaveRequest::create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
            'requested_days' => 5,
            'status' => 'approved',
            'created_by_user_id' => $user->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Une demande de congé existe déjà sur cette période.');

        // Overlapping request
        $data = [
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-08-04',
            'end_date' => '2026-08-10',
        ];

        $this->service->submitRequest($employee, $data, $user->id);
    }
}
