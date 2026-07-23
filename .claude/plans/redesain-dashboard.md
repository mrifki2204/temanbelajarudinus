# Redesain Dashboard — Restrukturisasi Penuh

## Konteks
File target: `resources/views/dashboard.blade.php` (meng-extend `layouts.app`).
User minta restrukturisasi penuh, dengan estetika clean-profesional konsisten dengan onboarding baru (kartu border tipis radius 0.75rem, satu warna aksen navy, tanpa gradient ramai).

## Data tersedia di controller/route (dari route:list)
- Route: `dashboard`, `rekomendasi.index`, `rekomendasi.show`, `permintaan.index`, `profil.edit`, `setting.index`
- Auth user: nama, nim, fakultas, program_studi, semester, angkatan, role
- Profile: minat, tujuan, gaya, jadwal, mode, whatsapp, instagram
- `receivedRequests()->pending()->count()` → permintaan masuk belum dibaca
- `sentRequests` + `receivedRequests` accepted → total koneksi
- CBF `getTopN($user, 3)` → top 3 rekomendasi (jika profil lengkap)
- SimilarityScore count → total kandidat
- Profil lengkap check

## Layout Baru (mobile-first)

```
┌─────────────────────────────────────────┐
│  GREETING BAR                            │  ← Halo, {nama}. + tanggal hari ini
│  Sapaan + tanggal + status profil chip   │
└─────────────────────────────────────────┘
┌──────────────┬──────────────────────────┐
│ QUICK        │ TOP REKOMENDASI          │  ← grid 2 kolom (desktop)
│ ACTIONS      │ (kartu kandidat)         │
│ 4 tombol:    │ Top 3, avatar inisial,   │
│ - Cari Teman │ skor %, fakultas, CTA    │
│ - Permintaan │                          │
│   (badge!)   │                          │
│ - Profil     │                          │
│ - Pengaturan │                          │
├──────────────┴──────────────────────────┤
│ STATS ROW (4 mini-stat horizontal)      │
│ Kandidat | Permintaan Masuk | Koneksi | …│
├─────────────────────────────────────────┤
│ PROFIL SAYA (gabung preferensi+akademik) │
│ Avatar + nama + NIM + fakultas          │
│ Chips preferensi (minat, gaya, mode...)  │
│ Info akademik (semester, angkatan)      │
│ [Edit Profil] button                     │
└─────────────────────────────────────────┘
```

Mobile: semua jadi 1 kolom stack.

## Komponen & Filosofi

### 1. Greeting Bar (bukan hero gradient)
- Background putih kartu, border tipis
- Kiri: "Halo, {nama depan} 👋" bold + tanggal hari ini (format Indonesia, mis. "Sabtu, 19 Juli 2026")
- Kanan: chip status profil — "Profil Lengkap" (hijau) atau "Lengkapi Profil" (oranye, link ke profil)
- **Ganti hero gradient** → kartu bersih

### 2. Quick Actions (2x2 grid atau 1x4 row)
4 kartu aksi tappable, masing-masing: ikon + label + deskripsi singkat
- 🔍 **Cari Teman** → `rekomendasi.index` ("Lihat rekomendasi partner")
- 📨 **Permintaan** → `permintaan.index`, dengan **badge angka** jika ada pending masuk
- 👤 **Profil** → `profil.edit` ("Edit preferensi belajar")
- ⚙️ **Pengaturan** → `setting.index` ("Keamanan & akun")
Kartu: border tipis, hover lift halus, ikon dalam rounded tint

### 3. Top Rekomendasi (fokus utama)
- Section head "Rekomendasi untukmu" + link "Lihat semua →"
- 3 kartu kandidat: avatar inisial lingkaran, nama, skor match dalam %, fakultas/prodi, slot jadwal, CTA "Lihat Detail"
- Jika profil belum lengkap → empty state dengan CTA "Lengkapi Profil"
- Jika belum ada kandidat → empty state "Belum ada rekomendasi"

### 4. Stats Row (4 mini-stat)
Baris horizontal 4 stat kecil (kartu mini): angka besar + label
- Total Kandidat (skor > 0)
- Permintaan Masuk (pending)
- Koneksi (accepted)
- Total Dikirim
Mini-card: angka bold 1.5rem + label muted kecil

### 5. Profil Saya (gabung preferensi + akademik)
- Header: avatar inisial + nama + NIM
- Chips preferensi: minat (multi), tujuan, gaya, mode — tampil sebagai pill kecil
- Jadwal: ringkas jadi "X slot terpilih"
- Info akademik: fakultas, prodi, semester, angkatan (grid 2x2 kecil)
- Footer: tombol "Edit Profil"

## Konsistensi dengan Onboarding
- Kartu: `background: white; border: 1px solid var(--tb-primary-light); border-radius: 0.75rem; padding: 1.1rem`
- Section head: ikon 40px rounded tint + judul + deskripsi (sama seperti onboarding)
- Warna aksen: navy `--tb-primary` utama, oranye `--tb-accent` hanya untuk badge/penting
- Font: Inter, token `--tb-ink`/`--tb-muted`
- Hover: `border-color: var(--tb-primary)` + background lembut

## Yang TIDAK Diubah
- Route `dashboard` (closure di web.php, return view)
- Logika data di `@php` block (CBF service call, query count) — tetap dipakai, mungkin direstrukturisasi
- Layout `app.blade.php` (sidebar/navbar)
- Controller (dashboard pakai closure, bukan controller)

## Implementasi
1. Rewrite `resources/views/dashboard.blade.php`:
   - `@php` block: siapkan data (tanggal Indonesia, namaDepan, counts, topRekomendasi)
   - `<style>`: semua CSS komponen baru
   - markup: greeting → quick actions + top rekomendasi → stats → profil saya
2. Helper tanggal Indonesia: pakai `Carbon\Carbon` dengan `format('l, j F Y')` + array translate, atau Carbon locale 'id'
3. Verifikasi Blade compile
4. Buat/extend feature test: akses GET /dashboard sebagai mahasiswa (profil lengkap & belum lengkap), assert elemen kunci

## Risk & Mitigasi
- **Profil belum lengkap**: topRekomendasi kosong → tampilkan empty state dengan CTA. Sudah ada di logika lama.
- **Admin akses dashboard**: admin tidak punya profile → `getTopN` bisa error. Mitigasi: cek `role === 'mahasiswa'` sebelum hitung rekomendasi. (Admin biasanya pakai `admin.dashboard` route terpisah, tapi tetap amankan.)
- **Carbon locale 'id'**: butuh ext intl. Mitigasi: pakai array manual translate hari/bulan agar tidak bergantung intl.

## Verifikasi
- Blade compile sukses
- HTTP 200 GET /dashboard (mahasiswa profil lengkap)
- HTTP 200 GET /dashboard (mahasiswa profil belum lengkap) → empty state
- HTTP 200 GET /dashboard (admin) → tidak crash
- Test backend tetap hijau
