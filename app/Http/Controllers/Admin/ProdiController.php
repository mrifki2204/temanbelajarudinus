<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProdiRequest;
use App\Http\Requests\Admin\UpdateProdiRequest;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdiController extends Controller
{
    public function index(Request $request): View
    {
        $query = Prodi::with('fakultas');

        if ($search = $request->input('q')) {
            $escapedSearch = $this->escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('nama', 'like', "%{$escapedSearch}%")
                    ->orWhere('kode', 'like', "%{$escapedSearch}%");
            });
        }

        if ($fakultasId = $request->input('fakultas_id')) {
            $query->where('fakultas_id', $fakultasId);
        }

        $prodiList = $query->orderBy('fakultas_id')->orderBy('nama')->paginate(15)->withQueryString();
        $fakultasList = Fakultas::orderBy('nama')->get();

        return view('admin.prodi.index', compact('prodiList', 'fakultasList'));
    }

    public function create(): View
    {
        $fakultasList = Fakultas::orderBy('nama')->get();

        return view('admin.prodi.create', compact('fakultasList'));
    }

    public function store(StoreProdiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $prodi = Prodi::create($validated);

        ActivityLog::record(
            'prodi.create',
            "Admin menambahkan program studi {$validated['nama']} ({$validated['kode']}).",
            $prodi,
        );

        return redirect()->route('admin.prodi.index')
            ->with('success', "Program Studi {$validated['nama']} berhasil ditambahkan.");
    }

    public function edit(Prodi $prodi): View
    {
        $fakultasList = Fakultas::orderBy('nama')->get();

        return view('admin.prodi.edit', compact('prodi', 'fakultasList'));
    }

    public function update(UpdateProdiRequest $request, Prodi $prodi): RedirectResponse
    {
        $validated = $request->validated();

        $prodi->update($validated);

        ActivityLog::record(
            'prodi.update',
            "Admin memperbarui program studi {$prodi->nama} ({$prodi->kode}).",
            $prodi,
        );

        return redirect()->route('admin.prodi.index')
            ->with('success', "Program Studi {$prodi->nama} berhasil diperbarui.");
    }

    public function destroy(Prodi $prodi): RedirectResponse
    {
        $mahasiswaCount = User::where('prodi_id', $prodi->id)->count();
        if ($mahasiswaCount > 0) {
            return redirect()->back()
                ->with('error', "Program Studi {$prodi->nama} tidak dapat dihapus karena masih memiliki {$mahasiswaCount} mahasiswa terdaftar.");
        }

        $nama = $prodi->nama;
        $prodi->delete();

        ActivityLog::record(
            'prodi.delete',
            "Admin menghapus program studi {$nama}.",
            null,
        );

        return redirect()->route('admin.prodi.index')
            ->with('success', "Program Studi {$nama} berhasil dihapus.");
    }
}
