<?php

namespace App\Http\Requests\Inventori;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreBahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === UserRole::Owner->value
            || $this->user()?->role?->name === UserRole::Manager->value;
    }

    public function rules(): array
    {
        $isOwner = $this->user()?->role?->name === UserRole::Owner->value;

        return [
            'branch_id' => $isOwner ? 'required|exists:branches,id' : 'nullable|exists:branches,id',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'initial_stock' => 'nullable|numeric|min:0',
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
            'branch_id' => 'Cabang',
            'code' => 'Kode Bahan',
            'name' => 'Nama Bahan',
            'unit' => 'Satuan Unit',
            'initial_stock' => 'Stok Awal',
            'minimum_stock' => 'Batas Stok Minimum',
            'cost_per_unit' => 'Harga Modal per Satuan',
            'description' => 'Deskripsi / Catatan',
        ];
    }
}
