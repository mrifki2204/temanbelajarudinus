@extends('layouts.app')

@section('title', '· Profil')

@php
    $user = auth()->user();
    $user->load('profile');
    $profile = $user->profile;
    $profilLengkap = $profile && $profile->minat && $profile->tujuan && $profile->gaya && $profile->jadwal && $profile->mode && $profile->whatsapp && $profile->instagram;
    $inisial = strtoupper(mb_substr(explode(' ', trim($user->nama))[0], 0, 1));

    $minatTerpilih = old('minat', $profile?->minat ?? []);
    if (! is_array($minatTerpilih)) { $minatTerpilih = []; }

    $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
    $waktuList = ['Pagi (06-11)','Siang (11-14)','Sore (14-18)','Malam (18-23)'];
    $selectedJadwal = old('jadwal', $profile?->jadwal ?? []);
    if (! is_array($selectedJadwal)) { $selectedJadwal = []; }
@endphp

@section('content')
<style>
    .tb-profil-head-card { display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap; }
    .tb-profil-avatar {
        width: 56px; height: 56px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--tb-primary), var(--tb-primary-dark));
        color: white; display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; font-weight: 700;
    }
    .tb-profil-name { font-size: 1.1rem; font-weight: 700; color: var(--tb-ink); margin: 0; word-break: break-word; }
    .tb-profil-email { font-size: 0.8rem; color: var(--tb-muted); margin: 0; word-break: break-all; }

    .tb-akad-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; padding: 0.6rem 0; border-bottom: 1px solid var(--tb-primary-light); flex-wrap: wrap; }
    .tb-akad-row:last-child { border-bottom: none; }
    .tb-akad-label-inline { font-size: 0.8rem; color: var(--tb-muted); flex: 0 0 auto; }
    .tb-akad-value-inline { font-size: 0.85rem; font-weight: 600; color: var(--tb-ink); text-align: right; min-width: 0; word-break: break-word; }
    @media (max-width: 575.98px) {
        .tb-akad-row { flex-direction: column; gap: 0.2rem; }
        .tb-akad-value-inline { text-align: left; }
        .tb-profil-head-card .tb-badge { margin-left: 0; }
    }

    .tb-jadwal-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; max-width: 100%; }
    .tb-jadwal-table { width: 100%; min-width: 520px; border-collapse: collapse; }
    .tb-jadwal-table th { background: var(--tb-primary-soft); color: var(--tb-primary); font-size: 0.72rem; font-weight: 700; padding: 0.6rem 0.3rem; text-align: center; text-transform: uppercase; letter-spacing: 0.03em; border-bottom: 1px solid var(--tb-primary-light); }
    .tb-jadwal-table th:first-child { text-align: left; padding-left: 0.7rem; }
    .tb-jadwal-table td { padding: 0.45rem; text-align: center; border-bottom: 1px solid var(--tb-primary-light); }
    @media (max-width: 575.98px) {
        .tb-jadwal-table { min-width: 460px; font-size: 0.78rem; }
    }
    .tb-jadwal-table tr:last-child td { border-bottom: none; }
    .tb-jadwal-table td:first-child { font-weight: 600; color: var(--tb-ink); text-align: left; padding-left: 0.7rem; white-space: nowrap; }
    .tb-jadwal-table input { accent-color: var(--tb-accent); width: 1.15rem; height: 1.15rem; cursor: pointer; margin: 0; }
</style>

<div class="tb-page-head">
    <div class="tb-page-head-text">
        <h1>Profil</h1>
        <p>Kelola preferensi belajar & informasi akun Anda</p>
    </div>
</div>

