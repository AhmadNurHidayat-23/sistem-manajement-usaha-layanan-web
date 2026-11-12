<?php

namespace App\Http\Requests\BookingAntrian;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Semua user terotentikasi dapat booking
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'service_id' => ['required', 'exists:services,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'extra_service_ids' => ['nullable', 'array'],
            'extra_service_ids.*' => ['exists:services,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $branchId = $this->input('branch_id');
            if ($branchId) {
                $branch = Branch::find($branchId);
                if ($branch && ! in_array($branch->service_mode, ['appointment', 'both'], true)) {
                    $validator->errors()->add('branch_id', 'Cabang yang dipilih tidak melayani booking janji temu (appointment).');
                }
            }

            $employeeId = $this->input('employee_id');
            if ($branchId && $employeeId) {
                $employee = Employee::find($employeeId);
                $isAssigned = $employee && (
                    (int) $employee->branch_id === (int) $branchId
                    || $employee->shifts()->where('branch_id', $branchId)->exists()
                );
                if (! $employee || ! $isAssigned || $employee->status !== 'active') {
                    $validator->errors()->add('employee_id', 'Petugas yang dipilih tidak bertugas pada cabang ini.');
                }
            }

            $serviceId = $this->input('service_id');
            if ($branchId && $serviceId) {
                $service = Service::find($serviceId);
                if (! $service || (int) $service->branch_id !== (int) $branchId) {
                    $validator->errors()->add('service_id', 'Layanan yang dipilih tidak tersedia pada cabang ini.');
                }
            }
        });
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
