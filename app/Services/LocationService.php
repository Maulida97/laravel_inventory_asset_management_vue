<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LocationService
{
    /**
     * Get paginated locations with applied filters and eager loaded relationships.
     */
    public function getPaginatedLocations(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Location::query()
            ->with(['parent', 'children'])
            ->withCount('children');

        // Filter search (code or name)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter type
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter status (active/inactive)
        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filter level (parents only or children only)
        if (! empty($filters['level'])) {
            if ($filters['level'] === 'parent') {
                $query->parents();
            } elseif ($filters['level'] === 'child') {
                $query->childrenOnly();
            }
        }

        // Filter by parent_id
        if (! empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        return $query
            ->orderByRaw('COALESCE(parent_id, id) ASC')
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get active top-level parent locations for selection dropdowns.
     */
    public function getActiveParentOptions(?int $excludeId = null): Collection
    {
        return Location::query()
            ->parents()
            ->active()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type']);
    }

    /**
     * Get location summary statistics for UI dashboard badges.
     */
    public function getStatistics(): array
    {
        return [
            'total' => Location::count(),
            'parents' => Location::parents()->count(),
            'children' => Location::childrenOnly()->count(),
            'active' => Location::active()->count(),
            'inactive' => Location::where('is_active', false)->count(),
        ];
    }

    /**
     * Create a new location record.
     */
    public function createLocation(array $data): Location
    {
        return DB::transaction(function () use ($data) {
            return Location::create([
                'code' => strtoupper(trim($data['code'])),
                'name' => trim($data['name']),
                'type' => $data['type'],
                'parent_id' => ! empty($data['parent_id']) ? (int) $data['parent_id'] : null,
                'address' => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing location record.
     */
    public function updateLocation(Location $location, array $data): Location
    {
        return DB::transaction(function () use ($location, $data) {
            $location->update([
                'code' => strtoupper(trim($data['code'])),
                'name' => trim($data['name']),
                'type' => $data['type'],
                'parent_id' => ! empty($data['parent_id']) ? (int) $data['parent_id'] : null,
                'address' => $data['address'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $location->is_active,
            ]);

            return $location->fresh(['parent', 'children']);
        });
    }

    /**
     * Toggle location active status.
     */
    public function toggleStatus(Location $location): Location
    {
        return DB::transaction(function () use ($location) {
            $location->update([
                'is_active' => ! $location->is_active,
            ]);

            return $location->fresh();
        });
    }
}
