<?php

namespace App\Http\Requests\Katalog;

use App\Enums\CommissionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $roleName = $user?->role?->name;

        if ($roleName === 'owner') {
            return true;
        }

        if ($roleName === 'manager') {
            $branchId = $this->input('branch_id');
            if ($branchId && ! $user->branches()->where('branches.id', $branchId)->exists()) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'estimated_duration' => ['nullable', 'integer', 'min:1'],
            'service_type' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'commission_type' => [
                'nullable',
                Rule::enum(CommissionType::class),
                'required_with:commission_value',
            ],
            'commission_value' => [
                'nullable',
                'numeric',
                'min:0',
                'required_with:commission_type',
                Rule::when(
                    $this->input('commission_type') === CommissionType::Percentage->value,
                    ['max:100']
                ),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'service_category_id' => 'Kategori Layanan',
            'branch_id' => 'Cabang',
            'name' => 'Nama Layanan',
            'description' => 'Deskripsi Layanan',
            'image' => 'Foto Layanan',
            'price' => 'Harga Layanan',
            'unit' => 'Satuan Unit Layanan',
            'estimated_duration' => 'Estimasi Durasi (Menit)',
            'service_type' => 'Tipe Layanan',
            'is_active' => 'Status Aktif',
            'commission_type' => 'Tipe Komisi',
            'commission_value' => 'Nilai Komisi',
        ];
    }

    public function messages(): array
    {
        return [
            'commission_value.max' => 'Persentase komisi tidak boleh melebihi 100%.',
            'commission_value.min' => 'Nilai komisi tidak boleh bernilai negatif.',
            'commission_type.required_with' => 'Tipe komisi harus dipilih jika nilai komisi diisi.',
            'commission_value.required_with' => 'Nilai komisi harus diisi jika tipe komisi dipilih.',
        ];
    }
}
