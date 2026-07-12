<?php

namespace App\Enums;

enum LeaveCategory: string
{
    case Leave = 'leave';
    case Permission = 'permission';
    case Authorization = 'authorization';
    case Suspension = 'suspension';
}
