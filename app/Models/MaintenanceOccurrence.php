<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceOccurrence extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceOccurrenceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maintenance_task_id',
        'due_date',
        'completed_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the maintenance task this occurrence belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTask::class, 'maintenance_task_id');
    }

    /**
     * Determine whether this occurrence is overdue (past due and not yet completed).
     */
    public function isOverdue(): bool
    {
        return $this->completed_at === null && $this->due_date->lt(today());
    }

    /**
     * Determine whether this occurrence is pending (not yet completed).
     */
    public function isPending(): bool
    {
        return $this->completed_at === null;
    }
}
