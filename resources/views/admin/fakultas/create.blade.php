@extends('layouts.app')

@section('title', '· Admin · ' . (isset($fakultas) ? 'Edit Fakultas' : 'Tambah Fakultas'))

@section('content')
<style>
    .tb-fak-field { margin-bottom: 1rem; }
    .tb-fak-label { display: block; font-weight: 600; font-size: 0.8rem; color: var(--tb-ink); margin-bottom: 0.35rem; }
    .tb-fak-label .req { color: #dc3545; margin-left: 0.15rem; }
    .tb-fak-hint { font-size: 0.74rem; color: var(--tb-muted); margin-top: 0.3rem; line-height: 1.45; display: flex; align-items: center; gap: 0.3rem; }
    .tb-fak-hint svg { width: 0.85rem; height: 0.85rem; color: var(--tb-accent-dark); flex-shrink: 0; }
    .tb-fak-error { font-size: 0.74rem; color: #dc3545; margin-top: 0.3rem; font-weight: 500; }
    .tb-fak-actions { display: flex; justify-content: flex-end; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--tb-primary-light); }
    .tb-input.is-invalid { border-color: #dc3545; }
</style>

<a href="{{ route('admin.fakultas.index') }}" class="tb-back">
    <x-icon name="arrow-left" /> Kembali ke Daftar Fakultas
</a>

<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="{{ isset($fakultas) ? 'pencil' : 'plus-lg' }}" /></span>
            <div>
                <h2 class="tb-section-title">{{ isset($fakultas) ? 'Edit Fakultas' : 'Tambah Fakultas Baru' }}</h2>
                <p class="tb-section-desc">{{ isset($fakultas) ? 'Perbarui informasi fakultas.' : 'Lengkapi data fakultas baru.' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ isset($fakultas) ? route('admin.fakultas.update', $fakultas) : route('admin.fakultas.store') }}">
        @csrf
        @if (isset($fakultas)) @method('PUT') @endif

        <div class="tb-field-group">
            <div class="tb-fak-field">
                <label for="kode" class="tb-fak-label">Kode Fakultas <span class="req">*</span></label>
                <input type="text" name="kode" id="kode" class="tb-input @error('kode') is-invalid @enderror" value="{{ old('kode', $fakultas->kode ?? '') }}" placeholder="FIK" required maxlength="20" style="text-transform:uppercase;">
                <p class="tb-fak-hint"><x-icon name="info-circle" /> Kode unik fakultas (contoh: FIK, FEB, FT).</p>
                @error('kode') <p class="tb-fak-error">{{ $message }}</p> @enderror
            </div>

            <div class="tb-fak-field">
                <label for="nama" class="tb-fak-label">Nama Fakultas <span class="req">*</span></label>
                <input type="text" name="nama" id="nama" class="tb-input @error('nama') is-invalid @enderror" value="{{ old('nama', $fakultas->nama ?? '') }}" placeholder="Fakultas Ilmu Komputer" required maxlength="255">
                @error('nama') <p class="tb-fak-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="tb-fak-actions">
            <a href="{{ route('admin.fakultas.index') }}" class="tb-btn tb-btn-ghost">Batal</a>
            <button type="submit" class="tb-btn"><x-icon name="save" /> Simpan</button>
        </div>
    </form>
</div>
@endsection
