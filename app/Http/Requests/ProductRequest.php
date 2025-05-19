<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'price' => ['required', 'numeric'],
            'stock' => ['nullable', 'integer'],
            'description' => ['nullable'],
            'offer_price' => ['required', 'numeric'],
            'published' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
