<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateGroup extends Model
{
    /** @use HasFactory<\Database\Factories\TemplateGroupFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'display_order',
    ];

    /**
     * Get the asset templates belonging to this group.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(AssetTemplate::class);
    }
}
