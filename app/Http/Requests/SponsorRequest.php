<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SponsorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'url' => ['nullable'],
            'order' => ['required', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
