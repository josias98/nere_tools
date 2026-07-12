<?php

namespace App\Http\Requests\Leaves;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'category' => ['required', 'in:leave,permission,authorization,suspension'],
            'unit' => ['required', 'in:calendar_day,working_day,hour,week,month'], 'is_paid' => ['nullable', 'boolean'],
            'counts_against_balance' => ['nullable', 'boolean'], 'requires_attachment' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'],
            'quota' => ['nullable', 'numeric', 'min:0'], 'maximum_duration' => ['nullable', 'numeric', 'min:0'], 'notice_hours' => ['required', 'integer', 'min:0'],
            'legal_reference' => ['nullable', 'string', 'max:500'], 'effective_from' => ['required', 'date'],
        ];
    }
}
