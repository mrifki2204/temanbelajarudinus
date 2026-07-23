@extends('layouts.app')

@section('title', '· Admin · Detail Mahasiswa')

@section('content')
{{-- ============ CSS SPESIFIK HALAMAN (prefix tb-admin-*) ============ --}}
<style>
    .tb-admin-profile-hero { display: flex; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .tb-admin-avatar {
        width: 64px; height: 64px; border-radius: 0.75rem; flex-shrink: 0;
        background: var(--tb-primary); color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; font-weight: 800;
    }
    .tb-admin-profile-name { font-size: 1.2rem; font-weight: 800; color: var(--tb-ink); margin: 0 0 0.2rem; letter-spacing: -0.02em; }
    .tb-admin-profile-meta { font-size: 0.82rem; color: var(--tb-muted); margin: 0; }
    .tb-admin-profile-meta strong { color: var(--tb-ink); font-weight: 600; }
    .tb-admin-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem 1.25rem; }
    .tb-admin-info-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--tb-muted); margin: 0 0 0.2rem; font-weight: 600; }
    .tb-admin-info-value { font-size: 0.88rem; color: var(--tb-ink); margin: 0; font-weight: 500; }
    .tb-admin-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem; }
    .tb-admin-stats-grid .tb-card { margin-top: 0; }
    .tb-admin-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
    @media (max-width: 575px) {
        .tb-admin-info-grid { grid-template-columns: 1fr; }
        .tb-admin-stats-grid { grid-template-columns: 1fr; }
    }
</style>

{{-- ============ BACK ============ --}}
<a href="{{ route('admin.mahasiswa.index') }}" class="tb-back">
    <x-icon name="arrow-left" /> Kembali ke Daftar Mahasiswa
</a>

