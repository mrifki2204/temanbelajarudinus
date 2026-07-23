<p align="center">
  <h1 align="center">🎓 Teman Belajar Udinus</h1>
  <p align="center">Sistem rekomendasi teman belajar berbasis <b>Content-Based Filtering (CBF)</b> untuk mahasiswa UDINUS.</p>
</p>

---

## 📖 Tentang Proyek

**Teman Belajar Udinus** adalah aplikasi web yang membantu mahasiswa Universitas Dian Nuswantoro (UDINUS) menemukan teman belajar yang cocok berdasarkan kesamaan preferensi: minat, tujuan belajar, gaya belajar, jadwal, dan mode belajar.

Sistem menggunakan algoritma **Content-Based Filtering** dengan menghitung **cosine similarity** antara vektor fitur preferensi setiap mahasiswa, lalu merekomendasikan mahasiswa lain dengan tingkat kesamaan tertinggi.

### Fitur Utama

- 👤 **Profil & Onboarding Mahasiswa** — pengisian preferensi belajar terpandu.
- 🤝 **Rekomendasi Teman Belajar** — top-N rekomendasi berdasarkan skor similarity CBF.
- 📨 **Permintaan Belajar** — kirim, terima, tolak, atau batalkan permintaan belajar antar mahasiswa.
- 📊 **Dashboard Mahasiswa** — ringkasan aktivitas & rekomendasi.
- 🛠️ **Panel Admin** — kelola mahasiswa, fakultas, program studi, opsi preferensi, dan lihat aktivitas sistem.
- 📝 **Log Aktivitas** — pencatatan aksi penting (login, update profil, kelola akun, dll).

---

## 🧱 Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL / MariaDB |
| Frontend | Blade, Tailwind CSS 4, Alpine.js, Vite |
| Filtering | Content-Based Filtering (cosine similarity) |
| Auth | Laravel Breeze (blade stack) |

---

## 📂 Struktur Folder

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Controller area admin (Dashboard, Mahasiswa, Fakultas, Prodi, ...)
│   │   ├── Auth/           # Controller autentikasi (login, register, ...)
│   │   └── Mahasiswa/      # Controller area mahasiswa (Dashboard, Profil, Rekomendasi, Permintaan, Aktivitas)
│   ├── Middleware/         # Middleware kustom (RoleMiddleware)
│   └── Requests/
│       ├── Admin/          # FormRequest area admin
│       └── Mahasiswa/      # FormRequest area mahasiswa
├── Models/                 # Model Eloquent (User, Profile, Fakultas, Prodi, OpsiPreferensi, SimilarityScore, StudyRequest, ActivityLog)
├── Observers/             # ProfileObserver (memicu rehitung CBF saat profil berubah)
└── Services/               # Logika domain (ContentBasedFilteringService, ActivityLogger, StudyRequestService)

resources/views/
├── admin/                  # Halaman admin
├── mahasiswa/              # Halaman mahasiswa (profil, rekomendasi, permintaan, aktivitas, dashboard, setting)
├── layouts/                # Layout induk (app, guest, profil-onboarding)
├── components/             # Komponen Blade reusable
└── auth/                   # Halaman autentikasi

database/
├── migrations/             # Skema database (urutan timestamp)
├── seeders/                # Data awal (Fakultas, Prodi, OpsiPreferensi, Admin)
└── factories/              # Factory untuk testing

routes/
├── web.php                 # Route web (grup admin & mahasiswa)
└── console.php             # Route artisan
```

> **Catatan:** Model dan Service dibuat flat (tidak dikelompokkan per role) karena merupakan domain bersama yang dipakai admin maupun mahasiswa. Hanya Controller, FormRequest, dan View yang dikelompokkan per role.

---

## ⚙️ Instalasi (Development)

### Prasyarat

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL / MariaDB

### Langkah

```bash
# 1. Clone repositori
git clone <repo-url> teman-belajar
cd teman-belajar

# 2. Install dependensi PHP
composer install

# 3. Install dependensi JS & build
npm install
npm run dev     # atau: npm run build

# 4. Salin konfigurasi env
cp .env.example .env
php artisan key:generate

# 5. Konfigurasi database di .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_DATABASE=teman_belajar_udinus
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Migrasi & seeding database
php artisan migrate --seed

# 7. Jalankan server
php artisan serve
```

Buka `http://localhost:8000`.

### Akun Default (hasil seeder)

Setelah `php artisan migrate --seed`, akun admin default dibuat. Lihat `database/seeders/AdminSeeder.php` untuk kredensial (email & password).

---

## 🧪 Testing

```bash
php artisan test              # jalankan semua test
php artisan test --parallel   # parallel (lebih cepat)
```

> Proyek ini dilengkapi test feature untuk alur autentikasi, peran (role middleware), CRUD admin, alur permintaan belajar, dan smoke test render halaman.

---

## 🚀 Deployment

Untuk panduan deploy ke VPS (Ubuntu/Debian + Nginx + PHP-FPM + MySQL), baca file **[DEPLOY.md](DEPLOY.md)**.

---

## 📑 Dokumentasi Konsep

- **Content-Based Filtering:** setiap mahasiswa memiliki *feature vector* berdasarkan preferensi (minat, tujuan, gaya, jadwal, mode). Similarity antar mahasiswa dihitung dengan cosine similarity dan disimpan di tabel `similarity_scores`. Saat profil preferensi diperbarui, `ProfileObserver` memicu perhitungan ulang similarity.
- **Blade templating:** halaman memakai `@extends('layouts.app')` (mewarisi layout induk `resources/views/layouts/app.blade.php`) dan `@section(...)` untuk mengisi slot konten.

---

## 📄 Lisensi

Proyek ini bersifat internal/akademik untuk UDINUS. Kerangka dasar menggunakan [Laravel Framework](https://laravel.com) (MIT License).
