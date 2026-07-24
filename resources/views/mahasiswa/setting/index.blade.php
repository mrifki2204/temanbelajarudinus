@extends('layouts.app')

@section('title', '· Pengaturan')

@php $user = auth()->user(); @endphp

@section('content')
<style>
    .tb-setting-grid { max-width: none; }
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
</div>
@endsection
