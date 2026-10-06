<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApproveUserRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole('Super Admin') || $user->can('user.approve-registration') || $user->can('approval.user-registration'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
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
            'roles.required' => 'Minimal satu role wajib dipilih untuk user ini.',
            'roles.array' => 'Format role tidak valid.',
            'roles.min' => 'Minimal satu role wajib dipilih untuk user ini.',
            'roles.*.required' => 'Nama role wajib diisi.',
            'roles.*.exists' => 'Role yang dipilih tidak terdaftar dalam sistem.',
        ];
    }
}
