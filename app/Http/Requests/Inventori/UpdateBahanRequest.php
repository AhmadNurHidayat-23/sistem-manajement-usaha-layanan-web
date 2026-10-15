<?php

namespace App\Http\Requests\Inventori;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === UserRole::Owner->value
            || $this->user()?->role?->name === UserRole::Manager->value;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|numeric|min:0',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Bahan',
            'code' => 'Kode Bahan',
            'unit' => 'Satuan Unit',
            'minimum_stock' => 'Batas Stok Minimum',
            'cost_per_unit' => 'Harga Modal per Satuan',
            'description' => 'Deskripsi / Catatan',
        ];
    }
}
