@extends('layouts.app')

@section('title', '· Beranda')

@php
    $user = auth()->user();
    $isAdmin = $user->role === 'admin';
    $user->load('profile');
    $namaDepan = explode(' ', trim($user->nama))[0];

    $hariId = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $bulanId = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $now = now();
    $tanggalStr = $hariId[$now->format('l')] . ', ' . $now->format('j') . ' ' . $bulanId[(int) $now->format('n')] . ' ' . $now->format('Y');

    $profilLengkap = ! $isAdmin && $user->profile && $user->profile->minat;

    // Ucapan kontekstual berdasarkan waktu — bukan duplikasi info dari card lain.
    $jam = (int) $now->format('H');
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

    // Rata-rata kecocokan kandidat — agregat, BUKAN duplikat angka per-item di card rekomendasi.
    $skorRata = 0;
    if (! $isAdmin && $profilLengkap) {
        $skorRows = \App\Models\SimilarityScore::where('user_id', $user->id)->where('skor', '>', 0)->pluck('skor');
        if ($skorRows->isNotEmpty()) {
            $skorRata = round($skorRows->avg() * 100);
        }
    }
    $pendingMasuk = $isAdmin ? 0 : $user->receivedRequests()->where('status', 'pending')->count();
    $acceptedCount = $isAdmin ? 0 : ($user->sentRequests()->where('status', 'accepted')->count() + $user->receivedRequests()->where('status', 'accepted')->count());
    $sentCount = $isAdmin ? 0 : $user->sentRequests()->count();
    $totalKandidat = $profilLengkap ? \App\Models\SimilarityScore::where('user_id', $user->id)->where('skor', '>', 0)->count() : 0;

    $topRekomendasi = collect();
    if ($profilLengkap) {
        $cbf = app(\App\Services\ContentBasedFilteringService::class);
        $topRekomendasi = $cbf->getTopN($user, 3);
    }

    $inisial = strtoupper(mb_substr($namaDepan, 0, 1));

    $prefChips = collect();
    if ($user->profile) {
        foreach (($user->profile->minat ?? []) as $m) { $prefChips->push($m); }
        if ($user->profile->tujuan) { $prefChips->push($user->profile->tujuan); }
        if ($user->profile->gaya) { $prefChips->push($user->profile->gaya); }
        if ($user->profile->mode) { $prefChips->push($user->profile->mode); }
    }
    $jadwalCount = is_array($user->profile?->jadwal) ? count($user->profile->jadwal) : 0;
@endphp

