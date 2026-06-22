<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\User;
use App\Services\TimesheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TimesheetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_allocation_and_generation_work(): void
    {
        Storage::fake('local');

        $service = app(TimesheetService::class);

        $this->assertCount(5, $service->weeks(2026, 4));
        $this->assertSame('02/02/2026', $service->signatureDate(2026, 1)->format('d/m/Y'));

        $user = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $job = Employee::query()->create([
            'first_name' => 'Job',
            'last_name' => 'ZONGO',
            'display_name' => 'Job ZONGO',
            'entity' => 'NERE CAPITAL PARTNERS',
            'location' => 'Ouagadougou',
            'job_title' => 'DG Fonds',
            'analytic_code' => '1.1.1 Personnel technique',
            'ipas_rate' => 0,
            'catal_rate' => 12,
            'ipde_rate' => 0,
            'requires_other_projects' => true,
            'signature_title' => 'Signature du DAF',
            'signatory_name' => 'BAKO/NAGALO A Germaine',
            'is_active' => true,
        ]);

        $columns = $service->columns($job);
        $this->assertSame('Autres projets', $columns[3]['label']);
        $this->assertSame(88.0, $columns[3]['value']);

        $generation = $service->generate([
            'period_type' => 'month',
            'year' => 2026,
            'start_month' => 1,
            'end_month' => 1,
            'employee_ids' => [$job->id],
        ], $user);

        $this->assertSame(1, $generation->pdf_count);
        Storage::disk('local')->assertExists($generation->zip_path);
        Storage::disk('local')->assertExists($generation->files()->first()->file_path);

        $csvGeneration = $service->generateRows([[
            'employee_id' => $job->id,
            'year' => 2026,
            'start_month' => 2,
            'end_month' => 2,
            'entity_label' => 'Nere Capital',
            'signature_date' => '2026-03-02',
            'signatory_name' => 'Responsable CSV',
            'comments_label' => 'Notes',
            'include_comments' => 0,
        ]], $user);

        $this->assertSame(1, $csvGeneration->pdf_count);
        Storage::disk('local')->assertExists($csvGeneration->zip_path);
    }
}
