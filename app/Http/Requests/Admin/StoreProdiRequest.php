<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fakultas_id' => ['required', 'exists:fakultas,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:20', 'unique:prodi,kode'],
            'jenjang' => ['required', 'in:D3,D4,S1'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.unique' => 'Kode prodi sudah digunakan.',
            'fakultas_id.exists' => 'Fakultas tidak ditemukan.',
        ];
    }
}
