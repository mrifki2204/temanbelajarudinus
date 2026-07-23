# Redesain Semua Halaman — Tema Terpadu Profesional

## Konteks & Tujuan
Redesain ~20 halaman dalam **1 tema konsisten**, tone warna tetap navy seperti sekarang, profesional. Landing page TIDAK diubah. Dashboard prioritas: hapus "Aksi Cepat", rekomendasi tidak bisa dipencet (harus "Lihat semua").

## Inventaris Halaman (20 file, landing page exclude)
### Mahasiswa (5)
- dashboard.blade.php ← PRIORITAS
- rekomendasi/index, rekomendasi/show, rekomendasi/belum-lengkap
- permintaan/index
### Profil & Setting (3)
- profil/onboarding (sudah redesain, tinggal samakan token)
- profil/edit
- setting/index
### Admin (12)
- admin/dashboard
- admin/fakultas/{index,create,edit}
- admin/prodi/{index,create,edit}
- admin/opsi/{index,create,edit}
- admin/mahasiswa/{index,show}

## Masalah Inkonsistensi Sekarang
1. Bootstrap mentah (`card border-0 shadow-sm`, `btn-primary`, `table-light`) di admin & setting → terlihat generik
2. Hero gradient navy di rekomendasi/show & profil/edit → berat, tidak clean
3. CSS inline duplikasi di tiap file (`.tb-form-control`, `.tb-btn-save` di-definisikan ulang di 5+ file)
4. Token `--tb-*` konsisten, tapi pemakaian tidak seragam (radius 0.45 vs 0.75 vs 0.875)

## Strategi: Design Token + Partial Bersama
Daripada duplikasi CSS di tiap file, buat **satu partial CSS** yang berisi komponen dasar, di-`@include` di tiap halaman. Ini menghemat duplikasi & menjamin konsistensi.

### File baru: `resources/views/layouts/partials/theme.blade.php`
Berisi `<style>` dengan seluruh komponen design system:
- **Card** `.tb-card`: putih, border tipis, radius 0.75rem, padding 1.1rem
- **Section head** `.tb-section-head`: ikon 34-40px tint navy + judul + deskripsi
- **Button** `.tb-btn` (primary solid navy), `.tb-btn-outline`, `.tb-btn-ghost`
- **Form** `.tb-input`, `.tb-select`, `.tb-label`, `.tb-field-error`
- **Badge/Pill** `.tb-chip`, `.tb-badge`
- **Stat** `.tb-stat`
- **Table** `.tb-table` (header tint, border tipis)
- **Empty state** `.tb-empty`
- **Page header** `.tb-page-head` (judul + deskripsi + aksi kanan)

Tiap halaman `@include('layouts.partials.theme')` di awal section content, lalu hanya tambah CSS spesifik halaman itu jika perlu.

## Tema Visual Baru (profesional, tone navy tetap)

### Token (di layout app, sudah ada, dipertahankan)
```
--tb-primary: #0b255b       (navy — aksen utama)
--tb-primary-dark: #071940
--tb-primary-light: #e6ebf5 (tint latar)
--tb-primary-soft: #f4f6fb  (latar halus)
--tb-accent: #ffa73a        (oranye — hanya badge/penting)
--tb-ink: #1a2b3c           (teks utama)
--tb-muted: #5a6b7d         (teks sekunder)
```

### Prinsip Tema
- **Tanpa hero gradient** → ganti dengan kartu putih bersih berisi avatar + identitas + skor (untuk show/profil)
- **Tanpa Bootstrap card mentah** → semua pakai `.tb-card`
- **Tanpa `btn-primary` Bootstrap** → pakai `.tb-btn` custom (navy solid, radius 0.5rem, hover dark)
- **Radius konsisten**: card 0.75rem, input/button 0.5rem, chip 0.4rem
- **Border konsisten**: `1px solid var(--tb-primary-light)`
- **Shadow halus** (opsional, hanya hover): `0 4px 12px rgba(11,37,91,0.06)`
- **Satu warna aksen navy** + oranye terbatas (badge, status pending)
- **Ikon**: Bootstrap Icons, di tint navy box rounded

