# 📘 Dokumentasi: Membangun Aplikasi "Teman Belajar Udinus" dari Awal

Dokumentasi langkah-demi-langkah membangun aplikasi **Teman Belajar Udinus** — sistem rekomendasi teman belajar untuk mahasiswa UDINUS menggunakan algoritma **Content-Based Filtering (CBF) dengan Cosine Similarity**.

> **Stack:** Laravel 12 · PHP 8.2+ · MySQL · Blade + Bootstrap 5 · Vite/Sass

---

## Daftar Isi

1. [Persiapan & Inisialisasi Project](#1-persiapan--inisialisasi-project)
2. [Konfigurasi Environment](#2-konfigurasi-environment)
3. [Database & Migration](#3-database--migration)
4. [Model & Relationship](#4-model--relationship)
5. [Seeder: Data Awal](#5-seeder-data-awal)
6. [Auth: Registrasi & Login Kustom](#6-auth-registrasi--login-kustom)
7. [Service: Content-Based Filtering](#7-service-content-based-filtering)
8. [Observer: Auto-Recalc Skor](#8-observer-auto-recalc-skor)
9. [Routing](#9-routing)
10. [Controller](#10-controller)
11. [Middleware Role](#11-middleware-role)
12. [View & Design System](#12-view--design-system)
13. [Fitur Permintaan Belajar](#13-fitur-permintaan-belajar)
14. [Panel Admin](#14-panel-admin)
15. [Testing](#15-testing)
16. [Build & Jalankan](#16-build--jalankan)

---

## 1. Persiapan & Inisialisasi Project

### Prasyarat
- **PHP 8.2+** dengan extension: pdo_mysql, mbstring, xml, curl, zip
- **Composer** (dependency manager PHP)
- **MySQL** (via Laragon/XAMPP/Herd)
- **Node.js 18+** & npm (untuk Vite asset build)
- **Git**

### Buat Project Laravel Baru

```bash
composer create-project laravel/laravel teman-belajar-udinus
cd teman-belajar-udinus
```

### Install Laravel Breeze (Auth Scaffold)

Breeze memberikan scaffolding login/register/password-reset yang nanti kita kustomisasi.

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Pilih: **Blade** (dengan Alpine.js). Ini akan generate:
- `app/Http/Controllers/Auth/*` (9 controller auth)
- `routes/auth.php`
- `resources/views/auth/*` & `resources/views/layouts/*`

### Install Bootstrap 5 (ganti Tailwind default)

Breeze default pakai Tailwind. Kita ganti ke Bootstrap 5 + Bootstrap Icons:

```bash
npm install bootstrap @popperjs/core bootstrap-icons
npm install --dev sass
```

Edit `resources/sass/app.scss`:
```scss
@import 'bootstrap/scss/bootstrap';
@import 'bootstrap-icons/font/bootstrap-icons';
```

Edit `resources/js/app.js`:
```js
import 'bootstrap';
```

### Dependency Tambahan

```bash
composer require laravel/pint --dev   # linter
```

---

## 2. Konfigurasi Environment

### `.env`

```env
APP_NAME="Teman Belajar Udinus"
APP_ENV=local
APP_KEY=                    # php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teman_belajar_udinus
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local
```

Generate app key:
```bash
php artisan key:generate
```

Buat database di MySQL:
```sql
CREATE DATABASE teman_belajar_udinus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 3. Database & Migration

### Skema Database (ERD)

```
users (1) ──── (1) profiles
users (1) ──── (N) study_requests  (sebagai pengirim)
users (1) ──── (N) study_requests  (sebagai penerima)
users (1) ──── (N) similarity_scores  (sebagai user target)
users (1) ──── (N) similarity_scores  (sebagai kandidat)
fakultas (1) ── (N) prodi
opsi_preferensi (independent lookup table)
```

### Daftar Migration (urutan eksekusi)

Buat file migration sesuai urutan:

#### 3.1 Modifikasi tabel `users`

Edit migration `create_users_table` default. Field tambahan: `nama`, `nim`, `role`, `status`, `semester`, `angkatan`, serta relasi `fakultas_id` & `prodi_id`.

> **Catatan (pembaruan):** Awalnya `fakultas` & `program_studi` disimpan sebagai string nama. Kini keduanya adalah **foreign key** (`fakultas_id` → `fakultas.id`, `prodi_id` → `prodi.id`, `nullOnDelete`) melalui migration `2025_01_01_000012_migrate_users_fakultas_prodi_to_fk.php`. Potongan kode di bawah memperlihatkan struktur awal; lihat migration untuk struktur final beserta index (`role,status`, `created_at`).

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('nama');                    // bukan 'name'
    $table->string('nim')->unique()->nullable();
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('role')->default('mahasiswa');        // mahasiswa | admin
    $table->string('status')->default('aktif');           // aktif | nonaktif
    $table->string('fakultas')->nullable();
    $table->string('program_studi')->nullable();
    $table->unsignedTinyInteger('semester')->nullable();
    $table->unsignedSmallInteger('angkatan')->nullable();
    $table->rememberToken();
    $table->timestamps();
});
```

#### 3.2 `fakultas`

```php
Schema::create('fakultas', function (Blueprint $table) {
    $table->id();
    $table->string('kode')->unique();          // FIK, FEB, dst
    $table->string('nama');
    $table->timestamps();
});
```

#### 3.3 `prodi`

```php
Schema::create('prodi', function (Blueprint $table) {
    $table->id();
    $table->foreignId('fakultas_id')->constrained()->cascadeOnDelete();
    $table->string('kode')->unique();          // A11, A12, dst
    $table->string('nama');
    $table->string('jenjang');                 // D3 | D4 | S1
    $table->timestamps();
});
```

#### 3.4 `opsi_preferensi`

Tabel lookup untuk semua opsi pilihan preferensi.

```php
Schema::create('opsi_preferensi', function (Blueprint $table) {
    $table->id();
    $table->string('tipe');                     // minat|tujuan|gaya|jadwal|mode
    $table->string('nilai');
    $table->timestamps();
    $table->unique(['tipe', 'nilai']);
});
```

#### 3.5 `profiles`

Menyimpan 5 atribut preferensi + kontak + cache feature_vector.

```php
Schema::create('profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->json('minat')->nullable();          // multi-label (array)
    $table->string('tujuan')->nullable();       // one-hot (string)
    $table->string('gaya')->nullable();         // one-hot (string)
    $table->json('jadwal')->nullable();          // multi-label (array)
    $table->string('mode')->nullable();          // one-hot (string)
    $table->string('whatsapp')->nullable();
    $table->string('instagram')->nullable();
    $table->json('feature_vector')->nullable(); // cache hasil CBF
    $table->timestamps();
});
```

#### 3.6 `study_requests`

Permintaan belajar antar mahasiswa.

```php
Schema::create('study_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('penerima_id')->constrained('users')->cascadeOnDelete();
    $table->string('status')->default('pending');  // pending|accepted|rejected
    $table->timestamp('waktu_kirim');
    $table->timestamps();
});
```

#### 3.7 `similarity_scores`

Penyimpanan hasil perhitungan CBF.

```php
Schema::create('similarity_scores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();      // user target
    $table->foreignId('kandidat_id')->constrained('users')->cascadeOnDelete();
    $table->float('skor');                  // 0.0 - 1.0
    $table->timestamps();
    $table->unique(['user_id', 'kandidat_id']);
});
```

#### 3.8 Migration data: slot jadwal Sabtu/Minggu Siang

```php
// 2025_01_01_000007_add_sabtu_minggu_siang_jadwal_opsi.php
public function up(): void
{
    DB::table('opsi_preferensi')->insertOrIgnore([
        ['tipe' => 'jadwal', 'nilai' => 'Sabtu Siang (11-14)'],
        ['tipe' => 'jadwal', 'nilai' => 'Minggu Siang (11-14)'],
    ]);
}
```

### Jalankan Migration

```bash
php artisan migrate
```

---

## 4. Model & Relationship

### 4.1 `User` (app/Models/User.php)

```php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nama', 'nim', 'email', 'password',
        'role', 'status',
        'fakultas_id', 'prodi_id', 'semester', 'angkatan',
    ];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    // Relasi
    public function profile(): HasOne { return $this->hasOne(Profile::class); }
    public function sentRequests(): HasMany {
        return $this->hasMany(StudyRequest::class, 'pengirim_id');
    }
    public function receivedRequests(): HasMany {
        return $this->hasMany(StudyRequest::class, 'penerima_id');
    }
    public function similarityScores(): HasMany {
        return $this->hasMany(SimilarityScore::class, 'user_id');
    }

    // Helper
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isMahasiswa(): bool { return $this->role === 'mahasiswa'; }
}
```

### 4.2 `Profile`

```php
class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'minat', 'tujuan', 'gaya', 'jadwal', 'mode',
        'whatsapp', 'instagram', 'feature_vector',
    ];

    protected function casts(): array
    {
        return [
            'minat' => 'array',
            'jadwal' => 'array',
            'feature_vector' => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
```

### 4.3 `StudyRequest`

```php
class StudyRequest extends Model
{
    protected $fillable = ['pengirim_id', 'penerima_id', 'status', 'waktu_kirim'];

    protected function casts(): array { return ['waktu_kirim' => 'datetime']; }

    public function pengirim(): BelongsTo {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
    public function penerima(): BelongsTo {
        return $this->belongsTo(User::class, 'penerima_id');
    }
}
```

### 4.4 `SimilarityScore`

```php
class SimilarityScore extends Model
{
    protected $fillable = ['user_id', 'kandidat_id', 'skor'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function kandidat(): BelongsTo {
        return $this->belongsTo(User::class, 'kandidat_id');
    }
}
```

### 4.5 `Fakultas`, `Prodi`, `OpsiPreferensi`

```php
class Fakultas extends Model {
    protected $fillable = ['kode', 'nama'];
    public function prodi(): HasMany { return $this->hasMany(Prodi::class); }
}

class Prodi extends Model {
    protected $fillable = ['fakultas_id', 'kode', 'nama', 'jenjang'];
    public function fakultas(): BelongsTo { return $this->belongsTo(Fakultas::class); }
}

class OpsiPreferensi extends Model {
    protected $fillable = ['tipe', 'nilai'];
}
```

### 4.6 Base Controller helper

```php
abstract class Controller
{
    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
```

---

## 5. Seeder: Data Awal

### 5.1 `FakultasSeeder` — 6 fakultas UDINUS

```php
public function run(): void
{
    $fakultas = [
        ['kode' => 'FIK', 'nama' => 'Fakultas Ilmu Komputer'],
        ['kode' => 'FEB', 'nama' => 'Fakultas Ekonomi dan Bisnis'],
        ['kode' => 'FIB', 'nama' => 'Fakultas Ilmu Budaya'],
        ['kode' => 'FKES', 'nama' => 'Fakultas Kesehatan Masyarakat'],
        ['kode' => 'FT', 'nama' => 'Fakultas Teknik'],
        ['kode' => 'FK', 'nama' => 'Fakultas Kedokteran'],
    ];
    foreach ($fakultas as $f) {
        Fakultas::updateOrCreate(['kode' => $f['kode']], $f);
    }
}
```

### 5.2 `ProdiSeeder` — 21 program studi

```php
public function run(): void
{
    $prodi = [
        ['fakultas_kode' => 'FIK', 'kode' => 'A11', 'nama' => 'Teknik Informatika S1', 'jenjang' => 'S1'],
        ['fakultas_kode' => 'FIK', 'kode' => 'A12', 'nama' => 'Sistem Informasi S1', 'jenjang' => 'S1'],
        ['fakultas_kode' => 'FIK', 'kode' => 'A14', 'nama' => 'Desain Komunikasi Visual S1', 'jenjang' => 'S1'],
        // ... 21 prodi total
    ];
    foreach ($prodi as $p) {
        $fak = Fakultas::where('kode', $p['fakultas_kode'])->first();
        Prodi::updateOrCreate(
            ['kode' => $p['kode']],
            ['fakultas_id' => $fak->id, 'nama' => $p['nama'], 'jenjang' => $p['jenjang']]
        );
    }
}
```

### 5.3 `OpsiPreferensiSeeder` — 60 opsi (5 kategori)

```php
public function run(): void
{
    $opsi = [
        'minat' => [
            'Coding & Programming', 'Data & Statistik', 'Jaringan & Cyber Security',
            'Desain & Multimedia', 'Bisnis & Marketing', 'Bahasa & Sastra',
            'Kesehatan & Medis', 'Teknik & Industri', 'Sains (MIPA)',
            'Akademik Umum', 'Soft Skills',
        ], // 11
        'tujuan' => [
            'Belajar UTS/UAS', 'Ngerjain Tugas', 'Proyek Kelompok', 'Skripsi/TA',
            'Belajar Materi Kuliah', 'Persiapan Magang/MSIB', 'Persiapan Lomba',
            'Belajar Bahasa Asing', 'Membangun Portfolio', 'Persiapan Sertifikasi',
        ], // 10
        'gaya' => [
            'Diskusi Bareng', 'Belajar Sendiri', 'Visual & Praktik', 'Praktik Langsung',
            'Belajar Terbimbing', 'Baca & Rangkum', 'Problem Solving', 'Saling Mengajar',
        ], // 8
        'jadwal' => [
            // 7 hari × 4 sesi (Pagi 06-11, Siang 11-14, Sore 14-18, Malam 18-23)
            'Senin Pagi (06-11)', 'Senin Siang (11-14)', 'Senin Sore (14-18)', 'Senin Malam (18-23)',
            // ... untuk Senin s/d Minggu
            'Sabtu Siang (11-14)', 'Minggu Siang (11-14)', // slot tambahan
        ], // 28
        'mode' => ['Online', 'Tatap Muka', 'Fleksibel'], // 3
    ];

    foreach ($opsi as $tipe => $nilaiList) {
        foreach ($nilaiList as $nilai) {
            OpsiPreferensi::updateOrCreate(
                ['tipe' => $tipe, 'nilai' => $nilai],
                ['tipe' => $tipe, 'nilai' => $nilai]
            );
        }
    }
}
```

### 5.4 `AdminSeeder`

```php
public function run(): void
{
    User::updateOrCreate(
        ['email' => 'admin@udinus.ac.id'],
        [
            'nama' => 'Administrator',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]
    );
}
```

### 5.5 `DatabaseSeeder`

```php
public function run(): void
{
    $this->call([
        FakultasSeeder::class,
        ProdiSeeder::class,
        OpsiPreferensiSeeder::class,
        AdminSeeder::class,
    ]);
}
```

Jalankan:
```bash
php artisan db:seed
# atau fresh migrate + seed:
php artisan migrate:fresh --seed
```

---

## 6. Auth: Registrasi & Login Kustom

### 6.1 `RegisteredUserController` — kustom field registrasi

```php
public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'nama'         => ['required', 'string', 'max:255'],
        'nim'          => ['required', 'string', 'max:50', 'unique:users'],
        'email'        => ['required', 'string', 'lowercase', 'email', 'max:255',
                            'unique:users',
                            'regex:/^[a-z0-9._]+@mhs\.dinus\.ac\.id$/i'],
        'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        // Pembaruan: kini memakai FK id, bukan string nama.
        'fakultas_id'  => ['required', 'exists:fakultas,id'],
        'prodi_id'     => ['required', 'exists:prodi,id'],
        'semester'     => ['required', 'integer', 'min:1', 'max:14'],
        'angkatan'     => ['required', 'integer', 'min:2000', 'max:'.date('Y')],
    ]);

    $user = User::create([
        'nama'         => $validated['nama'],
        'nim'          => $validated['nim'],
        'email'        => $validated['email'],
        'password'     => Hash::make($validated['password']),
        'role'         => 'mahasiswa',
        'status'       => 'aktif',
        'fakultas_id'  => $validated['fakultas_id'],
        'prodi_id'     => $validated['prodi_id'],
        'semester'     => $validated['semester'],
        'angkatan'     => $validated['angkatan'],
    ]);

    event(new Registered($user));
    Auth::login($user);

    // Wajib isi profil preferensi dulu
    return redirect()->route('profil.edit');
}
```

> **Kunci:** Email HANYA menerima `@mhs.dinus.ac.id` (regex). Setelah register → redirect ke `/profil` (onboarding), BUKAN dashboard.

### 6.2 `AuthenticatedSessionController` + `LoginRequest`

- Rate limit: 5 percobaan gagal → lockout.
- Cek `status` akun: kalau `nonaktif` → logout + throw error.
- Redirect setelah login:
  1. Admin → `admin.dashboard`
  2. Belum ada profil/fakultas/prodi → `profil.edit`
  3. Lainnya → `dashboard`

---

## 7. Service: Content-Based Filtering

**File:** `app/Services/ContentBasedFilteringService.php`

Ini jantung aplikasi. Implementasi CBF dengan **cosine similarity** dan **uniform weight** pada 5 atribut preferensi.

### 7.1 Algoritma — 5 Atribut Preferensi

| Atribut | Encoding | Tipe data |
|---------|----------|-----------|
| `minat` | binary multi-label | JSON array |
| `tujuan` | one-hot | string |
| `gaya` | one-hot | string |
| `jadwal` | binary multi-label | JSON array |
| `mode` | one-hot | string |

Semua atribut dibobot **seragam** (uniform weight) — sesuai Bab 4 skripsi.

### 7.2 `buildFeatureVector(Profile $profile): array`

Bangun vektor biner dari profil. Dimensi = union semua opsi di `opsi_preferensi` (format key `"{tipe}:{nilai}"`, total ~60 dimensi).

```php
public function buildFeatureVector(Profile $profile): array
{
    $dimensions = $this->getDimensions();
    $vector = array_fill_keys($dimensions->all(), 0);

    // multi-label (minat, jadwal) → set 1 untuk tiap item
    foreach (($profile->minat ?? []) as $nilai) {
        $key = "minat:{$nilai}";
        if (array_key_exists($key, $vector)) $vector[$key] = 1;
    }
    foreach (($profile->jadwal ?? []) as $nilai) {
        $key = "jadwal:{$nilai}";
        if (array_key_exists($key, $vector)) $vector[$key] = 1;
    }

    // one-hot (tujuan, gaya, mode) → set 1 untuk single value
    foreach (['tujuan', 'gaya', 'mode'] as $attr) {
        if ($profile->$attr) {
            $key = "{$attr}:{$profile->$attr}";
            if (array_key_exists($key, $vector)) $vector[$key] = 1;
        }
    }

    return $vector;
}
```

### 7.3 `cosineSimilarity(array $a, array $b): float`

Rumus: **cos(θ) = (A·B) / (||A|| · ||B||)**

```php
public function cosineSimilarity(array $a, array $b): float
{
    $dot = $normA = $normB = 0;
    foreach ($a as $key => $val) {
        $bVal = $b[$key] ?? 0;
        $dot += $val * $bVal;
        $normA += $val * $val;
        $normB += $bVal * $bVal;
    }
    $denominator = sqrt($normA) * sqrt($normB);
    return $denominator > 0 ? $dot / $denominator : 0.0;
}
```

> Karena vektor biner, hasil di rentang `[0..1]`. Guard `denominator > 0` anti divide-by-zero.

### 7.4 `calculateForUser(User $target): int`

Hitung skor similaritas user target terhadap SEMUA kandidat, batch upsert ke `similarity_scores`.

```php
public function calculateForUser(User $target): int
{
    $profile = $target->profile;
    if (! $profile || ! $this->isProfileLengkap($profile)) {
        SimilarityScore::where('user_id', $target->id)->delete();
        return 0;
    }

    $targetVector = $this->buildFeatureVector($profile);

    $kandidatList = User::where('role', 'mahasiswa')
        ->where('status', 'aktif')
        ->where('id', '!=', $target->id)
        ->with('profile')
        ->get();

    $rows = [];
    foreach ($kandidatList as $kandidat) {
        if (! $kandidat->profile || ! $this->isProfileLengkap($kandidat->profile)) continue;
        $kVector = $this->buildFeatureVector($kandidat->profile);
        $skor = $this->cosineSimilarity($targetVector, $kVector);
        $rows[] = [
            'user_id' => $target->id,
            'kandidat_id' => $kandidat->id,
            'skor' => $skor,
        ];
    }

    // Batch upsert: hapus skor lama, insert baru (chunk 500)
    SimilarityScore::where('user_id', $target->id)->delete();
    collect($rows)->chunk(500)->each(fn ($chunk) =>
        SimilarityScore::insert($chunk->all())
    );

    // Cache feature_vector (tanpa trigger observer)
    $profile->feature_vector = $targetVector;
    $profile->saveQuietly();

    return count($rows);
}
```

### 7.5 `getTopN(User $target, int $n = 10, array $filter = []): Collection`

Ambil Top-N rekomendasi dengan filter opsional (fakultas/prodi).

```php
public function getTopN(User $target, int $n = 10, array $filter = [])
{
    $query = SimilarityScore::with('kandidat.profile')
        ->where('user_id', $target->id)
        ->where('skor', '>', 0);

    // Pool lebih besar sebelum filter post-Top-N
    $poolMultiplier = (! empty($filter['fakultas_id']) || ! empty($filter['prodi_id'])) ? 10 : 5;
    $query->orderByDesc('skor')->limit($n * $poolMultiplier);
    $results = $query->get();

    // Filter post-Top-N (tidak mengganggu perhitungan CBF) — by FK id.
    if (! empty($filter['fakultas_id'])) {
        $results = $results->filter(fn ($s) => $s->kandidat && (string) $s->kandidat->fakultas_id === (string) $filter['fakultas_id']);
    }
    if (! empty($filter['prodi_id'])) {
        $results = $results->filter(fn ($s) => $s->kandidat && (string) $s->kandidat->prodi_id === (string) $filter['prodi_id']);
    }

    return $results->take($n)->values();
}
```

> Kandidat pending/accepted **tetap muncul** dengan badge status (lihat `getHubunganKandidat`).

### 7.6 `getHubunganKandidat(User $target): array`

Map status hubungan `[kandidat_id => 'pending'|'accepted'|'rejected'|null]` dari kedua arah.

```php
public function getHubunganKandidat(User $target): array
{
    $map = [];
    foreach ($target->sentRequests as $req) $map[$req->penerima_id] = $req->status;
    foreach ($target->receivedRequests as $req) {
        if (($map[$req->pengirim_id] ?? null) === 'accepted') continue;
        $map[$req->pengirim_id] = $req->status;
    }
    return $map;
}
```

### 7.7 `isProfileLengkap(Profile $profile): bool`

```php
public function isProfileLengkap(Profile $profile): bool
{
    return ! empty($profile->minat)
        && ! empty($profile->tujuan)
        && ! empty($profile->gaya)
        && ! empty($profile->jadwal)
        && ! empty($profile->mode);
}
```

---

## 8. Observer: Auto-Recalc Skor

**File:** `app/Observers/ProfileObserver.php`

Saat profil mahasiswa berubah (disimpan/dihapus), skor similarity harus dihitung ulang — **dua arah**:
- **Forward:** skor dari user yang berubah → seluruh kandidat
- **Reverse:** skor dari seluruh user lain → user yang berubah (penting untuk user baru)

### Daftarkan Observer di `AppServiceProvider`

```php
public function boot(): void
{
    Profile::observe(ProfileObserver::class);
}
```

### Implementasi Observer

```php
class ProfileObserver
{
    public function __construct(
        protected ContentBasedFilteringService $cbfService
    ) {}

    public function saved(Profile $profile): void
    {
        $owner = User::find($profile->user_id);
        if (! $owner || $owner->role !== 'mahasiswa' || $owner->status !== 'aktif') return;

        // Kalau profil belum lengkap → hapus skor, skip
        if (! $this->cbfService->isProfileLengkap($profile)) {
            SimilarityScore::where('user_id', $owner->id)->delete();
            SimilarityScore::where('kandidat_id', $owner->id)->delete();
            return;
        }

        // Forward: user yang berubah → kandidat lain
        $this->cbfService->calculateForUser($owner);

        // Reverse: user lain → user yang berubah
        $this->recalcReverseScores($owner);
    }

    public function deleted(Profile $profile): void
    {
        SimilarityScore::where('user_id', $profile->user_id)->delete();
        SimilarityScore::where('kandidat_id', $profile->user_id)->delete();
    }

    protected function recalcReverseScores(User $owner): void
    {
        $ownerVector = $this->cbfService->buildFeatureVector($owner->profile);

        $otherUsers = User::where('role', 'mahasiswa')
            ->where('status', 'aktif')
            ->where('id', '!=', $owner->id)
            ->whereHas('profile')
            ->with('profile')
            ->get();

        foreach ($otherUsers as $other) {
            if (! $this->cbfService->isProfileLengkap($other->profile)) continue;
            $otherVector = $this->cbfService->buildFeatureVector($other->profile);
            $skor = $this->cbfService->cosineSimilarity($otherVector, $ownerVector);

            SimilarityScore::updateOrCreate(
                ['user_id' => $other->id, 'kandidat_id' => $owner->id],
                ['skor' => $skor]
            );
        }
    }
}
```

> **Mengapa reverse penting?** Saat user baru mendaftar & isi profil, user lama perlu punya skor mengarah ke user baru (agar user baru muncul di rekomendasi user lama).

---

## 9. Routing

### `routes/web.php`

```php
use App\Http\Controllers\{
    AdminDashboardController, DashboardController,
    ProfilController, RekomendasiController,
    PermintaanBelajarController, SettingController,
};

// Landing page (publik)
Route::view('/', 'welcome')->name('welcome');

// Auth routes dari Breeze (login, register, logout, password reset, dll)
require __DIR__.'/auth.php';

// Grup mahasiswa (login + role mahasiswa + status aktif)
Route::middleware(['auth', 'role:mahasiswa', 'status:aktif'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // Setting
    Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
    Route::put('/setting/password', [ProfilController::class, 'updatePassword'])->name('profil.password.update');
    Route::delete('/setting/account', [ProfilController::class, 'destroy'])->name('profil.destroy');

    // Rekomendasi
    Route::get('/rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi.index');
    Route::get('/rekomendasi/{kandidatId}', [RekomendasiController::class, 'show'])->name('rekomendasi.show');

    // Permintaan belajar
    Route::get('/permintaan', [PermintaanBelajarController::class, 'index'])->name('permintaan.index');
    Route::post('/permintaan', [PermintaanBelajarController::class, 'store'])->name('permintaan.store');
    Route::patch('/permintaan/{permintaan}/accept', [PermintaanBelajarController::class, 'accept'])->name('permintaan.accept');
    Route::patch('/permintaan/{permintaan}/reject', [PermintaanBelajarController::class, 'reject'])->name('permintaan.reject');
    Route::delete('/permintaan/{permintaan}/cancel', [PermintaanBelajarController::class, 'cancel'])->name('permintaan.cancel');
});

// Grup admin (login + role admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('fakultas', \App\Http\Controllers\Admin\FakultasController::class)->except('show');
    Route::resource('prodi', \App\Http\Controllers\Admin\ProdiController::class)->except('show');
    Route::resource('opsi', \App\Http\Controllers\Admin\OpsiPreferensiController::class)->except('show');
    Route::resource('mahasiswa', \App\Http\Controllers\Admin\MahasiswaController::class)->only(['index', 'show']);
    Route::patch('/mahasiswa/{mahasiswa}/toggle-status', [\App\Http\Controllers\Admin\MahasiswaController::class, 'toggleStatus'])->name('mahasiswa.toggle-status');
});
```

### `routes/auth.php` (Breeze — dimodifikasi)

Daftar route auth: `login`, `logout`, `register`, `password.request`, `password.email`, `password.update`, `verification.*`, `password.confirm`. Route `register` mengarah ke `RegisteredUserController` kustom kita.

---

## 10. Controller

### Mahasiswa

| Controller | Method | Fungsi |
|-----------|-------|--------|
| `DashboardController` | `index` | Beranda mahasiswa: greeting, stat cards, top 3 rekomendasi, ringkasan profil |
| `ProfilController` | `edit` | Tampilkan form profil (onboarding kalau baru, edit kalau sudah) |
| | `update` | Simpan 5 atribut preferensi + kontak, reset feature_vector |
| | `updatePassword` | Ubah kata sandi |
| | `destroy` | Hapus akun (dengan konfirmasi password) |
| `SettingController` | `index` | Halaman pengaturan akun |
| `RekomendasiController` | `index` | Top 10 rekomendasi + filter fakultas/prodi |
| | `show` | Detail kandidat + tombol ajukan/terima/tolak |
| `PermintaanBelajarController` | `index` | Daftar permintaan terkirim/diterima |
| | `store` | Kirim permintaan baru (status pending) |
| | `accept` / `reject` | Penerima terima/tolak permintaan |
| | `cancel` | Pengirim batalkan permintaan pending |

### Admin (`Admin/`)

| Controller | Method | Fungsi |
|-----------|-------|--------|
| `FakultasController` | CRUD | Kelola fakultas (kode + nama) |
| `ProdiController` | CRUD | Kelola prodi + FK fakultas |
| `OpsiPreferensiController` | CRUD | Kelola opsi preferensi per tipe (minat/tujuan/gaya/jadwal/mode) |
| `MahasiswaController` | `index`, `show` | Daftar & detail mahasiswa |
| | `toggleStatus` | Aktif↔nonaktif mahasiswa (recalc skor saat aktif, hapus saat nonaktif) |

---

## 11. Middleware Role

### `bootstrap/app.php` (Laravel 12 style)

Daftarkan middleware alias `role` & `status`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'status' => \App\Http\Middleware\CheckStatus::class,
    ]);
})
```

### `app/Http/Middleware/CheckRole.php`

```php
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403);
        }
        return $next($request);
    }
}
```

### `app/Http/Middleware/CheckStatus.php`

```php
class CheckStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->status !== 'aktif') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan.');
        }
        return $next($request);
    }
}
```

---

## 12. View & Design System

### Struktur Folder View

```
resources/views/
├── welcome.blade.php                  # landing page (tidak diubah)
├── dashboard.blade.php                 # beranda mahasiswa
├── auth/                               # login, register, password reset, dll (Breeze)
├── profil/
│   ├── onboarding.blade.php            # pengisian profil pertama kali (tanpa navbar)
│   └── edit.blade.php                  # edit profil
├── rekomendasi/
│   ├── index.blade.php                 # daftar rekomendasi
│   ├── show.blade.php                  # detail kandidat
│   └── belum-lengkap.blade.php         # empty state
├── permintaan/index.blade.php          # daftar permintaan
├── setting/index.blade.php             # pengaturan akun
├── layouts/
│   ├── app.blade.php                   # layout utama (sidebar + topbar)
│   ├── guest.blade.php                 # layout auth
│   ├── navigation.blade.php            # sidebar menu
│   ├── profil-onboarding.blade.php     # layout khusus onboarding
│   └── partials/
│       ├── flash.blade.php             # notifikasi flash
│       └── theme.blade.php             # DESIGN SYSTEM terpusat
└── admin/
    ├── dashboard.blade.php
    ├── fakultas/{index,create,edit}.blade.php
    ├── prodi/{index,create,edit}.blade.php
    ├── opsi/{index,create,edit}.blade.php
    └── mahasiswa/{index,show}.blade.php
```

### Design System Terpusat — `layouts/partials/theme.blade.php`

Semua komponen CSS didefinisikan di satu file partial, di-include via `@include('layouts.partials.theme')` di `app.blade.php` & `profil-onboarding.blade.php`.

**Token warna (CSS variables):**
```css
:root {
    --tb-primary: #0b255b;       /* navy */
    --tb-primary-dark: #071940;
    --tb-primary-light: #e6ebf5;
    --tb-primary-soft: #f4f6fb;
    --tb-accent: #ffa73a;        /* oranye */
    --tb-accent-dark: #e88f1e;
    --tb-ink: #1a2b3c;
    --tb-muted: #5a6b7d;
}
```

**Komponen tersedia:** `.tb-page-head`, `.tb-card`, `.tb-section-head`, `.tb-section-icon`, `.tb-btn` (+ outline/ghost/danger/sm), `.tb-input`/`.tb-select`/`.tb-textarea`/`.tb-label`, `.tb-chip`/`.tb-badge`, `.tb-stat`, `.tb-table`, `.tb-empty`, `.tb-back`, `.tb-divider`.

Dengan design system ini, semua halaman konsisten tanpa duplikasi CSS.

---

## 13. Fitur Permintaan Belajar

### State Machine

```
            kirim
  [tidak ada] ──────► [pending]
                         │
                ┌────────┴────────┐
           accept              reject
                │                  │
                ▼                  ▼
         [accepted]           [rejected]
                                  │
                              kirim ulang
                                  ▼
                              [pending]
```

### Validasi Duplikat (`PermintaanStoreRequest`)

Cegah kirim ganda:
- Tidak boleh ke diri sendiri
- Profil pengirim harus lengkap
- Tidak boleh ada permintaan aktif (pending/accepted) ke penerima yang sama

```php
$validator->after(function ($validator) {
    $sudahAktif = StudyRequest::where('pengirim_id', $pengirim->id)
        ->where('penerima_id', $penerimaId)
        ->whereIn('status', ['pending', 'accepted'])
        ->exists();
    if ($sudahAktif) {
        $validator->errors()->add('penerima_id', 'Anda sudah memiliki permintaan aktif.');
    }
});
```

### Kontak Terkunci

Nomor WhatsApp & Instagram kandidat **hanya terlihat setelah permintaan diterima** (status `accepted`). Sebelum itu, ditampilkan sebagai konten terkunci.

---

## 14. Panel Admin

### Dashboard Admin (`admin/dashboard.blade.php`)

- 8 stat card: total mahasiswa, admin, aktif, nonaktif, profil lengkap, permintaan pending, accepted, total
- Distribusi mahasiswa per fakultas (bar visual)
- Top 10 program studi
- 5 mahasiswa terbaru

### CRUD Master Data

- **Fakultas** — kode (unique) + nama
- **Prodi** — FK fakultas + kode (unique) + nama + jenjang (D3/D4/S1)
- **Opsi Preferensi** — filter per tipe (minat/tujuan/gaya/jadwal/mode)

### Manajemen Mahasiswa

- Daftar mahasiswa dengan filter (fakultas, status) + search (nama/nim/email)
- Detail mahasiswa: profil, statistik permintaan
- **Toggle status aktif↔nonaktif**:
  - Saat nonaktif → hapus semua `SimilarityScore` terkait
  - Saat aktif → panggil `ContentBasedFilteringService::calculateForUser()` untuk recalc

---

## 15. Testing

### Konfigurasi `phpunit.xml`

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="DB_DATABASE" value="teman_belajar_udinus_testing"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
</php>
```

> Testing pakai MySQL (mewarisi dari `.env`). Buat database testing:
> ```sql
> CREATE DATABASE teman_belajar_udinus_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> ```

### Struktur Test (57 test total)

**Unit (16 test):**
- `ContentBasedFilteringServiceTest.php` (15) — algoritma CBF: cosine similarity, feature vector, calculateForUser, getTopN, getHubunganKandidat

**Feature (41 test):**
- `Auth/*Test.php` (18) — Breeze auth flow (login, register, password reset, dll)
- `ProfileObserverTest.php` (9) — observer + validasi permintaan
- `OnboardingRenderTest.php` (2) — render onboarding
- `DashboardRenderTest.php` (4) — render dashboard
- `AllViewsRenderTest.php` (4) — smoke test semua route

### Factory Pendukung

`database/factories/ProfileFactory.php` — generate profil dengan 5 atribut lengkap.

### Menjalankan Test

```bash
# Semua test
php artisan test

# Atau via phpunit langsung
vendor/bin/phpunit

# Filter test tertentu
vendor/bin/phpunit --filter="ContentBasedFilteringServiceTest"

# Lint dengan Pint
vendor/bin/pint --dirty
```

---

## 16. Build & Jalankan

### Build Frontend Asset

```bash
npm install
npm run dev        # development (watch)
npm run build      # production
```

### Jalankan Server

```bash
php artisan serve
# aplikasi berjalan di http://localhost:8000
```

### Setup Pertama Kali (Fresh)

```bash
composer install
cp .env.example .env
php artisan key:generate

# Edit .env: set DB_CONNECTION=mysql, DB_DATABASE, dll

# Buat database dev + testing di MySQL
php artisan migrate:fresh --seed

# Build asset
npm install && npm run dev

# Jalankan
php artisan serve
```

### Akun Default (dari seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@udinus.ac.id` | `password123` |
| Mahasiswa | (daftar via `/register` dengan email `@mhs.dinus.ac.id`) | — |

---

## 📋 Ringkasan Alur Pengguna

### Mahasiswa Baru

```
/register
   │ (isi nama, NIM, email @mhs.dinus.ac.id, password, fakultas, prodi, semester, angkatan)
   ▼
auto-login → /profil (onboarding)
   │ (isi 5 preferensi: minat, tujuan, gaya, jadwal, mode + kontak WA/IG)
   ▼
ProfilObserver.saved() → trigger recalc similarity (forward + reverse)
   ▼
/dashboard (beranda: greeting, stat, top 3 rekomendasi)
   │
   ├── /rekomendasi (top 10 + filter fakultas/prodi)
   │      └── /rekomendasi/{id} → detail kandidat
   │             └── Ajukan permintaan belajar
   │
   ├── /permintaan (daftar terkirim/diterima)
   │      ├── Accept / Reject (kalau diterima)
   │      └── Cancel (kalau pengirim, status pending)
   │
   ├── /profil (edit preferensi)
   └── /setting (ubah password, hapus akun)
```

### Admin

```
/admin/dashboard
   ├── /admin/mahasiswa (daftar + toggle status)
   ├── /admin/fakultas (CRUD)
   ├── /admin/prodi (CRUD)
   └── /admin/opsi (CRUD opsi preferensi)
```

---

## 🎯 Fitur Utama yang Diimplementasi

1. ✅ **Registrasi kustom** — email `@mhs.dinus.ac.id`, field `nama`/`nim`, auto-login
2. ✅ **Onboarding profil** — 5 form preferensi terpisah (minat, tujuan, gaya, jadwal, mode) + kontak
3. ✅ **Content-Based Filtering** — cosine similarity, uniform weight, 60 dimensi vektor
4. ✅ **Auto-recalc skor** — observer 2 arah (forward + reverse)
5. ✅ **Rekomendasi Top 10** — dengan filter fakultas/prodi, badge status hubungan
6. ✅ **Permintaan belajar** — state machine pending→accepted/rejected, cegah duplikat
7. ✅ **Kontak terkunci** — WA/IG hanya terlihat setelah diterima
8. ✅ **Panel admin** — dashboard statistik, CRUD master data, manajemen mahasiswa
9. ✅ **Design system terpusat** — tema navy profesional konsisten di semua halaman
10. ✅ **Testing** — 57 test (algoritma CBF, observer, render semua view)

---

## 📁 Estimasi Total File

| Kelompok | Jumlah |
|----------|--------|
| Migration | 8 |
| Model | 7 |
| Controller | 16 (3 admin + 9 auth + 4 root) |
| Service | 1 (CBF) |
| Observer | 1 |
| Middleware | 2 (role, status) |
| Seeder | 5 |
| View (.blade.php) | 35 |
| Request (FormRequest) | 1 (PermintaanStoreRequest) |
| Factory | 1 (ProfileFactory) |
| Test | 14 (57 method) |
| **Total kode inti** | **~91 file** |

---

## ⚠️ Catatan Penting

1. **Email domain** — HANYA menerima `@mhs.dinus.ac.id` (regex di registrasi).
2. **Testing pakai MySQL** — bukan SQLite in-memory. Buat database `teman_belajar_udinus_testing` dulu.
3. **Observer penting** — jangan hapus `Profile::observe()` di AppServiceProvider, atau skor tidak akan terupdate otomatis.
4. **Slot jadwal** — migration #7 menambahkan "Sabtu Siang" & "Minggu Siang". Jalankan `php artisan migrate` atau `db:seed --class=OpsiPreferensiSeeder`.
5. **Design system** — semua perubahan style sebaiknya lewat `layouts/partials/theme.blade.php`, bukan inline CSS per halaman.

---

*Dokumentasi terakhir diperbarui: Juli 2026*
