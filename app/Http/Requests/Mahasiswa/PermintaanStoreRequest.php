<?php

namespace App\Http\Requests\Mahasiswa;

use App\Models\StudyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermintaanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penerima_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'mahasiswa')
                        ->where('status', 'aktif');
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'penerima_id.required' => 'Kandidat penerima wajib dipilih.',
            'penerima_id.exists' => 'Kandidat tidak ditemukan atau tidak aktif.',
        ];
    }

    /**
     * Validasi tambahan setelah rules dasar lolos.
     *
     * @throws ValidationException
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $pengirim = $this->user();
            $penerimaId = (int) $this->input('penerima_id');

            // 1. Tidak boleh kirim ke diri sendiri
            if ($penerimaId === (int) $pengirim->id) {
                $validator->errors()->add('penerima_id', 'Anda tidak dapat mengirim permintaan kepada diri sendiri.');

                return;
            }

            // 2. Profil pengirim harus lengkap (5 preferensi CBF)
            $pengirim->loadMissing('profile');
            if (! $pengirim->profile || ! $pengirim->profile->isPreferensiLengkap()) {
                $validator->errors()->add('penerima_id', 'Lengkapi profil preferensi Anda terlebih dahulu sebelum mengirim permintaan.');

                return;
            }

            // 3. Tidak boleh ada permintaan aktif (pending/accepted) — lock baris
            //    untuk cegah race double-submit paralel.
            $sudahAktif = DB::transaction(function () use ($pengirim, $penerimaId) {
                return StudyRequest::where('pengirim_id', $pengirim->id)
                    ->where('penerima_id', $penerimaId)
                    ->whereIn('status', ['pending', 'accepted'])
                    ->lockForUpdate()
                    ->exists();
            });

            if ($sudahAktif) {
                $validator->errors()->add('penerima_id', 'Anda sudah memiliki permintaan aktif (menunggu respons atau diterima) kepada mahasiswa ini.');
            }
        });
    }
}
