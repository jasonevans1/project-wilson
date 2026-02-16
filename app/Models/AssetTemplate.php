<?php

namespace App\Models;

use App\Enums\AssetCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\AssetTemplateFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_group_id',
        'name',
        'description',
        'category',
        'location',
        'display_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => AssetCategory::class,
        ];
    }

    /**
     * Get the template group this template belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TemplateGroup::class, 'template_group_id');
    }
}
