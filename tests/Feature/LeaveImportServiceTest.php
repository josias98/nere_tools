<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Services\Leaves\LeaveImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LeaveImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_matches_by_employee_id_email_and_display_name(): void
    {
        $byId = $this->employee('id@nere.test', 'By Id');
        $byEmail = $this->employee('email@nere.test', 'By Email');
        $byName = $this->employee('name@nere.test', 'By Name');

        $report = $this->import(implode("\n", [
            'employee_id,email,display_name,solde_restant,total_acquis,total_pris,notes',
            "{$byId->id},,,12,20,8,via id",
            ',EMAIL@NERE.TEST,,8,18,10,via email',
            ',,By Name,6,16,10,via name',
        ]));

        $this->assertSame(3, $report['rows_imported']);
        $this->assertSame(12.0, (float) LeaveBalance::query()->where('employee_id', $byId->id)->value('initial_remaining_days'));
        $this->assertSame(8.0, (float) LeaveBalance::query()->where('employee_id', $byEmail->id)->value('initial_remaining_days'));
        $this->assertSame(6.0, (float) LeaveBalance::query()->where('employee_id', $byName->id)->value('initial_remaining_days'));
    }

    public function test_semicolon_csv_decimal_comma_errors_and_update_existing_balance(): void
    {
        $employee = $this->employee('known@nere.test', 'Known Person');
        $this->employee('dup1@nere.test', 'Ambiguous Person');
        $this->employee('dup2@nere.test', 'Ambiguous Person');
        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-31',
            'initial_remaining_days' => 1,
        ]);

        $report = $this->import(implode("\n", [
            'email;display_name;solde;acquis;pris',
            'known@nere.test;;22,5;30,5;8',
            ';Ambiguous Person;9;9;0',
            'missing@nere.test;;7;7;0',
            ';;5;5;0',
            ';Known Person;-1;0;0',
        ]));

        $this->assertSame(1, $report['rows_updated']);
        $this->assertSame(4, $report['rows_skipped']);
        $this->assertSame(22.5, (float) $employee->leaveBalances()->first()->fresh()->initial_remaining_days);
        $this->assertStringContainsString('Nom ambigu', implode(' ', $report['errors']));
        $this->assertStringContainsString('Employe introuvable', implode(' ', $report['errors']));
        $this->assertStringContainsString('Ligne sans identifiant', implode(' ', $report['errors']));
        $this->assertStringContainsString('solde negatif', implode(' ', $report['errors']));
    }

    private function import(string $csv): array
    {
        return app(LeaveImportService::class)->importCsv(
            UploadedFile::fake()->createWithContent('balances.csv', $csv),
            '2026-01-31',
        );
    }

    private function employee(string $email, string $name): Employee
    {
        return Employee::query()->create([
            'first_name' => strtok($name, ' '),
            'last_name' => trim(strstr($name, ' ') ?: 'Test'),
            'display_name' => $name,
            'email' => $email,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => 'TEST',
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);
    }
}
