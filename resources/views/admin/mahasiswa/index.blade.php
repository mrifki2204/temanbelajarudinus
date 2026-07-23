@extends('layouts.app')

@section('title', '· Admin · Kelola Mahasiswa')

@section('content')
{{-- ============ CSS SPESIFIK HALAMAN (prefix tb-admin-*) ============ --}}
<style>
    /* Card pencarian */
    .tb-search-card {
        background: linear-gradient(135deg, #f7f9fd 0%, #ffffff 60%);
        border: 1px solid var(--tb-primary-light);
        border-radius: 0.85rem; padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }
    .tb-mhs-toolbar { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: stretch; }
    .tb-mhs-toolbar .tb-btn { height: 46px; }
    .tb-mhs-search { position: relative; display: flex; align-items: center; flex: 1; min-width: 220px; }
    .tb-mhs-search > svg {
        position: absolute; left: 0.95rem; width: 1.05rem; height: 1.05rem;
        color: var(--tb-muted); pointer-events: none;
    }
    .tb-mhs-search .tb-input { width: 100%; height: 46px; font-size: 0.9rem; padding-left: 2.7rem; }
    .tb-mhs-toolbar .tb-select { flex: 0 1 200px; min-width: 160px; height: 46px; }
    .tb-search-active {
        display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
        margin-top: 0.85rem; padding-top: 0.85rem;
        border-top: 1px dashed var(--tb-primary-light);
        font-size: 0.78rem; color: var(--tb-muted);
    }
    .tb-search-active .label { font-weight: 600; color: var(--tb-ink); }
    .tb-search-chip {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: var(--tb-accent-light); color: var(--tb-accent-dark);
        border: 1px solid #f7d9a8; border-radius: 999px;
        padding: 0.28rem 0.4rem 0.28rem 0.75rem; font-weight: 600; font-size: 0.76rem;
    }
    .tb-search-chip a { display: inline-flex; color: var(--tb-accent-dark); text-decoration: none; opacity: 0.75; }
    .tb-search-chip a:hover { opacity: 1; }
    .tb-search-chip svg { width: 0.85rem; height: 0.85rem; }
    .tb-search-count { margin-left: auto; font-weight: 600; color: var(--tb-primary); }

    .tb-admin-pagination { display: flex; justify-content: center; margin-top: 1rem; }
    .tb-admin-pagination :is(nav, ul) { margin-bottom: 0; }
    .tb-admin-row-nonaktif { background: var(--tb-primary-soft); }
    .tb-admin-row-actions { display: flex; gap: 0.35rem; justify-content: flex-end; align-items: center; }
    /* Pemisah visual antara aksi umum (lihat/edit) & aksi status/destruktif */
    .tb-admin-row-actions .tb-act-sep {
        width: 1px; height: 1.4rem; background: var(--tb-primary-light);
        margin: 0 0.15rem; flex-shrink: 0;
    }

    /* ============ Tabel mahasiswa: lebar kolom & alignment konsisten ============ */
    .tb-mhs-table { table-layout: auto; }
    .tb-mhs-table th, .tb-mhs-table td { white-space: nowrap; }
    .tb-mhs-table .col-nama { min-width: 160px; }
    .tb-mhs-table .col-nim { width: 130px; }
    .tb-mhs-table .col-email { min-width: 200px; }
    .tb-mhs-table .col-prodi { min-width: 170px; }
    .tb-mhs-table .col-fakultas { min-width: 160px; }
    .tb-mhs-table .col-status { width: 100px; }
    .tb-mhs-table .col-profil { width: 80px; }
    .tb-mhs-table .col-aksi { width: 240px; }
    /* Email & teks panjang boleh wrap bila ruang sempit */
    .tb-mhs-table .tb-cell-wrap { white-space: normal; word-break: break-word; }
    /* Sel indikator (status, profil) center; nama left; aksi right */
    .tb-mhs-table .tb-cell-c { text-align: center; }
    .tb-mhs-table .tb-cell-r { text-align: right; }
</style>

{{-- ============ HEADER ============ --}}
<div class="tb-section-head" style="margin-bottom:1rem;">
    <div class="tb-section-head-left">
        <span class="tb-section-icon"><x-icon name="people" /></span>
        <div>
            <h2 class="tb-section-title">Kelola Akun Mahasiswa</h2>
            <p class="tb-section-desc">Lihat, edit, dan nonaktifkan akun mahasiswa terdaftar.</p>
        </div>
    </div>
</div>

{{-- ============ FILTER ============ --}}
@php
    $filterAktif = request('q') || request('fakultas_id') || request('status');
@endphp
<div class="tb-search-card">
    <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="tb-mhs-toolbar tb-instant-search">
        <div class="tb-mhs-search">
            <x-icon name="search" />
            <input type="text" name="q" class="tb-input" placeholder="Cari nama, NIM, atau email..." value="{{ request('q') }}" autofocus>
        </div>
        <select name="fakultas_id" class="tb-select">
            <option value="">Semua Fakultas</option>
            @foreach ($fakultasList as $fid => $nama)
                <option value="{{ $fid }}" @selected(request('fakultas_id') == $fid)>{{ $nama }}</option>
            @endforeach
        </select>
        <select name="status" class="tb-select">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        @if ($filterAktif)
            <a href="{{ route('admin.mahasiswa.index') }}" class="tb-btn tb-btn-ghost">
                <x-icon name="x-lg" /> Reset
            </a>
        @endif
    </form>
    @if ($filterAktif)
        @php
            $mhsFakAktif = request('fakultas_id') ? ($fakultasList[request('fakultas_id')] ?? null) : null;
            $mhsStatusAktif = request('status');
        @endphp
        <div class="tb-search-active">
            <span class="label">Filter aktif:</span>
            @if (request('q'))
                <span class="tb-search-chip">
                    "{{ request('q') }}"
                    <a href="{{ route('admin.mahasiswa.index', array_filter(request()->except('q'))) }}" title="Hapus pencarian"><x-icon name="x-lg" /></a>
                </span>
            @endif
            @if ($mhsFakAktif)
                <span class="tb-search-chip">
                    {{ $mhsFakAktif }}
                    <a href="{{ route('admin.mahasiswa.index', array_filter(request()->except('fakultas_id'))) }}" title="Hapus filter fakultas"><x-icon name="x-lg" /></a>
                </span>
            @endif
            @if ($mhsStatusAktif)
                <span class="tb-search-chip">
                    {{ ucfirst($mhsStatusAktif) }}
                    <a href="{{ route('admin.mahasiswa.index', array_filter(request()->except('status'))) }}" title="Hapus filter status"><x-icon name="x-lg" /></a>
                </span>
            @endif
            <span class="tb-search-count">{{ $mahasiswaList->total() }} mahasiswa ditemukan</span>
        </div>
    @endif
</div>

{{-- ============ TABEL ============ --}}
<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="people" /></span>
            <div>
                <h2 class="tb-section-title">Daftar Mahasiswa</h2>
                <p class="tb-section-desc">{{ $mahasiswaList->total() }} mahasiswa terdaftar.</p>
            </div>
        </div>
    </div>

    @if ($mahasiswaList->isEmpty())
        <div class="tb-empty">
            <div class="tb-empty-icon"><x-icon name="search" /></div>
            <p class="tb-empty-title">Tidak ada mahasiswa ditemukan</p>
            <p class="tb-empty-desc">Belum ada mahasiswa yang cocok dengan filter. Coba ubah kata kunci atau reset filter.</p>
        </div>
    @else
        <div class="tb-table-wrap">
            <table class="tb-table tb-mhs-table">
                <thead>
                    <tr>
                        <th class="col-nama">Nama</th>
                        <th class="col-nim">NIM</th>
                        <th class="col-email">Email</th>
                        <th class="col-prodi">Program Studi</th>
                        <th class="col-fakultas">Fakultas</th>
                        <th class="col-status tb-cell-c">Status</th>
                        <th class="col-profil tb-cell-c">Profil</th>
                        <th class="col-aksi tb-cell-r">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mahasiswaList as $m)
                        <tr @if ($m->status === 'nonaktif') class="tb-admin-row-nonaktif" @endif>
                            <td class="col-nama font-semibold">
                                <a href="{{ route('admin.mahasiswa.show', $m) }}" class="no-underline" style="color: var(--tb-ink);">
                                    {{ $m->nama }}
                                </a>
                            </td>
                            <td class="col-nim tb-text-sm">{{ $m->nim ?? '-' }}</td>
                            <td class="col-email tb-text-sm tb-muted tb-cell-wrap">{{ $m->email }}</td>
                            <td class="col-prodi tb-text-sm">{{ $m->prodi?->nama ?? '-' }}</td>
                            <td class="col-fakultas tb-text-sm">{{ $m->fakultas?->nama ?? '-' }}</td>
                            <td class="col-status tb-cell-c">
                                @if ($m->status === 'aktif')
                                    <span class="tb-badge tb-badge-success">Aktif</span>
                                @else
                                    <span class="tb-badge tb-badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="col-profil tb-cell-c">
                                @if ($m->profile)
                                    <x-icon name="check-circle-fill" style="color: var(--tb-accent-dark);" title="Profil lengkap" />
                                @else
                                    <x-icon name="x-circle" class="tb-muted" title="Profil belum diisi" />
                                @endif
                            </td>
                            <td class="col-aksi tb-cell-r">
                                <div class="tb-admin-row-actions">
                                    {{-- Aksi umum: Lihat & Edit --}}
                                    <a href="{{ route('admin.mahasiswa.show', $m) }}" class="tb-btn tb-btn-outline tb-btn-sm" title="Lihat detail" aria-label="Lihat detail">
                                        <x-icon name="eye" />
                                    </a>
                                    <a href="{{ route('admin.mahasiswa.edit', $m) }}" class="tb-btn tb-btn-outline tb-btn-sm" title="Edit" aria-label="Edit">
                                        <x-icon name="pencil" />
                                    </a>

                                    <span class="tb-act-sep" aria-hidden="true"></span>

                                    {{-- Aksi status: Aktifkan / Nonaktifkan --}}
                                    @if ($m->status === 'aktif')
                                        <form method="POST" action="{{ route('admin.mahasiswa.toggle-status', $m) }}" class="inline-flex" onsubmit="return confirm('Nonaktifkan akun {{ $m->nama }}? Mahasiswa tidak akan dapat login.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="tb-btn tb-btn-danger tb-btn-sm" title="Nonaktifkan" aria-label="Nonaktifkan">
                                                <x-icon name="slash-circle" />
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.mahasiswa.toggle-status', $m) }}" class="inline-flex" onsubmit="return confirm('Aktifkan kembali akun {{ $m->nama }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="tb-btn tb-btn-sm" title="Aktifkan" aria-label="Aktifkan" style="color: var(--tb-accent-dark); border-color: var(--tb-accent-dark);">
                                                <x-icon name="check-circle" />
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Aksi destruktif: Hapus permanen (bisa untuk akun aktif maupun nonaktif) --}}
                                    <form method="POST" action="{{ route('admin.mahasiswa.destroy', $m) }}" class="inline-flex" onsubmit="return confirm('HAPUS PERMANEN akun {{ $m->nama }}?\n\nSemua data terkait (profil, rekomendasi, permintaan belajar) akan ikut terhapus. Aksi ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tb-btn tb-btn-danger tb-btn-sm" title="Hapus permanen" aria-label="Hapus permanen">
                                            <x-icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tb-admin-pagination">{{ $mahasiswaList->links() }}</div>
    @endif
</div>
@endsection
