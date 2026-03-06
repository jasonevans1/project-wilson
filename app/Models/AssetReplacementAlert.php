<?php

namespace App\Models;

use App\Enums\ReplacementAlertType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReplacementAlert extends Model
{
    /** @use HasFactory<\Database\Factories\AssetReplacementAlertFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_id',
        'alert_type',
        'sent_at',
        'dismissed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alert_type' => ReplacementAlertType::class,
            'sent_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    /**
     * Get the asset this alert belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
