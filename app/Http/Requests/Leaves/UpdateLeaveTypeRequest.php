<?php

namespace App\Http\Requests\Leaves;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveTypeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('eligibility_rules')) && trim($this->input('eligibility_rules')) !== '') {
            $decoded = json_decode($this->input('eligibility_rules'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['eligibility_rules' => $decoded]);
            }
        }
    }

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
            'maximum_renewals' => ['required', 'integer', 'min:0', 'max:20'], 'requester_scope' => ['required', 'in:employee,admin,both'],
            'eligibility_rules' => ['nullable', 'array'], 'eligibility_rules.minimum_service_months' => ['nullable', 'integer', 'min:0'],
            'eligibility_rules.allowed_entities' => ['nullable', 'array'], 'eligibility_rules.allowed_entities.*' => ['string', 'max:255'],
            'eligibility_rules.allowed_locations' => ['nullable', 'array'], 'eligibility_rules.allowed_locations.*' => ['string', 'max:255'],
            'eligibility_rules.allowed_job_titles' => ['nullable', 'array'], 'eligibility_rules.allowed_job_titles.*' => ['string', 'max:255'],
            'legal_reference' => ['nullable', 'string', 'max:500'], 'effective_from' => ['required', 'date'],
        ];
    }
}
