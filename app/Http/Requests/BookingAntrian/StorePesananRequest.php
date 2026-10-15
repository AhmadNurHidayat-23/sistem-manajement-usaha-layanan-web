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
        ];
    }

    public function attributes(): array
    {
        return [
            'branch_id' => 'Cabang',
            'service_id' => 'Layanan',
            'estimated_quantity' => 'Estimasi Kuantitas',
            'pickup_option' => 'Opsi Pickup / Antar-Jemput',
            'notes' => 'Catatan',
        ];
    }
}
