<?php

namespace App\Modules\Karyawan\Requests;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKomisiRequest extends FormRequest
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
            'employee_id' => ['required', 'exists:employees,id'],
            'service_id' => ['required', 'exists:services,id'],
            'transaction_id' => ['nullable', 'exists:transactions,id'],
            'commission_type' => ['required', Rule::enum(CommissionType::class)],
            'commission_value' => ['required', 'numeric', 'min:0'],
            'commission_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::enum(CommissionStatus::class)],
        ];
    }
}
