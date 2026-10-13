<?php

namespace App\Modules\BookingAntrian\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,confirmed,in_progress,completed,cancelled'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'Status Booking',
        ];
    }
}
