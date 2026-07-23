@extends('layouts.app')

@section('title', '· Rekomendasi')

@section('content')
<style>
    .tb-empty-wrap { max-width: 640px; margin: 0 auto; }
    .tb-empty-attrs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; }
    @media (max-width: 575.98px) { .tb-empty-attrs-grid { grid-template-columns: 1fr; } }
    .tb-empty-attr-item {
        display: flex; align-items: center; gap: 0.7rem;
        padding: 0.7rem 0.85rem;
        border: 1px solid var(--tb-primary-light); border-radius: 0.6rem;
        background: var(--tb-primary-soft);
    }
    .tb-empty-attr-icon {
        width: 34px; height: 34px; border-radius: 0.5rem; flex-shrink: 0;
        background: white; color: var(--tb-primary);
        display: flex; align-items: center; justify-content: center; font-size: 0.95rem;
    }
    .tb-empty-attr-text { font-size: 0.85rem; font-weight: 600; color: var(--tb-ink); }
    .tb-empty-attr-item.full { grid-column: 1 / -1; }
</style>

<div class="tb-empty-wrap">
    <div class="tb-card" style="text-align:center; padding:2.5rem 2rem;">
        <div class="tb-empty-icon" style="width:64px;height:64px;font-size:1.7rem;"><x-icon name="clipboard-check" /></div>
        <h1 class="tb-empty-title" style="font-size:1.3rem;">Profil Belum Lengkap</h1>
        <p class="tb-empty-desc">Untuk mendapatkan rekomendasi teman belajar, lengkapi profil preferensi terlebih dahulu. Sistem akan mencocokkan-mu dengan mahasiswa lain berdasarkan lima atribut preferensi.</p>
        <a href="{{ route('profil.edit') }}" class="tb-btn"><x-icon name="pencil-square" /> Lengkapi Profil Sekarang</a>
    </div>

    <div class="tb-card" style="margin-top:1rem;">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="list-check" /></span>
                <h2 class="tb-section-title">Atribut yang Perlu Diisi</h2>
            </div>
        </div>
        <div class="tb-empty-attrs-grid">
            <div class="tb-empty-attr-item">
                <span class="tb-empty-attr-icon"><x-icon name="lightbulb" /></span>
                <span class="tb-empty-attr-text">Minat Bidang Belajar</span>
            </div>
            <div class="tb-empty-attr-item">
                <span class="tb-empty-attr-icon"><x-icon name="bullseye" /></span>
                <span class="tb-empty-attr-text">Tujuan Belajar</span>
            </div>
            <div class="tb-empty-attr-item">
                <span class="tb-empty-attr-icon"><x-icon name="people" /></span>
                <span class="tb-empty-attr-text">Gaya Belajar</span>
            </div>
            <div class="tb-empty-attr-item">
                <span class="tb-empty-attr-icon"><x-icon name="calendar3" /></span>
                <span class="tb-empty-attr-text">Jadwal Luang</span>
            </div>
            <div class="tb-empty-attr-item full">
                <span class="tb-empty-attr-icon"><x-icon name="laptop" /></span>
                <span class="tb-empty-attr-text">Mode Belajar (Online / Tatap Muka / Fleksibel)</span>
            </div>
        </div>
    </div>
</div>
@endsection
