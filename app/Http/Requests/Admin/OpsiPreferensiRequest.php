<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi field dasar untuk store & update OpsiPreferensi.
 * Cek unique composite (tipe+nilai) ditangani di controller karena
 * nilai jadwal di-compose dari hari+slot setelah validasi dasar.
 */
class OpsiPreferensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipe' => ['required', 'in:minat,tujuan,gaya,jadwal,mode'],
            'nilai' => ['nullable', 'sometimes', 'string', 'max:255'],
            'hari' => ['nullable', 'required_if:tipe,jadwal', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'slot' => ['nullable', 'required_if:tipe,jadwal', 'in:Pagi,Siang,Sore,Malam'],
        ];
    }

    public function messages(): array
    {
        return [
            'nilai.required' => 'Item wajib diisi.',
            'hari.required_if' => 'Hari wajib dipilih.',
            'slot.required_if' => 'Slot wajib dipilih.',
        ];
    }
}
