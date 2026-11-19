<?php

namespace App\Http\Requests\Karyawan;

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
            'end_time' => ['required', 'date_format:H:i'],
            'status' => ['nullable', Rule::enum(StatusShiftKaryawan::class)],
        ];
    }

    /**
     * Konfigurasi validasi tambahan untuk durasi shift maksimal 8 jam dan lintas tengah malam.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled(['start_time', 'end_time'])) {
                try {
                    $date = $this->input('shift_date') ?: Carbon::today('Asia/Jakarta')->format('Y-m-d');
                    $start = Carbon::parse($date.' '.(string) $this->input('start_time'), 'Asia/Jakarta');
                    $end = Carbon::parse($date.' '.(string) $this->input('end_time'), 'Asia/Jakarta');

                    if ($end->lte($start)) {
                        $end->addDay();
                    }

                    $duration = $start->diffInMinutes($end, false);
                    if ($duration <= 0) {
                        $validator->errors()->add('end_time', 'Jam selesai tidak boleh sama dengan jam mulai.');
                    } elseif ($duration > 480) {
                        $validator->errors()->add('end_time', 'Durasi satu shift kerja maksimal 8 jam.');
                    }
                } catch (\Throwable) {
                    // Diabaikan karena format jam ditangani date_format
                }
            }
        });
    }
}
