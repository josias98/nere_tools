<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveHoliday extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_recurring' => 'boolean'];
    }
}
