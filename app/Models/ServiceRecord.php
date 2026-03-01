<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRecord extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceRecordFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'asset_id',
        'service_date',
        'service_type',
        'description',
        'provider_name',
        'cost',
        'under_warranty',
        'warranty_expires_on',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'service_type' => ServiceType::class,
            'cost' => 'decimal:2',
            'under_warranty' => 'boolean',
            'warranty_expires_on' => 'date',
        ];
    }

    /**
     * Get the user that owns this service record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the asset this service record belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
