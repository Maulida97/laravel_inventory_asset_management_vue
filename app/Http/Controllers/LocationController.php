<?php

namespace App\Http\Controllers;

use App\Exceptions\LocationBusinessRuleException;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    /**
     * Display a listing of locations with search and filters.
     */
    public function index(Request $request, LocationService $locationService): Response
    {
        Gate::authorize('viewAny', Location::class);

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $filters = $request->only(['search', 'type', 'is_active', 'level', 'parent_id', 'per_page']);
        $locations = $locationService->getPaginatedLocations($filters, $perPage);

        $parentOptions = $locationService->getActiveParentOptions();
        $statistics = $locationService->getStatistics();

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
            'parentOptions' => $parentOptions,
            'statistics' => $statistics,
            'filters' => array_merge($filters, ['per_page' => $perPage]),
            'status' => session('status'),
            'warning' => session('warning'),
            'error' => session('error'),
        ]);
    }

    /**
     * Store a newly created location in storage with business rule protection.
     */
    public function store(StoreLocationRequest $request, LocationService $locationService): RedirectResponse
    {
        Gate::authorize('create', Location::class);

        try {
            $location = $locationService->createLocation($request->validated());

            $redirect = redirect()->route('locations.index')->with(
                'status',
                "Lokasi {$location->name} ({$location->code}) berhasil ditambahkan."
            );

            // BR-L02: Flash warning if adding child under parent that still stores items/assets
            if ($request->targetParentHasItems()) {
                $parent = $request->getTargetParent();
                $parentName = $parent ? $parent->name : 'induk';
                $redirect->with(
                    'warning',
                    "Perhatian: Lokasi induk '{$parentName}' saat ini masih menyimpan barang atau aset aktif. Pastikan Anda segera memindahkan stok ke sub-lokasi baru (BR-L02)."
                );
            }

            return $redirect;
        } catch (LocationBusinessRuleException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Gagal menambahkan lokasi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with(
                'error',
                'Terjadi kesalahan pada sistem saat menambahkan lokasi. Silakan coba beberapa saat lagi.'
            );
        }
    }

    /**
     * Update the specified location in storage with business rule protection (BR-L03, BR-L05).
     */
    public function update(UpdateLocationRequest $request, Location $location, LocationService $locationService): RedirectResponse
    {
        Gate::authorize('update', $location);

        try {
            $updated = $locationService->updateLocation($location, $request->validated());

            $redirect = redirect()->route('locations.index')->with(
                'status',
                "Lokasi {$updated->name} ({$updated->code}) berhasil diperbarui."
            );

            // BR-L02: Flash warning if moving child under parent that stores items/assets
            if ($request->targetParentHasItems()) {
                $parent = $request->getTargetParent();
                $parentName = $parent ? $parent->name : 'induk';
                $redirect->with(
                    'warning',
                    "Perhatian: Lokasi induk '{$parentName}' saat ini masih menyimpan barang atau aset aktif. Pastikan Anda segera memindahkan stok ke sub-lokasi baru (BR-L02)."
                );
            }

            return $redirect;
        } catch (LocationBusinessRuleException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Gagal memperbarui lokasi ID {$location->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with(
                'error',
                'Terjadi kesalahan pada sistem saat memperbarui data lokasi. Silakan coba beberapa saat lagi.'
            );
        }
    }

    /**
     * Toggle the active status of the specified location with business rule protection (BR-L03).
     */
    public function toggleStatus(Request $request, Location $location, LocationService $locationService): RedirectResponse
    {
        Gate::authorize('toggleStatus', $location);

        try {
            $toggled = $locationService->toggleStatus($location);
            $statusLabel = $toggled->is_active ? 'aktif' : 'nonaktif';

            return redirect()->route('locations.index')->with(
                'status',
                "Status lokasi {$toggled->name} berhasil diubah menjadi {$statusLabel}."
            );
        } catch (LocationBusinessRuleException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Gagal mengubah status lokasi ID {$location->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with(
                'error',
                'Terjadi kesalahan pada sistem saat memperbarui status lokasi. Silakan coba beberapa saat lagi.'
            );
        }
    }
}
