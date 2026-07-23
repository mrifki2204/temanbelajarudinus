@extends('layouts.guest')

@section('content')
    <div class="text-center mb-4">
        <x-icon name="shield-lock" class="text-tb-primary" style="font-size:2.5rem;" />
        <h4 class="mt-2 mb-1 fw-bold">Konfirmasi Kata Sandi</h4>
        <p class="text-muted small">Ini adalah area aman. Mohon konfirmasi kata sandi Anda sebelum melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <label for="password" class="tb-label">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><x-icon name="lock" /></span>
                <input type="password" class="tb-input @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
            </div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="d-grid">
            <button type="submit" class="inline-flex items-center justify-center w-full rounded-lg bg-tb-primary px-4 py-2.5 text-white font-semibold hover:bg-tb-primary-dark transition"><x-icon name="unlock" class="me-1" /> Konfirmasi</button>
        </div>
    </form>
@endsection
