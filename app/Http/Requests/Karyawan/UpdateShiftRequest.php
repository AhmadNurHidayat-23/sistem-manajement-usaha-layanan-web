<?php

namespace App\Http\Requests\Karyawan;

use App\Enums\StatusShiftKaryawan;
use App\Models\EmployeeShift;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
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
            'shift_date' => ['sometimes', 'required', 'date'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i', 'after:start_time'],
            'status' => ['sometimes', 'required', Rule::enum(StatusShiftKaryawan::class)],
        ];
    }

    /**
     * Konfigurasi validasi tambahan untuk durasi shift maksimal 8 jam.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');

            if (! $startTime || ! $endTime) {
                $shift = $this->route('shift');
                if ($shift instanceof EmployeeShift) {
                    $startTime = $startTime ?: substr((string) $shift->start_time, 0, 5);
                    $endTime = $endTime ?: substr((string) $shift->end_time, 0, 5);
                }
            }

            if ($startTime && $endTime) {
                try {
                    $start = Carbon::parse($startTime);
                    $end = Carbon::parse($endTime);
                    if ($start->diffInMinutes($end, false) > 480) {
                        $validator->errors()->add('end_time', 'Durasi satu shift kerja maksimal 8 jam.');
                    }
                } catch (\Throwable) {
                    // Format diuji oleh aturan bawaan
                }
            }
        });
    }
}
