<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasRole(['Super Admin', 'Admin']) || $user->can('location.edit'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Location|int|string $location */
        $location = $this->route('location');
        $locationId = $location instanceof Location ? $location->id : $location;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('locations', 'code')->ignore($locationId),
            ],
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
            /** @var Location|int|string $location */
            $location = $this->route('location');
            $currentLocation = $location instanceof Location ? $location : Location::find($location);

            if (! $currentLocation) {
                return;
            }

            $rawParentId = $this->input('parent_id');
            $newParentId = (! empty($rawParentId)) ? (int) $rawParentId : null;
            $oldParentId = $currentLocation->parent_id ? (int) $currentLocation->parent_id : null;

            // 1. Cannot set self as parent
            if ($newParentId !== null && $newParentId === (int) $currentLocation->id) {
                $validator->errors()->add(
                    'parent_id',
                    'Lokasi tidak dapat dijadikan sebagai induk dari dirinya sendiri.'
                );

                return;
            }

            // 2. Cannot assign parent if this location already has children (depth limit 2 levels)
            if ($newParentId !== null && $currentLocation->children()->exists()) {
                $validator->errors()->add(
                    'parent_id',
                    'Lokasi induk yang sudah memiliki sub-lokasi tidak dapat diubah menjadi sub-lokasi.'
                );

                return;
            }

            // 3. Target parent checks
            if ($newParentId !== null) {
                $targetParent = Location::find($newParentId);

                if ($targetParent) {
                    if (! $targetParent->isParent()) {
                        $validator->errors()->add(
                            'parent_id',
                            'Lokasi induk yang dipilih adalah sub-lokasi. Struktur lokasi dibatasi maksimal 2 tingkat (Parent → Child).'
                        );
                    }

                    if (! $targetParent->is_active) {
                        $validator->errors()->add(
                            'parent_id',
                            'Lokasi induk yang dipilih sedang nonaktif. Aktifkan lokasi induk terlebih dahulu.'
                        );
                    }
                }
            }

            // 4. BR-L05: Moving child to another parent OR changing child to top-level parent requires empty location
            if ($currentLocation->isChild() && $newParentId !== $oldParentId) {
                if (! $currentLocation->isEmpty(false)) {
                    $validator->errors()->add(
                        'parent_id',
                        "Sub-lokasi {$currentLocation->name} hanya dapat dipindahkan atau diubah strukturnya jika dalam keadaan kosong (tidak memiliki barang atau aset aktif) (BR-L05)."
                    );
                }
            }

            // 5. BR-L03: Cannot deactivate location if it still has items or active assets
            if ($this->has('is_active')) {
                $newIsActive = filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN);

                if ($currentLocation->is_active && ! $newIsActive) {
                    if (! $currentLocation->isEmpty(true)) {
                        $validator->errors()->add(
                            'is_active',
                            "Lokasi {$currentLocation->name} ({$currentLocation->code}) tidak dapat dinonaktifkan karena masih memiliki barang atau aset aktif (BR-L03)."
                        );
                    }
                }

                // Child cannot be active if its parent is inactive
                if (! $currentLocation->is_active && $newIsActive) {
                    $parentToCheck = $newParentId !== null ? Location::find($newParentId) : $currentLocation->parent;
                    if ($parentToCheck && ! $parentToCheck->is_active) {
                        $validator->errors()->add(
                            'is_active',
                            "Sub-lokasi tidak dapat diaktifkan karena lokasi induknya ({$parentToCheck->name}) masih berstatus nonaktif."
                        );
                    }
                }
            }
        });
    }

    /**
     * Check if the new target parent location currently holds items or assets (BR-L02).
     */
    public function targetParentHasItems(): bool
    {
        /** @var Location|int|string $location */
        $location = $this->route('location');
        $currentLocation = $location instanceof Location ? $location : Location::find($location);

        $rawParentId = $this->input('parent_id');
        $newParentId = (! empty($rawParentId)) ? (int) $rawParentId : null;
        $oldParentId = $currentLocation ? $currentLocation->parent_id : null;

        if ($newParentId === null || $newParentId === $oldParentId) {
            return false;
        }

        $parent = Location::find($newParentId);

        return $parent ? $parent->hasItemsOrAssets(false) : false;
    }

    /**
     * Get the target parent instance.
     */
    public function getTargetParent(): ?Location
    {
        $rawParentId = $this->input('parent_id');
        if (empty($rawParentId)) {
            return null;
        }

        return Location::find($rawParentId);
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
