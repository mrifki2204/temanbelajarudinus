<?php

namespace App\Http\Requests\Mahasiswa;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nim' => ['required', 'string', 'max:50', 'unique:'.User::class, 'regex:/^[A-Za-z0-9]+\.\d{4}\.\d+$/'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                'unique:'.User::class,
                'regex:/^[a-z0-9._]+@mhs\.dinus\.ac\.id$/i',
            ],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Info akademik — prodi harus milik fakultas terpilih
            'fakultas_id' => ['required', 'exists:fakultas,id'],
            'prodi_id' => [
                'required',
                Rule::exists('prodi', 'id')->where(fn ($q) => $q->where('fakultas_id', $this->input('fakultas_id'))),
            ],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
            'angkatan' => ['required', 'integer', 'min:2000', 'max:'.(int) date('Y')],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.regex' => 'Format NIM tidak valid. Gunakan format xxx.xxxx.xxx, contoh: A11.2021.13840.',
            'nim.unique' => 'NIM ini sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'email.regex' => 'Email harus menggunakan format email mahasiswa UDINUS (contoh: A11.2021.13840@mhs.dinus.ac.id).',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'fakultas_id.required' => 'Fakultas wajib dipilih.',
            'prodi_id.required' => 'Program studi wajib dipilih.',
            'prodi_id.exists' => 'Program studi tidak valid untuk fakultas yang dipilih.',
            'semester.required' => 'Semester wajib diisi.',
            'semester.integer' => 'Semester harus berupa angka.',
            'angkatan.required' => 'Tahun angkatan wajib diisi.',
            'angkatan.integer' => 'Tahun angkatan harus berupa angka.',
            'angkatan.min' => 'Tahun angkatan minimal 2000.',
            'angkatan.max' => 'Tahun angkatan maksimal tahun ini.',
        ];
    }
}
