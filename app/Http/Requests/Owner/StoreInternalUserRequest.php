<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInternalUserRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diotorisasi untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'owner';
    }

    /**
     * Aturan validasi penyimpanan akun internal baru.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $query->whereIn('name', ['manager', 'cashier']);
                }),
            ],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Pesan kustom untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role_id.exists' => 'Peran yang dipilih tidak valid. Hanya akun Manager dan Kasir yang dapat dibuat.',
            'branch_ids.required' => 'Wajib memilih minimal satu cabang penugasan.',
            'branch_ids.min' => 'Wajib memilih minimal satu cabang penugasan.',
        ];
    }

    /**
     * Atribut kustom.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'email' => 'Alamat Email',
            'phone' => 'Nomor Telepon',
            'role_id' => 'Peran Akun',
            'branch_ids' => 'Cabang Penugasan',
            'password' => 'Kata Sandi',
        ];
    }
}
