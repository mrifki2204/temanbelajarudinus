@extends('layouts.app')

@section('title', '· Rekomendasi')

@section('content')
@php
    $user = auth()->user();
@endphp

<style>
    .tb-filter-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; align-items: end; }
    @media (max-width: 991.98px) { .tb-filter-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .tb-filter-row { grid-template-columns: 1fr; } }

    .tb-rec-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    @media (max-width: 767.98px) { .tb-rec-grid { grid-template-columns: 1fr; } }
    @media (max-width: 575.98px) {
        .tb-rec-card { padding: 1rem; }
        .tb-rec-head { gap: 0.65rem; }
        .tb-rec-name { font-size: 0.9rem; word-break: break-word; }
    }

    .tb-rec-card {
        background: white; border: 1px solid var(--tb-primary-light); border-radius: 0.85rem;
        padding: 1.15rem; height: 100%; box-sizing: border-box; position: relative; overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        display: flex; flex-direction: column; gap: 0.85rem;
    }
    .tb-rec-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--tb-primary), var(--tb-accent)); transform: scaleX(0); transform-origin: left; transition: transform 0.3s ease; }
    .tb-rec-card:hover { border-color: rgba(11,37,91,0.18); box-shadow: 0 12px 28px rgba(11,37,91,0.10); transform: translateY(-3px); }
    .tb-rec-card:hover::before { transform: scaleX(1); }

    .tb-rec-head { display: flex; align-items: flex-start; gap: 0.8rem; }
    .tb-rec-avatar {
        width: 46px; height: 46px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1.1rem; color: white; flex-shrink: 0;
        background: linear-gradient(135deg, var(--tb-primary), #1a3a7a);
    }
    .tb-rec-info { flex: 1; min-width: 0; }
    .tb-rec-name-row { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
    .tb-rec-name { font-weight: 700; font-size: 0.95rem; color: var(--tb-ink); }
    .tb-rec-top-badge { background: var(--tb-accent); color: var(--tb-primary-dark); font-size: 0.6rem; font-weight: 700; padding: 0.12rem 0.45rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 0.2rem; }
    .tb-rec-top-badge svg { width: 0.7rem; height: 0.7rem; }
    .tb-rec-gender { font-size: 0.6rem; font-weight: 700; padding: 0.12rem 0.45rem; border-radius: 0.3rem; display: inline-flex; align-items: center; gap: 0.2rem; }
    .tb-rec-gender svg { width: 0.7rem; height: 0.7rem; }
    .tb-rec-gender.L { background: #e7f5ff; color: #1c7ed6; }
    .tb-rec-gender.P { background: #fff0f6; color: #d6336c; }
    .tb-rec-status { font-size: 0.6rem; font-weight: 700; padding: 0.12rem 0.45rem; border-radius: 0.3rem; display: inline-flex; align-items: center; gap: 0.2rem; }
    .tb-rec-status svg { width: 0.7rem; height: 0.7rem; }
    .tb-rec-status-pending { background: #fff3cd; color: #997404; }
    .tb-rec-status-accepted { background: #d1e7dd; color: #0a3622; }
    .tb-rec-status-rejected { background: #f8d7da; color: #842029; }
    .tb-rec-meta { font-size: 0.74rem; color: var(--tb-muted); margin-top: 0.2rem; }
    .tb-rec-score-box { flex-shrink: 0; text-align: center; }
    .tb-rec-score {
        font-weight: 800; font-size: 0.9rem; padding: 0.35rem 0.65rem; border-radius: 999px;
        line-height: 1; display: inline-block;
        background: var(--tb-accent-light); color: var(--tb-accent-dark);
    }
    .tb-rec-score-label { font-size: 0.56rem; color: var(--tb-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-top: 0.2rem; }
    .tb-rec-score-bar { width: 100%; height: 5px; background: var(--tb-primary-soft); border-radius: 999px; overflow: hidden; }
    .tb-rec-score-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--tb-accent), #ffd08a); transition: width 0.4s ease; }

    .tb-pref-badges { display: flex; flex-wrap: wrap; gap: 0.3rem; }
    .tb-pref-badge { background: var(--tb-primary-soft); color: var(--tb-primary); font-weight: 600; padding: 0.22rem 0.55rem; border-radius: 0.35rem; font-size: 0.7rem; }
    .tb-pref-badge-more { background: var(--tb-accent-light); color: var(--tb-accent-dark); }

    .tb-rec-details { display: flex; flex-wrap: wrap; gap: 0.4rem 0.85rem; font-size: 0.73rem; color: var(--tb-muted); }
    .tb-rec-details span { display: inline-flex; align-items: center; gap: 0.3rem; }
    .tb-rec-details svg { width: 0.85rem; height: 0.85rem; color: var(--tb-primary); }

    .tb-rec-detail-btn {
        margin-top: auto; width: 100%; padding: 0.55rem 0.85rem;
        border: 1px solid var(--tb-primary); border-radius: 0.55rem;
        background: white; color: var(--tb-primary); font-size: 0.82rem; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
        transition: all 0.2s ease;
    }
    .tb-rec-detail-btn svg { width: 1rem; height: 1rem; transition: transform 0.2s ease; }
    .tb-rec-detail-btn:hover { background: var(--tb-primary); color: white; }
    .tb-rec-detail-btn:hover svg { transform: translateX(3px); }

    .tb-rec-info-banner {
        display: flex; align-items: center; gap: 0.5rem;
        background: var(--tb-accent-light); border: 1px solid rgba(11,37,91,0.10);
        color: var(--tb-ink); padding: 0.7rem 1rem; border-radius: 0.6rem;
        font-size: 0.82rem; margin-bottom: 1rem;
    }
    .tb-rec-info-banner svg { color: var(--tb-primary); font-size: 1rem; flex-shrink: 0; width: 1.05rem; height: 1.05rem; }
</style>

<div class="tb-page-head">
    <div class="tb-page-head-text">
        <h1>Rekomendasi Teman Belajar</h1>
        <p>Top 10 kandidat berdasarkan Content-Based Filtering &amp; Cosine Similarity</p>
    </div>
    <span class="tb-badge tb-badge-navy" style="padding:0.45rem 0.85rem;font-size:0.82rem;">
        <x-icon name="people" /> {{ $totalKandidat }} kandidat
    </span>
</div>

{{-- Filter --}}
<div class="tb-card" style="margin-bottom:1.25rem;">
    <form method="GET" action="{{ route('rekomendasi.index') }}" id="filterForm">
        <div class="tb-filter-row">
            <div>
                <label class="tb-label" for="filterFakultas">Fakultas</label>
                <select name="fakultas_id" id="filterFakultas" class="tb-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Fakultas</option>
                    @foreach ($fakultasList as $f)
                        <option value="{{ $f->id }}" @selected($filterFakultas == $f->id)>{{ $f->nama }} ({{ $f->kode }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tb-label" for="filterProdi">Program Studi</label>
                <select name="prodi_id" id="filterProdi" class="tb-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Prodi</option>
                    @foreach ($prodiList as $p)
                        <option value="{{ $p->id }}" data-fakultas-id="{{ $p->fakultas_id }}" @selected($filterProdi == $p->id)>{{ $p->nama }} ({{ $p->jenjang }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tb-label" for="filterGender">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="filterGender" class="tb-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua</option>
                    <option value="L" @selected($filterGender === 'L')>Laki-laki</option>
                    <option value="P" @selected($filterGender === 'P')>Perempuan</option>
                </select>
            </div>
        </div>
        @if ($filterFakultas || $filterProdi || $filterGender)
            <div style="margin-top:0.75rem;display:flex;justify-content:flex-end;">
                <a href="{{ route('rekomendasi.index') }}" class="tb-btn tb-btn-ghost tb-btn-sm" title="Reset filter"><x-icon name="arrow-counterclockwise" /> Reset Filter</a>
            </div>
        @endif
    </form>
</div>

@if (($filterFakultas || $filterProdi || $filterGender) && ! $rekomendasi->isEmpty() && $totalKandidat < 10)
    <div class="tb-rec-info-banner" role="note">
        <x-icon name="info-circle" />
        Menampilkan {{ $totalKandidat }} dari maksimal 10 kandidat. Cakupan filter Anda sempit — coba perluas (reset filter) untuk melihat lebih banyak rekomendasi.
    </div>
@endif

@if ($rekomendasi->isEmpty())
    <div class="tb-card tb-empty">
        @if ($filterFakultas || $filterProdi || $filterGender)
            <span class="tb-empty-icon"><x-icon name="search" /></span>
            <h3 class="tb-empty-title">Tidak ada kandidat yang cocok</h3>
            <p class="tb-empty-desc">Coba ubah atau reset filter Anda untuk hasil yang lebih luas.</p>
            <a href="{{ route('rekomendasi.index') }}" class="tb-btn tb-btn-outline"><x-icon name="arrow-counterclockwise" /> Reset Filter</a>
        @else
            <span class="tb-empty-icon"><x-icon name="people" /></span>
            <h3 class="tb-empty-title">Belum ada rekomendasi</h3>
            <p class="tb-empty-desc">Belum ada mahasiswa lain dengan profil lengkap. Coba lagi nanti.</p>
        @endif
    </div>
@else
    <div class="tb-rec-grid">
        @foreach ($rekomendasi as $i => $rec)
            @php
                $kandidat = $rec->kandidat;
                $profile = $kandidat->profile;
                $persen = round($rec->skor * 100);
                $statusHub = $hubungan[$kandidat->id] ?? null;
                $jkLabel = $kandidat->jenis_kelamin === 'L' ? 'Laki-laki' : ($kandidat->jenis_kelamin === 'P' ? 'Perempuan' : null);
            @endphp
            <div class="tb-rec-card">
                <div class="tb-rec-head">
                    <span class="tb-rec-avatar">{{ strtoupper(mb_substr($kandidat->nama, 0, 1)) }}</span>
                    <div class="tb-rec-info">
                        <div class="tb-rec-name-row">
                            <span class="tb-rec-name">{{ $kandidat->nama }}</span>
                            @if ($jkLabel)
                                <span class="tb-rec-gender {{ $kandidat->jenis_kelamin }}"><x-icon name="{{ $kandidat->jenis_kelamin === 'L' ? 'gender-male' : 'gender-female' }}" /> {{ $jkLabel }}</span>
                            @endif
                            @if ($i === 0)
                                <span class="tb-rec-top-badge"><x-icon name="star-fill" /> Top Match</span>
                            @endif
                            @if ($statusHub === 'pending')
                                <span class="tb-rec-status tb-rec-status-pending"><x-icon name="clock" /> Menunggu</span>
                            @elseif ($statusHub === 'accepted')
                                <span class="tb-rec-status tb-rec-status-accepted"><x-icon name="check-circle-fill" /> Teman</span>
                            @elseif ($statusHub === 'rejected')
                                <span class="tb-rec-status tb-rec-status-rejected"><x-icon name="x-circle" /> Ditolak</span>
                            @endif
                        </div>
                        <div class="tb-rec-meta">{{ $kandidat->prodi?->nama ?? '-' }} · Semester {{ $kandidat->semester }}</div>
                    </div>
                    <div class="tb-rec-score-box">
                        <span class="tb-rec-score">{{ $persen }}%</span>
                        <div class="tb-rec-score-label">match</div>
                    </div>
                </div>

                <div class="tb-rec-score-bar"><div class="tb-rec-score-fill" style="width:{{ $persen }}%;"></div></div>

                @if (! empty($profile->minat))
                    <div class="tb-pref-badges">
                        @foreach (array_slice($profile->minat, 0, 3) as $m)
                            <span class="tb-pref-badge">{{ $m }}</span>
                        @endforeach
                        @if (count($profile->minat) > 3)
                            <span class="tb-pref-badge tb-pref-badge-more">+{{ count($profile->minat) - 3 }}</span>
                        @endif
                    </div>
                @endif

                <div class="tb-rec-details">
                    <span><x-icon name="bullseye" />{{ $profile->tujuan }}</span>
                    <span><x-icon name="people" />{{ $profile->gaya }}</span>
                    <span><x-icon name="laptop" />{{ $profile->mode }}</span>
                    <span><x-icon name="calendar3" />{{ count($profile->jadwal ?? []) }} slot</span>
                </div>

                <a href="{{ route('rekomendasi.show', $kandidat->id) }}" class="tb-rec-detail-btn">
                    Lihat Detail <x-icon name="arrow-right" />
                </a>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('scripts')
<script>
(function() {
    const fakultasSelect = document.getElementById('filterFakultas');
    const prodiSelect = document.getElementById('filterProdi');
    if (!fakultasSelect || !prodiSelect) return;
    const prodiOptions = Array.from(prodiSelect.options);
    function filterProdi() {
        const fakultasId = fakultasSelect.value;
        prodiOptions.forEach(opt => {
            if (!opt.value) return;
            opt.hidden = fakultasId && opt.dataset.fakultasId !== fakultasId;
        });
    }
    fakultasSelect.addEventListener('change', filterProdi);
    filterProdi();
})();
</script>
@endpush
