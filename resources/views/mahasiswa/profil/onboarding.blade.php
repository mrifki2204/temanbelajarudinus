@extends('layouts.profil-onboarding')

@section('content')
@php
    $user = auth()->user();
    $user->load('profile');
    $profile = $user->profile;

    $minatTerpilih = old('minat', $profile?->minat ?? []);
    if (! is_array($minatTerpilih)) { $minatTerpilih = []; }
    $jadwalTerpilih = old('jadwal', $profile?->jadwal ?? []);
    if (! is_array($jadwalTerpilih)) { $jadwalTerpilih = []; }

    // Urutan hari eksplisit (Senin -> Minggu) agar tampilan konsisten,
    // tidak bergantung pada urutan data dari database / orderBy('nilai').
    $urutanHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    $jadwalPerHari = [];
    $jamPerSlot = []; // Pagi => '06-11', dst
    foreach ($opsi['jadwal'] as $j) {
        $hari = explode(' ', $j)[0];
        $jadwalPerHari[$hari][] = $j;
        if (preg_match('/(Pagi|Siang|Sore|Malam)\s*\(([^)]+)\)/', $j, $m)) {
            $jamPerSlot[$m[1]] = $m[2];
        }
    }

    // Urutkan setiap slot dalam satu hari (Pagi -> Malam)
    $urutanSlot = ['Pagi', 'Siang', 'Sore', 'Malam'];
    foreach ($jadwalPerHari as $hari => $slots) {
        usort($jadwalPerHari[$hari], function ($a, $b) use ($urutanSlot) {
            $slotA = collect($urutanSlot)->search(fn($s) => str_starts_with($a, $s.' ')) ?? 99;
            $slotB = collect($urutanSlot)->search(fn($s) => str_starts_with($b, $s.' ')) ?? 99;
            return $slotA <=> $slotB;
        });
    }

    // Susun ulang key hari sesuai urutan Senin -> Minggu.
    $jadwalPerHariOrdered = [];
    foreach ($urutanHari as $h) {
        if (isset($jadwalPerHari[$h])) {
            $jadwalPerHariOrdered[$h] = $jadwalPerHari[$h];
        }
    }
    $jadwalPerHari = $jadwalPerHariOrdered;

    $slotKeys = ['Pagi', 'Siang', 'Sore', 'Malam'];
@endphp

