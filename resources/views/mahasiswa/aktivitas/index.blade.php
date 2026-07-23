@extends('layouts.app')

@section('title', '· Aktivitas Saya')

@section('content')
@php
    // Mapping jenis aksi (suffix setelah titik) → warna & label badge.
    // Pakai closure (bukan function global) agar tidak konflik redeclare
    // bila view ini & admin/aktivitas di-render pada request berdekatan.
    $aksiBadge = function (string $action): array {
        $suffix = str_contains($action, '.') ? substr($action, strpos($action, '.') + 1) : $action;
        $map = [
            'create'      => ['bg' => '#e6fcf5', 'color' => '#0ca678', 'label' => 'Tambah'],
            'update'      => ['bg' => '#e7f5ff', 'color' => '#1c7ed6', 'label' => 'Ubah'],
            'delete'      => ['bg' => '#fff0f0', 'color' => '#fa5252', 'label' => 'Hapus'],
            'toggle'      => ['bg' => '#fff4e3', 'color' => '#e88f1e', 'label' => 'Status'],
            'accept'      => ['bg' => '#e6fcf5', 'color' => '#0ca678', 'label' => 'Terima'],
            'reject'      => ['bg' => '#fff0f0', 'color' => '#fa5252', 'label' => 'Tolak'],
            'cancel'      => ['bg' => '#fff0f6', 'color' => '#d6336c', 'label' => 'Batal'],
            'register'    => ['bg' => '#f3f0ff', 'color' => '#7048e8', 'label' => 'Daftar'],
            'self-delete' => ['bg' => '#fff0f0', 'color' => '#fa5252', 'label' => 'Hapus'],
        ];
        return $map[$suffix] ?? ['bg' => '#f1f3f5', 'color' => '#495057', 'label' => ucfirst($suffix ?: $action)];
    };
@endphp
<style>
    /* Card pencarian */
    .tb-search-card {
        background: linear-gradient(135deg, #f7f9fd 0%, #ffffff 60%);
        border: 1px solid var(--tb-primary-light);
        border-radius: 0.85rem; padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }
    .tb-akt-toolbar { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: stretch; }
    .tb-akt-search { position: relative; display: flex; align-items: center; flex: 1; min-width: 220px; }
    .tb-akt-search > svg {
        position: absolute; left: 0.95rem; width: 1.05rem; height: 1.05rem;
        color: var(--tb-muted); pointer-events: none;
    }
    .tb-akt-search .tb-input { width: 100%; height: 46px; font-size: 0.9rem; padding-left: 2.7rem; }
    .tb-akt-search .tb-input:focus { border-color: var(--tb-primary); box-shadow: 0 0 0 3px rgba(11,37,91,0.10); }
    .tb-akt-toolbar .tb-select { flex: 0 1 240px; min-width: 180px; height: 46px; }
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

    .tb-akt-pagination { display: flex; justify-content: center; margin-top: 1rem; }
    .tb-akt-pagination :is(nav, ul) { margin-bottom: 0; }

    /* Badge aksi */
    .tb-akt-badge {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem;
        border-radius: 999px; white-space: nowrap;
    }
    .tb-akt-time { font-size: 0.76rem; color: var(--tb-muted); white-space: nowrap; }
    .tb-akt-time small { display: block; font-size: 0.68rem; opacity: 0.7; }
</style>

{{-- ============ HEADER ============ --}}
<div class="tb-section-head" style="margin-bottom:1rem;">
    <div class="tb-section-head-left">
        <span class="tb-section-icon"><x-icon name="clock-history" /></span>
        <div>
            <h2 class="tb-section-title">Aktivitas Saya</h2>
            <p class="tb-section-desc">Riwayat aktivitas akun Anda di Teman Belajar Udinus.</p>
        </div>
    </div>
</div>

{{-- ============ FILTER ============ --}}
@php
    $filterAktif = request('q') || request('kelompok');
@endphp
<div class="tb-search-card">
    <form method="GET" action="{{ route('aktivitas.index') }}" class="tb-akt-toolbar tb-instant-search">
        <div class="tb-akt-search">
            <x-icon name="search" />
            <input type="text" name="q" class="tb-input" placeholder="Cari keterangan aktivitas..." value="{{ request('q') }}">
        </div>
        <select name="kelompok" class="tb-select">
            <option value="">Semua Kategori</option>
            @foreach ($kelompokList as $key => $label)
                <option value="{{ $key }}" @selected(request('kelompok') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @if ($filterAktif)
            <a href="{{ route('aktivitas.index') }}" class="tb-btn tb-btn-ghost">
                <x-icon name="x-lg" /> Reset
            </a>
        @endif
    </form>
    @if ($filterAktif)
        <div class="tb-search-active">
            <span class="label">Filter aktif:</span>
            @if (request('q'))
                <span class="tb-search-chip">
                    "{{ request('q') }}"
                    <a href="{{ route('aktivitas.index', array_filter(request()->except('q'))) }}" title="Hapus pencarian"><x-icon name="x-lg" /></a>
                </span>
            @endif
            @if (request('kelompok'))
                <span class="tb-search-chip">
                    {{ $kelompokList[request('kelompok')] ?? request('kelompok') }}
                    <a href="{{ route('aktivitas.index', array_filter(request()->except('kelompok'))) }}" title="Hapus filter kategori"><x-icon name="x-lg" /></a>
                </span>
            @endif
            <span class="tb-search-count">{{ $aktivitas->total() }} aktivitas ditemukan</span>
        </div>
    @endif
</div>

{{-- ============ TABEL ============ --}}
<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="list" /></span>
            <div>
                <h2 class="tb-section-title">Riwayat Aktivitas</h2>
                <p class="tb-section-desc">{{ $aktivitas->total() }} catatan aktivitas.</p>
            </div>
        </div>
    </div>

    @if ($aktivitas->isEmpty())
        <div class="tb-empty">
            <div class="tb-empty-icon"><x-icon name="inbox" /></div>
            <p class="tb-empty-title">Belum ada aktivitas tercatat</p>
            <p class="tb-empty-desc">Riwayat aksi Anda akan muncul di sini.</p>
        </div>
    @else
        <div class="tb-table-wrap">
            <table class="tb-table">
                <thead>
                    <tr>
                        <th style="width:170px;">Waktu</th>
                        <th style="width:130px;">Aksi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aktivitas as $log)
                        @php $badge = $aksiBadge($log->action); @endphp
                        <tr>
                            <td class="tb-akt-time">
                                {{ $log->created_at->diffForHumans() }}
                                <small>{{ $log->created_at->format('d M Y, H:i') }}</small>
                            </td>
                            <td>
                                <span class="tb-akt-badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="tb-text-sm">{{ $log->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tb-akt-pagination">{{ $aktivitas->links() }}</div>
    @endif
</div>
@endsection
