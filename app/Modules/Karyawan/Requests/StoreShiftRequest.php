<?php

namespace App\Modules\Karyawan\Requests;

use App\Enums\StatusShiftKaryawan;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends FormRequest
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
            'branch_id' => ['required', 'exists:branches,id'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['nullable', Rule::enum(StatusShiftKaryawan::class)],
        ];
    }

    /**
     * Konfigurasi validasi tambahan untuk durasi shift maksimal 8 jam.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled(['start_time', 'end_time'])) {
                try {
                    $start = Carbon::createFromFormat('H:i', (string) $this->input('start_time'));
                    $end = Carbon::createFromFormat('H:i', (string) $this->input('end_time'));
                    if ($start->diffInMinutes($end, false) > 480) {
                        $validator->errors()->add('end_time', 'Durasi satu shift kerja maksimal 8 jam.');
                    }
                } catch (\Throwable) {
                    // Diabaikan karena format jam ditangani date_format
                }
            }
        });
    }
}
