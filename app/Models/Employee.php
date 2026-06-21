<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'first_name',
    'last_name',
    'display_name',
    'entity',
    'location',
    'job_title',
    'analytic_code',
    'ipas_rate',
    'catal_rate',
    'ipde_rate',
    'is_active',
    'requires_other_projects',
    'signature_title',
    'signatory_name',
])]
class Employee extends Model
{
    protected function casts(): array
    {
        return [
            'ipas_rate' => 'float',
            'catal_rate' => 'float',
            'ipde_rate' => 'float',
            'is_active' => 'boolean',
            'requires_other_projects' => 'boolean',
        ];
    }

    public function name(): string
    {
        return $this->display_name ?: trim($this->first_name.' '.$this->last_name);
    }
}
