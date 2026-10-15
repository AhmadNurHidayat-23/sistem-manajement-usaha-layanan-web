<?php

namespace App\Http\Requests\KasirPOS;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roleName = $this->user()?->role?->name;

        return in_array($roleName, ['owner', 'manager', 'cashier'], true);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'opening_cash.required' => 'Modal awal kas wajib diisi.',
            'opening_cash.numeric' => 'Modal awal harus berupa angka.',
            'opening_cash.min' => 'Modal awal tidak boleh negatif.',
        ];
    }
}
