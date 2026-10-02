<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the enterprise dashboard skeleton page.
     */
    public function index(Request $request): Response
    {
        // Eager-load relasi departemen untuk menghindari N+1 query
        $user = $request->user()->loadMissing('department');

        return Inertia::render('Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'department' => $user->department?->name ?? 'Semua Departemen',
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }
}