### Komponen Inti (di partial theme)
1. **Page head**: judul 1.35rem bold + deskripsi muted + (opsional) tombol aksi kanan
2. **Card**: kontainer universal
3. **Section head** dalam card: ikon box + judul + deskripsi
4. **Form controls**: input/select/textarea konsisten (44px tinggi, border tipis, focus ring navy)
5. **Buttons**: `.tb-btn` (primary), `.tb-btn-outline` (border navy, bg putih), `.tb-btn-danger` (merah, untuk hapus)
6. **Table**: header tint primary-soft, baris hover, border bawah tipis
7. **Badges**: `.tb-badge` (navy), `.tb-badge-warn` (oranye), `.tb-badge-success` (hijau), `.tb-badge-danger` (merah)
8. **Empty state**: ikon + judul + deskripsi + (opsional) CTA
9. **Stat card**: angka besar navy + label muted

## Implementasi Bertahap (urutan)

### Fase 1 — Fondasi
1. Buat `layouts/partials/theme.blade.php` (design system CSS)
2. Tambah `@include` di layout app.blade.php supaya otomatis tersedia (lebih efisien daripada include per halaman)
   - Atau include per halaman jika ingin granular. Pilih: include di app.blade.php (otomatis semua halaman)

### Fase 2 — Dashboard (prioritas, sesuai permintaan)
3. Rewrite dashboard.blade.php:
   - Hapus "Aksi Cepat"
   - Rekomendasi tidak bisa dipencet (hapus `<a>`, ganti jadi `<div>`), ada tombol "Lihat semua" ke rekomendasi.index
   - Layout: greeting bar + stats + rekomendasi (read-only) + profil saya

### Fase 3 — Halaman Mahasiswa
4. rekomendasi/index (sudah agak oke, samakan ke partial theme)
5. rekomendasi/show (hapus hero gradient → kartu putih bersih)
6. rekomendasi/belum-lengkap (empty state konsisten)
7. permintaan/index (tab + kartu permintaan pakai theme)

### Fase 4 — Profil & Setting
8. profil/edit (hapus hero gradient navy → kartu putih)
9. profil/onboarding (samakan ke partial theme, hapus duplikasi CSS)
10. setting/index (ganti Bootstrap card → .tb-card)

### Fase 5 — Admin (12 file)
11. admin/dashboard (stat cards pakai theme)
12-15. CRUD fakultas/prodi/opsi (index pakai .tb-table + .tb-card; create/edit pakai .tb-form)
16-17. mahasiswa index/show

### Fase 6 — Verifikasi
18. Blade compile semua
19. Feature test per kelompok halaman (render 200, elemen kunci)
20. Pint bersih

## Dashboard Khusus (spesifikasi detail sesuai permintaan)
- ❌ Hapus "Aksi Cepat" (sidebar sudah cukup)
- ❌ Rekomendasi TIDAK bisa dipencet (bukan `<a>`, jadi `<div>`)
- ✅ Tombol "Lihat semua" → route rekomendasi.index (satunya jalan ke detail)
- Layout baru:
```
┌─────────────────────────────────────────┐
│ GREETING: Halo, {nama} + tanggal + chip │
├─────────────────────────────────────────┤
│ STATS (4 mini-stat horizontal)          │
├─────────────────────────────────────────┤
│ REKOMENDASI UNTUKMU (read-only)          │
│  [avatar] Nama | fakultas | skor%         │  ← tidak bisa diklik
│  [avatar] Nama | fakultas | skor%         │
│  [avatar] Nama | fakultas | skor%         │
│  [Lihat semua →]  ← satu-satunya CTA      │
├─────────────────────────────────────────┤
│ PROFIL SAYA (gabung preferensi+akademik)  │
│  avatar + nama + NIM + chips + grid      │
│  [Edit Profil]                            │
└─────────────────────────────────────────┘
```

## Yang TIDAK Diubah
- Landing page (welcome.blade.php) — sudah sesuai
- Layout app.blade.php struktur (sidebar, navbar) — hanya tambah @include theme
- Controller, route, validasi, field form
- Token warna `--tb-*` (dipertahankan, tone navy tetap)

## Risk
- **20 file besar**: bisa bertahap, tiap fase independen. Dashboard duluan.
- **Bootstrap dependency masih dipakai** (grid, tab, modal) → tidak dihapus total, hanya card/button/form diganti custom. Tab permintaan pakai Bootstrap tab (sudah ada JS) tetap dipakai, hanya di-style.
- **Duplikasi CSS saat transisi**: saat sebagian halaman pakai partial, sebagian belum → pastikan partial tidak konflik dengan CSS lama (scope class `.tb-*` unik).
- **Form field name tidak berubah** → backend tetap jalan, test tetap hijau.

## Verifikasi per Fase
- Blade compile sukses
- HTTP 200 per halaman (login sebagai role sesuai)
- Test backend tetap hijau (tidak ada perubahan controller)
- Pint bersih
