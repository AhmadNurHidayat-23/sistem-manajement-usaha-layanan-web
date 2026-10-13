<?php

namespace App\Modules\Cabang\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCabangRequest extends FormRequest
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
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'opening_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'closing_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'service_mode' => ['required', 'in:appointment,drop_off,both'],
            'operating_days' => ['nullable', 'array'],
            'operating_days.*' => ['string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'max_slot_capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Cabang',
            'address' => 'Alamat Cabang',
            'phone' => 'Nomor Telepon',
            'opening_time' => 'Jam Buka',
            'closing_time' => 'Jam Tutup',
            'service_mode' => 'Mode Layanan',
            'max_slot_capacity' => 'Kapasitas Maksimal Slot',
            'is_active' => 'Status Aktif',
        ];
    }
}
