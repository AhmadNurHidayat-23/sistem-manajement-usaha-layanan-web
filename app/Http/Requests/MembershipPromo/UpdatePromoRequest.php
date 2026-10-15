<?php

namespace App\Http\Requests\MembershipPromo;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdatePromoRequest extends FormRequest
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
        $promoId = $this->route('promotion')?->id ?? $this->route('promotion');

        return [
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('promotions', 'code')->ignore($promoId)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'discount_type' => ['sometimes', 'required', new Enum(DiscountType::class)],
            'discount_value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', 'required', new Enum(PromotionStatus::class)],
        ];
    }
}
