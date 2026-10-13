<?php

namespace App\Modules\Karyawan\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKaryawanRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'branch_id' => ['required', 'exists:branches,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'skills' => ['nullable'],
            'skills.*' => ['string', 'max:100'],
        ];
    }
}
