<?php

namespace Tests\Unit\Leaves;

use App\Services\Leaves\LeaveDayCountService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class LeaveDayCountServiceTest extends TestCase
{
    private LeaveDayCountService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeaveDayCountService();
    }

    public function test_same_day_counts_as_one()
    {
        $date = Carbon::parse('2026-08-01');
        $this->assertEquals(1, $this->service->calculateDays($date, $date));
    }

    public function test_friday_to_monday_counts_four_days()
    {
        $friday = Carbon::parse('2026-08-07'); // a friday
        $monday = Carbon::parse('2026-08-10'); // next monday
        $this->assertEquals(4, $this->service->calculateDays($friday, $monday));
    }

    public function test_weekends_are_counted()
    {
        $saturday = Carbon::parse('2026-08-08');
        $sunday = Carbon::parse('2026-08-09');
        $this->assertEquals(2, $this->service->calculateDays($saturday, $sunday));
    }
}
