@extends('layouts.app')

@section('title', '· Pengaturan')

@php $user = auth()->user(); @endphp

@section('content')
<style>
    .tb-setting-grid { max-width: none; }
    .tb-danger-card { border-color: #f5c6c0 !important; }
</style>

<div class="tb-setting-grid">
    <div class="tb-page-head">
        <div class="tb-page-head-text">
            <h1>Pengaturan</h1>
            <p>Keamanan akun & informasi pengguna</p>
        </div>
    </div>

    {{-- Info Akun --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="person" /></span>
                <h2 class="tb-section-title">Informasi Akun</h2>
            </div>
        </div>
        <div class="tb-field-group">
            <label class="tb-label">Nama Lengkap</label>
            <input type="text" class="tb-input" value="{{ $user->nama }}" readonly>
        </div>
        <div class="tb-field-group">
            <label class="tb-label">Email</label>
            <input type="text" class="tb-input" value="{{ $user->email }}" readonly>
        </div>
        <p class="tb-text-sm tb-muted" style="margin:0;"><x-icon name="info-circle" /> Nama & email tidak dapat diubah. Hubungi admin jika ada perubahan.</p>
    </div>

    {{-- Keamanan --}}
    <div class="tb-card">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon"><x-icon name="shield-lock" /></span>
                <h2 class="tb-section-title">Ubah Kata Sandi</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('profil.password.update') }}">
            @csrf
            @method('PUT')
            <div class="tb-field-group">
                <label for="current_password" class="tb-label">Kata Sandi Saat Ini</label>
                <input type="password" name="current_password" id="current_password" class="tb-input" required>
            </div>
            <div class="tb-field-group">
                <label for="password" class="tb-label">Kata Sandi Baru</label>
                <input type="password" name="password" id="password" class="tb-input" required>
            </div>
            <div class="tb-field-group">
                <label for="password_confirmation" class="tb-label">Konfirmasi Sandi Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="tb-input" required>
            </div>
            <button type="submit" class="tb-btn tb-btn-block"><x-icon name="shield-check" /> Perbarui Kata Sandi</button>
        </form>
    </div>

    {{-- Hapus Akun --}}
    <div class="tb-card tb-danger-card" x-data="{ open: false }">
        <div class="tb-section-head">
            <div class="tb-section-head-left">
                <span class="tb-section-icon" style="background:#fdeaea;color:#c0392b;"><x-icon name="exclamation-triangle" /></span>
                <div>
                    <h2 class="tb-section-title" style="color:#c0392b;">Hapus Akun</h2>
                    <p class="tb-section-desc">Tindakan ini permanen. Seluruh data Anda akan dihapus.</p>
                </div>
            </div>
        </div>
        <button type="button" class="tb-btn tb-btn-danger tb-btn-block" @click="open = true">
            <x-icon name="trash" /> Hapus Akun
        </button>

        {{-- Modal konfirmasi --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-[1055] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Hapus Akun">
            <div x-show="open" @click="open = false" class="absolute inset-0 bg-black/50" x-transition.opacity></div>
            <div x-show="open" x-transition class="relative w-full max-w-lg rounded-xl bg-white shadow-xl">
                <form method="POST" action="{{ route('profil.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-between border-b border-tb-primary-light px-5 py-3">
                        <h6 class="text-sm font-semibold text-tb-ink"><x-icon name="exclamation-triangle" class="text-red-600 mr-2" />Hapus Akun</h6>
                        <button type="button" @click="open = false" class="text-tb-muted hover:text-tb-ink" aria-label="Tutup">
                            <x-icon name="x-lg" />
                        </button>
                    </div>
                    <div class="px-5 py-4">
                        <p class="tb-text-sm tb-muted">Yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.</p>
                        <input type="password" name="password" class="tb-input" placeholder="Masukkan kata sandi" required>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-tb-primary-light px-5 py-3">
                        <button type="button" class="tb-btn tb-btn-ghost tb-btn-sm" @click="open = false">Batal</button>
                        <button type="submit" class="tb-btn tb-btn-danger tb-btn-sm"><x-icon name="trash" /> Hapus Permanen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
