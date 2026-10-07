<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

        $locations = $locationService->getPaginatedLocations(
            $request->only(['search', 'type', 'is_active', 'level', 'parent_id']),
            10
        );

        $parentOptions = $locationService->getActiveParentOptions();
        $statistics = $locationService->getStatistics();

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
            'parentOptions' => $parentOptions,
            'statistics' => $statistics,
            'filters' => $request->only(['search', 'type', 'is_active', 'level', 'parent_id']),
            'status' => session('status'),
            'warning' => session('warning'),
        ]);
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(StoreLocationRequest $request, LocationService $locationService): RedirectResponse
    {
        Gate::authorize('create', Location::class);

        $location = $locationService->createLocation($request->validated());

        return redirect()->route('locations.index')->with(
            'status',
            "Lokasi {$location->name} ({$location->code}) berhasil ditambahkan."
        );
    }

    /**
     * Update the specified location in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location, LocationService $locationService): RedirectResponse
    {
        Gate::authorize('update', $location);

        $updated = $locationService->updateLocation($location, $request->validated());

        return redirect()->route('locations.index')->with(
            'status',
            "Lokasi {$updated->name} ({$updated->code}) berhasil diperbarui."
        );
    }

    /**
     * Toggle the active status of the specified location.
     */
    public function toggleStatus(Request $request, Location $location, LocationService $locationService): RedirectResponse
    {
        Gate::authorize('toggleStatus', $location);

        $toggled = $locationService->toggleStatus($location);
        $statusLabel = $toggled->is_active ? 'aktif' : 'nonaktif';

        return redirect()->route('locations.index')->with(
            'status',
            "Status lokasi {$toggled->name} berhasil diubah menjadi {$statusLabel}."
        );
    }
}
