<?php

namespace App\Livewire\Timesheets;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\TimesheetGeneration;
use App\Modules\Timesheets\Support\TimesheetCsvParser;
use App\Services\TimesheetService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use RuntimeException;
use Throwable;

class Wizard extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public ?string $method = null;

    public string $periodStart = '';

    public string $periodEnd = '';

    public string $zipLabel = '';

    public string $employeeSearch = '';

    public array $rows = [];

    public mixed $csvFile = null;

    public array $csvErrors = [];

    public bool $confirmed = false;

    public ?string $notice = null;

    #[Locked]
    public ?string $generationUuid = null;

    public function mount(): void
    {
        $start = now()->month <= 6 ? now()->startOfYear() : now()->month(7)->startOfMonth();
        $this->periodStart = $start->format('Y-m-d');
        $this->periodEnd = $start->copy()->addMonths(5)->endOfMonth()->format('Y-m-d');
        $this->syncZipLabel();
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->where('is_active', true)
            ->when($this->employeeSearch !== '', function ($query): void {
                $search = '%'.addcslashes($this->employeeSearch, '%_').'%';
                $query->where(fn ($query) => $query->where('display_name', 'like', $search)
                    ->orWhere('first_name', 'like', $search)->orWhere('last_name', 'like', $search));
            })
            ->orderBy('display_name')->limit(12)->get();
    }

    public function chooseMethod(string $method): void
    {
        abort_unless(in_array($method, ['csv', 'manual'], true), 422);
        $this->method = $method;
        $this->step = 2;
        $this->notice = null;
    }

    public function addEmployee(int $employeeId): void
    {
        $this->authorizeAction();
        if (collect($this->rows)->contains(fn (array $row): bool => (int) $row['employee_id'] === $employeeId)) {
            return;
        }

        $employee = Employee::query()->where('is_active', true)->findOrFail($employeeId);
        $this->rows[] = $this->rowFor($employee);
        $this->employeeSearch = '';
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function duplicateAllocation(int $index): void
    {
        $source = $this->rows[$index] ?? null;
        if (! $source) {
            return;
        }
        foreach ($this->rows as $rowIndex => $row) {
            if ($rowIndex !== $index) {
                foreach (['ipas_rate', 'catal_rate', 'ipde_rate', 'other_projects_rate', 'analytic_code'] as $field) {
                    $this->rows[$rowIndex][$field] = $source[$field] ?? null;
                }
            }
        }
        $this->notice = 'Répartition appliquée aux autres collaborateurs.';
    }

    public function analyzeCsv(TimesheetCsvParser $parser): void
    {
        $this->authorizeAction();
        $this->csvErrors = [];
        $this->validate(['csvFile' => ['required', 'file', 'mimes:csv,txt', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel', 'max:1024']]);
        try {
            RateLimiter::attempt('timesheet-upload:'.auth()->id(), 10, function () use ($parser): void {
                $this->rows = $parser->parse($this->csvFile->getRealPath());
            }, 60) || throw ValidationException::withMessages(['csvFile' => 'Trop de téléversements. Réessayez dans une minute.']);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['csvFile' => $exception->getMessage()]);
        }
        AuditLog::query()->create(['user_id' => auth()->id(), 'action' => 'timesheet.csv_analyzed', 'metadata' => ['rows' => count($this->rows)]]);
        $this->csvFile = null;
        $this->notice = count($this->rows).' ligne(s) analysée(s). Vous pouvez les corriger.';
        $this->step = 4;
    }

    public function next(): void
    {
        $this->authorizeAction();
        if ($this->step === 2) {
            $this->validateContext();
            $this->step = 3;

            return;
        }
        if ($this->step === 3 && $this->method === 'manual') {
            $this->validateRows();
            $this->step = 4;

            return;
        }
        if ($this->step === 4) {
            $this->validateRows();
            $this->step = 5;
        }
    }

    public function previous(): void
    {
        $this->step = max(1, $this->step - 1);
        if ($this->method === 'csv' && $this->step === 3 && $this->rows !== []) {
            $this->step = 2;
        }
    }

    public function generate(TimesheetService $service): void
    {
        $this->authorizeAction();
        $this->validateContext();
        $this->validateRows();
        $this->validate(['confirmed' => ['accepted']], ['confirmed.accepted' => 'Confirmez la génération du lot.']);

        $key = 'timesheet-generation:'.auth()->id().':'.hash('sha256', json_encode([$this->periodStart, $this->periodEnd, $this->rows]));
        if ($uuid = Cache::get($key.':result')) {
            $this->generationUuid = $uuid;
            $this->step = 6;
            $this->notice = 'Ce lot avait déjà été généré. Le livrable existant a été retrouvé.';

            return;
        }
        if (! RateLimiter::attempt('timesheet-generate:'.auth()->id(), 5, fn (): bool => true, 60)) {
            throw ValidationException::withMessages(['generation' => 'Trop de générations. Réessayez dans une minute.']);
        }

        $lock = Cache::lock($key, 120);
        if (! $lock->get()) {
            throw ValidationException::withMessages(['generation' => 'Ce lot est déjà en cours de génération.']);
        }
        try {
            $generation = $service->generateRows($this->rows, auth()->user(), [
                'period_start' => $this->periodStart,
                'period_end' => $this->periodEnd,
                'zip_label' => $this->zipLabel,
            ]);
            $this->generationUuid = $generation->uuid;
            Cache::put($key.':result', $generation->uuid, now()->addMinutes(10));
            $this->step = 6;
            $this->notice = 'Le lot a été généré avec succès.';
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['generation' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['generation' => 'La génération a échoué. Réessayez sans modifier la page.']);
        } finally {
            $lock->release();
        }
    }

    public function resetWizard(): void
    {
        $this->reset(['method', 'rows', 'csvFile', 'csvErrors', 'confirmed', 'notice', 'generationUuid']);
        $this->step = 1;
    }

    public function syncZipLabel(): void
    {
        if ($this->periodStart && $this->periodEnd) {
            $this->zipLabel = 'Feuilles_de_temps_'.str_replace('-', '', $this->periodStart).'_'.str_replace('-', '', $this->periodEnd);
        }
    }

    public function render()
    {
        return view('livewire.timesheets.wizard', [
            'generation' => $this->generationUuid ? TimesheetGeneration::query()->where('uuid', $this->generationUuid)->first() : null,
        ]);
    }

    private function validateContext(): void
    {
        $this->validate([
            'method' => ['required', 'in:csv,manual'],
            'periodStart' => ['required', 'date'],
            'periodEnd' => ['required', 'date', 'after_or_equal:periodStart'],
            'zipLabel' => ['required', 'string', 'max:120', 'regex:/^[\pL\pN _.-]+$/u'],
        ]);
    }

    private function validateRows(): void
    {
        $this->validate([
            'rows' => ['required', 'array', 'min:1', 'max:500'],
            'rows.*.employee_id' => ['required', 'integer', 'distinct', Rule::exists('employees', 'id')->where('is_active', true)],
            'rows.*.first_name' => ['nullable', 'string', 'max:80'],
            'rows.*.last_name' => ['nullable', 'string', 'max:80'],
            'rows.*.entity_name' => ['nullable', 'string', 'max:80'],
            'rows.*.ipas_rate' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.catal_rate' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.ipde_rate' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.other_projects_rate' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.analytic_code' => ['nullable', 'string', 'max:120'],
            'rows.*.location' => ['nullable', 'string', 'max:120'],
            'rows.*.signatory_name' => ['nullable', 'string', 'max:120'],
        ]);
        foreach ($this->rows as $index => $row) {
            $total = collect(['ipas_rate', 'catal_rate', 'ipde_rate', 'other_projects_rate'])->sum(fn (string $key): float => (float) ($row[$key] ?? 0));
            if (abs($total - 100) > 0.01) {
                throw ValidationException::withMessages(["rows.$index.other_projects_rate" => 'Le total doit être égal à 100 % (actuellement '.round($total, 2).' %).']);
            }
        }
    }

    private function rowFor(Employee $employee): array
    {
        $known = (float) $employee->ipas_rate + (float) $employee->catal_rate + (float) $employee->ipde_rate;

        return [
            'selected' => 1, 'employee_id' => $employee->id, 'first_name' => $employee->first_name,
            'last_name' => $employee->last_name, 'entity_name' => $employee->entity, 'function_title' => $employee->job_title,
            'ipas_rate' => $employee->ipas_rate, 'catal_rate' => $employee->catal_rate, 'ipde_rate' => $employee->ipde_rate,
            'other_projects_rate' => max(0, 100 - $known), 'analytic_code' => $employee->analytic_code,
            'location' => $employee->location, 'employee_signature_name' => $employee->name(),
            'signatory_name' => $employee->signatory_name, 'signature_title' => $employee->signature_title,
            'comments_label' => 'Commentaires / Détails', 'include_comments' => 1,
        ];
    }

    public function rowTotal(int $index): float
    {
        $row = $this->rows[$index] ?? [];

        return collect(['ipas_rate', 'catal_rate', 'ipde_rate', 'other_projects_rate'])
            ->sum(fn (string $key): float => (float) ($row[$key] ?? 0));
    }

    private function authorizeAction(): void
    {
        abort_unless(auth()->check() && auth()->user()->canAccessTool('timesheets'), 403);
    }
}
