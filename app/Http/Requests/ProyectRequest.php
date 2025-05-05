<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProyectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'content' => ['nullable'],
            'slug' => ['required'],
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
