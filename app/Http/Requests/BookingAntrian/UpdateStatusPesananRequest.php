<?php

namespace App\Http\Requests\BookingAntrian;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,received,processing,ready,completed,cancelled'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'Status Order',
        ];
    }
}
