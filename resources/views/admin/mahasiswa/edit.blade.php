@extends('layouts.app')

@section('title', '· Admin · Edit Mahasiswa')

@section('content')
<style>
    .tb-mhs-field { margin-bottom: 1rem; }
    .tb-mhs-label { display: block; font-weight: 600; font-size: 0.8rem; color: var(--tb-ink); margin-bottom: 0.35rem; }
    .tb-mhs-label .req { color: #dc3545; margin-left: 0.15rem; }
    .tb-mhs-hint { font-size: 0.74rem; color: var(--tb-muted); margin-top: 0.35rem; line-height: 1.5; display: flex; align-items: center; gap: 0.3rem; }
    .tb-mhs-hint svg { width: 0.85rem; height: 0.85rem; color: var(--tb-accent-dark); flex-shrink: 0; }
    .tb-mhs-err { font-size: 0.74rem; color: #dc3545; margin-top: 0.3rem; font-weight: 500; }
    .tb-mhs-actions { display: flex; justify-content: flex-end; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--tb-primary-light); }
    .tb-input.is-invalid, .tb-select.is-invalid { border-color: #dc3545; }
    .tb-mhs-divider { font-size: 0.72rem; font-weight: 700; color: var(--tb-primary); text-transform: uppercase; letter-spacing: 0.05em; margin: 1.25rem 0 0.75rem; padding-bottom: 0.4rem; border-bottom: 1px solid var(--tb-primary-light); }
    .tb-mhs-divider:first-of-type { margin-top: 0.5rem; }
    .tb-mhs-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    @media (max-width: 575.98px) { .tb-mhs-row { grid-template-columns: 1fr; } }
    /* Radio jenis kelamin */
    .tb-mhs-gender { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .tb-mhs-gender-opt { position: relative; }
    .tb-mhs-gender-opt input { position: absolute; opacity: 0; pointer-events: none; }
    .tb-mhs-gender-opt label {
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        padding: 0.65rem; border: 1px solid var(--tb-primary-light); border-radius: 0.5rem;
        cursor: pointer; font-size: 0.84rem; font-weight: 600; color: var(--tb-muted);
        background: white; transition: all 0.15s ease;
    }
    .tb-mhs-gender-opt label:hover { border-color: var(--tb-primary); color: var(--tb-primary); }
    .tb-mhs-gender-opt input:checked + label { border-color: var(--tb-primary); background: var(--tb-primary-light); color: var(--tb-primary); }
    .tb-mhs-gender-opt label svg { width: 1.05rem; height: 1.05rem; }
</style>

<a href="{{ route('admin.mahasiswa.index') }}" class="tb-back">
    <x-icon name="arrow-left" /> Kembali ke Daftar Mahasiswa
</a>

<div class="tb-card">
    <div class="tb-section-head">
        <div class="tb-section-head-left">
            <span class="tb-section-icon"><x-icon name="pencil" /></span>
            <div>
                <h2 class="tb-section-title">Edit Data Mahasiswa</h2>
                <p class="tb-section-desc">Ubah data akun dan akademik mahasiswa.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.mahasiswa.update', $mahasiswa) }}">
        @csrf
        @method('PUT')

        {{-- DATA DIRI --}}
        <div class="tb-mhs-divider">Data Diri</div>
        <div class="tb-field-group">
            <div class="tb-mhs-field">
                <label for="nama" class="tb-mhs-label">Nama Lengkap <span class="req">*</span></label>
                <input type="text" name="nama" id="nama" class="tb-input @error('nama') is-invalid @enderror" value="{{ old('nama', $mahasiswa->nama) }}" required maxlength="255" autofocus>
                @error('nama') <p class="tb-mhs-err">{{ $message }}</p> @enderror
            </div>

            <div class="tb-mhs-field">
                <label class="tb-mhs-label">Jenis Kelamin <span class="req">*</span></label>
                <div class="tb-mhs-gender">
                    <div class="tb-mhs-gender-opt">
                        <input type="radio" name="jenis_kelamin" id="jk-l" value="L" @checked(old('jenis_kelamin', $mahasiswa->jenis_kelamin) === 'L')>
                        <label for="jk-l"><x-icon name="gender-male" /> Laki-laki</label>
                    </div>
                    <div class="tb-mhs-gender-opt">
                        <input type="radio" name="jenis_kelamin" id="jk-p" value="P" @checked(old('jenis_kelamin', $mahasiswa->jenis_kelamin) === 'P')>
                        <label for="jk-p"><x-icon name="gender-female" /> Perempuan</label>
                    </div>
                </div>
                @error('jenis_kelamin') <p class="tb-mhs-err">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- AKADEMIK --}}
        <div class="tb-mhs-divider">Akademik</div>
        <div class="tb-field-group">
            <div class="tb-mhs-field">
                <label for="nim" class="tb-mhs-label">NIM <span class="req">*</span></label>
                <input type="text" name="nim" id="nim" class="tb-input @error('nim') is-invalid @enderror" value="{{ old('nim', $mahasiswa->nim) }}" required maxlength="50" style="text-transform:uppercase;">
                <p class="tb-mhs-hint"><x-icon name="info-circle" /> Format xxx.xxxx.xxx (contoh: A11.2021.13840).</p>
                @error('nim') <p class="tb-mhs-err">{{ $message }}</p> @enderror
            </div>

            <div class="tb-mhs-row">
                <div class="tb-mhs-field">
                    <label for="fakultas_id" class="tb-mhs-label">Fakultas <span class="req">*</span></label>
                    <select name="fakultas_id" id="fakultas_id" class="tb-select @error('fakultas_id') is-invalid @enderror" required>
                        <option value="">— Pilih Fakultas —</option>
                        @foreach ($fakultasList as $f)
                            <option value="{{ $f->id }}" @selected(old('fakultas_id', $mahasiswa->fakultas_id) == $f->id)>{{ $f->nama }} ({{ $f->kode }})</option>
                        @endforeach
                    </select>
                    @error('fakultas_id') <p class="tb-mhs-err">{{ $message }}</p> @enderror
                </div>

                <div class="tb-mhs-field">
                    <label for="prodi_id" class="tb-mhs-label">Program Studi <span class="req">*</span></label>
                    <select name="prodi_id" id="prodi_id" class="tb-select @error('prodi_id') is-invalid @enderror" required>
                        <option value="">— Pilih Prodi —</option>
                        @foreach ($prodiList as $p)
                            <option value="{{ $p->id }}" data-fakultas-id="{{ $p->fakultas_id }}" @selected(old('prodi_id', $mahasiswa->prodi_id) == $p->id)>{{ $p->nama }} ({{ $p->jenjang }})</option>
                        @endforeach
                    </select>
                    @error('prodi_id') <p class="tb-mhs-err">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="tb-mhs-row">
                <div class="tb-mhs-field">
                    <label for="semester" class="tb-mhs-label">Semester <span class="req">*</span></label>
                    <input type="number" name="semester" id="semester" class="tb-input @error('semester') is-invalid @enderror" value="{{ old('semester', $mahasiswa->semester) }}" required min="1" max="14">
                    @error('semester') <p class="tb-mhs-err">{{ $message }}</p> @enderror
                </div>

                <div class="tb-mhs-field">
                    <label for="angkatan" class="tb-mhs-label">Angkatan <span class="req">*</span></label>
                    <input type="number" name="angkatan" id="angkatan" class="tb-input @error('angkatan') is-invalid @enderror" value="{{ old('angkatan', $mahasiswa->angkatan) }}" required min="2000" max="{{ date('Y') }}">
                    @error('angkatan') <p class="tb-mhs-err">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- KONTAK / LOGIN --}}
        <div class="tb-mhs-divider">Kontak &amp; Login</div>
        <div class="tb-field-group">
            <div class="tb-mhs-field">
                <label for="email" class="tb-mhs-label">Email <span class="req">*</span></label>
                <input type="email" name="email" id="email" class="tb-input @error('email') is-invalid @enderror" value="{{ old('email', $mahasiswa->email) }}" required maxlength="255">
                <p class="tb-mhs-hint"><x-icon name="info-circle" /> Email digunakan untuk login mahasiswa.</p>
                @error('email') <p class="tb-mhs-err">{{ $message }}</p> @enderror
            </div>

            <div class="tb-mhs-row">
                <div class="tb-mhs-field">
                    <label for="whatsapp" class="tb-mhs-label">WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" class="tb-input @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp', $mahasiswa->profile?->whatsapp) }}" placeholder="08xxxxxxxxxx" maxlength="30">
                    @error('whatsapp') <p class="tb-mhs-err">{{ $message }}</p> @enderror
                </div>

                <div class="tb-mhs-field">
                    <label for="instagram" class="tb-mhs-label">Instagram</label>
                    <div style="display:flex;align-items:stretch;">
                        <span style="display:flex;align-items:center;padding:0 0.65rem;border:1px solid var(--tb-primary-light);border-right:none;border-radius:0.5rem 0 0 0.5rem;background:var(--tb-primary-soft);color:var(--tb-muted);font-size:0.85rem;font-weight:600;">@</span>
                        <input type="text" name="instagram" id="instagram" class="tb-input @error('instagram') is-invalid @enderror" value="{{ old('instagram', $mahasiswa->profile?->instagram) }}" placeholder="username" maxlength="60" style="border-radius:0 0.5rem 0.5rem 0;">
                    </div>
                    @error('instagram') <p class="tb-mhs-err">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="tb-mhs-actions">
            <a href="{{ route('admin.mahasiswa.index') }}" class="tb-btn tb-btn-ghost">Batal</a>
            <button type="submit" class="tb-btn"><x-icon name="save" /> Simpan</button>
        </div>
    </form>
</div>

<script>
// Filter program studi berdasarkan fakultas yang dipilih
(function () {
    var fakultasSelect = document.getElementById('fakultas_id');
    var prodiSelect = document.getElementById('prodi_id');
    if (!fakultasSelect || !prodiSelect) return;
    var allProdi = Array.from(prodiSelect.querySelectorAll('option[data-fakultas-id]'));
    var prodiTerpilih = prodiSelect.value;

    function filterProdi() {
        var fk = fakultasSelect.value;
        prodiSelect.innerHTML = '<option value="">— Pilih Prodi —</option>';
        allProdi.forEach(function (opt) {
            if (!fk || opt.dataset.fakultasId === fk) {
                prodiSelect.appendChild(opt.cloneNode(true));
            }
        });
        // Pertahankan pilihan awal jika masih relevan
        if (prodiTerpilih) {
            prodiSelect.value = prodiTerpilih;
            if (prodiSelect.value !== prodiTerpilih) prodiTerpilih = '';
        }
    }
    fakultasSelect.addEventListener('change', function () { prodiTerpilih = ''; filterProdi(); });
    filterProdi();
})();
</script>
@endsection
