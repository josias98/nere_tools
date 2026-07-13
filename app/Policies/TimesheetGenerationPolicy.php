<?php

namespace App\Policies;

use App\Models\TimesheetGeneration;
use App\Models\User;

class TimesheetGenerationPolicy
{
    public function view(User $user, TimesheetGeneration $generation): bool
    {
        return $generation->generated_by_user_id === $user->id || $user->role === User::ROLE_ADMIN;
    }
}
