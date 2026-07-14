<?php

namespace App\Livewire\Leaves\Forms;

use App\Models\Employee;
use App\Support\Leaves\LeaveRequestRules;
use Livewire\Form;

class LeaveRequestWizardForm extends Form
{
    public ?int $leave_type_id = null;

    public string $start_date = '';

    public string $end_date = '';

    public ?string $requester_comment = null;

    public ?string $relationship = null;

    public ?string $reason = null;

    public bool $replacement_needed = false;

    public ?int $replacement_employee_id = null;

    public string $replacement_search = '';

    public ?string $location = null;

    public ?string $contact = null;

    public ?string $salary_impact = null;

    public array $attachments = [];

    public function payload(): array
    {
        return [
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'requester_comment' => $this->requester_comment,
            'relationship' => $this->relationship,
            'reason' => $this->reason,
            'replacement_needed' => $this->replacement_needed,
            'replacement_employee_id' => $this->replacement_needed ? $this->replacement_employee_id : null,
            'replacement_search' => $this->replacement_search,
            'location' => $this->location,
            'contact' => $this->contact,
            'salary_impact' => $this->salary_impact,
            'attachments' => $this->attachments,
        ];
    }

    public function draftPayload(): array
    {
        return array_diff_key($this->payload(), ['attachments' => true]);
    }

    public function fillFromDraft(array $payload): void
    {
        foreach ($this->draftPayload() as $key => $_) {
            if (array_key_exists($key, $payload)) {
                $this->{$key} = $payload[$key];
            }
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return LeaveRequestRules::rules($this->payload(), 'form.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rulesForStep(int $step, ?Employee $employee = null): array
    {
        $rules = LeaveRequestRules::rules($this->payload(), '', $employee);

        return match ($step) {
            1 => array_intersect_key($rules, ['leave_type_id' => true]),
            2 => array_intersect_key($rules, [
                'leave_type_id' => true,
                'start_date' => true,
                'end_date' => true,
            ]),
            3 => $rules,
            4 => $rules,
            default => [],
        };
    }
}
