<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = Str::lower($this->input('email')); // Normalisasi email ke lowercase.
        $password = (string) $this->input('password');

        $user = User::where('email', $email)->first(); // Mencari user berdasarkan email.

        // Cek kecocokan password hash via Hash::check
        if (! $user || ! Hash::check($password, $user->password)) {
            $attempts = RateLimiter::hit($this->throttleKey(), 900); // Kunci 15 menit (900 detik) jika batas tercapai.

            if ($attempts === 4) {
                throw ValidationException::withMessages([
                    'email' => __('Email atau kata sandi salah (Percobaan ke-4 dari 5). Peringatan: Akun Anda akan dikunci selama 15 menit jika gagal 1 kali lagi.'),
                ]);
            }

            if ($attempts >= 5) {
                event(new Lockout($this));

                $seconds = RateLimiter::availableIn($this->throttleKey());
                $minutes = floor($seconds / 60);
                $remainingSeconds = $seconds % 60;
                $timeFormatted = sprintf('%02d:%02d', $minutes, $remainingSeconds);

                throw ValidationException::withMessages([
                    'email' => __('Terlalu banyak percobaan login. Akun Anda dikunci sementara. Silakan coba lagi dalam :time.', [
                        'time' => $timeFormatted,
                    ]),
                ]);
            }

            throw ValidationException::withMessages([
                'email' => __('Email atau kata sandi yang Anda masukkan salah.'),
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => __('Akun Anda telah dinonaktifkan. Silakan hubungi Administrator IT.'),
            ]);
        }

        if ($user->registration_status === 'pending') {
            throw ValidationException::withMessages([
                'email' => __('Pendaftaran akun Anda masih menunggu persetujuan dari Super Admin.'),
            ]);
        }

        if ($user->registration_status === 'rejected') {
            throw ValidationException::withMessages([
                'email' => __('Pendaftaran akun Anda ditolak. Silakan hubungi Administrator IT.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        $timeFormatted = sprintf('%02d:%02d', $minutes, $remainingSeconds);

        throw ValidationException::withMessages([
            'email' => __('Terlalu banyak percobaan login. Akun Anda dikunci sementara. Silakan coba lagi dalam :time.', [
                'time' => $timeFormatted,
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }
}
