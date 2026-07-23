@extends('layouts.guest')

@section('title', '· Masuk')

@section('content')
<div class="tb-auth-logo-row">
    <img src="{{ asset('img/logo.png') }}" alt="Teman Belajar Udinus">
</div>

<h1 class="tb-auth-title">Selamat Datang Kembali</h1>
<p class="tb-auth-subtitle">Masuk untuk menemukan teman belajar Anda</p>

<form method="POST" action="{{ route('login') }}" id="loginForm">
    @csrf

    {{-- Email --}}
    <div class="tb-field">
        <label for="email" class="tb-form-label">Email UDINUS</label>
        <div class="tb-field-input">
            <x-icon name="envelope" class="tb-field-icon" />
            <input type="email" class="@error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nim@mhs.dinus.ac.id" required autofocus>
        </div>
        @error('email') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    {{-- Password --}}
    <div class="tb-field">
        <div class="flex justify-between items-baseline">
            <label for="password" class="tb-form-label">Kata Sandi</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="tb-auth-link">Lupa kata sandi?</a>
            @endif
        </div>
        <div class="tb-field-input">
            <x-icon name="lock" class="tb-field-icon" />
            <input type="password" class="@error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan kata sandi" required>
            <button type="button" class="tb-field-toggle" data-target="password" aria-label="Tampilkan kata sandi" hidden>
                <x-icon name="eye" class="tb-toggle-show" style="display:none;" />
                <x-icon name="eye-slash" class="tb-toggle-hide" />
            </button>
        </div>
        @error('password') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    <div class="tb-auth-check mb-3">
        <input class="h-4 w-4 rounded border-tb-primary-light text-tb-primary focus:ring-tb-primary" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label for="remember">Ingat saya</label>
    </div>

    <button type="submit" class="tb-submit-btn">
        <x-icon name="box-arrow-in-right" class="me-1" /> Masuk
    </button>
</form>

<div class="tb-auth-divider">atau</div>

<div class="tb-auth-alt">
    Belum punya akun?&nbsp;<a href="{{ route('register') }}">Daftar sekarang</a>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.tb-field-toggle').forEach(btn => {
    const target = document.getElementById(btn.dataset.target);
    if (!target) return;

    // Tampilkan tombol toggle hanya saat input punya isi
    const syncVisibility = () => {
        btn.hidden = !target.value;
    };
    target.addEventListener('input', syncVisibility);
    syncVisibility();

    // Klik toggle: ubah type input + ganti ikon
    btn.addEventListener('click', function() {
        const showEye = this.querySelector('.tb-toggle-show');
        const hideEye = this.querySelector('.tb-toggle-hide');
        if (target.type === 'password') {
            target.type = 'text';
            if (showEye) showEye.style.display = 'inline-flex';
            if (hideEye) hideEye.style.display = 'none';
        } else {
            target.type = 'password';
            if (showEye) showEye.style.display = 'none';
            if (hideEye) hideEye.style.display = 'inline-flex';
        }
    });
});
</script>
@endpush
