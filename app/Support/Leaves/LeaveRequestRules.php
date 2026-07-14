<?php

namespace App\Support\Leaves;

use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Validation\Rule;

class LeaveRequestRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(array $input = [], string $prefix = '', ?Employee $employee = null): array
    {
        $typeId = (int) ($input['leave_type_id'] ?? 0);
        $start = (string) ($input['start_date'] ?? '');
        $type = $typeId > 0 ? LeaveType::query()->find($typeId) : null;
        $configuration = $type?->ruleAt(new \DateTimeImmutable($start !== '' ? $start : 'now'))?->configuration ?? [];
        $requiresAttachment = (bool) (($configuration['requires_attachment'] ?? null) ?? $type?->requires_attachment);

        $key = fn (string $name): string => $prefix.$name;

        return [
            $key('leave_type_id') => ['required', Rule::exists('leave_types', 'id')->where('is_active', true)],
            $key('start_date') => ['required', 'date'],
            $key('end_date') => ['required', 'date', 'after_or_equal:'.$key('start_date')],
            $key('requester_comment') => ['nullable', 'string', 'max:3000'],
            $key('relationship') => ['nullable', 'string', 'max:120'],
            $key('reason') => ['nullable', 'string', 'max:3000'],
            $key('replacement_needed') => ['nullable', 'boolean'],
            $key('replacement_employee_id') => [
                'nullable',
                Rule::exists('employees', 'id')->where('is_active', true),
                $employee ? Rule::notIn([$employee->id]) : 'nullable',
            ],
            $key('location') => ['nullable', 'string', 'max:255'],
            $key('contact') => ['nullable', 'string', 'max:255'],
            $key('salary_impact') => ['nullable', 'string', 'max:255'],
            $key('attachments') => [$requiresAttachment ? 'required' : 'nullable', 'array', 'max:5'],
            $key('attachments.*') => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'attachments.required' => 'Le justificatif est obligatoire pour ce type de conge.',
            'attachments.*.max' => 'Chaque justificatif doit faire au maximum 10 Mo.',
            'attachments.*.mimes' => 'Les justificatifs doivent etre des fichiers PDF, JPG ou PNG.',
            'form.attachments.required' => 'Le justificatif est obligatoire pour ce type de conge.',
            'form.attachments.*.max' => 'Chaque justificatif doit faire au maximum 10 Mo.',
            'form.attachments.*.mimes' => 'Les justificatifs doivent etre des fichiers PDF, JPG ou PNG.',
        ];
    }
}
