<?php

namespace App\Http\Requests\Owner;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInternalUserRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diotorisasi untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'owner';
    }

    /**
     * Aturan validasi pembaruan akun internal.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User|null $targetUser */
        $targetUser = $this->route('internal_user');
        $userId = $targetUser?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:25'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $query->whereIn('name', ['owner', 'manager', 'cashier']);
                }),
            ],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
            'role_id.exists' => 'Peran yang dipilih tidak valid.',
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
