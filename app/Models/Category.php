<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public const TYPE_INVENTORY = 'inventory';

    public const TYPE_ASSET = 'asset';

    public const TYPE_BOTH = 'both';

    public const TYPES = [
        self::TYPE_INVENTORY,
        self::TYPE_ASSET,
        self::TYPE_BOTH,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter categories by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Get all items classified under this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
