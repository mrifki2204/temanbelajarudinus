<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFakultasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fakultas = $this->route('fakultas');

        return [
            'nama' => ['required', 'string', 'max:255', Rule::unique('fakultas', 'nama')->ignore($fakultas)],
            'kode' => ['required', 'string', 'max:20', Rule::unique('fakultas', 'kode')->ignore($fakultas)],
        ];
    }
}
