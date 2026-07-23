@extends('layouts.guest')

@section('content')
    <div class="text-center mb-4">
        <x-icon name="mailbox" class="text-tb-primary" style="font-size:2.5rem;" />
        <h4 class="mt-2 mb-1 fw-bold">Verifikasi Email</h4>
    </div>

    <div class="text-center">
        <p class="text-muted">
            Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke alamat email Anda.
        </p>
        <p class="text-muted">
            Jika Anda tidak menerima email tersebut, kami dengan senang hati mengirimkan email baru kepada Anda.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800"><x-icon name="check-circle" class="mt-0.5 me-1" /> Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.</div>
        @endif

        <div class="grid gap-2 mt-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center w-full rounded-lg bg-tb-primary px-4 py-2.5 text-white font-semibold hover:bg-tb-primary-dark transition"><x-icon name="send" class="me-1" /> Kirim Ulang Tautan Verifikasi</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link text-decoration-none">Keluar</button>
            </form>
        </div>
    </div>
@endsection
