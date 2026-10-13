<?php

namespace App\Modules\Inventori\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StokMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === UserRole::Owner->value
            || $this->user()?->role?->name === UserRole::Manager->value;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'quantity' => 'Jumlah Stok Masuk',
            'reference_type' => 'Kategori / Referensi Masuk',
            'notes' => 'Keterangan',
        ];
    }
}
