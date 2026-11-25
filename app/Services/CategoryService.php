<?php

namespace App\Services;

use App\Models\Category;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategoryService
{
    /**
     * Get paginated categories with search, type, and active status filters.
     */
    public function getPaginatedCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Category::query();

        if ($this->hasItemsTable()) {
            $query->withCount('items');
        } else {
            $query->select('categories.*')->selectRaw('0 as items_count');
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['type']) && in_array($filters['type'], Category::TYPES, true)) {
            $query->byType($filters['type']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query
            ->orderBy('name', 'asc')
            ->orderBy('code', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new category inside a database transaction.
     */
    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            return Category::create([
                'code' => strtoupper(trim($data['code'])),
                'name' => trim($data['name']),
                'type' => $data['type'],
                'description' => isset($data['description']) ? trim((string) $data['description']) ?: null : null,
                'is_active' => array_key_exists('is_active', $data)
                    ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN)
                    : true,
            ]);
        });
    }

    /**
     * Update an existing category inside a database transaction.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            $payload = [
                'code' => strtoupper(trim($data['code'])),
                'name' => trim($data['name']),
                'type' => $data['type'],
                'description' => isset($data['description']) ? trim((string) $data['description']) ?: null : null,
            ];

            if (array_key_exists('is_active', $data) && $data['is_active'] !== null) {
                $payload['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }

            $category->update($payload);

            return $category->fresh();
        });
    }

    /**
     * Toggle active status of a category (Zero Soft-Deletes compliant).
     */
    public function toggleStatus(Category $category): Category
    {
        return DB::transaction(function () use ($category): Category {
            $category->update([
                'is_active' => ! $category->is_active,
            ]);

            return $category->fresh();
        });
    }

    /**
     * Delete a category only if it has no associated items (ON DELETE RESTRICT protection).
     *
     * @throws DomainException
     */
    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category): void {
            if ($this->hasItemsTable() && DB::table('items')->where('category_id', $category->id)->exists()) {
                throw new DomainException(
                    'Kategori tidak dapat dihapus karena masih digunakan oleh data item. Nonaktifkan kategori sebagai gantinya.'
                );
            }

            $category->delete();
        });
    }

    /**
     * Get summary statistics for categories using a single aggregate query.
     *
     * @return array{total: int, active: int, inactive: int, inventory: int, asset: int, both: int}
     */
    public function getStatistics(): array
    {
        $stats = Category::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive')
            ->selectRaw("SUM(CASE WHEN type = 'inventory' THEN 1 ELSE 0 END) as inventory")
            ->selectRaw("SUM(CASE WHEN type = 'asset' THEN 1 ELSE 0 END) as asset")
            ->selectRaw("SUM(CASE WHEN type = 'both' THEN 1 ELSE 0 END) as both_count")
            ->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'active' => (int) ($stats->active ?? 0),
            'inactive' => (int) ($stats->inactive ?? 0),
            'inventory' => (int) ($stats->inventory ?? 0),
            'asset' => (int) ($stats->asset ?? 0),
            'both' => (int) ($stats->both_count ?? 0),
        ];
    }

    /**
     * Check whether the items table already exists in the current schema.
     */
    protected function hasItemsTable(): bool
    {
        return Schema::hasTable('items');
    }
}
