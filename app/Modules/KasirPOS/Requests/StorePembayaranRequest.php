<?php

namespace App\Modules\KasirPOS\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'exists:transactions,id'],
            'payment_method' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }
}
