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
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'status' => ['sometimes', 'required', Rule::enum(StatusShiftKaryawan::class)],
        ];
    }

    /**
     * Konfigurasi validasi tambahan untuk durasi shift maksimal 8 jam dan lintas tengah malam.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $shiftDate = $this->input('shift_date');

            $shift = $this->route('shift');
            if ($shift instanceof EmployeeShift) {
                $shiftDate = $shiftDate ?: ($shift->shift_date instanceof \DateTimeInterface ? $shift->shift_date->format('Y-m-d') : substr((string) $shift->shift_date, 0, 10));
                $startTime = $startTime ?: substr((string) $shift->start_time, 0, 5);
                $endTime = $endTime ?: substr((string) $shift->end_time, 0, 5);
            }

            if ($startTime && $endTime) {
                try {
                    $date = $shiftDate ?: Carbon::today('Asia/Jakarta')->format('Y-m-d');
                    $start = Carbon::parse($date.' '.$startTime, 'Asia/Jakarta');
                    $end = Carbon::parse($date.' '.$endTime, 'Asia/Jakarta');

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
                    // Format diuji oleh aturan bawaan
                }
            }
        });
    }
}
