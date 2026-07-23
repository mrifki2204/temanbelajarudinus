<?php

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'minat',
        'tujuan',
        'gaya',
        'jadwal',
        'mode',
        'whatsapp',
        'instagram',
        'feature_vector',
    ];

    protected function casts(): array
    {
        return [
            'minat' => 'array',
            'jadwal' => 'array',
            'feature_vector' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Apakah 5 atribut preferensi (untuk CBF) sudah terisi?
     * Dipakai ContentBasedFilteringService & ProfileObserver.
     */
    public function isPreferensiLengkap(): bool
    {
        return ! empty($this->minat)
            && ! empty($this->tujuan)
            && ! empty($this->gaya)
            && ! empty($this->jadwal)
            && ! empty($this->mode);
    }

    /**
     * Apakah profil onboarding lengkap (5 preferensi + kontak)?
     * Dipakai untuk gate onboarding vs halaman profil normal.
     */
    public function isOnboardingComplete(): bool
    {
        return $this->isPreferensiLengkap()
            && ! empty($this->whatsapp)
            && ! empty($this->instagram);
    }
}
