<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['Super Admin', 'Admin']) || $user->can('location.create'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:locations,code'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:building,warehouse,room,area'],
            'parent_id' => ['nullable', 'integer', 'exists:locations,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('parent_id')) {
                $parent = Location::find($this->input('parent_id'));
                if ($parent && ! $parent->isParent()) {
                    $validator->errors()->add(
                        'parent_id',
                        'Lokasi induk yang dipilih adalah sub-lokasi. Struktur lokasi dibatasi maksimal 2 tingkat (Parent → Child).'
                    );
                }
            }
        });
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode lokasi wajib diisi.',
            'code.unique' => 'Kode lokasi sudah digunakan oleh lokasi lain.',
            'code.max' => 'Kode lokasi maksimal 20 karakter.',
            'name.required' => 'Nama lokasi wajib diisi.',
            'name.max' => 'Nama lokasi maksimal 100 karakter.',
            'type.required' => 'Tipe lokasi wajib dipilih.',
            'type.in' => 'Tipe lokasi harus salah satu dari: building, warehouse, room, atau area.',
            'parent_id.exists' => 'Lokasi induk yang dipilih tidak valid.',
            'address.max' => 'Alamat maksimal 500 karakter.',
        ];
    }
}
