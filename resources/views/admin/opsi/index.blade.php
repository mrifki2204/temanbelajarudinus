@extends('layouts.app')

@section('title', '· Admin · ' . $tipeMeta[$tipeAktif]['label'])

@section('content')
@php
    $tw = $tipeMeta[$tipeAktif];
    $labelItem = $tw['label'];
@endphp
<style>
    /* Hero kategori */
    .tb-opsi-hero {
        background: linear-gradient(135deg, {{ $tw['bg'] }} 0%, #ffffff 70%);
        border: 1px solid var(--tb-primary-light);
        border-radius: 0.85rem; padding: 1.1rem 1.25rem;
        margin-bottom: 1rem; display: flex; align-items: center; gap: 0.85rem;
    }
    .tb-opsi-hero-icon {
        width: 46px; height: 46px; border-radius: 0.6rem; flex-shrink: 0;
        background: {{ $tw['bg'] }}; color: {{ $tw['color'] }};
        display: flex; align-items: center; justify-content: center;
        border: 1px solid {{ $tw['color'] }}22;
    }
    .tb-opsi-hero-icon svg { width: 1.4rem; height: 1.4rem; }
    .tb-opsi-hero-info { flex: 1; min-width: 0; }
    .tb-opsi-hero-title {
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 1.05rem; font-weight: 800; color: var(--tb-ink); margin: 0;
        letter-spacing: -0.01em;
    }
    .tb-opsi-hero-badge {
        font-size: 0.66rem; font-weight: 700; padding: 0.15rem 0.5rem;
        border-radius: 999px; background: {{ $tw['bg'] }}; color: {{ $tw['color'] }};
        text-transform: capitalize;
    }
    .tb-opsi-hero-desc { font-size: 0.78rem; color: var(--tb-muted); margin: 0.15rem 0 0; }
    .tb-opsi-hero-actions { display: flex; gap: 0.5rem; flex-shrink: 0; }

    /* Pencarian dalam kategori */
    .tb-opsi-search-bar { margin-bottom: 1rem; }
    .tb-opsi-toolbar { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: stretch; }
    .tb-opsi-toolbar .tb-btn { height: 46px; }
    .tb-opsi-search { position: relative; display: flex; align-items: center; flex: 1; min-width: 220px; }
    .tb-opsi-search > svg {
        position: absolute; left: 0.95rem; width: 1.05rem; height: 1.05rem;
        color: var(--tb-muted); pointer-events: none;
    }
    .tb-opsi-search .tb-input { width: 100%; height: 46px; font-size: 0.9rem; padding-left: 2.7rem; }
    .tb-opsi-search .tb-input:focus { border-color: var(--tb-primary); box-shadow: 0 0 0 3px rgba(11,37,91,0.10); }
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

    /* Tabel */
    .tb-opsi-row { transition: background 0.12s ease; }
    .tb-opsi-row:hover { background: var(--tb-primary-soft); }
    .tb-opsi-item { font-weight: 600; color: var(--tb-ink); display: flex; align-items: center; gap: 0.6rem; }
    .tb-opsi-item-dot { width: 8px; height: 8px; border-radius: 50%; background: {{ $tw['color'] }}; flex-shrink: 0; }
    .tb-crud-actions { display: flex; gap: 0.35rem; justify-content: flex-end; }

    /* Tampilan khusus jadwal */
    .tb-jadwal-view { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .tb-jadwal-hari {
        display: inline-flex; align-items: center; justify-content: center;
        background: {{ $tw['color'] }}; color: #fff;
        font-weight: 700; font-size: 0.76rem;
        padding: 0.3rem 0.65rem; border-radius: 0.45rem; min-width: 64px;
    }
    .tb-jadwal-slot { font-weight: 700; color: var(--tb-ink); font-size: 0.84rem; }
    .tb-jadwal-jam-pill {
        font-size: 0.68rem; font-weight: 700; color: var(--tb-accent-dark);
        background: var(--tb-accent-light); padding: 0.15rem 0.5rem; border-radius: 999px;
    }
</style>

{{-- ============ HERO KATEGORI ============ --}}
<div class="tb-opsi-hero">
    <span class="tb-opsi-hero-icon"><x-icon name="{{ $tw['icon'] }}" /></span>
    <div class="tb-opsi-hero-info">
        <h2 class="tb-opsi-hero-title">
            {{ $labelItem }}
            <span class="tb-opsi-hero-badge">{{ $opsiList->total() }} item</span>
        </h2>
        <p class="tb-opsi-hero-desc">{{ $tw['desc'] }}</p>
    </div>
    <div class="tb-opsi-hero-actions">
        <a href="{{ route('admin.opsi.create') }}?tipe={{ $tipeAktif }}" class="tb-btn tb-btn-sm">
            <x-icon name="plus-lg" /> Tambah {{ $labelItem }}
        </a>
    </div>
</div>

{{-- ============ PENCARIAN ============ --}}
<div class="tb-opsi-search-bar">
    <form method="GET" action="{{ route('admin.opsi.index', ['tipe' => $tipeAktif]) }}" class="tb-opsi-toolbar tb-instant-search">
        <input type="hidden" name="tipe" value="{{ $tipeAktif }}">
        <div class="tb-opsi-search">
            <x-icon name="search" />
            <input type="text" name="q" class="tb-input" placeholder="Cari {{ strtolower($labelItem) }}..." value="{{ request('q') }}" autofocus>
        </div>
    </form>
    @if (request('q'))
        <div class="tb-search-active">
            <span class="label">Menampilkan hasil untuk:</span>
            <span class="tb-search-chip">
                "{{ request('q') }}"
                <a href="{{ route('admin.opsi.index', ['tipe' => $tipeAktif]) }}" title="Hapus pencarian"><x-icon name="x-lg" /></a>
            </span>
            <span class="tb-search-count">{{ $opsiList->total() }} {{ strtolower($labelItem) }} ditemukan</span>
        </div>
    @endif
</div>

{{-- ============ TABEL ITEM ============ --}}
<div class="tb-card">
    @if ($opsiList->isEmpty())
        <div class="tb-empty">
            <div class="tb-empty-icon"><x-icon name="inbox" /></div>
            <p class="tb-empty-title">Belum ada {{ strtolower($labelItem) }}</p>
            <p class="tb-empty-desc">Tambahkan {{ strtolower($labelItem) }} pertama untuk kategori preferensi ini.</p>
            <a href="{{ route('admin.opsi.create') }}?tipe={{ $tipeAktif }}" class="tb-btn tb-btn-outline tb-btn-sm">
                <x-icon name="plus-lg" /> Tambah {{ $labelItem }}
            </a>
        </div>
    @else
        <div class="tb-table-wrap">
            <table class="tb-table">
                <thead>
                    <tr>
                        <th>{{ $labelItem }}</th>
                        <th class="text-end" style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($opsiList as $o)
                        <tr class="tb-opsi-row">
                            <td>
                                @php
                                    $isJadwal = $tipeAktif === 'jadwal';
                                    $jHari = null; $jSlot = null; $jJam = null;
                                    if ($isJadwal && preg_match('/^(\w+)\s+(Pagi|Siang|Sore|Malam)\s*\(([^)]+)\)/', $o->nilai, $jm)) {
                                        $jHari = $jm[1]; $jSlot = $jm[2]; $jJam = $jm[3];
                                    }
                                @endphp
                                @if ($isJadwal && $jHari)
                                    <div class="tb-jadwal-view">
                                        <span class="tb-jadwal-hari">{{ $jHari }}</span>
                                        <span class="tb-jadwal-slot">{{ $jSlot }}</span>
                                        <span class="tb-jadwal-jam-pill">{{ $jJam }}</span>
                                    </div>
                                @else
                                    <span class="tb-opsi-item"><span class="tb-opsi-item-dot"></span>{{ $o->nilai }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="tb-crud-actions">
                                    <a href="{{ route('admin.opsi.edit', $o) }}" class="tb-btn tb-btn-outline tb-btn-sm" title="Edit" aria-label="Edit">
                                        <x-icon name="pencil" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.opsi.destroy', $o) }}" onsubmit="return confirm('Hapus item {{ $o->nilai }}?');">
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
        <div class="tb-crud-pagination">{{ $opsiList->links() }}</div>
    @endif
</div>
@endsection
