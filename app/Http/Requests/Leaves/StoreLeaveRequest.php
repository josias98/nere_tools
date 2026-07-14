<?php

namespace App\Http\Requests\Leaves;

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

        return self::rulesFor($type, $this->string('start_date')->toString() ?: null);
    }

    public static function rulesFor(?LeaveType $type, ?string $startDate = null): array
    {
        $configuration = $type?->ruleAt(new \DateTimeImmutable($startDate ?: 'now'))?->configuration ?? [];

        return [
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('is_active', true)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_period' => ['nullable', 'in:full,morning,afternoon'],
            'end_period' => ['nullable', 'in:full,morning,afternoon'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'requester_comment' => ['nullable', 'string', 'max:3000'],
            'relationship' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'max:3000'],
            'replacement_needed' => ['nullable', 'boolean'],
            'replacement_employee_id' => ['nullable', Rule::exists('employees', 'id')->where('is_active', true)],
            'location' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'salary_impact' => ['nullable', 'string', 'max:255'],
            'attachments' => [($configuration['requires_attachment'] ?? $type?->requires_attachment) ? 'required' : 'nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return self::sharedMessages();
    }

    public static function sharedMessages(): array
    {
        return [
            'attachments.required' => 'Le justificatif est obligatoire pour ce type de congé.',
            'attachments.*.max' => 'Chaque justificatif doit faire au maximum 10 Mo.',
            'attachments.*.mimes' => 'Les justificatifs doivent être des fichiers PDF, JPG ou PNG.',
        ];
    }
}
