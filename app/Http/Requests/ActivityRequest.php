<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'slug' => ['required'],
            'content' => ['required'],
            'resume' => ['required'],
            'date' => ['nullable', 'date'],
            'address' => ['nullable'],
            'donacion' => ['nullable', 'boolean'],
            'published' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
