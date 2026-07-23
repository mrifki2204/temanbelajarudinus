@extends('layouts.app')

@section('title', '· Admin · ' . (isset($prodi) ? 'Edit Prodi' : 'Tambah Prodi'))

@section('content')
<style>
    .tb-prodi-field { margin-bottom: 1rem; }
    .tb-prodi-label { display: block; font-weight: 600; font-size: 0.8rem; color: var(--tb-ink); margin-bottom: 0.35rem; }
    .tb-prodi-label .req { color: #dc3545; margin-left: 0.15rem; }
    .tb-prodi-hint { font-size: 0.74rem; color: var(--tb-muted); margin-top: 0.3rem; line-height: 1.45; display: flex; align-items: center; gap: 0.3rem; }
    .tb-prodi-hint svg { width: 0.85rem; height: 0.85rem; color: var(--tb-accent-dark); flex-shrink: 0; }
    .tb-prodi-err { font-size: 0.74rem; color: #dc3545; margin-top: 0.3rem; font-weight: 500; }
    .tb-prodi-actions { display: flex; justify-content: flex-end; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--tb-primary-light); }
    .tb-input.is-invalid, .tb-select.is-invalid { border-color: #dc3545; }
</style>

<a href="{{ route('admin.prodi.index') }}" class="tb-back">
    <x-icon name="arrow-left" /> Kembali ke Daftar Program Studi
</a>

<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="{{ isset($prodi) ? 'pencil' : 'plus-lg' }}" /></span>
            <div>
                <h2 class="tb-section-title">{{ isset($prodi) ? 'Edit Program Studi' : 'Tambah Program Studi Baru' }}</h2>
                <p class="tb-section-desc">{{ isset($prodi) ? 'Perbarui informasi program studi.' : 'Lengkapi data program studi baru.' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ isset($prodi) ? route('admin.prodi.update', $prodi) : route('admin.prodi.store') }}">
        @csrf
        @if (isset($prodi)) @method('PUT') @endif

        <div class="tb-field-group">
            <div class="tb-prodi-field">
                <label for="fakultas_id" class="tb-prodi-label">Fakultas <span class="req">*</span></label>
                <select name="fakultas_id" id="fakultas_id" class="tb-select @error('fakultas_id') is-invalid @enderror" required>
                    <option value="">— Pilih Fakultas —</option>
                    @foreach ($fakultasList as $f)
                        <option value="{{ $f->id }}" @selected(old('fakultas_id', $prodi->fakultas_id ?? '') == $f->id)>{{ $f->nama }} ({{ $f->kode }})</option>
                    @endforeach
                </select>
                @error('fakultas_id') <p class="tb-prodi-err">{{ $message }}</p> @enderror
            </div>

            <div class="tb-prodi-field">
                <label for="kode" class="tb-prodi-label">Kode Prodi <span class="req">*</span></label>
                <input type="text" name="kode" id="kode" class="tb-input @error('kode') is-invalid @enderror" value="{{ old('kode', $prodi->kode ?? '') }}" placeholder="A11" required maxlength="20" style="text-transform:uppercase;">
                <p class="tb-prodi-hint"><x-icon name="info-circle" /> Kode unik prodi (contoh: A11, A22, B11).</p>
                @error('kode') <p class="tb-prodi-err">{{ $message }}</p> @enderror
            </div>

            <div class="tb-prodi-field">
                <label for="nama" class="tb-prodi-label">Nama Program Studi <span class="req">*</span></label>
                <input type="text" name="nama" id="nama" class="tb-input @error('nama') is-invalid @enderror" value="{{ old('nama', $prodi->nama ?? '') }}" placeholder="Teknik Informatika" required maxlength="255">
                @error('nama') <p class="tb-prodi-err">{{ $message }}</p> @enderror
            </div>

            <div class="tb-prodi-field">
                <label for="jenjang" class="tb-prodi-label">Jenjang <span class="req">*</span></label>
                <select name="jenjang" id="jenjang" class="tb-select @error('jenjang') is-invalid @enderror" required>
                    <option value="">— Pilih Jenjang —</option>
                    @foreach (['D3', 'D4', 'S1'] as $j)
                        <option value="{{ $j }}" @selected(old('jenjang', $prodi->jenjang ?? '') === $j)>{{ $j }}</option>
                    @endforeach
                </select>
                @error('jenjang') <p class="tb-prodi-err">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="tb-prodi-actions">
            <a href="{{ route('admin.prodi.index') }}" class="tb-btn tb-btn-ghost">Batal</a>
            <button type="submit" class="tb-btn"><x-icon name="save" /> Simpan</button>
        </div>
    </form>
</div>
@endsection