@section('content')
<style>
    /* ===== GREETING (hero navy gradient) ===== */
    .tb-greet {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, var(--tb-primary-dark) 0%, var(--tb-primary) 70%);
        color: #fff; border: none; border-radius: 1rem;
        margin-bottom: 1rem; padding: 1.5rem 1.5rem;
    }
    .tb-greet::before {
        content: ""; position: absolute; top: -40%; right: -8%;
        width: 280px; height: 280px; border-radius: 50%;
        background: radial-gradient(circle, rgba(255,167,58,0.18) 0%, transparent 65%);
        pointer-events: none;
    }
    .tb-greet-top { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .tb-greet h2 { font-size: 1.4rem; font-weight: 800; color: #fff; margin: 0 0 0.25rem; letter-spacing: -0.02em; }
    .tb-greet-date { font-size: 0.82rem; color: rgba(255,255,255,0.72); margin: 0; display: inline-flex; align-items: center; gap: 0.35rem; }
    .tb-greet-date svg { width: 0.95rem; height: 0.95rem; }
    .tb-greet-right { display: flex; flex-direction: column; align-items: flex-end; gap: 0.45rem; }
    .tb-greet-avatar {
        width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
        background: rgba(255,255,255,0.16); border: 2px solid rgba(255,255,255,0.35);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; font-weight: 800;
    }
    .tb-greet-badge {
        font-size: 0.7rem; font-weight: 700; padding: 0.35rem 0.75rem; border-radius: 999px;
        display: inline-flex; align-items: center; gap: 0.3rem; text-decoration: none;
        border: 1px solid transparent;
    }
    .tb-greet-badge svg { width: 0.85rem; height: 0.85rem; }
    .tb-greet-badge.ok { background: rgba(74,222,128,0.18); color: #bbf7d0; border-color: rgba(74,222,128,0.4); }
    .tb-greet-badge.warn { background: rgba(255,167,58,0.22); color: #ffd08a; border-color: rgba(255,167,58,0.5); }
    .tb-greet-badge.admin { background: rgba(255,255,255,0.14); color: #fff; border-color: rgba(255,255,255,0.3); }
    .tb-greet-hint { position: relative; z-index: 2; font-size: 0.8rem; color: rgba(255,255,255,0.78); margin: 1rem 0 0; padding-top: 0.85rem; border-top: 1px solid rgba(255,255,255,0.15); }
    .tb-greet-hint a { color: #ffd08a; font-weight: 600; text-decoration: none; }
    .tb-greet-hint a:hover { text-decoration: underline; }

    /* ===== STATS ===== */
    .tb-stats-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1rem; }
    @media (max-width: 991.98px) { .tb-stats-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 379.98px) { .tb-stats-row { grid-template-columns: 1fr; } }
    @media (max-width: 575.98px) {
        .tb-greet { padding: 1.15rem 1rem; }
        .tb-greet h2 { font-size: 1.15rem; }
        .tb-greet-right { align-items: flex-start; width: 100%; }
        .tb-dash-stat { padding: 0.8rem 0.85rem; }
        .tb-dash-stat-num { font-size: 1.35rem; }
        .tb-profil-simple-info { min-width: 0; flex: 1 1 100%; }
        .tb-profil-edit { width: 100%; }
        .tb-profil-edit .tb-btn { width: 100%; }
    }
    .tb-dash-stat {
        background: white; border: 1px solid var(--tb-primary-light);
        border-radius: 0.75rem; padding: 0.9rem 1rem; position: relative; overflow: hidden;
        transition: all 0.2s ease;
    }
    .tb-dash-stat:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(11,37,91,0.08); }
    .tb-dash-stat::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: var(--stat-c, var(--tb-primary)); }
    .tb-dash-stat-row { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
    .tb-dash-stat-num { font-size: 1.6rem; font-weight: 800; color: var(--tb-ink); line-height: 1.1; letter-spacing: -0.02em; }
    .tb-dash-stat-label { font-size: 0.72rem; color: var(--tb-muted); margin-top: 0.15rem; font-weight: 500; }
    .tb-dash-stat-icon {
        width: 38px; height: 38px; border-radius: 0.6rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: var(--stat-soft, var(--tb-primary-soft)); color: var(--stat-c, var(--tb-primary));
    }
    .tb-dash-stat-icon svg { width: 1.15rem; height: 1.15rem; }
    .tb-dash-stat.s-navy   { --stat-c: #0b255b; --stat-soft: #e6ebf5; }
    .tb-dash-stat.s-orange { --stat-c: #e88f1e; --stat-soft: #fff4e3; }
    .tb-dash-stat.s-green  { --stat-c: #0ca678; --stat-soft: #e6fcf5; }
    .tb-dash-stat.s-purple { --stat-c: #7048e8; --stat-soft: #f3f0ff; }

    /* ===== REKOMENDASI ===== */
    .tb-rec-item { display: flex; align-items: center; gap: 0.85rem; padding: 0.85rem; border-bottom: 1px solid var(--tb-primary-light); transition: background 0.15s ease; }
    .tb-rec-item:last-child { border-bottom: none; }
    .tb-rec-item:hover { background: var(--tb-primary-soft); }
    .tb-rec-avatar {
        width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--tb-primary), #1a3a7a);
        color: white; display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; font-weight: 700;
    }
    .tb-rec-body { flex: 1; min-width: 0; }
    .tb-rec-name { font-size: 0.9rem; font-weight: 700; color: var(--tb-ink); margin: 0 0 0.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tb-rec-meta { font-size: 0.74rem; color: var(--tb-muted); display: flex; align-items: center; gap: 0.25rem; }
    .tb-rec-top {
        display: inline-flex; align-items: center; gap: 0.25rem;
        font-size: 0.62rem; font-weight: 700; letter-spacing: 0.02em;
        color: var(--tb-accent-dark); background: var(--tb-accent-light);
        padding: 0.12rem 0.45rem; border-radius: 0.3rem;
        vertical-align: middle; margin-left: 0.4rem;
    }
    .tb-rec-top svg { width: 0.75rem; height: 0.75rem; color: var(--tb-accent); }
    .tb-rec-gender {
        display: inline-flex; align-items: center; gap: 0.2rem;
        font-size: 0.62rem; font-weight: 700;
        padding: 0.12rem 0.45rem; border-radius: 0.3rem;
        vertical-align: middle; margin-left: 0.4rem;
    }
    .tb-rec-gender svg { width: 0.75rem; height: 0.75rem; }
    .tb-rec-gender.L { background: #e7f5ff; color: #1c7ed6; }
    .tb-rec-gender.P { background: #fff0f6; color: #d6336c; }
    .tb-rec-score { text-align: right; flex-shrink: 0; }
    .tb-rec-score-num {
        font-size: 0.95rem; font-weight: 800; line-height: 1;
        display: inline-flex; align-items: center; gap: 0.25rem;
        padding: 0.3rem 0.6rem; border-radius: 999px;
        background: var(--tb-accent-light); color: var(--tb-accent-dark);
    }
    .tb-rec-score-label { font-size: 0.6rem; color: var(--tb-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-top: 0.25rem; }

    /* ===== PROFIL SIMPLE ===== */
    .tb-profil-simple { display: flex; align-items: center; gap: 0.9rem; flex-wrap: wrap; }
    .tb-profil-avatar {
        width: 50px; height: 50px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--tb-primary), var(--tb-primary-dark));
        color: white; display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; font-weight: 800;
    }
    .tb-profil-simple-info { flex: 1; min-width: 200px; }
    .tb-profil-eyebrow { display: block; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: var(--tb-accent-dark); margin-bottom: 0.15rem; }
    .tb-profil-name { font-size: 1rem; font-weight: 700; color: var(--tb-ink); margin: 0 0 0.3rem; }
    .tb-profil-meta { display: flex; flex-wrap: wrap; gap: 0.35rem 0.9rem; font-size: 0.76rem; color: var(--tb-muted); margin-bottom: 0.2rem; }
    .tb-profil-meta span { display: inline-flex; align-items: center; gap: 0.3rem; }
    .tb-profil-meta svg { width: 0.85rem; height: 0.85rem; }
    .tb-profil-tag { font-weight: 600; padding: 0.05rem 0.4rem; border-radius: 0.3rem; font-size: 0.72rem; }
    .tb-profil-tag.ok { background: #d1e7dd; color: #0a3622; }
    .tb-profil-tag.warn { background: var(--tb-accent-light); color: var(--tb-accent-dark); }
    .tb-profil-tag svg { width: 0.8rem; height: 0.8rem; }
    .tb-profil-edit { flex-shrink: 0; }
</style>

<div class="tb-greet">
    <div class="tb-greet-top">
        <div>
            <h2>{{ $sapaan }}, {{ $namaDepan }}! 👋</h2>
            <p class="tb-greet-date"><x-icon name="calendar3" /> {{ $tanggalStr }}</p>
        </div>
        <div class="tb-greet-right">
            @if ($isAdmin)
                <span class="tb-greet-badge admin"><x-icon name="shield-check" /> Mode Admin</span>
            @elseif ($profilLengkap)
                <span class="tb-greet-badge ok"><x-icon name="check-circle" /> Profil Lengkap</span>
            @else
                <a href="{{ route('profil.edit') }}" class="tb-greet-badge warn"><x-icon name="exclamation-circle" /> Lengkapi Profil</a>
            @endif
        </div>
    </div>
    @if ($isAdmin)
        <p class="tb-greet-hint">Kelola data master &amp; mahasiswa dari <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>.</p>
    @elseif (! $profilLengkap)
        <p class="tb-greet-hint">Lengkapi preferensi belajar Anda agar sistem bisa mencocokkan dengan mahasiswa lain. <a href="{{ route('profil.edit') }}">Isi sekarang</a></p>
    @endif
</div>

@if ($isAdmin)
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="speedometer2" /></span>
                <div>
                    <h2 class="tb-section-title">Panel Admin</h2>
                    <p class="tb-section-desc">Kelola data master & mahasiswa dari panel admin</p>
                </div>
            </div>
        </div>
        <p class="tb-text-sm tb-muted" style="margin:0 0 1rem;">Gunakan menu di sidebar untuk mengakses fitur admin.</p>
        <a href="{{ route('admin.dashboard') }}" class="tb-btn"><x-icon name="graph-up" /> Buka Dashboard Admin</a>
    </div>
@else

<div class="tb-stats-row">
    <div class="tb-dash-stat s-navy">
        <div class="tb-dash-stat-row">
            <div>
                <div class="tb-dash-stat-num">{{ $totalKandidat }}</div>
                <div class="tb-dash-stat-label">Kandidat</div>
            </div>
            <span class="tb-dash-stat-icon"><x-icon name="people" /></span>
        </div>
    </div>
    <div class="tb-dash-stat s-orange">
        <div class="tb-dash-stat-row">
            <div>
                <div class="tb-dash-stat-num">{{ $pendingMasuk }}</div>
                <div class="tb-dash-stat-label">Permintaan Masuk</div>
            </div>
            <span class="tb-dash-stat-icon"><x-icon name="inbox" /></span>
        </div>
    </div>
    <div class="tb-dash-stat s-green">
        <div class="tb-dash-stat-row">
            <div>
                <div class="tb-dash-stat-num">{{ $acceptedCount }}</div>
                <div class="tb-dash-stat-label">Koneksi</div>
            </div>
            <span class="tb-dash-stat-icon"><x-icon name="link-45deg" /></span>
        </div>
    </div>
    <div class="tb-dash-stat s-purple">
        <div class="tb-dash-stat-row">
            <div>
                <div class="tb-dash-stat-num">{{ $sentCount }}</div>
                <div class="tb-dash-stat-label">Dikirim</div>
            </div>
            <span class="tb-dash-stat-icon"><x-icon name="send" /></span>
        </div>
    </div>
</div>

<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="stars" /></span>
            <div>
                <h2 class="tb-section-title">Rekomendasi untukmu</h2>
                <p class="tb-section-desc">3 partner belajar paling cocok berdasarkan preferensimu</p>
            </div>
        </div>
        @if ($topRekomendasi->isNotEmpty())
            <a href="{{ route('rekomendasi.index') }}" class="tb-link-more">Lihat semua →</a>
        @endif
    </div>

    @if (! $profilLengkap)
        <div class="tb-empty">
            <span class="tb-empty-icon"><x-icon name="clipboard-check" /></span>
            <h3 class="tb-empty-title">Lengkapi profil dulu</h3>
            <p class="tb-empty-desc">Isi preferensi belajar-mu agar sistem bisa mencocokkan partner yang paling cocok.</p>
            <a href="{{ route('profil.edit') }}" class="tb-btn"><x-icon name="pencil" /> Lengkapi Profil</a>
        </div>
    @elseif ($topRekomendasi->isEmpty())
        <div class="tb-empty">
            <span class="tb-empty-icon"><x-icon name="search" /></span>
            <h3 class="tb-empty-title">Belum ada rekomendasi</h3>
            <p class="tb-empty-desc">Saat ini belum ada mahasiswa lain yang profilnya cocok. Coba lagi nanti.</p>
        </div>
    @else
        @foreach ($topRekomendasi as $rec)
            @php
                $kandidat = $rec->kandidat;
                $inisialK = strtoupper(mb_substr(explode(' ', trim($kandidat->nama))[0], 0, 1));
                $skorPct = round($rec->skor * 100);
                $jkLabel = $kandidat->jenis_kelamin === 'L' ? 'Laki-laki' : ($kandidat->jenis_kelamin === 'P' ? 'Perempuan' : null);
            @endphp
            {{-- READ-ONLY: tidak bisa dipencet. Harus lewat "Lihat semua" --}}
            <div class="tb-rec-item">
                <span class="tb-rec-avatar">{{ $inisialK }}</span>
                <div class="tb-rec-body">
                    <h3 class="tb-rec-name">
                        {{ $kandidat->nama }}
                        @if ($jkLabel)
                            <span class="tb-rec-gender {{ $kandidat->jenis_kelamin }}"><x-icon name="{{ $kandidat->jenis_kelamin === 'L' ? 'gender-male' : 'gender-female' }}" /> {{ $jkLabel }}</span>
                        @endif
                        @if ($loop->first)
                            <span class="tb-rec-top"><x-icon name="star-fill" /> Top Match</span>
                        @endif
                    </h3>
                    <div class="tb-rec-meta">{{ $kandidat->fakultas?->nama ?? '-' }} · {{ $kandidat->prodi?->nama ?? '-' }}</div>
                </div>
                <div class="tb-rec-score">
                    <div class="tb-rec-score-num">{{ $skorPct }}%</div>
                    <div class="tb-rec-score-label">match</div>
                </div>
            </div>
        @endforeach
        <div style="text-align:center; margin-top:1rem;">
            <a href="{{ route('rekomendasi.index') }}" class="tb-btn tb-btn-outline"><x-icon name="arrow-right" /> Lihat semua rekomendasi</a>
        </div>
    @endif
</div>

<div class="tb-card">
    <div class="tb-profil-simple">
        <span class="tb-profil-avatar">{{ $inisial }}</span>
        <div class="tb-profil-simple-info">
            <span class="tb-profil-eyebrow">Profil Saya</span>
            <h3 class="tb-profil-name">{{ $user->nama }}</h3>
            <div class="tb-profil-meta">
                @if ($user->nim)<span><x-icon name="mortarboard" /> {{ $user->nim }}</span>@endif
                <span><x-icon name="building" /> {{ $user->fakultas?->nama ?? '-' }}</span>
                <span><x-icon name="mortarboard" /> {{ $user->prodi?->nama ?? '-' }}</span>
            </div>
            <div class="tb-profil-meta">
                <span><x-icon name="calendar-week" /> Semester {{ $user->semester ?? '-' }}</span>
                <span><x-icon name="calendar3" /> Angkatan {{ $user->angkatan ?? '-' }}</span>
                @if ($profilLengkap)
                    <span class="tb-profil-tag ok"><x-icon name="check-circle" /> Preferensi lengkap</span>
                @else
                    <span class="tb-profil-tag warn"><x-icon name="exclamation-circle" /> Preferensi belum lengkap</span>
                @endif
            </div>
        </div>
        <a href="{{ route('profil.edit') }}" class="tb-btn tb-btn-sm tb-btn-outline tb-profil-edit"><x-icon name="pencil" /> Edit</a>
    </div>
</div>

@endif
@endsection
