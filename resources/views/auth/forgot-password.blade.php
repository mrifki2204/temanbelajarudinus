@extends('layouts.guest')

@section('content')
    <div class="text-center mb-4">
        <x-icon name="key" class="text-tb-primary" style="font-size:2.5rem;" />
        <h4 class="mt-2 mb-1 fw-bold">Lupa Kata Sandi?</h4>
        <p class="text-muted mb-0 small">Masukkan email Anda. Kami akan kirim tautan reset kata sandi.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="tb-label">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><x-icon name="envelope" /></span>
                <input type="email" class="tb-input @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <div class="d-grid">
            <button type="submit" class="inline-flex items-center justify-center w-full rounded-lg bg-tb-primary px-4 py-2.5 text-white font-semibold hover:bg-tb-primary-dark transition"><x-icon name="send" class="me-1" /> Kirim Tautan Reset</button>
        </div>
    </form>
@endsection
