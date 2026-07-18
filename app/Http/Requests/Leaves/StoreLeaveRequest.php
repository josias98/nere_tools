<?php

namespace App\Http\Requests\Leaves;

use App\Enums\LeaveUnit;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->employee;
    }

    public function rules(): array
    {
        $type = LeaveType::query()->find($this->integer('leave_type_id'));
        $startDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->string('start_date')->toString()) ?: new \DateTimeImmutable;
        $configuration = $type?->ruleAt($startDate)?->configuration ?? [];
        $requiresTime = ($configuration['unit'] ?? $type?->unit?->value) === LeaveUnit::Hour->value;

        return [
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('is_active', true)],
            'renewal_of_request_id' => ['nullable', 'integer', 'exists:leave_requests,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => [Rule::requiredIf($requiresTime), 'nullable', 'date_format:H:i'],
            'end_time' => [Rule::requiredIf($requiresTime), 'nullable', 'date_format:H:i'],
            'requester_comment' => ['nullable', 'string', 'max:3000'],
            'relationship' => [Rule::requiredIf($type?->slug === 'family_event'), 'nullable', 'string', 'max:120'],
            'reason' => [Rule::requiredIf($type?->slug === 'other_absence'), 'nullable', 'string', 'max:3000'],
            'replacement_needed' => ['nullable', 'boolean'],
            'replacement_employee_id' => ['nullable', 'exists:employees,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'salary_impact' => ['nullable', 'string', 'max:255'],
            'attachments' => [($configuration['requires_attachment'] ?? $type?->requires_attachment) ? 'required' : 'nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.required' => "L'heure de début est obligatoire pour une demande horaire.",
            'end_time.required' => "L'heure de fin est obligatoire pour une demande horaire.",
            'relationship.required' => 'Le lien de parenté est obligatoire pour un congé lié à un décès.',
            'reason.required' => 'Le motif est obligatoire pour un autre type de congé.',
            'attachments.required' => 'Le justificatif est obligatoire pour ce type de congé.',
            'attachments.*.max' => 'Chaque justificatif doit faire au maximum 10 Mo.',
            'attachments.*.mimes' => 'Les justificatifs doivent être des fichiers PDF, JPG ou PNG.',
        ];
    }
}
