<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'title' => ['required'],
      'slug' => ['required'],
    ];
  }

  public function authorize(): bool
  {
    return true;
  }
}
