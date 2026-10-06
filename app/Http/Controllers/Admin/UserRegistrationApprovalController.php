<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveUserRegistrationRequest;
use App\Http\Requests\Admin\RejectUserRegistrationRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\Admin\UserRegistrationApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserRegistrationApprovalController extends Controller
{
    /**
     * Display a listing of pending user registrations.
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request->user());

        $query = User::with('department')
            ->pendingRegistration()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->input('search'));
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->where('department_id', $request->input('department_id'));
            })
            ->latest('id');

        $pendingUsers = $query->paginate(10)->withQueryString();

        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $availableRoles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/UserRegistrations/Index', [
            'pendingUsers' => $pendingUsers,
            'departments' => $departments,
            'availableRoles' => $availableRoles,
            'filters' => $request->only(['search', 'department_id']),
            'status' => session('status'),
        ]);
    }

    /**
     * Approve a pending user registration and assign roles.
     */
    public function approve(
        ApproveUserRegistrationRequest $request,
        User $user,
        UserRegistrationApprovalService $approvalService
    ): RedirectResponse {
        if ($user->registration_status !== 'pending') {
            return back()->withErrors([
                'error' => __('Pendaftaran pengguna ini sudah diproses sebelumnya.'),
            ]);
        }

        $approvalService->approve($user, $request->validated('roles'));

        return redirect()->route('admin.user-registrations.index')->with(
            'status',
            __('Pendaftaran pengguna :name berhasil disetujui.', ['name' => $user->name])
        );
    }

    /**
     * Reject a pending user registration.
     */
    public function reject(
        RejectUserRegistrationRequest $request,
        User $user,
        UserRegistrationApprovalService $approvalService
    ): RedirectResponse {
        if ($user->registration_status !== 'pending') {
            return back()->withErrors([
                'error' => __('Pendaftaran pengguna ini sudah diproses sebelumnya.'),
            ]);
        }

        $approvalService->reject($user);

        return redirect()->route('admin.user-registrations.index')->with(
            'status',
            __('Pendaftaran pengguna :name telah ditolak.', ['name' => $user->name])
        );
    }

    /**
     * Ensure the user is authorized to manage user registration approvals.
     */
    protected function authorizeAdmin(?User $user): void
    {
        if (! $user || (! $user->hasRole('Super Admin') && ! $user->can('user.approve-registration') && ! $user->can('approval.user-registration'))) {
            abort(403, __('Anda tidak memiliki hak akses untuk mengelola persetujuan registrasi pengguna.'));
        }
    }
}
