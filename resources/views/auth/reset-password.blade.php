@extends('layouts.guest')

@section('content')
    <div class="text-center mb-4">
        <x-icon name="shield-lock" class="text-tb-primary" style="font-size:2.5rem;" />
        <h4 class="mt-2 mb-1 fw-bold">Reset Kata Sandi</h4>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="tb-label">Email</label>
            <input type="email" class="tb-input @error('email') is-invalid @enderror" id="email" name="email" value="{{ $request->email ?? old('email') }}" required autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="tb-label">Kata Sandi Baru</label>
            <input type="password" class="tb-input @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="new-password">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="tb-label">Konfirmasi Kata Sandi</label>
            <input type="password" class="tb-input" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
        </div>

        <div class="d-grid">
            <button type="submit" class="inline-flex items-center justify-center w-full rounded-lg bg-tb-primary px-4 py-2.5 text-white font-semibold hover:bg-tb-primary-dark transition"><x-icon name="check2-circle" class="me-1" /> Reset Kata Sandi</button>
        </div>
    </form>
@endsection
