<?php

namespace App\Models;

use App\Enums\LeaveCategory;
use App\Enums\LeaveUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'category' => LeaveCategory::class,
            'unit' => LeaveUnit::class,
            'is_paid' => 'boolean',
            'counts_against_balance' => 'boolean',
            'requires_attachment' => 'boolean',
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'conditional_fields' => 'array',
            'eligibility_rules' => 'array',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(LeaveRule::class)->orderByDesc('version');
    }

    public function ruleAt(\DateTimeInterface $date): ?LeaveRule
    {
        return $this->rules()->whereDate('effective_from', '<=', $date)->where(fn ($q) => $q->whereNull('effective_until')->orWhereDate('effective_until', '>=', $date))->first();
    }
}
