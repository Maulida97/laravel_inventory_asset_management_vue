<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

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
}
