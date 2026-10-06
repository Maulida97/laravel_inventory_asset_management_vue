<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? Str::lower(trim($this->email)) : null,
            'employee_id' => $this->employee_id ? trim($this->employee_id) : null,
            'name' => $this->name ? trim($this->name) : null,
            'position' => $this->position ? trim($this->position) : null,
            'phone_number' => $this->phone_number ? trim($this->phone_number) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'employee_id' => ['nullable', 'string', 'max:30', 'unique:users,employee_id'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where('is_active', true),
            ],
            'position' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari 100 karakter.',
            'email.required' => 'Email korporat wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 100 karakter.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'employee_id.max' => 'Nomor Karyawan (NIP) tidak boleh lebih dari 30 karakter.',
            'employee_id.unique' => 'Nomor Karyawan (NIP) ini sudah terdaftar.',
            'department_id.required' => 'Departemen wajib dipilih.',
            'department_id.exists' => 'Departemen yang dipilih tidak valid atau tidak aktif.',
            'position.max' => 'Jabatan tidak boleh lebih dari 100 karakter.',
            'phone_number.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
