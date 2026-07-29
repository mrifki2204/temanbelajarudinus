@extends('layouts.app')

@section('title', '· Dashboard Admin')

@section('content')
@php
    $admin = auth()->user();
    $namaDepan = explode(' ', trim($admin->nama))[0];
    $hariId = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $bulanId = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $now = now();
    $tanggalStr = $hariId[$now->format('l')] . ', ' . $now->format('j') . ' ' . $bulanId[(int) $now->format('n')] . ' ' . $now->format('Y');
    $jam = (int) $now->format('H');
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<style>
    /* ===== GREETING ===== */
    .tb-admin-greet {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, var(--tb-primary-dark) 0%, var(--tb-primary) 70%);
        color: #fff; border-radius: 1rem; padding: 1.4rem 1.5rem; margin-bottom: 1rem;
    }
    .tb-admin-greet::before {
        content: ""; position: absolute; top: -40%; right: -8%;
        width: 260px; height: 260px; border-radius: 50%;
        background: radial-gradient(circle, rgba(255,167,58,0.18) 0%, transparent 65%);
        pointer-events: none;
    }
    .tb-admin-greet::after {
        content: ""; position: absolute; bottom: -60%; left: 20%;
        width: 200px; height: 200px; border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        pointer-events: none;
    }
    .tb-admin-greet h2 { position: relative; z-index: 2; font-size: 1.4rem; font-weight: 800; margin: 0 0 0.25rem; letter-spacing: -0.02em; }
    .tb-admin-greet p { position: relative; z-index: 2; font-size: 0.82rem; color: rgba(255,255,255,0.78); margin: 0; display: inline-flex; align-items: center; gap: 0.35rem; }
    .tb-admin-greet p svg { width: 0.95rem; height: 0.95rem; }
    .tb-admin-greet-badge {
        position: absolute; top: 1.4rem; right: 1.5rem; z-index: 2;
        font-size: 0.7rem; font-weight: 700; padding: 0.35rem 0.75rem; border-radius: 999px;
        background: rgba(255,255,255,0.14); color: #fff; border: 1px solid rgba(255,255,255,0.3);
        display: inline-flex; align-items: center; gap: 0.3rem;
    }
    .tb-admin-greet-badge svg { width: 0.85rem; height: 0.85rem; color: var(--tb-accent); }

    /* ===== STAT CARDS ===== */
    .tb-admin-stats {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.85rem; margin-bottom: 1rem;
    }
    .tb-admin-stat {
        background: white; border: 1px solid var(--tb-primary-light);
        border-radius: 0.75rem; padding: 0.95rem 1.05rem; position: relative; overflow: hidden;
        transition: all 0.2s ease;
    }
    .tb-admin-stat:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(11,37,91,0.08); }
    .tb-admin-stat::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: var(--stat-c, var(--tb-primary)); }
    .tb-admin-stat-row { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
    .tb-admin-stat-num { font-size: 1.55rem; font-weight: 800; color: var(--tb-ink); line-height: 1.1; letter-spacing: -0.02em; }
    .tb-admin-stat-label { font-size: 0.72rem; color: var(--tb-muted); margin-top: 0.2rem; font-weight: 500; }
    .tb-admin-stat-icon {
        width: 38px; height: 38px; border-radius: 0.55rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: var(--stat-soft, var(--tb-primary-soft)); color: var(--stat-c, var(--tb-primary));
    }
    .tb-admin-stat-icon svg { width: 1.15rem; height: 1.15rem; }
    .tb-admin-stat.s-navy   { --stat-c: #0b255b; --stat-soft: #e6ebf5; }
    .tb-admin-stat.s-green  { --stat-c: #0ca678; --stat-soft: #e6fcf5; }
    .tb-admin-stat.s-orange { --stat-c: #e88f1e; --stat-soft: #fff4e3; }
    .tb-admin-stat.s-red    { --stat-c: #e03131; --stat-soft: #fff5f5; }
    .tb-admin-stat.s-blue   { --stat-c: #1c7ed6; --stat-soft: #e7f5ff; }
    .tb-admin-stat.s-purple { --stat-c: #7048e8; --stat-soft: #f3f0ff; }

    /* ===== GRID 2 KOLOM ===== */
    .tb-admin-grid-2 { display: grid; grid-template-columns: 1.3fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    .tb-admin-grid-2 .tb-card { margin-top: 0; }

    /* ===== DISTRIBUSI ===== */
    .tb-admin-distribusi-list { display: flex; flex-direction: column; gap: 0.75rem; margin: 0; padding: 0; list-style: none; }
    .tb-admin-distribusi-row { display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; align-items: center; }
    .tb-admin-distribusi-row .tb-admin-distribusi-sub { font-size: 0.68rem; color: var(--tb-muted); }
    .tb-admin-bar-track { height: 7px; border-radius: 999px; background: var(--tb-primary-soft); overflow: hidden; margin-top: 0.4rem; }
    .tb-admin-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--tb-primary), #1a3a7a); transition: width 0.4s ease; }
    .tb-admin-bar-fill.tb-admin-bar-accent { background: linear-gradient(90deg, var(--tb-accent), #ffd08a); }

    /* ===== AKTIVITAS FEED ===== */
    .tb-akt-feed { display: flex; flex-direction: column; }
    .tb-akt-feed-item {
        display: flex; align-items: flex-start; gap: 0.7rem;
        padding: 0.6rem 0; border-bottom: 1px solid var(--tb-primary-soft);
    }
    .tb-akt-feed-item:last-child { border-bottom: none; }
    .tb-akt-feed-icon {
        width: 32px; height: 32px; border-radius: 0.5rem; flex-shrink: 0;
        background: var(--tb-primary-light); color: var(--tb-primary);
        display: flex; align-items: center; justify-content: center;
    }
    .tb-akt-feed-icon svg { width: 0.95rem; height: 0.95rem; }
    .tb-akt-feed-body { min-width: 0; flex: 1; }
    .tb-akt-feed-desc { margin: 0; font-size: 0.82rem; color: var(--tb-ink); line-height: 1.4; }
    .tb-akt-feed-meta { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--tb-muted); }

    /* ===== TABEL MAHASISWA TERBARU ===== */
    .tb-admin-pagination { display: flex; justify-content: center; margin-top: 1rem; }
    .tb-admin-pagination :is(nav, ul) { margin-bottom: 0; }

    @media (max-width: 1199px) { .tb-admin-grid-2 { grid-template-columns: 1fr; } }
    @media (max-width: 991px) {
        .tb-admin-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .tb-admin-greet { padding: 1.15rem 1.1rem; }
        .tb-admin-greet h2 { font-size: 1.2rem; }
    }
    @media (max-width: 575px) {
        .tb-admin-stats { grid-template-columns: 1fr; }
        .tb-admin-greet-badge { position: static; margin-top: 0.6rem; display: inline-flex; }
        .tb-admin-stat { padding: 0.85rem; }
        .tb-admin-stat-num { font-size: 1.35rem; }
    }
</style>

{{-- ============ GREETING ============ --}}
<div class="tb-admin-greet">
    <span class="tb-admin-greet-badge"><x-icon name="shield-check" /> Mode Admin</span>
    <h2>{{ $sapaan }}, {{ $namaDepan }}! 👋</h2>
    <p><x-icon name="calendar3" /> {{ $tanggalStr }}</p>
</div>

{{-- ============ STAT CARDS ============ --}}
<div class="tb-admin-stats">
    <div class="tb-admin-stat s-navy">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['total_mahasiswa'] }}</div>
                <div class="tb-admin-stat-label">Total Mahasiswa</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="people-fill" /></span>
        </div>
    </div>
    <div class="tb-admin-stat s-green">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['mahasiswa_aktif'] }}</div>
                <div class="tb-admin-stat-label">Mahasiswa Aktif</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="person-check-fill" /></span>
        </div>
    </div>
    <div class="tb-admin-stat s-red">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['mahasiswa_nonaktif'] }}</div>
                <div class="tb-admin-stat-label">Akun Nonaktif</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="slash-circle-fill" /></span>
        </div>
    </div>
    <div class="tb-admin-stat s-blue">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['profil_lengkap'] }}</div>
                <div class="tb-admin-stat-label">Profil Lengkap</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="patch-check-fill" /></span>
        </div>
    </div>
    <div class="tb-admin-stat s-purple">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['total_permintaan'] }}</div>
                <div class="tb-admin-stat-label">Total Permintaan</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="send-fill" /></span>
        </div>
    </div>
    <div class="tb-admin-stat s-green">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['permintaan_accepted'] }}</div>
                <div class="tb-admin-stat-label">Permintaan Diterima</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="hand-thumbs-up-fill" /></span>
        </div>
    </div>
    <div class="tb-admin-stat s-orange">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['fakultas_count'] }}</div>
                <div class="tb-admin-stat-label">Fakultas</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="diagram-3-fill" /></span>
        </div>
    </div>
    <div class="tb-admin-stat s-navy">
        <div class="tb-admin-stat-row">
            <div>
                <div class="tb-admin-stat-num">{{ $stats['prodi_count'] }}</div>
                <div class="tb-admin-stat-label">Program Studi</div>
            </div>
            <span class="tb-admin-stat-icon"><x-icon name="mortarboard-fill" /></span>
        </div>
    </div>
