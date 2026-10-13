<?php

namespace App\Modules\BookingAntrian\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $booking = $this->route('booking');
        if (is_numeric($booking)) {
            $booking = Booking::find($booking);
        }

        return $booking instanceof Booking ? $user->can('update', $booking) : false;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['sometimes', 'exists:branches,id'],
            'service_id' => ['sometimes', 'exists:services,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'extra_service_ids' => ['nullable', 'array'],
            'extra_service_ids.*' => ['exists:services,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'branch_id' => 'Cabang',
            'service_id' => 'Layanan Utama',
            'employee_id' => 'Pegawai',
            'booking_date' => 'Tanggal Booking',
            'booking_time' => 'Jam Booking',
            'notes' => 'Catatan',
        ];
    }
}
