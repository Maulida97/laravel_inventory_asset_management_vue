<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->hasRole('Super Admin') || $user->can('category.manage'));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code') && is_string($this->input('code'))) {
            $this->merge([
                'code' => strtoupper(trim($this->input('code'))),
            ]);
        }

        if ($this->has('name') && is_string($this->input('name'))) {
            $this->merge([
                'name' => trim($this->input('name')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('categories', 'code')],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::in(Category::TYPES)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode kategori wajib diisi.',
            'code.max' => 'Kode kategori maksimal 20 karakter.',
            'code.unique' => 'Kode kategori sudah digunakan. Gunakan kode yang berbeda.',
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'type.required' => 'Tipe peruntukan kategori wajib dipilih.',
            'type.in' => 'Tipe kategori harus salah satu dari: inventory, asset, atau both.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ];
    }
}
