<?php

namespace App\Modules\Katalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriLayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roleName = $this->user()?->role?->name;

        return in_array($roleName, ['owner', 'manager'], true);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Kategori',
            'description' => 'Deskripsi Kategori',
            'is_active' => 'Status Aktif',
        ];
    }
}
