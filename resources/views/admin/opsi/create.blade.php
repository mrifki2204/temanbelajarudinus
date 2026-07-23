@extends('layouts.app')

@php
    // Tentukan tipe aktif: dari opsi (edit), dari query (create dengan ?tipe=), atau null
    $tipeAktif = isset($opsi) ? $opsi->tipe : ($tipeDipilih ?? null);
    $tw = $tipeAktif && isset($tipeMeta[$tipeAktif]) ? $tipeMeta[$tipeAktif] : null;
    $labelItem = $tw ? $tw['label'] : 'Item';
    $kembaliTipe = $tipeAktif ?? 'minat';
    // Untuk edit jadwal: hari & slot hasil parse nilai (null saat create)
    $jadwalHari = $jadwalHari ?? null;
    $jadwalSlot = $jadwalSlot ?? null;
@endphp
@section('title', '· Admin · ' . (isset($opsi) ? 'Edit' : 'Tambah') . ' ' . $labelItem)

@section('content')
<style>
    .tb-opsi-field { margin-bottom: 1rem; }
    .tb-opsi-label { display: block; font-weight: 600; font-size: 0.8rem; color: var(--tb-ink); margin-bottom: 0.35rem; }
    .tb-opsi-label .req { color: #dc3545; margin-left: 0.15rem; }
    .tb-opsi-hint { font-size: 0.74rem; color: var(--tb-muted); margin-top: 0.35rem; line-height: 1.5; display: flex; align-items: flex-start; gap: 0.3rem; }
    .tb-opsi-hint svg { width: 0.85rem; height: 0.85rem; color: var(--tb-accent-dark); flex-shrink: 0; margin-top: 0.1rem; }
    .tb-opsi-err { font-size: 0.74rem; color: #dc3545; margin-top: 0.3rem; font-weight: 500; }
    .tb-opsi-actions { display: flex; justify-content: flex-end; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--tb-primary-light); }
    .tb-input.is-invalid, .tb-select.is-invalid { border-color: #dc3545; }

    /* Badge kategori terpilih */
    .tb-opsi-kategori-badge {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.7rem 0.85rem; border-radius: 0.55rem;
        border: 1px solid {{ $tw ? $tw['color'].'22' : 'var(--tb-primary-light)' }};
        background: {{ $tw ? $tw['bg'] : 'var(--tb-primary-soft)' }};
    }
    .tb-opsi-kategori-badge .ico {
        width: 34px; height: 34px; border-radius: 0.45rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: white; color: {{ $tw ? $tw['color'] : 'var(--tb-primary)' }};
    }
    .tb-opsi-kategori-badge .ico svg { width: 1.05rem; height: 1.05rem; }
    .tb-opsi-kategori-badge .txt { flex: 1; }
    .tb-opsi-kategori-badge .nama { font-size: 0.85rem; font-weight: 700; color: var(--tb-ink); text-transform: capitalize; }
    .tb-opsi-kategori-badge .ket { font-size: 0.72rem; color: var(--tb-muted); }
    .tb-opsi-placeholder-hint { font-size: 0.74rem; color: var(--tb-muted); margin-top: 0.35rem; line-height: 1.5; }
    .tb-jadwal-pick { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    @media (max-width: 575.98px) { .tb-jadwal-pick { grid-template-columns: 1fr; } }
    .tb-jadwal-pick-field { display: flex; flex-direction: column; }
    .tb-jadwal-pick-label { font-size: 0.74rem; font-weight: 600; color: var(--tb-muted); margin-bottom: 0.35rem; }
</style>

<a href="{{ route('admin.opsi.index', ['tipe' => $kembaliTipe]) }}" class="tb-back">
    <x-icon name="arrow-left" /> Kembali ke {{ ucfirst($kembaliTipe) }}
</a>

<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="{{ isset($opsi) ? 'pencil' : 'plus-lg' }}" /></span>
            <div>
                <h2 class="tb-section-title">{{ isset($opsi) ? 'Edit ' . $labelItem : 'Tambah ' . $labelItem }}</h2>
                <p class="tb-section-desc">{{ isset($opsi) ? 'Perbarui ' . strtolower($labelItem) . ' preferensi.' : 'Lengkapi ' . strtolower($labelItem) . ' preferensi baru.' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ isset($opsi) ? route('admin.opsi.update', $opsi) : route('admin.opsi.store') }}">
        @csrf
        @if (isset($opsi)) @method('PUT') @endif

        <div class="tb-field-group">
            {{-- KATEGORI --}}
            <div class="tb-opsi-field">
                <label class="tb-opsi-label">Kategori <span class="req">*</span></label>
                @if ($tipeAktif)
                    {{-- Kategori sudah pasti: tampilkan badge read-only --}}
                    <input type="hidden" name="tipe" value="{{ $tipeAktif }}">
                    <div class="tb-opsi-kategori-badge">
                        <span class="ico"><x-icon name="{{ $tw['icon'] }}" /></span>
                        <div class="txt">
                            <div class="nama">{{ $labelItem }}</div>
                            <div class="ket">Kategori preferensi ini</div>
                        </div>
                    </div>
                @else
                    {{-- Belum pilih kategori: tampilkan select --}}
                    <select name="tipe" id="tipe" class="tb-select @error('tipe') is-invalid @enderror" required>
                        <option value="">— Pilih Kategori —</option>
                        @foreach ($tipeList as $t)
                            <option value="{{ $t }}" @selected(old('tipe') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    @error('tipe') <p class="tb-opsi-err">{{ $message }}</p> @enderror
                @endif
            </div>

            {{-- ITEM --}}
            <div class="tb-opsi-field">
                @if ($tipeAktif === 'jadwal')
                    {{-- Form khusus jadwal: Hari + Slot + jam otomatis --}}
                    <label class="tb-opsi-label">{{ $labelItem }} <span class="req">*</span></label>
                    <input type="hidden" name="nilai" value="{{ old('nilai') ?? ($jadwalHari && $jadwalSlot ? "{$jadwalHari} {$jadwalSlot} (" . ($slotJam[$jadwalSlot] ?? '') . ")" : '') }}">
                    <div class="tb-jadwal-pick">
                        <div class="tb-jadwal-pick-field">
                            <label for="hari" class="tb-jadwal-pick-label">Hari</label>
                            <select name="hari" id="hari" class="tb-select @error('hari') is-invalid @enderror" required>
                                <option value="">— Pilih Hari —</option>
                                @foreach ($hariList as $h)
                                    <option value="{{ $h }}" @selected(old('hari', $jadwalHari) === $h)>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari') <p class="tb-opsi-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="tb-jadwal-pick-field">
                            <label for="slot" class="tb-jadwal-pick-label">Slot Waktu</label>
                            <select name="slot" id="slot" class="tb-select @error('slot') is-invalid @enderror" required>
                                <option value="">— Pilih Slot —</option>
                                @foreach ($slotJam as $slot => $jam)
                                    <option value="{{ $slot }}" @selected(old('slot', $jadwalSlot) === $slot)>{{ $slot }} ({{ $jam }})</option>
                                @endforeach
                            </select>
                            @error('slot') <p class="tb-opsi-err">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="tb-opsi-placeholder-hint">Jam otomatis terisi sesuai slot yang dipilih.</p>
                @else
                    {{-- Form biasa: input teks --}}
                    <label for="nilai" class="tb-opsi-label">{{ $labelItem }} <span class="req">*</span></label>
                    <input type="text" name="nilai" id="nilai" class="tb-input @error('nilai') is-invalid @enderror" value="{{ old('nilai', $opsi->nilai ?? '') }}" placeholder="{{ $tw['placeholder'] ?? '' }}" required maxlength="255" autofocus>
                    <p class="tb-opsi-placeholder-hint">Tulis nama {{ strtolower($labelItem) }} yang ingin ditambahkan.</p>
                    @error('nilai') <p class="tb-opsi-err">{{ $message }}</p> @enderror
                @endif
            </div>
        </div>

        <div class="tb-opsi-actions">
            <a href="{{ route('admin.opsi.index', ['tipe' => $kembaliTipe]) }}" class="tb-btn tb-btn-ghost">Batal</a>
            <button type="submit" class="tb-btn"><x-icon name="save" /> Simpan</button>
        </div>
    </form>
</div>
@endsection
