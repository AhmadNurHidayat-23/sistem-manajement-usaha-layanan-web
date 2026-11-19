<?php

namespace App\Http\Requests\BookingAntrian;

use App\Models\Branch;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $isCustomerRoute = $this->routeIs('customer.pesanan.*') || $this->is('customer/pesanan*');

        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'service_id' => [
                'required',
                'exists:services,id',
                Rule::exists('services', 'id')->where(function ($query) {
                    $query->where('is_active', true)
                        ->where('branch_id', $this->input('branch_id'));
                }),
            ],
            'estimated_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'pickup_option' => ['nullable', 'boolean'],
            'pickup_address' => [$isCustomerRoute ? 'required_if:pickup_option,1' : 'nullable', 'nullable', 'string', 'max:1000'],
            'pickup_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'different_delivery_location' => ['nullable', 'boolean'],
            'delivery_address' => ['required_if:different_delivery_location,1', 'nullable', 'string', 'max:1000'],
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_notes' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'extra_service_ids' => ['nullable', 'array'],
            'extra_service_ids.*' => [
                'exists:services,id',
                Rule::exists('services', 'id')->where(function ($query) {
                    $query->where('is_active', true)
                        ->where('branch_id', $this->input('branch_id'));
                }),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $branchId = $this->input('branch_id');
            $serviceId = $this->input('service_id');

            if ($branchId) {
                $branch = Branch::find($branchId);
                if ($branch && ! in_array($branch->service_mode, ['drop_off', 'both'], true)) {
                    $validator->errors()->add('branch_id', 'Cabang yang dipilih tidak melayani pesanan drop-off.');
                }
            }

            if ($branchId && $serviceId) {
                $service = Service::find($serviceId);
                if ($service && (int) $service->branch_id !== (int) $branchId) {
                    $validator->errors()->add('service_id', 'Layanan yang dipilih tidak tersedia pada cabang tersebut.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'service_id.exists' => 'Layanan yang dipilih tidak tersedia pada cabang tersebut.',
            'pickup_address.required_if' => 'Alamat penjemputan wajib diisi saat layanan antar-jemput aktif.',
            'delivery_address.required_if' => 'Alamat pengantaran wajib diisi jika lokasi pengantaran berbeda.',
        ];
    }

    public function attributes(): array
    {
        return [
            'branch_id' => 'Cabang',
            'service_id' => 'Layanan',
            'estimated_quantity' => 'Estimasi Kuantitas',
            'pickup_option' => 'Opsi Pickup / Antar-Jemput',
            'pickup_address' => 'Alamat Penjemputan',
            'delivery_address' => 'Alamat Pengantaran',
            'notes' => 'Catatan',
        ];
    }
}
