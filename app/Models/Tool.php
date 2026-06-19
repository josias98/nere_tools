<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'route', 'status', 'required_role', 'display_order'])]
class Tool extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMING_SOON = 'coming_soon';

    public const STATUS_DISABLED = 'disabled';
}
