<?php

namespace App\Modules\Katalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUlasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rating' => 'Rating',
            'komentar' => 'Komentar',
        ];
    }
}
