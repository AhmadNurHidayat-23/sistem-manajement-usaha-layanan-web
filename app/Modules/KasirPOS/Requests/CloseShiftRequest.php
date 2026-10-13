<?php

namespace App\Modules\KasirPOS\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CloseShiftRequest extends FormRequest
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
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'closing_cash.required' => 'Jumlah uang fisik saat penutupan wajib diisi.',
            'closing_cash.numeric' => 'Jumlah uang harus berupa angka.',
            'closing_cash.min' => 'Jumlah uang tidak boleh bernilai negatif.',
        ];
    }
}