{{-- ============ PROFILE HERO ============ --}}
<div class="tb-card">
    <div class="tb-admin-profile-hero">
        <div class="tb-admin-avatar">
            {{ strtoupper(substr($mahasiswa->nama, 0, 1)) }}
        </div>
        <div style="flex: 1; min-width: 200px;">
            <h2 class="tb-admin-profile-name">{{ $mahasiswa->nama }}</h2>
            <p class="tb-admin-profile-meta">
                <x-icon name="mortarboard" class="me-1" />{{ $mahasiswa->prodi?->nama ?? 'Belum diisi' }} ({{ $mahasiswa->fakultas?->nama ?? '-' }})
            </p>
            <p class="tb-admin-profile-meta">
                @if ($mahasiswa->nim) NIM: <strong>{{ $mahasiswa->nim }}</strong> &middot; @endif
                Semester <strong>{{ $mahasiswa->semester ?? '-' }}</strong> &middot; Angkatan <strong>{{ $mahasiswa->angkatan ?? '-' }}</strong>
            </p>
            @if ($mahasiswa->jenis_kelamin)
                <p class="tb-admin-profile-meta">
                    Jenis Kelamin: <strong>{{ $mahasiswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong>
                </p>
            @endif
        </div>
        <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
            @if ($mahasiswa->status === 'aktif')
                <span class="tb-badge tb-badge-success"><x-icon name="check-circle" class="me-1" /> Aktif</span>
            @else
                <span class="tb-badge tb-badge-danger"><x-icon name="slash-circle" class="me-1" /> Nonaktif</span>
            @endif
            <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}" class="tb-btn tb-btn-outline tb-btn-sm">
                <x-icon name="pencil" /> Edit
            </a>
        </div>
    </div>

    <hr class="tb-divider">

    <div class="tb-admin-info-grid">
        <div>
            <p class="tb-admin-info-label">Email</p>
            <p class="tb-admin-info-value">{{ $mahasiswa->email }}</p>
        </div>
        <div>
            <p class="tb-admin-info-label">Terdaftar</p>
            <p class="tb-admin-info-value">{{ $mahasiswa->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    <hr class="tb-divider">

    <div class="flex gap-2 flex-wrap">
        @if ($mahasiswa->status === 'aktif')
            <form method="POST" action="{{ route('admin.mahasiswa.toggle-status', $mahasiswa) }}" onsubmit="return confirm('Nonaktifkan akun {{ $mahasiswa->nama }}? Mahasiswa tidak akan dapat login.');">
                @csrf
                @method('PATCH')
                <button type="submit" class="tb-btn tb-btn-danger">
                    <x-icon name="slash-circle" /> Nonaktifkan Akun
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.mahasiswa.toggle-status', $mahasiswa) }}" onsubmit="return confirm('Aktifkan kembali akun {{ $mahasiswa->nama }}?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="tb-btn">
                    <x-icon name="check-circle" /> Aktifkan Kembali
                </button>
            </form>
        @endif
    </div>
</div>

{{-- ============ STATISTIK AKTIVITAS ============ --}}
<div class="tb-admin-stats-grid">
    <div class="tb-card">
        <div class="tb-stat-row">
            <div>
                <p class="tb-stat-label">Permintaan Terkirim</p>
                <p class="tb-stat-num">{{ $permintaanTerkirim }}</p>
            </div>
            <span class="tb-stat-icon"><x-icon name="send" /></span>
        </div>
    </div>
    <div class="tb-card">
        <div class="tb-stat-row">
            <div>
                <p class="tb-stat-label">Permintaan Diterima</p>
                <p class="tb-stat-num">{{ $permintaanDiterima }}</p>
            </div>
            <span class="tb-stat-icon"><x-icon name="inbox" /></span>
        </div>
    </div>
    <div class="tb-card">
        <div class="tb-stat-row">
            <div>
                <p class="tb-stat-label">Skor Similaritas</p>
                <p class="tb-stat-num">{{ $skorDiberikan }}</p>
            </div>
            <span class="tb-stat-icon"><x-icon name="stars" /></span>
        </div>
    </div>
</div>

{{-- ============ PROFIL PREFERENSI ============ --}}
@if ($mahasiswa->profile)
    @php $profile = $mahasiswa->profile; @endphp
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="sliders" /></span>
                <div>
                    <h2 class="tb-section-title">Profil Preferensi</h2>
                    <p class="tb-section-desc">Preferensi belajar yang diisi mahasiswa untuk pencocokan.</p>
                </div>
            </div>
        </div>

        <div class="tb-admin-info-grid">
            <div>
                <p class="tb-admin-info-label">Minat Bidang</p>
                @if (!empty($profile->minat))
                    <div class="tb-admin-chips">
                        @foreach ($profile->minat as $m)
                            <span class="tb-chip">{{ $m }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="tb-admin-info-value tb-muted">-</p>
                @endif
            </div>
            <div>
                <p class="tb-admin-info-label">Tujuan Belajar</p>
                <p class="tb-admin-info-value">{{ $profile->tujuan ?? '-' }}</p>
            </div>
            <div>
                <p class="tb-admin-info-label">Gaya Belajar</p>
                <p class="tb-admin-info-value">{{ $profile->gaya ?? '-' }}</p>
            </div>
            <div>
                <p class="tb-admin-info-label">Mode Belajar</p>
                <p class="tb-admin-info-value">{{ $profile->mode ?? '-' }}</p>
            </div>
            <div style="grid-column: 1 / -1;">
                <p class="tb-admin-info-label">Jadwal Luang</p>
                @if (!empty($profile->jadwal))
                    <div class="tb-admin-chips">
                        @foreach ($profile->jadwal as $j)
                            <span class="tb-chip">{{ $j }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="tb-admin-info-value tb-muted">-</p>
                @endif
            </div>
            <div>
                <p class="tb-admin-info-label"><x-icon name="whatsapp" class="me-1" />WhatsApp</p>
                <p class="tb-admin-info-value">{{ $profile->whatsapp ?? '-' }}</p>
            </div>
            <div>
                <p class="tb-admin-info-label"><x-icon name="instagram" class="me-1" />Instagram</p>
                <p class="tb-admin-info-value">{{ $profile->instagram ? '@' . $profile->instagram : '-' }}</p>
            </div>
        </div>
    </div>
@else
    <div class="tb-card">
        <div class="tb-empty">
            <div class="tb-empty-icon"><x-icon name="clipboard-x" /></div>
            <p class="tb-empty-title">Profil preferensi belum diisi</p>
            <p class="tb-empty-desc">Mahasiswa ini belum mengisi profil preferensi belajar.</p>
        </div>
    </div>
@endif
@endsection
