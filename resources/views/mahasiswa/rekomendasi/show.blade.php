@extends('layouts.app')

@section('title', '· Detail Teman Belajar')

@section('content')
<style>
    /* ============ LAYOUT HALAMAN ============ */
    .tb-show-wrap { max-width: 1100px; margin: 0 auto; }
    .tb-show-grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1.25rem;
        align-items: start;
    }
    .tb-show-main { min-width: 0; display: flex; flex-direction: column; gap: 1rem; }
    .tb-show-aside {
        display: flex; flex-direction: column; gap: 1rem;
        position: sticky; top: 1rem;
    }
    @media (max-width: 991.98px) {
        .tb-show-grid { grid-template-columns: 1fr; }
        .tb-show-aside { position: static; }
    }

    /* ============ HEADER PROFIL (pengganti hero gradient) ============ */
    .tb-show-profile {
        display: flex; align-items: center; flex-wrap: wrap;
        gap: 1.1rem; padding: 0.25rem 0;
    }
    .tb-show-avatar {
        width: 72px; height: 72px; border-radius: 50%;
        background: linear-gradient(135deg, var(--tb-primary) 0%, var(--tb-primary-dark) 100%);
        color: white; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem; font-weight: 800;
        box-shadow: 0 2px 6px rgba(11,37,91,0.18);
    }
    .tb-show-identity { flex: 1 1 240px; min-width: 0; }
    .tb-show-name {
        font-size: 1.35rem; font-weight: 800; color: var(--tb-ink);
        margin: 0 0 0.3rem; letter-spacing: -0.02em; line-height: 1.2;
    }
    .tb-show-prodi {
        font-size: 0.86rem; color: var(--tb-muted); margin: 0;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .tb-show-prodi i { color: var(--tb-primary); }
    .tb-show-sub {
        font-size: 0.77rem; color: var(--tb-muted); margin: 0.2rem 0 0;
    }

    .tb-show-score {
        text-align: center; flex-shrink: 0;
        background: var(--tb-primary-soft);
        border: 1px solid var(--tb-primary-light);
        border-radius: 0.75rem;
        padding: 0.7rem 1.05rem; min-width: 115px;
    }
    .tb-show-score-num {
        font-size: 1.9rem; font-weight: 800; color: var(--tb-accent-dark);
        line-height: 1;
    }
    .tb-show-score-num .pct { color: var(--tb-accent-dark); }
    .tb-show-score-label {
        font-size: 0.64rem; color: var(--tb-muted); margin-top: 0.35rem;
        text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600;
    }
    .tb-show-score-bar { width: 100%; height: 6px; background: var(--tb-primary-light); border-radius: 999px; overflow: hidden; margin-top: 0.55rem; }
    .tb-show-score-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--tb-accent), #ffd08a); }

    .tb-show-gender { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.68rem; font-weight: 700; padding: 0.18rem 0.5rem; border-radius: 999px; vertical-align: middle; }
    .tb-show-gender svg { width: 0.8rem; height: 0.8rem; }
    .tb-show-gender.L { background: #e7f5ff; color: #1c7ed6; }
    .tb-show-gender.P { background: #fff0f6; color: #d6336c; }

    .tb-show-meta-row {
        display: flex; flex-wrap: wrap; gap: 0.45rem;
        margin-top: 1.1rem; padding-top: 1.05rem;
        border-top: 1px solid var(--tb-primary-light);
    }
    .tb-show-meta-row .tb-chip { font-size: 0.74rem; }

    @media (max-width: 575.98px) {
        .tb-show-profile { gap: 0.85rem; }
        .tb-show-avatar { width: 58px; height: 58px; font-size: 1.4rem; }
        .tb-show-name { font-size: 1.15rem; word-break: break-word; }
        .tb-show-score { padding: 0.6rem 0.85rem; min-width: 0; width: 100%; }
        .tb-show-score-num { font-size: 1.5rem; }
        .tb-show-identity { flex: 1 1 100%; }
        .tb-show-meta-row { gap: 0.35rem; }
    }

    /* ============ SECTION TITLE DALAM CARD ============ */
    .tb-show-cardhead {
        display: flex; align-items: center; gap: 0.55rem;
        font-size: 0.95rem; font-weight: 700; color: var(--tb-ink);
        margin: 0 0 1.1rem; padding-bottom: 0.85rem;
        border-bottom: 1px solid var(--tb-primary-light);
    }
    .tb-show-cardhead i { color: var(--tb-primary); font-size: 1.05rem; }

    /* ============ STATUS AKSI PERMINTAAN ============ */
    .tb-show-alert {
        display: flex; align-items: flex-start; gap: 0.85rem;
        border-radius: 0.6rem; padding: 0.95rem 1.05rem;
    }
    .tb-show-alert i { font-size: 1.25rem; flex-shrink: 0; margin-top: 0.05rem; }
    .tb-show-alert-body { flex: 1; min-width: 0; }
    .tb-show-alert-title { font-weight: 700; font-size: 0.9rem; margin-bottom: 0.2rem; }
    .tb-show-alert-text { font-size: 0.82rem; line-height: 1.5; }

    .tb-show-alert-success { background: #e3f7ec; border: 1px solid rgba(29,138,78,0.25); }
    .tb-show-alert-success i { color: #1d8a4e; }
    .tb-show-alert-success .tb-show-alert-title { color: #155724; }
    .tb-show-alert-success .tb-show-alert-text { color: #5a6b7d; }

    .tb-show-alert-pending { background: var(--tb-primary-soft); border: 1px solid var(--tb-primary-light); margin-bottom: 0.85rem; }
    .tb-show-alert-pending i { color: var(--tb-primary); }
    .tb-show-alert-pending .tb-show-alert-title { color: var(--tb-ink); }
    .tb-show-alert-pending .tb-show-alert-text { color: var(--tb-muted); }

    .tb-show-alert-rejected { background: var(--tb-accent-light); border: 1px solid rgba(255,167,58,0.3); margin-bottom: 0.85rem; }
    .tb-show-alert-rejected i { color: var(--tb-accent-dark); }
    .tb-show-alert-rejected .tb-show-alert-title { color: var(--tb-ink); }
    .tb-show-alert-rejected .tb-show-alert-text { color: var(--tb-muted); }

    .tb-show-cta {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 1rem;
    }
    .tb-show-cta-body { flex: 1 1 240px; min-width: 0; }
    .tb-show-cta-title { font-size: 1.05rem; font-weight: 700; color: var(--tb-ink); margin: 0 0 0.3rem; }
    .tb-show-cta-text { font-size: 0.84rem; color: var(--tb-muted); margin: 0; line-height: 1.5; }
    @media (max-width: 575.98px) {
        .tb-show-cta { flex-direction: column; align-items: stretch; }
        .tb-show-cta .tb-btn { justify-content: center; }
    }

    /* ============ PREFERENSI ============ */
    .tb-show-pref-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem 1.5rem; }
    @media (max-width: 575.98px) { .tb-show-pref-grid { grid-template-columns: 1fr; gap: 1rem; } }
    .tb-show-pref-label {
        font-size: 0.7rem; color: var(--tb-muted); font-weight: 600;
        margin-bottom: 0.45rem; text-transform: uppercase; letter-spacing: 0.05em;
        display: flex; align-items: center; gap: 0.35rem;
    }
    .tb-show-pref-label i { color: var(--tb-accent-dark); font-size: 0.85rem; }
    .tb-show-pref-value { font-size: 0.92rem; font-weight: 600; color: var(--tb-ink); line-height: 1.45; }
    .tb-show-pref-badges { display: flex; flex-wrap: wrap; gap: 0.4rem; }

    /* ============ JADWAL ============ */
    .tb-show-jadwal-block { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--tb-primary-light); }
    .tb-show-jadwal-wrap { overflow-x: auto; margin-top: 0.5rem; }
    .tb-show-jadwal-table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 0.76rem; }
    .tb-show-jadwal-table th, .tb-show-jadwal-table td {
        border: 1px solid var(--tb-primary-light);
        padding: 0.45rem 0.5rem; text-align: center; white-space: nowrap;
    }
    .tb-show-jadwal-table thead th {
        background: var(--tb-primary-soft); color: var(--tb-primary);
        font-weight: 600; font-size: 0.68rem;
        text-transform: uppercase; letter-spacing: 0.03em;
    }
    .tb-show-jadwal-table tbody td:first-child {
        background: var(--tb-primary-soft); color: var(--tb-ink);
        font-weight: 600; text-align: left;
    }
    .tb-show-jadwal-check { color: #1d8a4e; font-size: 1rem; }
    .tb-show-jadwal-empty { color: var(--tb-primary-light); }
    .tb-show-jadwal-table td:has(.tb-show-jadwal-check) { background: var(--tb-accent-light); }
    .tb-show-jadwal-jam { display: block; font-size: 0.58rem; font-weight: 700; color: var(--tb-accent-dark); text-transform: none; letter-spacing: 0; margin-top: 0.15rem; background: var(--tb-accent-light); padding: 0.05rem 0.3rem; border-radius: 0.3rem; }

    /* ============ KONTAK ============ */
    .tb-show-kontak-stack { display: flex; flex-direction: column; gap: 1rem; }
    .tb-show-kontak-input { display: flex; align-items: stretch; }
    .tb-show-kontak-icon {
        width: 44px; display: flex; align-items: center; justify-content: center;
        background: var(--tb-primary-soft); border: 1px solid var(--tb-primary-light);
        border-right: none; border-radius: 0.5rem 0 0 0.5rem; font-size: 0.95rem; flex-shrink: 0;
    }
    .tb-show-kontak-value {
        flex: 1; min-width: 0; padding: 0 0.85rem; height: 44px;
        border: 1px solid var(--tb-primary-light); border-radius: 0 0.5rem 0.5rem 0;
        background: white; font-size: 0.86rem; color: var(--tb-ink); font-weight: 500;
        display: flex; align-items: center;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .tb-show-kontak-link {
        margin-left: 0.45rem; width: 44px; height: 44px; border-radius: 0.5rem;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.95rem; font-weight: 600; text-decoration: none;
        transition: all 0.15s ease; flex-shrink: 0;
    }
    .tb-show-kontak-link.wa { background: #1d8a4e; color: white; }
    .tb-show-kontak-link.wa:hover { background: #157347; }
    .tb-show-kontak-link.ig { background: white; border: 1px solid #dc3545; color: #dc3545; }
    .tb-show-kontak-link.ig:hover { background: #dc3545; color: white; }

    .tb-show-kontak-locked { text-align: center; padding: 1.5rem 1rem; }
    .tb-show-kontak-locked-text {
        font-size: 0.84rem; color: var(--tb-muted); margin: 0 auto;
        max-width: 280px; line-height: 1.5;
    }
    .tb-show-kontak-locked-text strong { color: var(--tb-ink); }

    /* ============ INFO SINGKAT ============ */
    .tb-show-info-list { display: flex; flex-direction: column; gap: 0.7rem; }
    .tb-show-info-item { display: flex; align-items: center; gap: 0.65rem; }
    .tb-show-info-icon {
        width: 34px; height: 34px; border-radius: 0.45rem;
        background: var(--tb-primary-soft); color: var(--tb-primary);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; flex-shrink: 0;
    }
    .tb-show-info-body { flex: 1; min-width: 0; }
    .tb-show-info-label {
        font-size: 0.66rem; color: var(--tb-muted); font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.1rem;
    }
    .tb-show-info-value {
        font-size: 0.84rem; font-weight: 600; color: var(--tb-ink);
        line-height: 1.3; word-break: break-word;
    }
    .tb-show-info-icon.tb-show-info-ok { background: #d1e7dd; color: #0a3622; }
    .tb-show-info-icon.tb-show-info-warn { background: var(--tb-accent-light); color: var(--tb-accent-dark); }

    .tb-show-match-score { text-align: center; margin-bottom: 1rem; padding: 0.6rem; background: var(--tb-accent-light); border-radius: 0.6rem; }
    .tb-show-match-num { font-size: 1.6rem; font-weight: 800; color: var(--tb-accent-dark); line-height: 1; }
    .tb-show-match-label { font-size: 0.66rem; color: var(--tb-accent-dark); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-top: 0.3rem; }
</style>

<div class="tb-show-wrap">

    <a href="{{ route('rekomendasi.index') }}" class="tb-back">
        <x-icon name="arrow-left" /> Kembali ke Rekomendasi
    </a>

    @php $profile = $kandidat->profile; @endphp

    @if ($skor !== null)
        @php
            $persen = round($skor * 100);
            $jkLabel = $kandidat->jenis_kelamin === 'L' ? 'Laki-laki' : ($kandidat->jenis_kelamin === 'P' ? 'Perempuan' : null);
        @endphp
    @else
        @php
            $jkLabel = $kandidat->jenis_kelamin === 'L' ? 'Laki-laki' : ($kandidat->jenis_kelamin === 'P' ? 'Perempuan' : null);
        @endphp
    @endif

    <div class="tb-show-grid">

    <div class="tb-show-main">

    {{-- HEADER PROFIL (kartu putih pengganti hero gradient) --}}
    <div class="tb-card">
        <div class="tb-show-profile">
            <div class="tb-show-avatar">{{ strtoupper(mb_substr($kandidat->nama, 0, 1)) }}</div>
            <div class="tb-show-identity">
                <h2 class="tb-show-name">
                    {{ $kandidat->nama }}
                    @if ($jkLabel)
                        <span class="tb-show-gender {{ $kandidat->jenis_kelamin }}"><x-icon name="{{ $kandidat->jenis_kelamin === 'L' ? 'gender-male' : 'gender-female' }}" /> {{ $jkLabel }}</span>
                    @endif
                </h2>
                <p class="tb-show-prodi"><x-icon name="mortarboard" /> {{ $kandidat->prodi?->nama ?? '-' }} ({{ $kandidat->fakultas?->nama ?? '-' }})</p>
                <p class="tb-show-sub">
                    @if ($kandidat->nim) NIM: {{ $kandidat->nim }} &middot; @endif
                    Semester {{ $kandidat->semester }} &middot; Angkatan {{ $kandidat->angkatan }}
                </p>
            </div>
            @if ($skor !== null)
                <div class="tb-show-score">
                    <div class="tb-show-score-num">{{ $persen }}<span class="pct">%</span></div>
                    <div class="tb-show-score-label">Skor Kecocokan</div>
                    <div class="tb-show-score-bar"><div class="tb-show-score-fill" style="width:{{ $persen }}%;"></div></div>
                </div>
            @endif
        </div>
    </div>

    {{-- AKSI PERMINTAAN --}}
    <div class="tb-card">
        <h2 class="tb-show-cardhead"><x-icon name="chat-dots" /> Permintaan Belajar</h2>

        @if ($sudahTerhubung)
            <div class="tb-show-alert tb-show-alert-success">
                <x-icon name="check-circle-fill" />
                <div class="tb-show-alert-body">
                    <div class="tb-show-alert-title">Anda sudah terhubung dengan {{ $kandidat->nama }}.</div>
                    <div class="tb-show-alert-text">Kontak WhatsApp &amp; Instagram {{ $kandidat->nama }} kini tersedia di bagian Kontak di samping.</div>
                </div>
            </div>
        @elseif ($permintaanTerkirim)
            @if ($permintaanTerkirim->status === 'pending')
                <div class="tb-show-alert tb-show-alert-pending">
                    <x-icon name="hourglass-split" />
                    <div class="tb-show-alert-body">
                        <div class="tb-show-alert-title">Permintaan menunggu respons.</div>
                        <div class="tb-show-alert-text">Anda mengirim permintaan pada {{ $permintaanTerkirim->waktu_kirim->format('d M Y, H:i') }}. Kontak akan tersedia setelah {{ $kandidat->nama }} menerima permintaan Anda.</div>
                    </div>
                </div>
            @elseif ($permintaanTerkirim->status === 'rejected')
                <div class="tb-show-alert tb-show-alert-rejected">
                    <x-icon name="x-circle" />
                    <div class="tb-show-alert-body">
                        <div class="tb-show-alert-title">Permintaan sebelumnya ditolak.</div>
                        <div class="tb-show-alert-text">Anda dapat mengirim permintaan baru kepada {{ $kandidat->nama }} jika ingin mencoba kembali.</div>
                    </div>
                </div>
                @if (Route::has('permintaan.store'))
                <form method="POST" action="{{ route('permintaan.store') }}">
                    @csrf
                    <input type="hidden" name="penerima_id" value="{{ $kandidat->id }}">
                    <button type="submit" class="tb-btn"><x-icon name="send" /> Kirim Permintaan Baru</button>
                </form>
                @endif
            @endif
        @else
            @if (Route::has('permintaan.store'))
            <form method="POST" action="{{ route('permintaan.store') }}">
                @csrf
                <input type="hidden" name="penerima_id" value="{{ $kandidat->id }}">
                <div class="tb-show-cta">
                    <div class="tb-show-cta-body">
                        <h3 class="tb-show-cta-title">Ingin belajar bersama?</h3>
                        <p class="tb-show-cta-text">Kirim permintaan belajar ke {{ $kandidat->nama }}. Kontak WhatsApp &amp; Instagram akan terlihat setelah permintaan Anda diterima.</p>
                    </div>
                    <button type="submit" class="tb-btn"><x-icon name="send" /> Kirim Permintaan</button>
                </div>
            </form>
            @else
                <div class="tb-empty">
                    <div class="tb-empty-icon"><x-icon name="lock" /></div>
                    <p class="tb-empty-desc">Fitur kirim permintaan belajar akan segera hadir.</p>
                </div>
            @endif
        @endif
    </div>

    {{-- PROFIL PREFERENSI --}}
    <div class="tb-card">
        <h2 class="tb-show-cardhead"><x-icon name="sliders" /> Profil Preferensi Belajar</h2>

        @if ($profile)
        <div class="tb-show-pref-grid">
            <div>
                <div class="tb-show-pref-label"><x-icon name="lightbulb" /> Minat Bidang</div>
                <div class="tb-show-pref-badges">
                    @foreach ($profile->minat ?? [] as $m)
                        <span class="tb-chip">{{ $m }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="tb-show-pref-label"><x-icon name="bullseye" /> Tujuan Belajar</div>
                <div class="tb-show-pref-value">{{ $profile->tujuan }}</div>
            </div>
            <div>
                <div class="tb-show-pref-label"><x-icon name="people" /> Gaya Belajar</div>
                <div class="tb-show-pref-value">{{ $profile->gaya }}</div>
            </div>
            <div>
                <div class="tb-show-pref-label"><x-icon name="laptop" /> Mode Belajar</div>
                <div class="tb-show-pref-value">{{ $profile->mode }}</div>
            </div>
        </div>

        <div class="tb-show-jadwal-block">
            <div class="tb-show-pref-label"><x-icon name="calendar3" /> Jadwal Luang</div>
            @php
                $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                $waktuList = ['Pagi (06-11)','Siang (11-14)','Sore (14-18)','Malam (18-23)'];
                $selectedJadwal = $profile->jadwal ?? [];
            @endphp
            <div class="tb-show-jadwal-wrap">
                <table class="tb-show-jadwal-table">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Hari</th>
                            @foreach ($waktuList as $w)
                                @php [$namaWaktu, $jam] = array_map('trim', explode('(', rtrim($w, ')'), 2)); @endphp
                                <th>
                                    {{ $namaWaktu }}
                                    <span class="tb-show-jadwal-jam">{{ $jam }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hariList as $h)
                            <tr>
                                <td>{{ $h }}</td>
                                @foreach ($waktuList as $w)
                                    @php $val = "$h $w"; @endphp
                                    <td>
                                        @if (in_array($val, $selectedJadwal))
                                            <x-icon name="check-lg" class="tb-show-jadwal-check" />
                                        @else
                                            <span class="tb-show-jadwal-empty">·</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    </div>

    {{-- ASIDE --}}
    <div class="tb-show-aside">

    @php
        $userProfile = auth()->user()->profile;
        $minatSama = $userProfile && $profile
            ? collect($userProfile->minat ?? [])->intersect($profile->minat ?? [])->count()
            : 0;
        $jadwalSama = $userProfile && $profile
            ? collect($userProfile->jadwal ?? [])->intersect($profile->jadwal ?? [])->count()
            : 0;
        $statusHub = $permintaanTerkirim->status ?? ($sudahTerhubung ? 'accepted' : null);
    @endphp

    {{-- Kecocokan ringkas --}}
    <div class="tb-card">
        <h2 class="tb-show-cardhead"><x-icon name="sliders" /> Kecocokan</h2>

        @if ($skor !== null)
            <div class="tb-show-match-score">
                <div class="tb-show-match-num">{{ $persen }}%</div>
                <div class="tb-show-match-label">Skor kecocokan</div>
            </div>
        @endif

        <div class="tb-show-info-list">
            <div class="tb-show-info-item">
                <span class="tb-show-info-icon"><x-icon name="lightbulb" /></span>
                <div class="tb-show-info-body">
                    <div class="tb-show-info-label">Minat sama</div>
                    <div class="tb-show-info-value">{{ $minatSama }} bidang</div>
                </div>
            </div>
            <div class="tb-show-info-item">
                <span class="tb-show-info-icon"><x-icon name="calendar3" /></span>
                <div class="tb-show-info-body">
                    <div class="tb-show-info-label">Jadwal tumpang tindih</div>
                    <div class="tb-show-info-value">{{ $jadwalSama }} slot</div>
                </div>
            </div>
            <div class="tb-show-info-item">
                <span class="tb-show-info-icon @if($statusHub === 'accepted') tb-show-info-ok @elseif($statusHub === 'pending') tb-show-info-warn @endif">
                    <x-icon name="@if($statusHub === 'accepted') check-circle-fill @elseif($statusHub === 'pending') hourglass-split @else person-plus @endif" />
                </span>
                <div class="tb-show-info-body">
                    <div class="tb-show-info-label">Status hubungan</div>
                    <div class="tb-show-info-value">
                        @if ($statusHub === 'accepted') Sudah terhubung
                        @elseif ($statusHub === 'pending') Menunggu respons
                        @elseif ($statusHub === 'rejected') Ditolak sebelumnya
                        @else Belum ada permintaan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KONTAK --}}
    <div class="tb-card">
        <h2 class="tb-show-cardhead"><x-icon name="telephone" /> Kontak</h2>
        @if ($tampilkanKontak && $profile)
            <div class="tb-show-kontak-stack">
                <div class="tb-field-group">
                    <label class="tb-label"><x-icon name="whatsapp" style="color:#1d8a4e;" /> WhatsApp</label>
                    <div class="tb-show-kontak-input">
                        <span class="tb-show-kontak-icon" style="color:#1d8a4e;"><x-icon name="whatsapp" /></span>
                        <div class="tb-show-kontak-value">{{ $profile->whatsapp ?: '-' }}</div>
                        @if ($profile->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->whatsapp) }}" target="_blank" class="tb-show-kontak-link wa" title="Buka WhatsApp"><x-icon name="chat" /></a>
                        @endif
                    </div>
                </div>
                <div class="tb-field-group" style="margin-bottom:0;">
                    <label class="tb-label"><x-icon name="instagram" style="color:#dc3545;" /> Instagram</label>
                    <div class="tb-show-kontak-input">
                        <span class="tb-show-kontak-icon" style="color:#dc3545;"><x-icon name="instagram" /></span>
                        <div class="tb-show-kontak-value">{{ $profile->instagram ? '@' . $profile->instagram : '-' }}</div>
                        @if ($profile->instagram)
                            <a href="https://instagram.com/{{ $profile->instagram }}" target="_blank" class="tb-show-kontak-link ig" title="Buka Instagram"><x-icon name="box-arrow-up-right" /></a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="tb-show-kontak-locked">
                <div class="tb-empty-icon" style="margin-bottom:0.7rem;"><x-icon name="lock-fill" /></div>
                <p class="tb-show-kontak-locked-text">
                    Kontak WhatsApp &amp; Instagram <strong>{{ $kandidat->nama }}</strong> hanya dapat dilihat setelah permintaan belajar Anda diterima.
                </p>
            </div>
        @endif
    </div>
    </div>

    </div>
</div>
@endsection
