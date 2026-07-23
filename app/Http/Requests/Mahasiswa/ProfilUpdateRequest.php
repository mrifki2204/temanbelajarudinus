<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class ProfilUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Preferensi belajar
            'minat' => ['required', 'array', 'min:1'],
            'minat.*' => ['string', 'exists:opsi_preferensi,nilai'],
            'tujuan' => ['required', 'string', 'exists:opsi_preferensi,nilai'],
            'gaya' => ['required', 'string', 'exists:opsi_preferensi,nilai'],
            'jadwal' => ['required', 'array', 'min:1'],
            'jadwal.*' => ['string', 'exists:opsi_preferensi,nilai'],
            'mode' => ['required', 'string', 'exists:opsi_preferensi,nilai'],

            // Kontak (wajib, baru terlihat setelah permintaan diterima)
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'instagram' => ['required', 'string', 'max:50', 'regex:/^@?[a-zA-Z0-9._]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'minat.required' => 'Pilih minimal satu minat bidang belajar.',
            'minat.min' => 'Pilih minimal satu minat bidang belajar.',
            'jadwal.required' => 'Pilih minimal satu jadwal luang.',
            'jadwal.min' => 'Pilih minimal satu jadwal luang.',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid (hanya angka, +, -, spasi).',
            'instagram.regex' => 'Format username Instagram tidak valid.',
        ];
    }
}
