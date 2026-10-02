<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // 1. Cek jika status user dinonaktifkan
            if (! $user->is_active) {
                return $this->logoutAndRedirect(
                    $request,
                    __('Akun Anda telah dinonaktifkan. Silakan hubungi Administrator IT.')
                );
            }
            // 2. Cek jika status pendaftaran masih pending
            if ($user->registration_status === 'pending') {
                return $this->logoutAndRedirect(
                    $request,
                    __('Pendaftaran akun Anda masih menunggu persetujuan dari Super Admin.')
                );
            }
            // 3. Cek jika status pendaftaran ditolak
            if ($user->registration_status === 'rejected') {
                return $this->logoutAndRedirect(
                    $request,
                    __('Pendaftaran akun Anda ditolak. Silakan hubungi Administrator IT.')
                );
            }
        }

        return $next($request);
    }

    /**
     * Log the user out, invalidate session, and redirect to login with an error message.
     */
    protected function logoutAndRedirect(Request $request, string $message): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => $message,
        ]);
    }
}
