<?php

namespace App\Http\Requests\KasirPOS;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roleName = $this->user()?->role?->name;

        return in_array($roleName, ['owner', 'manager', 'cashier'], true);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', 'exists:services,id'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris,debit,credit,e_wallet,virtual_account'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Cabang transaksi wajib ditentukan.',
            'items.required' => 'Minimal pilih 1 layanan untuk melakukan transaksi.',
            'items.min' => 'Minimal pilih 1 layanan untuk melakukan transaksi.',
            'items.*.service_id.required' => 'Layanan yang dipilih tidak valid.',
            'items.*.qty.required' => 'Jumlah layanan harus diisi.',
            'items.*.qty.min' => 'Jumlah layanan minimal 1.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ];
    }
}
