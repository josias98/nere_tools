<?php

namespace Tests\Unit\Leaves;

use App\Models\Employee;
use App\Models\LeaveSetting;
use App\Services\Leaves\LeaveAccrualService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveAccrualServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeaveAccrualService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeaveAccrualService();

        LeaveSetting::create([
            'key' => 'accrual_policy',
            'value' => 'end_of_month',
            'value_type' => 'string'
        ]);

        LeaveSetting::create([
            'key' => 'monthly_accrual_days',
            'value' => '2.5',
            'value_type' => 'decimal'
        ]);
    }

    public function test_monthly_accrual_is_two_point_five()
    {
        $employee = new Employee();
        $employee->leave_eligible = true;

        $referenceDate = Carbon::parse('2026-01-01');
        $targetDate = Carbon::parse('2026-03-31'); // 3 full months passed: Jan, Feb, Mar

        $accrued = $this->service->calculateAccruedDays($employee, $referenceDate, $targetDate);

        // Expect 3 months * 2.5 = 7.5 days
        $this->assertEquals(7.5, $accrued);
    }
}