<form method="POST" action="{{ route('profil.update') }}">
    @csrf
    @method('PUT')

    {{-- Header profil --}}
    <div class="tb-card tb-profil-head-card" style="margin-bottom:1rem;">
        <span class="tb-profil-avatar">{{ $inisial }}</span>
        <div style="flex:1;min-width:0;">
            <h2 class="tb-profil-name">{{ $user->nama }}</h2>
            <p class="tb-profil-email">{{ $user->email }}</p>
        </div>
        @if ($profilLengkap)
            <span class="tb-badge tb-badge-success"><x-icon name="check-circle" /> Lengkap</span>
        @else
            <span class="tb-badge tb-badge-warn"><x-icon name="exclamation-circle" /> Belum Lengkap</span>
        @endif
    </div>

    {{-- Info Akademik (read-only) --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="mortarboard" /></span>
                <h2 class="tb-section-title">Informasi Akademik</h2>
            </div>
        </div>
        <div class="tb-akad-row"><span class="tb-akad-label-inline">Fakultas</span><span class="tb-akad-value-inline">{{ $user->fakultas?->nama ?? '-' }}</span></div>
        <div class="tb-akad-row"><span class="tb-akad-label-inline">Jenis Kelamin</span><span class="tb-akad-value-inline">{{ $user->jenis_kelamin === 'L' ? 'Laki-laki' : ($user->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</span></div>
        <div class="tb-akad-row"><span class="tb-akad-label-inline">Program Studi</span><span class="tb-akad-value-inline">{{ $user->prodi?->nama ?? '-' }}</span></div>
        <div class="tb-akad-row"><span class="tb-akad-label-inline">Semester</span><span class="tb-akad-value-inline">{{ $user->semester ?? '-' }}</span></div>
        <div class="tb-akad-row"><span class="tb-akad-label-inline">Tahun Angkatan</span><span class="tb-akad-value-inline">{{ $user->angkatan ?? '-' }}</span></div>
        <p class="tb-text-sm tb-muted" style="margin:0.85rem 0 0;"><x-icon name="info-circle" /> Informasi akademik tidak dapat diubah. Hubungi admin jika ada perubahan.</p>
    </div>

    {{-- Minat --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="lightbulb" /></span>
                <div>
                    <h2 class="tb-section-title">Minat Bidang Belajar</h2>
                    <p class="tb-section-desc">Pilih satu atau beberapa minat</p>
                </div>
            </div>
        </div>
        <div class="tb-opt-grid">
            @foreach ($opsi['minat'] as $m)
                <label class="tb-opt" for="minat_{{ Str::slug($m) }}">
                    <input type="checkbox" name="minat[]" value="{{ $m }}" id="minat_{{ Str::slug($m) }}" @checked(in_array($m, $minatTerpilih))>
                    <span>{{ $m }}</span>
                </label>
            @endforeach
        </div>
        @error('minat') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    {{-- Tujuan --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="bullseye" /></span>
                <div>
                    <h2 class="tb-section-title">Tujuan Belajar</h2>
                    <p class="tb-section-desc">Tujuan utama belajar bersama</p>
                </div>
            </div>
        </div>
        <label for="tujuan" class="tb-label">Tujuan <span class="req">*</span></label>
        <select name="tujuan" id="tujuan" class="tb-select @error('tujuan') is-invalid @enderror" required>
            <option value="">— Pilih tujuan —</option>
            @foreach ($opsi['tujuan'] as $t)
                <option value="{{ $t }}" @selected(old('tujuan', $profile?->tujuan) === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('tujuan') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    {{-- Gaya --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="people" /></span>
                <div>
                    <h2 class="tb-section-title">Gaya Belajar</h2>
                    <p class="tb-section-desc">Bagaimana Anda paling nyaman belajar</p>
                </div>
            </div>
        </div>
        <label for="gaya" class="tb-label">Gaya <span class="req">*</span></label>
        <select name="gaya" id="gaya" class="tb-select @error('gaya') is-invalid @enderror" required>
            <option value="">— Pilih gaya —</option>
            @foreach ($opsi['gaya'] as $g)
                <option value="{{ $g }}" @selected(old('gaya', $profile?->gaya) === $g)>{{ $g }}</option>
            @endforeach
        </select>
        @error('gaya') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    {{-- Jadwal --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="calendar3" /></span>
                <div>
                    <h2 class="tb-section-title">Jadwal Luang</h2>
                    <p class="tb-section-desc">Centang waktu kosong Anda</p>
                </div>
            </div>
        </div>
        <div class="tb-jadwal-wrap">
            <table class="tb-jadwal-table" style="width:100%;border-collapse:separate;border-spacing:0;">
                <thead>
                    <tr><th>Hari</th>@foreach ($waktuList as $w)<th>{{ $w }}</th>@endforeach</tr>
                </thead>
                <tbody>
                    @foreach ($hariList as $h)
                    <tr>
                        <td>{{ $h }}</td>
                        @foreach ($waktuList as $w)
                            @php $val = "$h $w"; @endphp
                            <td><input type="checkbox" name="jadwal[]" value="{{ $val }}" id="jadwal_{{ Str::slug($val) }}" @checked(in_array($val, $selectedJadwal))></td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @error('jadwal') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    {{-- Mode --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="laptop" /></span>
                <div>
                    <h2 class="tb-section-title">Mode Belajar</h2>
                    <p class="tb-section-desc">Preferensi pertemuan</p>
                </div>
            </div>
        </div>
        <label for="mode" class="tb-label">Mode <span class="req">*</span></label>
        <select name="mode" id="mode" class="tb-select @error('mode') is-invalid @enderror" required>
            <option value="">— Pilih mode —</option>
            @foreach ($opsi['mode'] as $md)
                <option value="{{ $md }}" @selected(old('mode', $profile?->mode) === $md)>{{ $md }}</option>
            @endforeach
        </select>
        @error('mode') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    {{-- Kontak --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="chat-dots" /></span>
                <div>
                    <h2 class="tb-section-title">Kontak</h2>
                    <p class="tb-section-desc">Hanya terlihat oleh mahasiswa yang permintaan belajarnya Anda terima</p>
                </div>
            </div>
        </div>
        <div class="tb-row-2">
            <div>
                <label for="whatsapp" class="tb-label">WhatsApp <span class="req">*</span></label>
                <div class="tb-input-prefix">
                    <span class="tb-prefix-label"><x-icon name="whatsapp" style="color:#1d8a4e;" /></span>
                    <input type="text" name="whatsapp" id="whatsapp" class="tb-input @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp', $profile?->whatsapp) }}" placeholder="081234567890" required>
                </div>
                @error('whatsapp') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="instagram" class="tb-label">Instagram <span class="req">*</span></label>
                <div class="tb-input-prefix">
                    <span class="tb-prefix-label">@</span>
                    <input type="text" name="instagram" id="instagram" class="tb-input @error('instagram') is-invalid @enderror" value="{{ old('instagram', $profile?->instagram) }}" placeholder="username" required>
                </div>
                @error('instagram') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div style="text-align:center; margin-top:1.25rem;">
        <button type="submit" class="tb-btn" style="max-width:320px;"><x-icon name="check-lg" /> Perbarui Profil</button>
    </div>
</form>
@endsection
