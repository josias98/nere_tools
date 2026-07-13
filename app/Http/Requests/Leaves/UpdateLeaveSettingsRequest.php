<?php

namespace App\Http\Requests\Leaves;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessAdmin() === true;
    }

    public function rules(): array
    {
        return ['monthly_accrual_days' => ['required', 'numeric', 'min:0', 'max:31'], 'accrual_policy' => ['required', 'in:end_of_month,start_of_month,prorated'], 'LEAVE_CERTIFICATE_SIGNATORY_NAME' => ['nullable', 'string', 'max:255'], 'LEAVE_CERTIFICATE_SIGNATORY_TITLE' => ['nullable', 'string', 'max:255'], 'settings_updated_at' => ['nullable', 'date'], 'change_reason' => ['nullable', 'string', 'max:1000']];
    }
}
