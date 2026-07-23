@extends('layouts.app')

@section('title', '· Admin · Fakultas')

@section('content')
<style>
    /* Card pencarian */
    .tb-search-card {
        background: linear-gradient(135deg, #f7f9fd 0%, #ffffff 60%);
        border: 1px solid var(--tb-primary-light);
        border-radius: 0.85rem; padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }
    .tb-fak-toolbar { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: stretch; }
    .tb-fak-toolbar .tb-btn { height: 46px; }
    .tb-fak-search {
        position: relative; display: flex; align-items: center; flex: 1; min-width: 220px;
    }
    .tb-fak-search > svg {
        position: absolute; left: 0.95rem; width: 1.05rem; height: 1.05rem;
        color: var(--tb-muted); pointer-events: none;
    }
    .tb-fak-search .tb-input { width: 100%; height: 46px; font-size: 0.9rem; padding-left: 2.7rem; }
    .tb-fak-search .tb-input:focus { border-color: var(--tb-primary); box-shadow: 0 0 0 3px rgba(11,37,91,0.10); }
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
    .tb-fak-pagination { display: flex; justify-content: center; margin-top: 1rem; }
    .tb-fak-pagination :is(nav, ul) { margin-bottom: 0; }
    .tb-fak-row { transition: background 0.12s ease; }
    .tb-fak-row:hover { background: var(--tb-primary-soft); }
    .tb-fak-kode {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 48px; font-weight: 700; font-size: 0.72rem;
        padding: 0.3rem 0.6rem; border-radius: 0.45rem;
        background: var(--tb-primary); color: #fff; letter-spacing: 0.02em;
    }
    .tb-fak-count {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.78rem; font-weight: 600; color: var(--tb-primary);
        background: var(--tb-primary-light); padding: 0.22rem 0.55rem; border-radius: 999px;
    }
    .tb-fak-count svg { width: 0.85rem; height: 0.85rem; }
</style>

{{-- ============ HEADER ============ --}}
<div class="tb-section-head" style="margin-bottom:1rem;">
    <div class="tb-section-head-left">
        <span class="tb-section-icon"><x-icon name="diagram-3" /></span>
        <div>
            <h2 class="tb-section-title">Kelola Fakultas</h2>
            <p class="tb-section-desc">Tambah, ubah, atau hapus data fakultas UDINUS.</p>
        </div>
    </div>
    <a href="{{ route('admin.fakultas.create') }}" class="tb-btn tb-btn-sm">
        <x-icon name="plus-lg" /> Tambah
    </a>
</div>

{{-- ============ FILTER ============ --}}
<div class="tb-search-card">
    <form method="GET" action="{{ route('admin.fakultas.index') }}" class="tb-fak-toolbar tb-instant-search">
        <div class="tb-fak-search">
            <x-icon name="search" />
            <input type="text" name="q" class="tb-input" placeholder="Ketik nama atau kode fakultas..." value="{{ request('q') }}" autofocus>
        </div>
    </form>
    @if (request('q'))
        <div class="tb-search-active">
            <span class="label">Menampilkan hasil untuk:</span>
            <span class="tb-search-chip">
                "{{ request('q') }}"
                <a href="{{ route('admin.fakultas.index') }}" title="Hapus pencarian"><x-icon name="x-lg" /></a>
            </span>
            <span class="tb-search-count">{{ $fakultasList->total() }} fakultas ditemukan</span>
        </div>
    @endif
</div>

{{-- ============ TABEL ============ --}}
<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="list" /></span>
            <div>
                <h2 class="tb-section-title">Daftar Fakultas</h2>
                <p class="tb-section-desc">{{ $fakultasList->total() }} fakultas terdaftar.</p>
            </div>
        </div>
    </div>

    @if ($fakultasList->isEmpty())
        <div class="tb-empty">
            <div class="tb-empty-icon"><x-icon name="inbox" /></div>
            <p class="tb-empty-title">Belum ada fakultas terdaftar</p>
            <p class="tb-empty-desc">Tambahkan fakultas pertama untuk mulai mengelola program studi.</p>
            <a href="{{ route('admin.fakultas.create') }}" class="tb-btn tb-btn-outline tb-btn-sm">
                <x-icon name="plus-lg" /> Tambah Fakultas
            </a>
        </div>
    @else
        <div class="tb-table-wrap">
            <table class="tb-table">
                <thead>
                    <tr>
                        <th style="width:90px;">Kode</th>
                        <th>Nama Fakultas</th>
                        <th class="text-center" style="width:140px;">Jumlah Prodi</th>
                        <th class="text-end" style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fakultasList as $f)
                        <tr class="tb-fak-row">
                            <td><span class="tb-fak-kode">{{ $f->kode }}</span></td>
                            <td class="font-semibold" style="color:var(--tb-ink);">{{ $f->nama }}</td>
                            <td class="text-center"><span class="tb-fak-count"><x-icon name="mortarboard" /> {{ $f->prodi_count }}</span></td>
                            <td>
                                <div class="tb-crud-actions" style="display:flex;gap:0.35rem;justify-content:flex-end;">
                                    <a href="{{ route('admin.fakultas.edit', $f) }}" class="tb-btn tb-btn-outline tb-btn-sm" title="Edit" aria-label="Edit">
                                        <x-icon name="pencil" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.fakultas.destroy', $f) }}" onsubmit="return confirm('Hapus fakultas {{ $f->nama }}?');">
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
        <div class="tb-fak-pagination">{{ $fakultasList->links() }}</div>
    @endif
</div>
@endsection
