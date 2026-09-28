<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $email = Str::lower($this->input('email')); //Normalisasi email ke lowercase.
        $password = (string) $this->input('password');

        $user = User::where('email', $email)->first(); //Mencari user berdasarkan email.

        //Cek kecocokan password hash via Hash::check
        if (! $user || ! Hash::check($password, $user->password)) { //Memeriksa apakah user ada dan password benar.
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

        Auth::login($user, $this->boolean('remember'));
    }
}
