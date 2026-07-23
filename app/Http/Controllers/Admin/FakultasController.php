<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFakultasRequest;
use App\Http\Requests\Admin\UpdateFakultasRequest;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FakultasController extends Controller
{
    public function index(Request $request): View
    {
        $query = Fakultas::withCount('prodi');

        if ($search = $request->input('q')) {
            $escapedSearch = $this->escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('nama', 'like', "%{$escapedSearch}%")
                    ->orWhere('kode', 'like', "%{$escapedSearch}%");
            });
        }

        $fakultasList = $query->orderBy('nama')->paginate(15)->withQueryString();

        return view('admin.fakultas.index', compact('fakultasList'));
    }

    public function create(): View
    {
        return view('admin.fakultas.create');
    }

    public function store(StoreFakultasRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $fakultas = Fakultas::create($validated);

        ActivityLog::record(
            'fakultas.create',
            "Admin menambahkan fakultas {$validated['nama']} ({$validated['kode']}).",
            $fakultas,
        );

        return redirect()->route('admin.fakultas.index')
            ->with('success', "Fakultas {$validated['nama']} berhasil ditambahkan.");
    }

    public function edit(Fakultas $fakultas): View
    {
        return view('admin.fakultas.edit', compact('fakultas'));
    }

    public function update(UpdateFakultasRequest $request, Fakultas $fakultas): RedirectResponse
    {
        $validated = $request->validated();

        $fakultas->update($validated);

        ActivityLog::record(
            'fakultas.update',
            "Admin memperbarui fakultas {$fakultas->nama} ({$fakultas->kode}).",
            $fakultas,
        );

        return redirect()->route('admin.fakultas.index')
            ->with('success', "Fakultas {$fakultas->nama} berhasil diperbarui.");
    }

    public function destroy(Fakultas $fakultas): RedirectResponse
    {
        if ($fakultas->prodi()->exists()) {
            return redirect()->back()
                ->with('error', "Fakultas {$fakultas->nama} tidak dapat dihapus karena masih memiliki program studi. Hapus atau pindahkan prodi terlebih dahulu.");
        }

        $nama = $fakultas->nama;
        $fakultas->delete();

        ActivityLog::record(
            'fakultas.delete',
            "Admin menghapus fakultas {$nama}.",
            null,
        );

        return redirect()->route('admin.fakultas.index')
            ->with('success', "Fakultas {$nama} berhasil dihapus.");
    }
}
