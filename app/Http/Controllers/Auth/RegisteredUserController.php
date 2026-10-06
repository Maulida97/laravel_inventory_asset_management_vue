<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Auth/Register', [
            'departments' => $departments,
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'department_id' => $request->validated('department_id'),
                'employee_id' => $request->validated('employee_id'),
                'position' => $request->validated('position'),
                'phone_number' => $request->validated('phone_number'),
                'is_active' => false,
                'registration_status' => 'pending',
            ]);
        });

        return redirect()->route('login')->with(
            'status',
            __('Pendaftaran akun berhasil! Akun Anda sedang menunggu persetujuan dari Super Admin sebelum dapat digunakan.')
        );
    }
}
