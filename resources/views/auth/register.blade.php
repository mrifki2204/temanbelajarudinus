@extends('layouts.guest')

@section('title', '· Daftar Akun')

@section('content')
<div class="tb-auth-logo-row">
    <img src="{{ asset('img/logo.png') }}" alt="Teman Belajar Udinus">
</div>

<h1 class="tb-auth-title">Buat Akun Baru</h1>
<p class="tb-auth-subtitle">Daftar dengan email mahasiswa UDINUS &amp; temukan teman belajar yang cocok</p>

<form method="POST" action="{{ route('register') }}" id="registerForm">
    @csrf

    {{-- Section: Data Diri --}}
    <div class="tb-reg-section">
        <span class="tb-reg-section-label"><x-icon name="person" /> Data Diri</span>

        <div class="tb-field">
            <label for="nama" class="tb-form-label">Nama Lengkap</label>
            <div class="tb-field-input">
                <x-icon name="person" class="tb-field-icon" />
                <input type="text" class="@error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Budi Santoso" required autofocus>
            </div>
            @error('nama') <div class="tb-field-error">{{ $message }}</div> @enderror
        </div>

        <div class="tb-field-row">
            <div class="tb-field">
                <label class="tb-form-label">Jenis Kelamin</label>
                <div class="tb-radio-group @error('jenis_kelamin') is-invalid @enderror">
                    <label class="tb-radio-pill">
                        <input type="radio" name="jenis_kelamin" value="L" @checked(old('jenis_kelamin') === 'L') required>
                        <span><x-icon name="gender-male" /> Laki-laki</span>
                    </label>
                    <label class="tb-radio-pill">
                        <input type="radio" name="jenis_kelamin" value="P" @checked(old('jenis_kelamin') === 'P')>
                        <span><x-icon name="gender-female" /> Perempuan</span>
                    </label>
                </div>
                @error('jenis_kelamin') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
            <div class="tb-field">
                <label for="nim" class="tb-form-label">NIM</label>
                <div class="tb-field-input">
                    <x-icon name="mortarboard" class="tb-field-icon" />
                    <input type="text" class="@error('nim') is-invalid @enderror" id="nim" name="nim" value="{{ old('nim') }}" placeholder="A11.2021.13840" pattern="[\w]+\.\d{4}\.\d+" required>
                </div>
                <div class="tb-field-hint"><x-icon name="info-circle" /> <span>Format <code>xxx.xxxx.xxx</code> — contoh <code>A11.2021.13840</code></span></div>
                @error('nim') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Section: Akademik --}}
    <div class="tb-reg-section">
        <span class="tb-reg-section-label"><x-icon name="mortarboard" /> Akademik</span>

        <div class="tb-field">
            <label for="email" class="tb-form-label">Email Mahasiswa</label>
            <div class="tb-field-input">
                <x-icon name="envelope" class="tb-field-icon" />
                <input type="email" class="@error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="111202113840@mhs.dinus.ac.id" required>
            </div>
            <div class="tb-field-hint"><x-icon name="info-circle" /> <span>Wajib domain <code>@mhs.dinus.ac.id</code> — contoh <code>111202113840@mhs.dinus.ac.id</code></span></div>
            @error('email') <div class="tb-field-error">{{ $message }}</div> @enderror
        </div>

        <div class="tb-field-row">
            <div class="tb-field">
                <label for="fakultas_id" class="tb-form-label">Fakultas</label>
                <select name="fakultas_id" id="fakultas_id" class="@error('fakultas_id') is-invalid @enderror" required>
                    <option value="">— Pilih Fakultas —</option>
                    @foreach ($fakultasList as $f)
                        <option value="{{ $f->id }}" data-kode="{{ $f->kode }}" @selected(old('fakultas_id') == $f->id)>{{ $f->nama }} ({{ $f->kode }})</option>
                    @endforeach
                </select>
                @error('fakultas_id') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
            <div class="tb-field">
                <label for="prodi_id" class="tb-form-label">Program Studi</label>
                <select name="prodi_id" id="prodi_id" class="@error('prodi_id') is-invalid @enderror" required disabled>
                    <option value="">— Pilih Fakultas dulu —</option>
                    @foreach ($prodiList as $p)
                        <option value="{{ $p->id }}" data-fakultas-id="{{ $p->fakultas_id }}" @selected(old('prodi_id') == $p->id)>{{ $p->nama }} ({{ $p->jenjang }})</option>
                    @endforeach
                </select>
                @error('prodi_id') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="tb-field-row">
            <div class="tb-field">
                <label for="semester" class="tb-form-label">Semester</label>
                <div class="tb-field-input">
                    <x-icon name="calendar-week" class="tb-field-icon" />
                    <input type="number" class="@error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester') }}" min="1" max="14" placeholder="1-14" required>
                </div>
                @error('semester') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
            <div class="tb-field">
                <label for="angkatan" class="tb-form-label">Tahun Angkatan</label>
                <div class="tb-field-input">
                    <x-icon name="calendar3" class="tb-field-icon" />
                    <input type="number" class="@error('angkatan') is-invalid @enderror" id="angkatan" name="angkatan" value="{{ old('angkatan') }}" min="2000" max="{{ date('Y') }}" placeholder="2021" required>
                </div>
                @error('angkatan') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Section: Keamanan --}}
    <div class="tb-reg-section">
        <span class="tb-reg-section-label"><x-icon name="lock" /> Keamanan</span>

        <div class="tb-field-row">
            <div class="tb-field">
                <label for="password" class="tb-form-label">Kata Sandi</label>
                <div class="tb-field-input">
                    <x-icon name="lock" class="tb-field-icon" />
                    <input type="password" class="@error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                    <button type="button" class="tb-field-toggle" data-target="password" aria-label="Tampilkan sandi" hidden>
                        <x-icon name="eye-slash" class="tb-toggle-show" />
                        <x-icon name="eye" class="tb-toggle-hide" />
                    </button>
                </div>
                @error('password') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
            <div class="tb-field">
                <label for="password_confirmation" class="tb-form-label">Konfirmasi Sandi</label>
                <div class="tb-field-input">
                    <x-icon name="lock-fill" class="tb-field-icon" />
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                    <button type="button" class="tb-field-toggle" data-target="password_confirmation" aria-label="Tampilkan sandi" hidden>
                        <x-icon name="eye-slash" class="tb-toggle-show" />
                        <x-icon name="eye" class="tb-toggle-hide" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="tb-submit-btn">
        <x-icon name="person-plus" /> Daftar Sekarang
    </button>
