<?php

namespace App\Enums;

enum ReminderType: string
{
    case ThirtyDay = '30_day';
    case SevenDay = '7_day';
    case OneDay = '1_day';

    public function label(): string
    {
        return match ($this) {
            self::ThirtyDay => '30-Day Reminder',
            self::SevenDay => '7-Day Reminder',
            self::OneDay => '1-Day Reminder',
        };
    }

    /**
     * Get the number of days before due date for this reminder type.
     */
    public function daysBeforeDue(): int
    {
        return match ($this) {
            self::ThirtyDay => 30,
            self::SevenDay => 7,
            self::OneDay => 1,
        };
    }
}
