<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFakultasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', 'unique:fakultas,nama'],
            'kode' => ['required', 'string', 'max:20', 'unique:fakultas,kode'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.unique' => 'Nama fakultas sudah ada.',
            'kode.unique' => 'Kode fakultas sudah digunakan.',
        ];
    }
}
