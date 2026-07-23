@extends('layouts.guest')

@section('title', '· Login Admin')

@section('content')

<div class="tb-admin-badge">
    <x-icon name="shield-check" /> Panel Admin
</div>

<div class="tb-admin-logo">
    <img src="{{ asset('img/logo.png') }}" alt="Teman Belajar Udinus">
</div>

<h1 class="tb-auth-title">Login Administrator</h1>
<p class="tb-auth-subtitle">Akses khusus pengelola platform Teman Belajar Udinus</p>

<form method="POST" action="{{ route('admin.login') }}" id="adminLoginForm">
    @csrf

    {{-- Email --}}
    <div class="tb-field">
        <label for="email" class="tb-form-label">Email Admin</label>
        <div class="tb-field-input">
            <x-icon name="envelope" class="tb-field-icon" />
            <input type="email" class="@error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="admin@udinus.ac.id" required autofocus>
        </div>
        @error('email') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    {{-- Password --}}
    <div class="tb-field">
        <label for="password" class="tb-form-label">Kata Sandi</label>
        <div class="tb-field-input">
            <x-icon name="lock" class="tb-field-icon" />
            <input type="password" class="@error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan kata sandi" required>
            <button type="button" class="tb-field-toggle" data-target="password" aria-label="Tampilkan sandi" hidden>
                <x-icon name="eye-slash" class="tb-toggle-show" />
                <x-icon name="eye" class="tb-toggle-hide" />
            </button>
        </div>
        @error('password') <div class="tb-field-error">{{ $message }}</div> @enderror
    </div>

    <div class="tb-auth-check mb-3">
        <input class="h-4 w-4 rounded border-tb-primary-light text-tb-primary focus:ring-tb-primary" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label for="remember">Ingat saya</label>
    </div>

    <button type="submit" class="tb-submit-btn">
        <x-icon name="box-arrow-in-right" /> Masuk sebagai Admin
    </button>
</form>

<div class="tb-auth-divider">atau</div>

<div class="tb-auth-alt">
    Bukan admin?&nbsp;<a href="{{ route('login') }}">Login mahasiswa di sini</a>
</div>

<style>
    /* Badge Panel Admin — di tengah */
    .tb-admin-badge {
        display: flex; align-items: center; justify-content: center;
        gap: 0.4rem; width: fit-content;
        margin: 0 auto 0.9rem;
        background: var(--tb-primary); color: #fff;
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;
        padding: 0.4rem 0.95rem; border-radius: 999px;
        text-transform: uppercase;
    }
    .tb-admin-badge svg { width: 0.85rem; height: 0.85rem; color: var(--tb-accent); }

    /* Logo di tengah */
    .tb-admin-logo { display: flex; justify-content: center; margin-bottom: 0.7rem; }
    .tb-admin-logo img { width: 56px; height: 56px; display: block; }
</style>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.tb-field-toggle').forEach(btn => {
    const target = document.getElementById(btn.dataset.target);
    if (!target) return;

    // Tampilkan tombol toggle hanya saat input punya isi
    const syncVisibility = () => { btn.hidden = !target.value; };
    target.addEventListener('input', syncVisibility);
    syncVisibility();

    btn.addEventListener('click', function() {
        const showEye = this.querySelector('.tb-toggle-show');
        const hideEye = this.querySelector('.tb-toggle-hide');
        if (target.type === 'password') {
            target.type = 'text';
            showEye.style.display = 'none';
            hideEye.style.display = 'inline-flex';
        } else {
            target.type = 'password';
            showEye.style.display = 'inline-flex';
            hideEye.style.display = 'none';
        }
    });
});
</script>
@endpush
