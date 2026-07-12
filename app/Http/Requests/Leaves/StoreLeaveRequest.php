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
        $configuration = $type?->ruleAt(new \DateTimeImmutable($this->string('start_date')->toString() ?: 'now'))?->configuration ?? [];

        return [
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('is_active', true)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'requester_comment' => ['nullable', 'string', 'max:3000'],
            'relationship' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'max:3000'],
            'replacement_needed' => ['nullable', 'boolean'],
            'replacement_employee_id' => ['nullable', 'exists:employees,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'salary_impact' => ['nullable', 'string', 'max:255'],
            'attachments' => [($configuration['requires_attachment'] ?? $type?->requires_attachment) ? 'required' : 'nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }
}
