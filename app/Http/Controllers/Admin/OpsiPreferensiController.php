<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OpsiPreferensiRequest;
use App\Models\ActivityLog;
use App\Models\OpsiPreferensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OpsiPreferensiController extends Controller
{
    /** Metadata per kategori: warna, ikon, label, deskripsi, placeholder. */
    private array $tipeMeta = [
        'minat'  => ['bg' => '#e7f5ff', 'color' => '#1c7ed6', 'icon' => 'heart',     'label' => 'Minat',        'desc' => 'Bidang minat mahasiswa untuk belajar bersama.', 'placeholder' => 'Programming'],
        'tujuan' => ['bg' => '#fff4e3', 'color' => '#e88f1e', 'icon' => 'bullseye',  'label' => 'Tujuan',       'desc' => 'Tujuan belajar yang ingin dicapai.',           'placeholder' => 'Mencari Ilmu'],
        'gaya'   => ['bg' => '#f3f0ff', 'color' => '#7048e8', 'icon' => 'palette',   'label' => 'Gaya Belajar', 'desc' => 'Gaya belajar yang dipreferensi.',              'placeholder' => 'Visual'],
        'jadwal' => ['bg' => '#e6fcf5', 'color' => '#0ca678', 'icon' => 'calendar3', 'label' => 'Jadwal',       'desc' => 'Slot jadwal luang belajar.',                   'placeholder' => 'Senin Pagi', 'isJadwal' => true],
        'mode'   => ['bg' => '#fff0f6', 'color' => '#d6336c', 'icon' => 'laptop',    'label' => 'Mode Belajar', 'desc' => 'Mode belajar yang dipreferensi.',              'placeholder' => 'Online'],
    ];

    /** Slot jadwal → rentang jam. */
    private array $slotJam = [
        'Pagi'  => '06-11',
        'Siang' => '11-14',
        'Sore'  => '14-18',
        'Malam' => '18-23',
    ];

    /** Hari (urutan). */
    private array $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index(Request $request): View
    {
        $tipeList = ['minat', 'tujuan', 'gaya', 'jadwal', 'mode'];
        $tipeMeta = $this->tipeMeta;
        $tipeAktif = $request->input('tipe', 'minat');
        if (! in_array($tipeAktif, $tipeList, true)) {
            $tipeAktif = 'minat';
        }

        $query = OpsiPreferensi::query();
        $query->where('tipe', $tipeAktif);

        if ($search = $request->input('q')) {
            $query->where('nilai', 'like', '%'.$this->escapeLike($search).'%');
        }

        // Urutkan jadwal per hari (Senin..Minggu) + slot (Pagi..Malam), lainnya alfabetis
        if ($tipeAktif === 'jadwal') {
            $hariOrder = collect($this->hariList)->map(fn ($h, $i) => "'{$h}'")->implode(',');
            $slotOrder = collect(array_keys($this->slotJam))->map(fn ($s, $i) => "'{$s}'")->implode(',');
            $query->orderByRaw("FIELD(SUBSTRING_INDEX(nilai, ' ', 1), {$hariOrder})")
                  ->orderByRaw("FIELD(SUBSTRING_INDEX(SUBSTRING_INDEX(nilai, ' ', 2), ' ', -1), {$slotOrder})");
        } else {
            $query->orderBy('nilai');
        }

        $opsiList = $query->paginate(20)->withQueryString();

        // Count per tipe
        $countPerTipe = [];
        foreach ($tipeList as $t) {
            $countPerTipe[$t] = OpsiPreferensi::where('tipe', $t)->count();
        }

        return view('admin.opsi.index', compact('opsiList', 'tipeList', 'tipeAktif', 'countPerTipe', 'tipeMeta'));
    }

    public function create(Request $request): View
    {
        $tipeList = ['minat', 'tujuan', 'gaya', 'jadwal', 'mode'];
        $tipeMeta = $this->tipeMeta;
        $tipeDipilih = $request->input('tipe');
        if (! in_array($tipeDipilih, $tipeList, true)) {
            $tipeDipilih = null;
        }

        return view('admin.opsi.create', compact('tipeList', 'tipeDipilih', 'tipeMeta'))
            ->with('slotJam', $this->slotJam)
            ->with('hariList', $this->hariList);
    }

    public function store(OpsiPreferensiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Untuk jadwal: gabung hari + slot + (jam) menjadi nilai
        if ($validated['tipe'] === 'jadwal') {
            $jam = $this->slotJam[$validated['slot']] ?? '';
            $validated['nilai'] = "{$validated['hari']} {$validated['slot']} ({$jam})";
        } else {
            // Kategori biasa wajib ada nilai (input teks)
            if (empty($validated['nilai'])) {
                throw ValidationException::withMessages(['nilai' => 'Item wajib diisi.']);
            }
        }

        // Cek unique composite manual agar pesan lebih jelas
        $exists = OpsiPreferensi::where('tipe', $validated['tipe'])
            ->where('nilai', $validated['nilai'])
            ->exists();
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', "{$validated['nilai']} untuk kategori '{$validated['tipe']}' sudah ada.");
        }

        $opsi = OpsiPreferensi::create($validated);

        ActivityLog::record(
            'opsi.create',
            "Admin menambahkan item preferensi '{$validated['nilai']}' ({$this->tipeMeta[$validated['tipe']]['label']}).",
            $opsi,
        );

        Cache::forget('cbf.dimensions');

        return redirect()->route('admin.opsi.index', ['tipe' => $validated['tipe']])
            ->with('success', "{$validated['nilai']} berhasil ditambahkan.");
    }

    public function edit(OpsiPreferensi $opsi): View
    {
        $tipeList = ['minat', 'tujuan', 'gaya', 'jadwal', 'mode'];
        $tipeMeta = $this->tipeMeta;

        // Parse nilai jadwal (mis. "Senin Pagi (06-11)") → hari & slot untuk form
        $jadwalHari = null;
        $jadwalSlot = null;
        if ($opsi->tipe === 'jadwal' && preg_match('/^(\w+)\s+(Pagi|Siang|Sore|Malam)/', $opsi->nilai, $m)) {
            $jadwalHari = $m[1];
            $jadwalSlot = $m[2];
        }

        return view('admin.opsi.edit', compact('opsi', 'tipeList', 'tipeMeta', 'jadwalHari', 'jadwalSlot'))
            ->with('slotJam', $this->slotJam)
            ->with('hariList', $this->hariList);
    }

    public function update(OpsiPreferensiRequest $request, OpsiPreferensi $opsi): RedirectResponse
    {
        $validated = $request->validated();

        // Untuk jadwal: gabung hari + slot + (jam) menjadi nilai
        if ($validated['tipe'] === 'jadwal') {
            $jam = $this->slotJam[$validated['slot']] ?? '';
            $validated['nilai'] = "{$validated['hari']} {$validated['slot']} ({$jam})";
        } else {
            if (empty($validated['nilai'])) {
                throw ValidationException::withMessages(['nilai' => 'Item wajib diisi.']);
            }
        }

        $exists = OpsiPreferensi::where('tipe', $validated['tipe'])
            ->where('nilai', $validated['nilai'])
            ->where('id', '!=', $opsi->id)
            ->exists();
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', "{$validated['nilai']} untuk kategori '{$validated['tipe']}' sudah ada.");
        }

        $opsi->update($validated);

        ActivityLog::record(
            'opsi.update',
            "Admin memperbarui item preferensi '{$validated['nilai']}' ({$this->tipeMeta[$validated['tipe']]['label']}).",
            $opsi,
        );

        Cache::forget('cbf.dimensions');

        return redirect()->route('admin.opsi.index', ['tipe' => $validated['tipe']])
            ->with('success', "{$validated['nilai']} berhasil diperbarui.");
    }

    public function destroy(OpsiPreferensi $opsi): RedirectResponse
    {
        $tipe = $opsi->tipe;
        $nilai = $opsi->nilai;
        $opsi->delete();

        ActivityLog::record(
            'opsi.delete',
            "Admin menghapus item preferensi '{$nilai}' ({$this->tipeMeta[$tipe]['label']}).",
            null,
        );

        Cache::forget('cbf.dimensions');

        return redirect()->route('admin.opsi.index', ['tipe' => $tipe])
            ->with('success', "Item '{$nilai}' berhasil dihapus.");
    }
}
