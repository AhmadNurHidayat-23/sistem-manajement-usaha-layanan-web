<?php

namespace App\Modules\MembershipPromo\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
            'user_id' => ['required_without:name', 'nullable', 'exists:users,id', 'unique:memberships,user_id'],
            'name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'phone' => ['required_without:user_id', 'nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'member_code' => ['nullable', 'string', 'max:50', 'unique:memberships,member_code'],
            'redirect_to' => ['nullable', 'string'],
        ];
    }
}
