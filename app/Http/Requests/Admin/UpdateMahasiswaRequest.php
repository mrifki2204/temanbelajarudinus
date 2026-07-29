<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mahasiswa = $this->route('mahasiswa');

        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($mahasiswa),
                'regex:/^[a-z0-9._]+@mhs\.dinus\.ac\.id$/i',
            ],
            'nim' => ['required', 'string', 'max:50', Rule::unique('users', 'nim')->ignore($mahasiswa), 'regex:/^[A-Za-z0-9]+\.\d{4}\.\d+$/'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'fakultas_id' => ['required', 'exists:fakultas,id'],
            'prodi_id' => [
                'required',
                Rule::exists('prodi', 'id')->where(fn ($q) => $q->where('fakultas_id', $this->input('fakultas_id'))),
            ],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
            'angkatan' => ['required', 'integer', 'min:2000', 'max:'.(int) date('Y')],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'instagram' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'email.regex' => 'Email harus menggunakan domain mahasiswa UDINUS (@mhs.dinus.ac.id).',
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'Format NIM tidak valid. Gunakan format xxx.xxxx.xxx.',
            'nim.unique' => 'NIM ini sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'fakultas_id.required' => 'Fakultas wajib dipilih.',
            'prodi_id.required' => 'Program studi wajib dipilih.',
            'prodi_id.exists' => 'Program studi tidak valid untuk fakultas yang dipilih.',
            'semester.required' => 'Semester wajib diisi.',
            'semester.integer' => 'Semester harus berupa angka.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'angkatan.integer' => 'Angkatan harus berupa angka.',
        ];
    }
}
