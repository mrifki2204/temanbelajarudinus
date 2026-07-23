# Redesain Halaman Onboarding Profil (pasca-register)

## Konteks
File target: `resources/views/profil/onboarding.blade.php` (meng-extend `layouts.profil-onboarding`).
Halaman ini muncul setelah register ketika profil belum lengkap. Saat ini: 4 section dalam satu form panjang, tombol simpan full-width di bawah.

User meminta redesain komprehensif (semua 4 aspek):
1. Tombol simpan **center & prominent** di bawah
2. Poles visual section & header (lebih bersih, modern)
3. Interaksi field lebih modern (kartu opsi besar, pill buttons, validasi real-time)
4. Perbaikan responsive mobile

## Pendekatan
**Tetap single-page** (bukan wizard) — sesuai catatan "simpan profilnya bisa center dibawah".
Semua perubahan **dalam satu file** `onboarding.blade.php`. Tidak mengubah controller, route, layout, atau nama field (backend tetap jalan). Token warna `--tb-*` yang sudah ada dipakai konsisten.

## Rincian Desain

### A. Header welcome dipoles
- Avatar inisial nama user di tengah (lingkaran gradient primary→primary-dark)
- Badge "Langkah terakhir" dengan ikon ✦
- Judul sapaan personal: "Halo, {nama depan}!" + subjudul tujuan
- Progress bar 4-step menjadi lebih halus: pill segmented dengan label step aktif

### B. Section card dipoles
- Background putih (bukan soft), border lebih tipis, shadow lembut
- Section header: ikon dalam rounded-square dengan tint warna berbeda per section (biru/oranye/hijau/ungu) untuk visual hierarchy
- Nomor step bulat di kiri ikon (1–4)
- Label "Wajib" jadi pill kecil

### C. Field interaksi modern
1. **Minat** → kartu opsi besar 2-3 kolom, ikon centang muncul saat aktif, animasi scale halus. Counter "X dipilih".
2. **Tujuan/Gaya/Mode** → pilihan select diganti **pill button group** (radio card). Setiap opsi jadi tombol yang bisa diketuk, aktif = filled primary. Untuk opsi banyak (fallback ke select native jika >6 opsi).
3. **Jadwal** → tabel tetap di desktop, tapi cell lebih besar & mudah diketuk; **di mobile** berubah jadi grid kartu per hari (accordion/collapse per hari) agar tidak horizontal-scroll.
4. **Kontak** → input dengan ikon di kiri, mask visual WhatsApp/IG. Catatan privasi jadi banner rounded dengan ikon perisai.

### D. Tombol simpan center & prominent
- Section simpan terpisah dengan divider di atas
- **Tombol center** (max-width 320px, `margin: 0 auto`), bukan full-width
- Tinggi 52px, gradient primary→primary-dark, ikon check, label "Simpan Profil & Lihat Rekomendasi"
- Sub-note di bawah: "Profil bisa diedit kapan saja"
- Animasi hover (lift + shadow)

### E. Validasi real-time (JS ringan, vanilla)
- Counter minat terpilih langsung update
- Progress bar step otomatis terisi (done) saat section terisi valid
- Tombol simpan tetap enabled (backend yang validasi), tapi indikator visual section-completion membantu user tahu progress

### F. Responsive
- Mobile: section padding dikurangi, grid minat 2 kolom, jadwal jadi kartu per-hari, tombol simpan tetap center
- Breakpoint `575.98px` & `767.98px` konsisten dengan yang sudah ada

## Yang TIDAK Diubah
- `ProfilController::edit()` — logic tetap, hanya view berubah
- Nama field form (`minat[]`, `tujuan`, `gaya`, `jadwal[]`, `mode`, `whatsapp`, `instagram`) — backend validasi tetap jalan
- `layouts/profil-onboarding.blade.php` — tetap dipakai apa adanya
- Opsi preferensi dari controller (`$opsi`, `$fakultasList`, `$prodiList`) — tetap dipakai
- Tombol simpan tetap POST ke route `profil.update`

## Implementasi
1. Baca ulang full file onboarding.blade.php (bagian 90-175 yang belum dibaca) untuk pastikan tidak ada field terlewat
2. Rewrite `resources/views/profil/onboarding.blade.php`:
   - Bagian `<style>` — CSS baru untuk semua komponen di atas
   - Bagian `@section('content')` — markup baru (header, 4 section, tombol simpan center)
   - Tambah `<script>` vanilla di akhir untuk counter & progress real-time
3. Build asset (vite) tidak wajib karena style inline di blade; tapi verifikasi tidak break
4. Jalankan browser test via `php artisan serve` tidak diminta — user akan lihat sendiri

## Risk
- **Pill button group untuk select**: jika opsi preferensi >6, layout bisa sempit. Mitigasi: deteksi count di Blade, fallback ke select native jika >6 opsi. (Saat ini tujuan/gaya/mode kemungkinan sedikit.)
- **Jadwal mobile accordion**: butuh JS toggle. Pakai Bootstrap collapse (sudah ada dependency) — tidak tambah lib.
- **Validasi error Laravel**: tetap tampil via `@error()` — pastikan class `is-invalid`/`tb-field-error` tetap dipakai.

## Verifikasi
- Form tetap submit ke `profil.update` dengan field yang sama → controller & observer tetap jalan
- Error validasi tetap muncul (test: submit kosong)
- Tidak ada perubahan backend → test ProfileObserverTest tetap hijau
