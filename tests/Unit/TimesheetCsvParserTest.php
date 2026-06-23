<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Modules\Timesheets\Support\TimesheetCsvParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetCsvParserTest extends TestCase
{
    use RefreshDatabase;

    public function test_parser_matches_active_employee_and_hydrates_defaults(): void
    {
        Employee::query()->create([
            'first_name' => 'Job',
            'last_name' => 'ZONGO',
            'display_name' => 'Job ZONGO',
            'entity' => 'NERE CAPITAL PARTNERS',
            'location' => 'Ouagadougou',
            'job_title' => 'DG Fonds',
            'analytic_code' => '1.1.1 Personnel technique',
            'catal_rate' => 12,
            'ipde_rate' => 0,
            'is_active' => true,
        ]);

        $path = storage_path('framework/testing/timesheet-parser.csv');
        file_put_contents($path, implode("\n", [
            'prenom;nom;catal_rate;entity_name',
            'Job;ZONGO;12;Nere Capital',
        ]));

        $rows = app(TimesheetCsvParser::class)->parse($path);

        $this->assertCount(1, $rows);
        $this->assertSame('Job', $rows[0]['first_name']);
        $this->assertSame('ZONGO', $rows[0]['last_name']);
        $this->assertSame('Nere Capital', $rows[0]['entity_name']);
        $this->assertSame('Ouagadougou', $rows[0]['location']);
    }
}
