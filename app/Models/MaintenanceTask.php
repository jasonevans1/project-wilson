<?php

namespace App\Models;

use App\Enums\RecurrenceUnit;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaintenanceTask extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceTaskFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'asset_id',
        'name',
        'description',
        'recurrence_unit',
        'recurrence_count',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recurrence_unit' => RecurrenceUnit::class,
            'recurrence_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the asset this task belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get all occurrences for this task.
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(MaintenanceOccurrence::class);
    }

    /**
     * Get the single pending (incomplete) occurrence for this task.
     */
    public function pendingOccurrence(): HasOne
    {
        return $this->hasOne(MaintenanceOccurrence::class)->whereNull('completed_at');
    }

    /**
     * Scope a query to only active tasks.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
