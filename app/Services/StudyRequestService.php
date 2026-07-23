<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\StudyRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

/**
 * Mengelola lifecycle permintaan belajar: accept, reject, cancel.
 *
 * Memisahkan authorization + aturan transisi status dari HTTP layer
 * (controller). Service melempar exception:
 * - AuthorizationException → 403 (bukan pihak yang berhak)
 * - ValidationException   → state invalid (sudah diproses / bukan pending)
 */
class StudyRequestService
{
    /**
     * Penerima menerima permintaan.
     *
     * @throws AuthorizationException  Bukan penerima yang berhak.
     * @throws ValidationException     Status bukan pending.
     */
    public function accept(StudyRequest $permintaan, User $actor): StudyRequest
    {
        $this->ensurePenerima($permintaan, $actor);
        $this->ensurePending($permintaan, 'Permintaan ini sudah diproses sebelumnya.');

        $permintaan->update(['status' => 'accepted']);

        $pengirim = $permintaan->pengirim;
        ActivityLog::record(
            'permintaan.accept',
            "{$actor->nama} menerima permintaan belajar dari {$pengirim->nama}.",
            $permintaan,
        );

        return $permintaan;
    }

    /**
     * Penerima menolak permintaan.
     *
     * @throws AuthorizationException  Bukan penerima yang berhak.
     * @throws ValidationException     Status bukan pending.
     */
    public function reject(StudyRequest $permintaan, User $actor): StudyRequest
    {
        $this->ensurePenerima($permintaan, $actor);
        $this->ensurePending($permintaan, 'Permintaan ini sudah diproses sebelumnya.');

        $permintaan->update(['status' => 'rejected']);

        $pengirim = $permintaan->pengirim;
        ActivityLog::record(
            'permintaan.reject',
            "{$actor->nama} menolak permintaan belajar dari {$pengirim->nama}.",
            $permintaan,
        );

        return $permintaan;
    }

    /**
     * Pengirim membatalkan permintaan yang masih pending.
     *
     * @throws AuthorizationException  Bukan pengirim yang berhak.
     * @throws ValidationException     Status bukan pending.
     */
    public function cancel(StudyRequest $permintaan, User $actor): StudyRequest
    {
        if ((int) $permintaan->pengirim_id !== (int) $actor->id) {
            throw new AuthorizationException('Anda tidak berhak membatalkan permintaan ini.');
        }
        $this->ensurePending($permintaan, 'Hanya permintaan yang masih menunggu respons yang dapat dibatalkan.');

        $penerima = $permintaan->penerima;
        $permintaan->delete();

        ActivityLog::record(
            'permintaan.cancel',
            "{$actor->nama} membatalkan permintaan belajar kepada {$penerima->nama}.",
            null,
        );

        return $permintaan;
    }

    protected function ensurePenerima(StudyRequest $permintaan, User $actor): void
    {
        if ((int) $permintaan->penerima_id !== (int) $actor->id) {
            throw new AuthorizationException('Anda tidak berhak memproses permintaan ini.');
        }
    }

    protected function ensurePending(StudyRequest $permintaan, string $message): void
    {
        if ($permintaan->status !== 'pending') {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }
}
