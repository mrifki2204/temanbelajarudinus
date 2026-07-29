<?php

namespace App\Observers;

use App\Models\Profile;
use App\Models\SimilarityScore;
use App\Models\User;
use App\Services\ContentBasedFilteringService;

class ProfileObserver
{
    public function __construct(
        protected ContentBasedFilteringService $cbfService
    ) {}

    /**
     * Saat profil dibuat/diupdate: hitung ulang feature_vector & similarity_scores.
     * Recalc melibatkan:
     * - user pemilik profil (terhadap seluruh kandidat) — arah forward
     * - setiap user lain (profil lengkap) terhadap user ini — arah reverse
     *   (penting saat user baru dibuat: user lama belum punya skor ke user baru)
     */
    public function saved(Profile $profile): void
    {
        $owner = User::find($profile->user_id);
        if (! $owner || $owner->role !== 'mahasiswa' || $owner->status !== 'aktif') {
            return;
        }

        // Jika profil owner belum lengkap, hapus skor lama owner (forward & reverse)
        if (! $this->cbfService->isProfileLengkap($profile)) {
            SimilarityScore::where('user_id', $owner->id)->delete();
            SimilarityScore::where('kandidat_id', $owner->id)->delete();
            return;
        }

        // 1. Hitung ulang skor owner → seluruh kandidat (forward)
        $this->cbfService->calculateForUser($owner);

        // 2. Update skor reverse: seluruh user lain (profil lengkap) → owner
        $this->cbfService->recalcReverseScores($owner);
    }

    /**
     * Saat profil dihapus: hapus semua skor terkait user ini.
     */
    public function deleted(Profile $profile): void
    {
        $owner = User::find($profile->user_id);
        if (! $owner) {
            return;
        }

        SimilarityScore::where('user_id', $owner->id)->delete();
        SimilarityScore::where('kandidat_id', $owner->id)->delete();
    }
}
