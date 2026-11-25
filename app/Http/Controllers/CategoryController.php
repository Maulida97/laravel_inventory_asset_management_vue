<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with search and filters.
     */
    public function index(Request $request, CategoryService $categoryService): Response
    {
        Gate::authorize('viewAny', Category::class);

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $filters = $request->only(['search', 'type', 'is_active', 'per_page']);
        $categories = $categoryService->getPaginatedCategories($filters, $perPage);
        $statistics = $categoryService->getStatistics();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'statistics' => $statistics,
            'filters' => array_merge($filters, ['per_page' => $perPage]),
            'status' => session('status'),
            'warning' => session('warning'),
            'error' => session('error'),
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request, CategoryService $categoryService): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $category = $categoryService->createCategory($request->validated());

        return redirect()
            ->back(fallback: route('categories.index'))
            ->with('status', "Kategori \"{$category->name}\" ({$category->code}) berhasil ditambahkan.");
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category, CategoryService $categoryService): RedirectResponse
    {
        Gate::authorize('update', $category);

        $updated = $categoryService->updateCategory($category, $request->validated());

        return redirect()
            ->back(fallback: route('categories.index'))
            ->with('status', "Kategori \"{$updated->name}\" ({$updated->code}) berhasil diperbarui.");
    }

    /**
     * Toggle active status of the specified category.
     */
    public function toggleStatus(Category $category, CategoryService $categoryService): RedirectResponse
    {
        Gate::authorize('toggleStatus', $category);

        $updated = $categoryService->toggleStatus($category);
        $label = $updated->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back(fallback: route('categories.index'))
            ->with('status', "Kategori \"{$updated->name}\" berhasil {$label}.");
    }

    /**
     * Remove the specified category from storage if unused.
     */
    public function destroy(Category $category, CategoryService $categoryService): RedirectResponse
    {
        Gate::authorize('delete', $category);

        try {
            $name = $category->name;
            $categoryService->deleteCategory($category);

            return redirect()
                ->back(fallback: route('categories.index'))
                ->with('status', "Kategori \"{$name}\" berhasil dihapus.");
        } catch (DomainException $e) {
            return redirect()
                ->back(fallback: route('categories.index'))
                ->with('error', $e->getMessage());
        }
    }
}