</form>

<div class="tb-auth-divider">atau</div>

<div class="tb-auth-alt">
    Sudah punya akun?&nbsp;<a href="{{ route('login') }}">Masuk di sini</a>
</div>

<style>
    /* Perlebar card khusus register (banyak field berdampingan) */
    .tb-auth-card { max-width: 660px !important; }
    @media (max-width: 575.98px) { .tb-auth-card { padding: 1.5rem 1.25rem; } }

    /* Section divider */
    .tb-reg-section { margin-bottom: 1.1rem; }
    .tb-reg-section-label {
        display: inline-flex; align-items: center; gap: 0.4rem;
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;
        text-transform: uppercase; color: var(--tb-accent-dark);
        background: var(--tb-accent-light);
        padding: 0.3rem 0.7rem; border-radius: 999px; margin-bottom: 0.8rem;
    }
    .tb-reg-section-label svg { width: 0.85rem; height: 0.85rem; }
</style>
@endsection

@push('scripts')
<script>
(function() {
    const fakultasSelect = document.getElementById('fakultas_id');
    const prodiSelect = document.getElementById('prodi_id');
    if (!fakultasSelect || !prodiSelect) return;
    const allProdi = Array.from(prodiSelect.querySelectorAll('option[data-fakultas-id]'));
    function filterProdi() {
        if (!fakultasSelect.value) {
            prodiSelect.disabled = true;
            prodiSelect.innerHTML = '<option value="">— Pilih Fakultas dulu —</option>';
            return;
        }
        prodiSelect.disabled = false;
        prodiSelect.innerHTML = '<option value="">— Pilih Prodi —</option>';
        allProdi.forEach(opt => {
            if (opt.dataset.fakultasId === fakultasSelect.value) {
                prodiSelect.appendChild(opt.cloneNode(true));
            }
        });
    }
    fakultasSelect.addEventListener('change', filterProdi);
    filterProdi();
})();

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