<style>
    /* ============ HEADER ============ */
    .tb-head { margin-bottom: 1.5rem; }
    .tb-head-title {
        font-size: 1.35rem; font-weight: 800; color: var(--tb-ink);
        margin: 0 0 0.35rem; letter-spacing: -0.02em; line-height: 1.2;
    }
    .tb-head-sub { font-size: 0.84rem; color: var(--tb-muted); margin: 0; line-height: 1.5; }
    .tb-head-sub strong { color: var(--tb-ink); font-weight: 600; }

    /* ============ SECTION CARD — seperti rekomendasi ============ */
    .tb-section {
        background: white; border: 1px solid var(--tb-primary-light);
        border-radius: 0.75rem; padding: 1.1rem; margin-bottom: 0.85rem;
    }
    .tb-section-head { display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 1rem; }
    .tb-section-icon {
        width: 40px; height: 40px; border-radius: 0.5rem;
        background: var(--tb-primary-light); color: var(--tb-primary);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; flex-shrink: 0;
    }
    .tb-section-info { flex: 1; min-width: 0; }
    .tb-section-title { font-size: 0.95rem; font-weight: 700; color: var(--tb-ink); margin: 0 0 0.15rem; }
    .tb-section-desc { font-size: 0.78rem; color: var(--tb-muted); margin: 0; line-height: 1.4; }
    .tb-field-error { font-size: 0.75rem; color: #c0392b; margin-top: 0.5rem; font-weight: 500; }

    /* ============ LABEL ============ */
    .tb-label { font-size: 0.78rem; font-weight: 600; color: var(--tb-ink); margin: 0 0 0.4rem; display: block; }
    .tb-label .req { color: #c0392b; margin-left: 0.15rem; }

    /* ============ MINAT — checkbox grid ============ */
    .tb-opt-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; }
    @media (max-width: 575.98px) { .tb-opt-grid { grid-template-columns: 1fr 1fr; } }
    .tb-opt {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.6rem 0.75rem; background: var(--tb-primary-soft);
        border: 1px solid var(--tb-primary-light); border-radius: 0.5rem;
        cursor: pointer; transition: border-color 0.15s ease, background 0.15s ease;
        font-size: 0.8rem; color: var(--tb-ink); user-select: none; line-height: 1.3;
    }
    .tb-opt:hover { border-color: var(--tb-primary); background: white; }
    .tb-opt input { accent-color: var(--tb-primary); margin: 0; width: 15px; height: 15px; flex-shrink: 0; }
    .tb-opt:has(input:checked) { border-color: var(--tb-primary); background: var(--tb-primary-light); font-weight: 600; }

    /* ============ SELECT ============ */
    .tb-select {
        width: 100%; height: 44px; padding: 0 2.2rem 0 0.8rem;
        border: 1px solid var(--tb-primary-light); border-radius: 0.5rem; background: white;
        font-size: 0.86rem; color: var(--tb-ink); outline: none; appearance: none; cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%235a6b7d' stroke-width='2' d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.8rem center; background-size: 14px;
    }
    .tb-select:focus { border-color: var(--tb-primary); box-shadow: 0 0 0 3px rgba(11,37,91,0.10); }
    .tb-select.is-invalid { border-color: #c0392b; }

    /* ============ JADWAL ============ */
    .tb-jadwal-desktop { display: block; }
    .tb-jadwal-mobile { display: none; }
    .tb-jadwal-wrap { overflow-x: auto; }
    .tb-jadwal-table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 0.8rem; }
    .tb-jadwal-table th {
        background: var(--tb-primary-soft); color: var(--tb-primary);
        font-size: 0.72rem; font-weight: 600; text-align: center;
        border: none; border-bottom: 1px solid var(--tb-primary-light);
        padding: 0.6rem 0.3rem; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.03em;
    }
    .tb-jadwal-table th:first-child { text-align: left; padding-left: 0.7rem; border-radius: 0.5rem 0 0 0; }
    .tb-jadwal-table th:last-child { border-radius: 0 0.5rem 0 0; }
    .tb-jadwal-jam {
        display: block; font-size: 0.62rem; font-weight: 700;
        color: var(--tb-accent-dark); text-transform: none; letter-spacing: 0;
        margin-top: 0.15rem; background: var(--tb-accent-light);
        padding: 0.05rem 0.35rem; border-radius: 0.3rem;
    }
    .tb-jadwal-table td { border: none; border-bottom: 1px solid var(--tb-primary-light); padding: 0; text-align: center; }
    .tb-jadwal-table tr:last-child td { border-bottom: none; }
    .tb-jadwal-table td:first-child { font-weight: 600; color: var(--tb-ink); text-align: left; padding: 0.55rem 0.7rem; white-space: nowrap; }
    .tb-jadwal-cell-label { display: flex; align-items: center; justify-content: center; padding: 0.55rem 0.3rem; cursor: pointer; transition: background 0.15s ease; border-radius: 0.35rem; }
    .tb-jadwal-cell-label:hover { background: var(--tb-primary-soft); }
    .tb-jadwal-cell-label input { accent-color: var(--tb-accent); width: 1.2rem; height: 1.2rem; cursor: pointer; margin: 0; }
    .tb-jadwal-cell-label:has(input:checked) { background: var(--tb-accent-light); }
    .tb-jadwal-cell-label:has(input:checked):hover { background: #fde9cf; }
    .tb-jadwal-cell-empty { display: flex; align-items: center; justify-content: center; padding: 0.55rem 0.3rem; color: #d6dde6; font-size: 0.85rem; }
    .tb-jadwal-empty { color: #d6dde6; }

    .tb-jadwal-day { border-bottom: 1px solid var(--tb-primary-light); padding: 0.7rem 0; }
    .tb-jadwal-day:last-child { border-bottom: none; }
    .tb-jadwal-day-head {
        display: flex; justify-content: space-between; align-items: center;
        cursor: pointer; font-weight: 600; font-size: 0.85rem; color: var(--tb-ink); user-select: none;
    }
    .tb-jadwal-day-head svg { color: var(--tb-muted); transition: transform 0.2s ease; width: 1rem; height: 1rem; }
    .tb-jadwal-day.open .tb-jadwal-day-head svg { transform: rotate(180deg); }
    .tb-jadwal-day-body { display: none; padding-top: 0.6rem; }
    .tb-jadwal-day.open .tb-jadwal-day-body { display: block; }
    .tb-jadwal-slot {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.55rem 0.7rem; border: 1px solid var(--tb-primary-light); border-radius: 0.5rem;
        margin-top: 0.4rem; cursor: pointer; font-size: 0.8rem; color: var(--tb-ink);
        background: var(--tb-primary-soft); transition: all 0.15s ease; user-select: none;
    }
    .tb-jadwal-slot:has(input:checked) {
        background: var(--tb-accent-light); border-color: var(--tb-accent);
        color: var(--tb-accent-dark); font-weight: 600;
    }
    .tb-jadwal-slot input { accent-color: var(--tb-accent); width: 1.1rem; height: 1.1rem; cursor: pointer; margin: 0; }
    .tb-jadwal-slot:hover { border-color: var(--tb-primary); background: white; }
    .tb-jadwal-slot:has(input:checked):hover { background: #fde9cf; border-color: var(--tb-accent-dark); }

    @media (max-width: 767.98px) {
        .tb-jadwal-desktop { display: none; }
        .tb-jadwal-mobile { display: block; }
    }

    /* ============ KONTAK ============ */
    .tb-input {
        width: 100%; height: 44px; padding: 0 0.8rem;
        border: 1px solid var(--tb-primary-light); border-radius: 0.5rem; background: white;
        font-size: 0.86rem; color: var(--tb-ink); outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .tb-input:focus { border-color: var(--tb-primary); box-shadow: 0 0 0 3px rgba(11,37,91,0.10); }
    .tb-input.is-invalid { border-color: #c0392b; }
    .tb-input-prefix { display: flex; align-items: stretch; }
    .tb-prefix-label {
        display: flex; align-items: center; padding: 0 0.65rem;
        border: 1px solid var(--tb-primary-light); border-right: none; border-radius: 0.5rem 0 0 0.5rem;
        background: var(--tb-primary-soft); color: var(--tb-muted); font-size: 0.85rem; font-weight: 600;
    }
    .tb-input-prefix .tb-input { border-radius: 0 0.5rem 0.5rem 0; }
    .tb-input-prefix:has(.is-invalid) .tb-prefix-label { border-color: #c0392b; }
    .tb-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    @media (max-width: 575.98px) { .tb-row-2 { grid-template-columns: 1fr; } }

    /* ============ SUBMIT ============ */
    .tb-submit { margin-top: 1.5rem; text-align: center; }
    .tb-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;
        width: 100%; max-width: 320px; margin: 0 auto;
        height: 48px; padding: 0 1.5rem;
        background: var(--tb-primary); color: white; border: none; border-radius: 0.5rem;
        font-size: 0.88rem; font-weight: 600; cursor: pointer;
        transition: background 0.15s ease, transform 0.1s ease;
    }
    .tb-btn:hover { background: var(--tb-primary-dark); }
    .tb-btn:active { transform: translateY(1px); }
    .tb-submit-note { font-size: 0.75rem; color: var(--tb-muted); margin-top: 0.7rem; }
</style>

<form method="POST" action="{{ route('profil.update') }}">
    @csrf
    @method('PUT')

    {{-- HEADER --}}
    <div class="tb-head">
        <h1 class="tb-head-title">Lengkapi Preferensimu</h1>
        <p class="tb-head-sub">Isi <strong>sesuai kondisi aslimu</strong> — semakin akurat jawabanmu, semakin tepat sistem mencocokkan partner belajar yang cocok.</p>
    </div>

    {{-- 1. MINAT --}}
    <section class="tb-section">
        <div class="tb-section-head">
            <span class="tb-section-icon"><x-icon name="lightbulb" /></span>
            <div class="tb-section-info">
                <h2 class="tb-section-title">Minat Bidang Belajar</h2>
                <p class="tb-section-desc">Pilih satu atau beberapa minat yang sesuai denganmu.</p>
            </div>
        </div>
        <div class="tb-opt-grid">
            @foreach ($opsi['minat'] as $m)
                <label class="tb-opt" for="minat_{{ Str::slug($m) }}">
                    <input type="checkbox" name="minat[]" value="{{ $m }}" id="minat_{{ Str::slug($m) }}" @checked(in_array($m, $minatTerpilih))>
                    <span>{{ $m }}</span>
                </label>
            @endforeach
        </div>
        @error('minat') <div class="tb-field-error">{{ $message }}</div> @enderror
    </section>

    {{-- 2. TUJUAN --}}
    <section class="tb-section">
        <div class="tb-section-head">
            <span class="tb-section-icon"><x-icon name="bullseye" /></span>
            <div class="tb-section-info">
                <h2 class="tb-section-title">Tujuan Belajar</h2>
                <p class="tb-section-desc">Apa tujuan utama-mu belajar bersama?</p>
            </div>
        </div>
        <label for="tujuan" class="tb-label">Tujuan <span class="req">*</span></label>
        <select name="tujuan" id="tujuan" class="tb-select @error('tujuan') is-invalid @enderror" required>
            <option value="">— Pilih tujuan —</option>
            @foreach ($opsi['tujuan'] as $t)
                <option value="{{ $t }}" @selected(old('tujuan', $profile?->tujuan) === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('tujuan') <div class="tb-field-error">{{ $message }}</div> @enderror
    </section>

    {{-- 3. GAYA --}}
    <section class="tb-section">
        <div class="tb-section-head">
            <span class="tb-section-icon"><x-icon name="people" /></span>
            <div class="tb-section-info">
                <h2 class="tb-section-title">Gaya Belajar</h2>
                <p class="tb-section-desc">Bagaimana kamu paling nyaman belajar?</p>
            </div>
        </div>
        <label for="gaya" class="tb-label">Gaya <span class="req">*</span></label>
        <select name="gaya" id="gaya" class="tb-select @error('gaya') is-invalid @enderror" required>
            <option value="">— Pilih gaya —</option>
            @foreach ($opsi['gaya'] as $g)
                <option value="{{ $g }}" @selected(old('gaya', $profile?->gaya) === $g)>{{ $g }}</option>
            @endforeach
        </select>
        @error('gaya') <div class="tb-field-error">{{ $message }}</div> @enderror
    </section>

    {{-- 4. JADWAL --}}
    <section class="tb-section">
        <div class="tb-section-head">
            <span class="tb-section-icon"><x-icon name="calendar3" /></span>
            <div class="tb-section-info">
                <h2 class="tb-section-title">Jadwal Luang</h2>
                <p class="tb-section-desc">Centang waktu kosong-mu untuk belajar bersama.</p>
            </div>
        </div>
        <div class="tb-jadwal-desktop">
            <div class="tb-jadwal-wrap">
                <table class="tb-jadwal-table">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            @foreach ($slotKeys as $slotKey)
                                <th>
                                    {{ $slotKey }}
                                    @isset($jamPerSlot[$slotKey])
                                        <span class="tb-jadwal-jam">{{ $jamPerSlot[$slotKey] }}</span>
                                    @endisset
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwalPerHari as $hari => $slots)
                            @php
                                $bySlot = [];
                                foreach ($slots as $s) {
                                    if (preg_match('/(Pagi|Siang|Sore|Malam)/', $s, $m)) { $bySlot[$m[1]] = $s; }
                                }
                            @endphp
                            <tr>
                                <td>{{ $hari }}</td>
                                @foreach ($slotKeys as $slotKey)
                                    <td>
                                        @if (isset($bySlot[$slotKey]))
                                            <label class="tb-jadwal-cell-label" title="{{ $bySlot[$slotKey] }}">
                                                <input type="checkbox" name="jadwal[]" value="{{ $bySlot[$slotKey] }}" @checked(in_array($bySlot[$slotKey], $jadwalTerpilih))>
                                            </label>
                                        @else
                                            <span class="tb-jadwal-cell-empty" title="Slot tidak tersedia">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tb-jadwal-mobile">
            @foreach ($jadwalPerHari as $hari => $slots)
                <div class="tb-jadwal-day">
                    <div class="tb-jadwal-day-head">
                        <span>{{ $hari }}</span>
                        <x-icon name="chevron-down" />
                    </div>
                    <div class="tb-jadwal-day-body">
                        @foreach ($slots as $s)
                            @php
                                // tampilkan "Pagi (06-11)" saja (hari sudah di header)
                                $labelSlot = trim(preg_replace('/^' . preg_quote($hari, '/') . '\s+/', '', $s));
                            @endphp
                            <label class="tb-jadwal-slot">
                                <input type="checkbox" name="jadwal[]" value="{{ $s }}" @checked(in_array($s, $jadwalTerpilih))>
                                <span>{{ $labelSlot }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        @error('jadwal') <div class="tb-field-error">{{ $message }}</div> @enderror
    </section>

    {{-- 5. MODE --}}
    <section class="tb-section">
        <div class="tb-section-head">
            <span class="tb-section-icon"><x-icon name="laptop" /></span>
            <div class="tb-section-info">
                <h2 class="tb-section-title">Mode Belajar</h2>
                <p class="tb-section-desc">Preferensi pertemuan: daring, tatap muka, atau fleksibel.</p>
            </div>
        </div>
        <label for="mode" class="tb-label">Mode <span class="req">*</span></label>
        <select name="mode" id="mode" class="tb-select @error('mode') is-invalid @enderror" required>
            <option value="">— Pilih mode —</option>
            @foreach ($opsi['mode'] as $mo)
                <option value="{{ $mo }}" @selected(old('mode', $profile?->mode) === $mo)>{{ $mo }}</option>
            @endforeach
        </select>
        @error('mode') <div class="tb-field-error">{{ $message }}</div> @enderror
    </section>

    {{-- 6. KONTAK --}}
    <section class="tb-section">
        <div class="tb-section-head">
            <span class="tb-section-icon"><x-icon name="chat-dots" /></span>
            <div class="tb-section-info">
                <h2 class="tb-section-title">Kontak</h2>
                <p class="tb-section-desc">Hanya terlihat oleh mahasiswa yang permintaan belajarnya kamu terima.</p>
            </div>
        </div>
        <div class="tb-row-2">
            <div>
                <label for="whatsapp" class="tb-label">WhatsApp <span class="req">*</span></label>
                <div class="tb-input-prefix">
                    <span class="tb-prefix-label"><x-icon name="whatsapp" /></span>
                    <input type="text" name="whatsapp" id="whatsapp" class="tb-input @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp', $profile?->whatsapp) }}" placeholder="081234567890" required>
                </div>
                @error('whatsapp') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="instagram" class="tb-label">Instagram <span class="req">*</span></label>
                <div class="tb-input-prefix">
                    <span class="tb-prefix-label">@</span>
                    <input type="text" name="instagram" id="instagram" class="tb-input @error('instagram') is-invalid @enderror" value="{{ old('instagram', $profile?->instagram) }}" placeholder="username" required>
                </div>
                @error('instagram') <div class="tb-field-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </section>

    {{-- SUBMIT --}}
    <div class="tb-submit">
        <button type="submit" class="tb-btn">Simpan &amp; Lihat Rekomendasi</button>
        <p class="tb-submit-note">Profil dapat diedit kapan saja.</p>
    </div>
</form>

<script>
(function () {
    document.querySelectorAll('.tb-jadwal-day-head').forEach(function (head) {
        head.addEventListener('click', function () {
            head.closest('.tb-jadwal-day').classList.toggle('open');
        });
    });
    var firstDay = document.querySelector('.tb-jadwal-day');
    if (firstDay) firstDay.classList.add('open');
})();
</script>
@endsection
