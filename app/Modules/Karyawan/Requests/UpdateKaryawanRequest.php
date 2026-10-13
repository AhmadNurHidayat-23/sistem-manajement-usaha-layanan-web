<?php

namespace App\Modules\Karyawan\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKaryawanRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'branch_id' => ['sometimes', 'required', 'exists:branches,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'skills' => ['nullable'],
            'skills.*' => ['string', 'max:100'],
        ];
    }
}
