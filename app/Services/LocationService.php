<?php

namespace App\Services;

use App\Exceptions\LocationBusinessRuleException;
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
     * Create a new location record with hierarchy validation.
     *
     * @throws LocationBusinessRuleException
     */
    public function createLocation(array $data): Location
    {
        $parentId = ! empty($data['parent_id']) ? (int) $data['parent_id'] : null;

        if ($parentId !== null) {
            $parent = Location::find($parentId);
            if ($parent && ! $parent->is_active) {
                throw new LocationBusinessRuleException(
                    "Lokasi induk {$parent->name} sedang nonaktif. Aktifkan lokasi induk terlebih dahulu sebelum menambahkan sub-lokasi."
                );
            }
        }

        return DB::transaction(function () use ($data, $parentId) {
            return Location::create([
                'code' => strtoupper(trim($data['code'])),
                'name' => trim($data['name']),
                'type' => $data['type'],
                'parent_id' => $parentId,
                'address' => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing location record with business rule validations (BR-L03, BR-L05).
     *
     * @throws LocationBusinessRuleException
     */
    public function updateLocation(Location $location, array $data): Location
    {
        $rawParentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $location->parent_id;
        $newParentId = ! empty($rawParentId) ? (int) $rawParentId : null;
        $oldParentId = $location->parent_id ? (int) $location->parent_id : null;

        // BR-L05: Moving child location to another parent or converting to top-level parent requires empty location
        if ($location->isChild() && $newParentId !== $oldParentId) {
            if (! $location->isEmpty(false)) {
                throw new LocationBusinessRuleException(
                    "Sub-lokasi {$location->name} hanya dapat dipindahkan atau diubah strukturnya jika dalam keadaan kosong (tidak memiliki barang atau aset aktif) (BR-L05)."
                );
            }
        }

        // BR-L03: Deactivating location requires empty items and active assets
        if (array_key_exists('is_active', $data)) {
            $newIsActive = (bool) $data['is_active'];

            if ($location->is_active && ! $newIsActive) {
                if (! $location->isEmpty(true)) {
                    throw new LocationBusinessRuleException(
                        "Lokasi {$location->name} ({$location->code}) tidak dapat dinonaktifkan karena masih memiliki barang atau aset aktif (BR-L03)."
                    );
                }
            }

            // Activating child when parent is inactive is prohibited
            if (! $location->is_active && $newIsActive) {
                $targetParentId = $newParentId ?? $oldParentId;
                if ($targetParentId !== null) {
                    $parent = Location::find($targetParentId);
                    if ($parent && ! $parent->is_active) {
                        throw new LocationBusinessRuleException(
                            "Sub-lokasi {$location->name} tidak dapat diaktifkan karena lokasi induknya ({$parent->name}) masih berstatus nonaktif."
                        );
                    }
                }
            }
        }

        return DB::transaction(function () use ($location, $data, $newParentId) {
            $wasActive = $location->is_active;
            $willBeActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $location->is_active;

            $location->update([
                'code' => strtoupper(trim($data['code'])),
                'name' => trim($data['name']),
                'type' => $data['type'],
                'parent_id' => $newParentId,
                'address' => $data['address'] ?? null,
                'is_active' => $willBeActive,
            ]);

            // Cascade deactivation: if parent is deactivated, deactivate its children
            if ($location->isParent() && $wasActive && ! $willBeActive) {
                $location->children()->update(['is_active' => false]);
            }

            return $location->fresh(['parent', 'children']);
        });
    }

    /**
     * Toggle location active status with business rule protection (BR-L03).
     *
     * @throws LocationBusinessRuleException
     */
    public function toggleStatus(Location $location): Location
    {
        // 1. If currently active, we are deactivating: verify BR-L03
        if ($location->is_active) {
            if (! $location->isEmpty(true)) {
                throw new LocationBusinessRuleException(
                    "Lokasi {$location->name} ({$location->code}) tidak dapat dinonaktifkan karena masih memiliki barang atau aset aktif (BR-L03)."
                );
            }
        } else {
            // 2. If currently inactive, we are activating: child cannot be active if parent is inactive
            if ($location->isChild()) {
                $parent = $location->parent;
                if ($parent && ! $parent->is_active) {
                    throw new LocationBusinessRuleException(
                        "Sub-lokasi {$location->name} tidak dapat diaktifkan karena lokasi induknya ({$parent->name}) masih berstatus nonaktif."
                    );
                }
            }
        }

        return DB::transaction(function () use ($location) {
            $newStatus = ! $location->is_active;
            $location->update([
                'is_active' => $newStatus,
            ]);

            // If a parent is deactivated, cascade deactivation to its children
            if ($location->isParent() && ! $newStatus) {
                $location->children()->update([
                    'is_active' => false,
                ]);
            }

            return $location->fresh(['parent', 'children']);
        });
    }
}
