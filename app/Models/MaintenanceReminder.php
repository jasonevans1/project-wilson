<?php

namespace App\Models;

use App\Enums\ReminderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceReminder extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceReminderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'maintenance_occurrence_id',
        'reminder_type',
        'sent_at',
        'snoozed_until',
        'snooze_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reminder_type' => ReminderType::class,
            'sent_at' => 'datetime',
            'snoozed_until' => 'date',
            'snooze_count' => 'integer',
        ];
    }

    /**
     * Get the user this reminder belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the maintenance occurrence this reminder is for.
     */
    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(MaintenanceOccurrence::class, 'maintenance_occurrence_id');
    }
}
