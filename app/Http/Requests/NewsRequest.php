<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'slug' => ['required'],
            'content' => ['nullable'],
            'resume' => ['nullable'],
            'donacion' => ['boolean'],
            'published' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
