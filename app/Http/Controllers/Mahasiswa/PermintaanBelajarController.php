<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\PermintaanStoreRequest;
use App\Models\ActivityLog;
use App\Models\StudyRequest;
use App\Models\User;
use App\Services\StudyRequestService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PermintaanBelajarController extends Controller
{
    /**
     * Tampilkan daftar permintaan belajar (terkirim & diterima).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $permintaanTerkirim = StudyRequest::with(['penerima.profile', 'penerima.prodi'])
            ->where('pengirim_id', $user->id)
            ->orderByDesc('waktu_kirim')
            ->get();

        $permintaanDiterima = StudyRequest::with(['pengirim.profile', 'pengirim.prodi'])
            ->where('penerima_id', $user->id)
            ->orderByDesc('waktu_kirim')
            ->get();

        $jumlahPendingDiterima = $permintaanDiterima->where('status', 'pending')->count();

        return view('mahasiswa.permintaan.index', compact(
            'user',
            'permintaanTerkirim',
            'permintaanDiterima',
            'jumlahPendingDiterima',
        ));
    }

    /**
     * Simpan permintaan belajar baru.
     */
    public function store(PermintaanStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $pengirim = $request->user();

        $permintaan = StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $validated['penerima_id'],
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $penerima = User::find($validated['penerima_id']);

        ActivityLog::record(
            'permintaan.create',
            "{$pengirim->nama} mengirim permintaan belajar kepada {$penerima->nama}.",
            $permintaan,
        );

        return redirect()->back()->with('success', "Permintaan belajar berhasil dikirim kepada {$penerima->nama}.");
    }

    /**
     * Terima permintaan belajar.
     */
    public function accept(Request $request, StudyRequest $permintaan): RedirectResponse
    {
        return $this->processLifecycle(
            fn () => app(StudyRequestService::class)->accept($permintaan, $request->user()),
            "Permintaan dari {$permintaan->pengirim->nama} diterima. Kontak Anda kini dapat dilihat oleh mereka.",
        );
    }

    /**
     * Tolak permintaan belajar.
     */
    public function reject(Request $request, StudyRequest $permintaan): RedirectResponse
    {
        return $this->processLifecycle(
            fn () => app(StudyRequestService::class)->reject($permintaan, $request->user()),
            "Permintaan dari {$permintaan->pengirim->nama} ditolak.",
        );
    }

    /**
     * Batalkan permintaan yang masih pending (oleh pengirim).
     */
    public function cancel(Request $request, StudyRequest $permintaan): RedirectResponse
    {
        return $this->processLifecycle(
            fn () => app(StudyRequestService::class)->cancel($permintaan, $request->user()),
            "Permintaan belajar kepada {$permintaan->penerima->nama} dibatalkan.",
        );
    }

    /**
     * Jalankan aksi lifecycle di service, terjemahkan exception ke flash.
     * AuthorizationException → abort 403; ValidationException (state) → error flash.
     */
    protected function processLifecycle(callable $action, string $successMessage): RedirectResponse
    {
        try {
            $action();
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage() ?: 'Anda tidak berhak melakukan aksi ini.');
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->validator->errors()->first());
        }

        return redirect()->back()->with('success', $successMessage);
    }
}
