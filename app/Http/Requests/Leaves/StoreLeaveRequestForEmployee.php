<?php

namespace App\Http\Requests\Leaves;

use Illuminate\Validation\Rule;

class StoreLeaveRequestForEmployee extends StoreLeaveRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')
                    ->where('is_active', true)
                    ->where('leave_eligible', true),
            ],
            ...parent::rules(),
        ];
    }
}