</div>

{{-- ============ BARIS 1: Distribusi Fakultas + Mahasiswa Terbaru ============ --}}
<div class="tb-admin-grid-2">
    {{-- Distribusi per Fakultas --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="diagram-3" /></span>
                <div>
                    <h2 class="tb-section-title">Distribusi per Fakultas</h2>
                    <p class="tb-section-desc">Sebaran mahasiswa berdasarkan fakultas.</p>
                </div>
            </div>
        </div>

        @if ($distribusiFakultas->isEmpty())
            <div class="tb-empty">
                <div class="tb-empty-icon"><x-icon name="inbox" /></div>
                <p class="tb-empty-title">Belum ada data fakultas</p>
                <p class="tb-empty-desc">Data akan tampil setelah ada mahasiswa yang mengisi fakultas.</p>
            </div>
        @else
            <ul class="tb-admin-distribusi-list">
                @php
                    $maxFakultas = $distribusiFakultas->first()->jumlah ?? 1;
                @endphp
                @foreach ($distribusiFakultas as $row)
                    <li>
                        <div class="tb-admin-distribusi-row">
                            <span class="tb-text-sm font-semibold">{{ $row->fakultas }}</span>
                            <span class="tb-badge tb-badge-navy">{{ $row->jumlah }}</span>
                        </div>
                        <div class="tb-admin-bar-track">
                            <div class="tb-admin-bar-fill" style="width: {{ $maxFakultas > 0 ? round($row->jumlah / $maxFakultas * 100) : 0 }}%;"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Mahasiswa Terbaru --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="clock-history" /></span>
                <div>
                    <h2 class="tb-section-title">Mahasiswa Terbaru</h2>
                    <p class="tb-section-desc">5 pendaftar paling baru.</p>
                </div>
            </div>
            <a href="{{ route('admin.mahasiswa.index') }}" class="tb-link-more">Lihat semua <x-icon name="arrow-right" /></a>
        </div>

        @if ($mahasiswaTerbaru->isEmpty())
            <div class="tb-empty">
                <div class="tb-empty-icon"><x-icon name="inbox" /></div>
                <p class="tb-empty-title">Belum ada mahasiswa terdaftar</p>
                <p class="tb-empty-desc">Mahasiswa baru akan muncul di sini setelah mendaftar.</p>
            </div>
        @else
            <div class="tb-table-wrap">
                <table class="tb-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:right;">Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mahasiswaTerbaru as $mhs)
                            <tr>
                                <td class="font-semibold">
                                    <a href="{{ route('admin.mahasiswa.show', $mhs) }}" class="no-underline" style="color: var(--tb-ink);">
                                        {{ $mhs->nama }}
                                    </a>
                                </td>
                                <td class="tb-text-sm">{{ $mhs->prodi?->nama ?? '-' }}</td>
                                <td style="text-align:center;">
                                    @if ($mhs->status === 'aktif')
                                        <span class="tb-badge tb-badge-success">Aktif</span>
                                    @else
                                        <span class="tb-badge tb-badge-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="tb-text-sm tb-muted" style="text-align:right;">{{ $mhs->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ============ BARIS 2: Distribusi Prodi + Aktivitas Terbaru ============ --}}
<div class="tb-admin-grid-2">
    {{-- Distribusi per Program Studi (Top 10) --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="mortarboard" /></span>
                <div>
                    <h2 class="tb-section-title">Program Studi Teratas</h2>
                    <p class="tb-section-desc">10 prodi dengan mahasiswa terbanyak.</p>
                </div>
            </div>
        </div>

        @if ($distribusiProdi->isEmpty())
            <div class="tb-empty">
                <div class="tb-empty-icon"><x-icon name="inbox" /></div>
                <p class="tb-empty-title">Belum ada data prodi</p>
                <p class="tb-empty-desc">Data akan tampil setelah ada mahasiswa yang mengisi program studi.</p>
            </div>
        @else
            <ul class="tb-admin-distribusi-list">
                @php
                    $maxProdi = $distribusiProdi->first()->jumlah ?? 1;
                @endphp
                @foreach ($distribusiProdi as $row)
                    <li>
                        <div class="tb-admin-distribusi-row">
                            <span>
                                <span class="tb-text-sm font-semibold">{{ $row->program_studi }}</span>
                                <div class="tb-admin-distribusi-sub">{{ $row->fakultas }}</div>
                            </span>
                            <span class="tb-badge tb-badge-navy">{{ $row->jumlah }}</span>
                        </div>
                        <div class="tb-admin-bar-track">
                            <div class="tb-admin-bar-fill tb-admin-bar-accent" style="width: {{ $maxProdi > 0 ? round($row->jumlah / $maxProdi * 100) : 0 }}%;"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="clock-history" /></span>
                <div>
                    <h2 class="tb-section-title">Aktivitas Terbaru</h2>
                    <p class="tb-section-desc">5 aksi terakhir di sistem.</p>
                </div>
            </div>
            <a href="{{ route('admin.aktivitas.index') }}" class="tb-link-more">Lihat semua <x-icon name="arrow-right" /></a>
        </div>

        @if ($aktivitasTerbaru->isEmpty())
            <div class="tb-empty">
                <div class="tb-empty-icon"><x-icon name="inbox" /></div>
                <p class="tb-empty-title">Belum ada aktivitas</p>
                <p class="tb-empty-desc">Riwayat aksi akan muncul di sini.</p>
            </div>
        @else
            @php
                $ikonAksi = [
                    'create' => 'plus-lg', 'update' => 'pencil', 'delete' => 'trash',
                    'toggle' => 'arrow-repeat', 'accept' => 'check-circle', 'reject' => 'x-circle',
                    'cancel' => 'x-lg', 'register' => 'person-plus', 'self-delete' => 'trash',
                ];
            @endphp
            <div class="tb-akt-feed">
                @foreach ($aktivitasTerbaru as $log)
                    @php
                        $suffix = str_contains($log->action, '.') ? substr($log->action, strpos($log->action, '.') + 1) : $log->action;
                        $ikon = $ikonAksi[$suffix] ?? 'clock-history';
                    @endphp
                    <div class="tb-akt-feed-item">
                        <span class="tb-akt-feed-icon"><x-icon name="{{ $ikon }}" /></span>
                        <div class="tb-akt-feed-body">
                            <p class="tb-akt-feed-desc">{{ $log->description }}</p>
                            <p class="tb-akt-feed-meta">
                                {{ $log->user?->nama ?? 'Akun terhapus' }} · {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
