<?php

namespace App\Modules\MembershipPromo\Requests;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePromoRequest extends FormRequest
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
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'string', 'max:50', 'unique:promotions,code'],
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', new Enum(DiscountType::class)],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', new Enum(PromotionStatus::class)],
        ];
    }
}
