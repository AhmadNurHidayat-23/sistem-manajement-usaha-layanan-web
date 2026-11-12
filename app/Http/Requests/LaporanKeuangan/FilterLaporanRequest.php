<?php

namespace App\Http\Requests\LaporanKeuangan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterLaporanRequest extends FormRequest
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
            'period' => ['nullable', 'string', 'in:today,this_week,this_month,this_year,custom,week,month,year'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'day' => ['nullable', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu,1,2,3,4,5,6,7'],
            'date' => ['nullable', 'integer', 'between:1,31'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'digits:4'],
        ];
    }
}
