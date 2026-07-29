@extends('layouts.app')

@section('title', '· Permintaan')

@section('content')
{{-- ============ CSS SPESIFIK HALAMAN (prefix tb-req-* / tb-tab-*) ============ --}}
<style>
    /* Tab navigasi konsisten navy */
    .tb-tab-list { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .tb-tab {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;
        color: var(--tb-muted); background: white;
        border: 1px solid var(--tb-primary-light); border-radius: 0.5rem;
        cursor: pointer; text-decoration: none; transition: all 0.15s ease;
    }
    .tb-tab:hover { color: var(--tb-primary); border-color: var(--tb-primary); }
    .tb-tab.active {
        background: var(--tb-primary); color: white; border-color: var(--tb-primary);
    }
    .tb-tab-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px; padding: 0 0.35rem;
        background: var(--tb-accent); color: white;
        border-radius: 0.4rem; font-size: 0.68rem; font-weight: 700;
    }
    .tb-tab.active .tb-tab-count { background: white; color: var(--tb-primary); }

    /* Kartu permintaan */
    .tb-req-card {
        position: relative; overflow: hidden;
        border-left-width: 4px; border-left-style: solid;
        transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.1s ease;
    }
    .tb-req-card:hover { box-shadow: 0 8px 24px rgba(11,37,91,0.08); }
    .tb-req-card:active { transform: translateY(1px); }
    .tb-req-card.is-pending { border-left-color: var(--tb-accent); }
    .tb-req-card.is-accepted { border-left-color: #1d8a4e; }
    .tb-req-card.is-rejected { border-left-color: #c0392b; opacity: 0.78; }

    .tb-req-top { display: flex; align-items: flex-start; gap: 0.8rem; }
    .tb-req-avatar {
        width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1rem; color: white;
        background: linear-gradient(135deg, var(--tb-primary), #1a3a7a);
    }
    .tb-req-main { flex: 1; min-width: 0; }
    .tb-req-name {
        font-size: 0.94rem; font-weight: 700; color: var(--tb-ink);
        text-decoration: none; display: inline-block;
    }
    .tb-req-name:hover { color: var(--tb-primary); }
    .tb-req-gender { display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 0.3rem; vertical-align: middle; margin-left: 0.3rem; }
    .tb-req-gender svg { width: 0.7rem; height: 0.7rem; }
    .tb-req-gender.L { background: #e7f5ff; color: #1c7ed6; }
    .tb-req-gender.P { background: #fff0f6; color: #d6336c; }
    .tb-req-meta { font-size: 0.75rem; color: var(--tb-muted); margin-top: 0.15rem; }
    .tb-req-skor {
        font-size: 0.72rem; font-weight: 700; color: var(--tb-accent-dark);
        background: var(--tb-accent-light); padding: 0.22rem 0.55rem; border-radius: 999px;
        white-space: nowrap; line-height: 1;
    }
    .tb-req-info {
        font-size: 0.76rem; color: var(--tb-muted);
        display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 0.7rem 0;
    }
    .tb-req-info span { display: inline-flex; align-items: center; gap: 0.3rem; }
    .tb-req-info svg { width: 0.85rem; height: 0.85rem; color: var(--tb-primary); }
    .tb-req-foot {
        display: flex; justify-content: space-between; align-items: center;
        gap: 0.6rem; flex-wrap: wrap; margin-top: 0.75rem;
        padding-top: 0.75rem; border-top: 1px solid var(--tb-primary-light);
    }
    .tb-req-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

    /* Tombol kecil khusus aksi */
    .tb-btn-icon { width: 36px; min-width: 36px; padding: 0; }

    @media (max-width: 575.98px) {
        .tb-tab { flex: 1 1 calc(50% - 0.25rem); justify-content: center; min-height: 44px; }
        .tb-req-top { gap: 0.65rem; }
        .tb-req-name { font-size: 0.9rem; word-break: break-word; }
        .tb-req-foot { flex-direction: column; align-items: stretch; }
        .tb-req-actions { width: 100%; }
        .tb-req-actions .tb-btn { flex: 1 1 auto; justify-content: center; }
        .tb-req-skor { align-self: flex-start; }
    }
</style>

{{-- ============ PAGE HEAD ============ --}}
<div class="tb-page-head">
    <div class="tb-page-head-text">
        <h1>Permintaan Belajar</h1>
        <p>Kelola permintaan yang Anda kirim dan terima dari teman belajar.</p>
    </div>
    <div class="tb-page-head-actions">
        @if ($jumlahPendingDiterima > 0)
            <span class="tb-badge tb-badge-warn">
                <x-icon name="bell-fill" /> {{ $jumlahPendingDiterima }} menunggu respons
            </span>
        @endif
    </div>
</div>

{{-- ============ TAB NAV (Alpine.js) ============ --}}
<div x-data="{ tab: '{{ $jumlahPendingDiterima > 0 ? 'terkirim' : 'diterima' }}' }">
<ul class="tb-tab-list" id="permintaanTabs" role="tablist">
    <li role="presentation">
        <button class="tb-tab nav-link" :class="{ 'active': tab === 'diterima' }"
                @click="tab = 'diterima'" type="button" role="tab">
            <x-icon name="inbox" /> Diterima
            @if ($jumlahPendingDiterima > 0)
                <span class="tb-tab-count">{{ $jumlahPendingDiterima }}</span>
            @endif
        </button>
    </li>
    <li role="presentation">
        <button class="tb-tab nav-link" :class="{ 'active': tab === 'terkirim' }"
                @click="tab = 'terkirim'" type="button" role="tab">
            <x-icon name="send" /> Terkirim
        </button>
    </li>
</ul>

{{-- ============ TAB CONTENT ============ --}}
<div class="tab-content">

    {{-- ---------- DITERIMA ---------- --}}
    <div x-show="tab === 'diterima'" x-transition
         id="diterima" role="tabpanel">
        @if ($permintaanDiterima->isEmpty())
            <div class="tb-card">
                <div class="tb-empty">
                    <div class="tb-empty-icon"><x-icon name="inbox" /></div>
                    <p class="tb-empty-title">Belum ada permintaan masuk</p>
                    <p class="tb-empty-desc">Permintaan belajar dari mahasiswa lain akan muncul di sini.</p>
                    <a href="{{ route('rekomendasi.index') }}" class="tb-btn tb-btn-outline tb-btn-sm">
                        <x-icon name="stars" /> Cari Teman
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($permintaanDiterima as $req)
                    @php
                        $pengirim = $req->pengirim;
                        $profile = $pengirim->profile;
                        $skor = \App\Models\SimilarityScore::where('user_id', $user->id)
                                    ->where('kandidat_id', $pengirim->id)->value('skor');
                        $persen = $skor !== null ? round($skor * 100) : null;
                        $inisial = strtoupper(mb_substr($pengirim->nama, 0, 1));
                        $jkLabel = $pengirim->jenis_kelamin === 'L' ? 'Laki-laki' : ($pengirim->jenis_kelamin === 'P' ? 'Perempuan' : null);
                    @endphp
                    <div class="tb-card tb-req-card is-{{ $req->status }}">
                        <div class="tb-req-top">
                            <span class="tb-req-avatar">{{ $inisial }}</span>
                            <div class="tb-req-main">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('rekomendasi.show', $pengirim->id) }}" class="tb-req-name">
                                            {{ $pengirim->nama }}
                                        </a>
                                        @if ($jkLabel)
                                            <span class="tb-req-gender {{ $pengirim->jenis_kelamin }}"><x-icon name="{{ $pengirim->jenis_kelamin === 'L' ? 'gender-male' : 'gender-female' }}" /></span>
                                        @endif
                                        <div class="tb-req-meta">
                                            {{ $pengirim->prodi?->nama ?? '-' }} · Semester {{ $pengirim->semester }}
                                        </div>
                                    </div>
                                    @if ($persen !== null)
                                        <span class="tb-req-skor">{{ $persen }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="tb-req-info">
                            <span><x-icon name="clock" /> {{ $req->waktu_kirim->diffForHumans() }}</span>
                            @if ($profile && !empty($profile->minat))
                                <span><x-icon name="lightbulb" /> {{ implode(', ', array_slice($profile->minat ?? [], 0, 2)) }}</span>
                            @endif
                        </div>

                        <div class="tb-req-foot">
                            @switch($req->status)
                                @case('pending')
                                    <span class="tb-badge tb-badge-warn">
                                        <x-icon name="hourglass-split" /> Menunggu
                                    </span>
                                    <div class="tb-req-actions">
                                        <form method="POST" action="{{ route('permintaan.accept', $req) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="tb-btn tb-btn-sm">
                                                <x-icon name="check-lg" /> Terima
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('permintaan.reject', $req) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="tb-btn tb-btn-danger tb-btn-sm tb-btn-icon" title="Tolak" aria-label="Tolak">
                                                <x-icon name="x-lg" />
                                            </button>
                                        </form>
                                    </div>
                                    @break
                                @case('accepted')
                                    <span class="tb-badge tb-badge-success">
                                        <x-icon name="check-circle" /> Diterima
                                    </span>
                                    <a href="{{ route('rekomendasi.show', $pengirim->id) }}" class="tb-btn tb-btn-outline tb-btn-sm">
                                        <x-icon name="eye" /> Kontak
                                    </a>
                                    @break
                                @case('rejected')
                                    <span class="tb-badge tb-badge-danger">
                                        <x-icon name="x-circle" /> Ditolak
                                    </span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ---------- TERKIRIM ---------- --}}
    <div x-show="tab === 'terkirim'" x-transition
         id="terkirim" role="tabpanel">
        @if ($permintaanTerkirim->isEmpty())
            <div class="tb-card">
                <div class="tb-empty">
                    <div class="tb-empty-icon"><x-icon name="send" /></div>
                    <p class="tb-empty-title">Belum ada permintaan terkirim</p>
                    <p class="tb-empty-desc">Kirim permintaan ke teman yang direkomendasikan sistem untuk mulai belajar bersama.</p>
                    <a href="{{ route('rekomendasi.index') }}" class="tb-btn tb-btn-outline tb-btn-sm">
                        <x-icon name="stars" /> Lihat Rekomendasi
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($permintaanTerkirim as $req)
                    @php
                        $penerima = $req->penerima;
                        $profile = $penerima->profile;
                        $skor = \App\Models\SimilarityScore::where('user_id', $user->id)
                                    ->where('kandidat_id', $penerima->id)->value('skor');
                        $persen = $skor !== null ? round($skor * 100) : null;
                        $inisial = strtoupper(mb_substr($penerima->nama, 0, 1));
                        $jkLabel = $penerima->jenis_kelamin === 'L' ? 'Laki-laki' : ($penerima->jenis_kelamin === 'P' ? 'Perempuan' : null);
                    @endphp
                    <div class="tb-card tb-req-card is-{{ $req->status }}">
                        <div class="tb-req-top">
                            <span class="tb-req-avatar">{{ $inisial }}</span>
                            <div class="tb-req-main">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('rekomendasi.show', $penerima->id) }}" class="tb-req-name">
                                            {{ $penerima->nama }}
                                        </a>
                                        @if ($jkLabel)
                                            <span class="tb-req-gender {{ $penerima->jenis_kelamin }}"><x-icon name="{{ $penerima->jenis_kelamin === 'L' ? 'gender-male' : 'gender-female' }}" /></span>
                                        @endif
                                        <div class="tb-req-meta">
                                            {{ $penerima->prodi?->nama ?? '-' }} · Semester {{ $penerima->semester }}
                                        </div>
                                    </div>
                                    @if ($persen !== null)
                                        <span class="tb-req-skor">{{ $persen }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="tb-req-info">
                            <span><x-icon name="clock" /> Dikirim {{ $req->waktu_kirim->diffForHumans() }}</span>
                            @if ($profile && !empty($profile->minat))
                                <span><x-icon name="lightbulb" /> {{ implode(', ', array_slice($profile->minat ?? [], 0, 2)) }}</span>
                            @endif
                        </div>

                        <div class="tb-req-foot">
                            @switch($req->status)
                                @case('pending')
                                    <span class="tb-badge tb-badge-navy">
                                        <x-icon name="hourglass-split" /> Menunggu respons
                                    </span>
                                    <form method="POST" action="{{ route('permintaan.destroy', $req) }}"
                                          onsubmit="return confirm('Batalkan permintaan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="tb-btn tb-btn-ghost tb-btn-sm">
                                            <x-icon name="x-lg" /> Batalkan
                                        </button>
                                    </form>
                                    @break
                                @case('accepted')
                                    <span class="tb-badge tb-badge-success">
                                        <x-icon name="check-circle" /> Diterima
                                    </span>
                                    <a href="{{ route('rekomendasi.show', $penerima->id) }}" class="tb-btn tb-btn-outline tb-btn-sm">
                                        <x-icon name="eye" /> Kontak
                                    </a>
                                    @break
                                @case('rejected')
                                    <span class="tb-badge tb-badge-danger">
                                        <x-icon name="x-circle" /> Ditolak
                                    </span>
                                    <a href="{{ route('rekomendasi.show', $penerima->id) }}" class="tb-btn tb-btn-outline tb-btn-sm">
                                        <x-icon name="arrow-repeat" /> Kirim Ulang
                                    </a>
                                    @break
                            @endswitch
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
