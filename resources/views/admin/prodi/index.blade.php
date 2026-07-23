@extends('layouts.app')

@section('title', '· Admin · Program Studi')

@section('content')
<style>
    /* Card pencarian */
    .tb-search-card {
        background: linear-gradient(135deg, #f7f9fd 0%, #ffffff 60%);
        border: 1px solid var(--tb-primary-light);
        border-radius: 0.85rem; padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }
    .tb-prodi-toolbar { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: stretch; }
    .tb-prodi-toolbar .tb-btn { height: 46px; }
    .tb-prodi-toolbar .tb-select { height: 46px; }
    .tb-prodi-search {
        position: relative; display: flex; align-items: center; flex: 1; min-width: 220px;
    }
    .tb-prodi-search > svg {
        position: absolute; left: 0.95rem; width: 1.05rem; height: 1.05rem;
        color: var(--tb-muted); pointer-events: none;
    }
    .tb-prodi-search .tb-input { width: 100%; height: 46px; font-size: 0.9rem; padding-left: 2.7rem; }
    .tb-prodi-search .tb-input:focus { border-color: var(--tb-primary); box-shadow: 0 0 0 3px rgba(11,37,91,0.10); }
    .tb-prodi-toolbar .tb-select { flex: 0 1 220px; min-width: 180px; }
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

    .tb-prodi-pagination { display: flex; justify-content: center; margin-top: 1rem; }
    .tb-prodi-pagination :is(nav, ul) { margin-bottom: 0; }
    .tb-prodi-row { transition: background 0.12s ease; }
    .tb-prodi-row:hover { background: var(--tb-primary-soft); }
    .tb-prodi-kode {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 48px; font-weight: 700; font-size: 0.72rem;
        padding: 0.3rem 0.6rem; border-radius: 0.45rem;
        background: var(--tb-primary); color: #fff; letter-spacing: 0.02em;
    }
    .tb-prodi-jenjang {
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.7rem;
        padding: 0.22rem 0.5rem; border-radius: 0.35rem; min-width: 38px;
    }
    .tb-prodi-jenjang.s1 { background: #e7f5ff; color: #1c7ed6; }
    .tb-prodi-jenjang.d3 { background: #fff4e3; color: #e88f1e; }
    .tb-prodi-jenjang.d4 { background: #f3f0ff; color: #7048e8; }
    .tb-prodi-fak {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.78rem; font-weight: 600; color: var(--tb-primary);
    }
    .tb-prodi-fak .kode { color: var(--tb-muted); font-weight: 500; }
</style>

{{-- ============ HEADER ============ --}}
<div class="tb-section-head" style="margin-bottom:1rem;">
    <div class="tb-section-head-left">
        <span class="tb-section-icon"><x-icon name="mortarboard" /></span>
        <div>
            <h2 class="tb-section-title">Kelola Program Studi</h2>
            <p class="tb-section-desc">Tambah, ubah, atau hapus data program studi.</p>
        </div>
    </div>
    <a href="{{ route('admin.prodi.create') }}" class="tb-btn tb-btn-sm">
        <x-icon name="plus-lg" /> Tambah
    </a>
</div>

{{-- ============ FILTER ============ --}}
@php
    $prodiFakAktif = request('fakultas_id') ? $fakultasList->firstWhere('id', (int) request('fakultas_id')) : null;
@endphp
<div class="tb-search-card">
    <form method="GET" action="{{ route('admin.prodi.index') }}" class="tb-prodi-toolbar tb-instant-search">
        <div class="tb-prodi-search">
            <x-icon name="search" />
            <input type="text" name="q" class="tb-input" placeholder="Ketik nama atau kode prodi..." value="{{ request('q') }}" autofocus>
        </div>
        <select name="fakultas_id" class="tb-select">
            <option value="">Semua Fakultas</option>
            @foreach ($fakultasList as $f)
                <option value="{{ $f->id }}" @selected((string) request('fakultas_id') === (string) $f->id)>{{ $f->nama }}</option>
            @endforeach
        </select>
    </form>
    @if (request('q') || $prodiFakAktif)
        <div class="tb-search-active">
            <span class="label">Filter aktif:</span>
            @if (request('q'))
                <span class="tb-search-chip">
                    "{{ request('q') }}"
                    <a href="{{ route('admin.prodi.index', array_filter(['fakultas_id' => request('fakultas_id')])) }}" title="Hapus pencarian"><x-icon name="x-lg" /></a>
                </span>
            @endif
            @if ($prodiFakAktif)
                <span class="tb-search-chip">
                    {{ $prodiFakAktif->nama }}
                    <a href="{{ route('admin.prodi.index', array_filter(['q' => request('q')])) }}" title="Hapus filter fakultas"><x-icon name="x-lg" /></a>
                </span>
            @endif
            <a href="{{ route('admin.prodi.index') }}" class="tb-btn tb-btn-ghost tb-btn-sm" style="margin-left:0.25rem;">
                <x-icon name="x-lg" /> Reset Semua
            </a>
            <span class="tb-search-count">{{ $prodiList->total() }} prodi ditemukan</span>
        </div>
    @endif
</div>

{{-- ============ TABEL ============ --}}
<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="list" /></span>
            <div>
                <h2 class="tb-section-title">Daftar Program Studi</h2>
                <p class="tb-section-desc">{{ $prodiList->total() }} program studi terdaftar.</p>
            </div>
        </div>
    </div>

    @if ($prodiList->isEmpty())
        <div class="tb-empty">
            <div class="tb-empty-icon"><x-icon name="inbox" /></div>
            <p class="tb-empty-title">Belum ada program studi terdaftar</p>
            <p class="tb-empty-desc">Tambahkan program studi pertama atau ubah filter pencarian.</p>
            <a href="{{ route('admin.prodi.create') }}" class="tb-btn tb-btn-outline tb-btn-sm">
                <x-icon name="plus-lg" /> Tambah Prodi
            </a>
        </div>
    @else
        <div class="tb-table-wrap">
            <table class="tb-table">
                <thead>
                    <tr>
                        <th style="width:90px;">Kode</th>
                        <th>Nama Program Studi</th>
                        <th style="width:90px;">Jenjang</th>
                        <th>Fakultas</th>
                        <th class="text-end" style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prodiList as $p)
                        <tr class="tb-prodi-row">
                            <td><span class="tb-prodi-kode">{{ $p->kode }}</span></td>
                            <td class="font-semibold" style="color:var(--tb-ink);">{{ $p->nama }}</td>
                            <td><span class="tb-prodi-jenjang {{ strtolower($p->jenjang) }}">{{ $p->jenjang }}</span></td>
                            <td><span class="tb-prodi-fak">{{ $p->fakultas->nama }} <span class="kode">({{ $p->fakultas->kode }})</span></span></td>
                            <td>
                                <div class="tb-crud-actions" style="display:flex;gap:0.35rem;justify-content:flex-end;">
                                    <a href="{{ route('admin.prodi.edit', $p) }}" class="tb-btn tb-btn-outline tb-btn-sm" title="Edit" aria-label="Edit">
                                        <x-icon name="pencil" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.prodi.destroy', $p) }}" onsubmit="return confirm('Hapus prodi {{ $p->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tb-btn tb-btn-danger tb-btn-sm" title="Hapus" aria-label="Hapus">
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
        <div class="tb-prodi-pagination">{{ $prodiList->links() }}</div>
    @endif
</div>
@endsection
