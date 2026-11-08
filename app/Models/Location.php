<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    /**
     * Resolvers for calculating stock count and active assets (used for testing and forward-compatibility).
     *
     * @var array<string, callable|null>
     */
    protected static array $stockResolvers = [];

    /**
     * @var array<string, callable|null>
     */
    protected static array $assetResolvers = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'type',
        'address',
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
     * Get the parent location (if this is a sub-location/child).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child locations (sub-locations belonging to this location).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Scope a query to only include active locations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include top-level parent locations.
     */
    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include child/sub-locations.
     */
    public function scopeChildrenOnly(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Alias scope to only include sub-locations.
     */
    public function scopeSubLocations(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to only include locations eligible to store inventory stock directly (BR-L01).
     * An eligible location is an active child location OR an active parent location without any children.
     */
    public function scopeAcceptingStock(Builder $query): Builder
    {
        return $query->active()->where(function ($q) {
            $q->childrenOnly()
                ->orWhere(function ($parentQuery) {
                    $parentQuery->parents()->doesntHave('children');
                });
        });
    }

    /**
     * Check if the current location is a top-level parent location.
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if the current location is a child/sub-location.
     */
    public function isChild(): bool
    {
        return ! is_null($this->parent_id);
    }

    /**
     * Determine whether this location can directly receive inventory stock (BR-L01).
     * A parent can only hold stock if it currently has no sub-locations.
     */
    public function canAcceptDirectStock(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isParent()) {
            return $this->children()->count() === 0;
        }

        return true;
    }

    /**
     * Get the total inventory item stock currently stored at this location.
     * Forward-compatible with the stock_ledger table (Phase 4).
     */
    public function currentStockCount(): int
    {
        if (isset(static::$stockResolvers['default'])) {
            return (int) call_user_func(static::$stockResolvers['default'], $this);
        }

        if (Schema::hasTable('stock_ledger')) {
            return (int) DB::table('stock_ledger')
                ->where('location_id', $this->id)
                ->sum('quantity');
        }

        return 0;
    }

    /**
     * Get the total count of active assets currently located at this location.
     * Forward-compatible with the assets table (Phase 5).
     */
    public function activeAssetsCount(): int
    {
        if (isset(static::$assetResolvers['default'])) {
            return (int) call_user_func(static::$assetResolvers['default'], $this);
        }

        if (Schema::hasTable('assets')) {
            return (int) DB::table('assets')
                ->where('location_id', $this->id)
                ->whereIn('status', ['in_use', 'available', 'in_maintenance', 'booked'])
                ->count();
        }

        return 0;
    }

    /**
     * Determine whether this location currently holds any stock items or active assets.
     * If $includeChildren is true and this is a parent, also checks all child locations.
     */
    public function hasItemsOrAssets(bool $includeChildren = true): bool
    {
        if ($this->currentStockCount() > 0 || $this->activeAssetsCount() > 0) {
            return true;
        }

        if ($includeChildren && $this->isParent()) {
            // Check direct children
            $children = $this->children;
            foreach ($children as $child) {
                if ($child->hasItemsOrAssets(false)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether this location is completely empty of items and assets.
     */
    public function isEmpty(bool $includeChildren = true): bool
    {
        return ! $this->hasItemsOrAssets($includeChildren);
    }

    /**
     * Register a resolver callback to calculate stock for testing/mocking.
     */
    public static function resolveStockUsing(?callable $resolver): void
    {
        static::$stockResolvers['default'] = $resolver;
    }

    /**
     * Register a resolver callback to calculate active assets for testing/mocking.
     */
    public static function resolveAssetsUsing(?callable $resolver): void
    {
        static::$assetResolvers['default'] = $resolver;
    }
}
