<?php

namespace App\Enums;

enum LeaveUnit: string
{
    case CalendarDay = 'calendar_day';
    case WorkingDay = 'working_day';
    case Hour = 'hour';
    case Week = 'week';
    case Month = 'month';

    public function label(): string
    {
        return match ($this) {
            self::CalendarDay => 'jour calendaire',
            self::WorkingDay => 'jour ouvrable',
            self::Hour => 'heure',
            self::Week => 'semaine',
            self::Month => 'mois',
        };
    }
}
