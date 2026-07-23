# 🚀 Panduan Deploy ke VPS

Panduan deploy **Teman Belajar Udinus** ke VPS Ubuntu/Debian menggunakan **Nginx + PHP-FPM + MySQL**.

> Asumsi: VPS baru dengan akses `root`/`sudo`, domain sudah mengarah ke IP VPS.
>
> 📌 **Pakai subdomain?** Langkah lengkap arah DNS + konfigurasi Nginx + SSL ada di bagian **[🌐 Implementasi Domain (Subdomain)](#-implementasi-domain-subdomain)** di akhir dokumen ini.

---

## 1. Spesifikasi Minimum

| Komponen | Versi |
|---|---|
| OS | Ubuntu 22.04 / 24.04 LTS (atau Debian 12) |
| PHP | 8.2+ |
| Web Server | Nginx |
| Database | MySQL 8 / MariaDB 10.6+ |
| Node.js | 18+ (hanya untuk build asset) |
| Composer | 2.x |

---

## 2. Install Dependency Server

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.2 + ekstensi yang dibutuhkan Laravel
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl \
    php8.2-readline unzip git

# Nginx, MySQL, Node.js, Composer
sudo apt install -y nginx mysql-server
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

---

## 3. Konfigurasi MySQL

Amankan instalasi MySQL:

```bash
sudo mysql_secure_installation
```

Buat database & user khusus (jangan pakai `root`):

```bash
sudo mysql
```

```sql
CREATE DATABASE teman_belajar_udinus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'teman_belajar'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT_DI_SINI';
GRANT ALL PRIVILEGES ON teman_belajar_udinus.* TO 'teman_belajar'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Deploy Kode

Pilih salah satu cara:

### Opsi A — Git clone (disarankan)

```bash
sudo mkdir -p /var/www/teman-belajar
sudo chown $USER:$USER /var/www/teman-belajar
git clone <repo-url> /var/www/teman-belajar
cd /var/www/teman-belajar
```

### Opsi B — Upload manual

Upload file proyek (tanpa `vendor/` dan `node_modules/`) ke `/var/www/teman-belajar` via SSH/SCP/rsync.

---

## 5. Konfigurasi Aplikasi

```bash
cd /var/www/teman-belajar

# Install dependensi PHP (tanpa dev dependency)
composer install --no-dev --optimize-autoloader

# Build asset frontend
npm install
npm run build

# Konfigurasi environment produksi
cp .env.production.example .env
php artisan key:generate
```

Edit `.env`:

```bash
nano .env
```

Pastikan diisi dengan benar:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_DATABASE=teman_belajar_udinus
DB_USERNAME=teman_belajar
DB_PASSWORD=PASSWORD_KUAT_DI_SINI
```

> ⚠️ **Penting:**
> - Jika `DB_PASSWORD` mengandung karakter khusus (`#`, `=`, spasi, `$`), **bungkus dengan tanda kutip**, mis. `DB_PASSWORD="rahasia#123"`. Tanda `#` tanpa kutip dianggap awal komentar dan password terpotong.
> - Pastikan `APP_MAINTENANCE_DRIVER=file` (bukan `database`) untuk VPS single — driver `database` butuh tabel maintenance tersendiri dan dapat menyebabkan error `Driver [database] not supported`.
> - Setelah edit `.env`, **wajib** jalankan `php artisan config:cache` agar perubahan dibaca.

---

## 6. Migrasi & Optimasi

```bash
# Migrasi database + seed data awal (fakultas, prodi, opsi preferensi, admin)
php artisan migrate --seed --force

# Optimasi produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> ⚠️ Setiap kali mengubah `.env`, jalankan ulang `php artisan config:cache`.

### Link storage (jika ada upload file)

```bash
php artisan storage:link
```

---

## 7. Set Permission Folder

```bash
sudo chown -R www-data:www-data /var/www/teman-belajar
sudo find /var/www/teman-belajar -type d -exec chmod 755 {} \;
sudo find /var/www/teman-belajar -type f -exec chmod 644 {} \;

# Folder yang harus writable
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

---

## 8. Konfigurasi Nginx

Buat virtual host:

```bash
sudo nano /etc/nginx/sites-available/teman-belajar
```

Isi (ganti `domain-anda.com`):

```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/teman-belajar/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan & uji:

```bash
sudo ln -s /etc/nginx/sites-available/teman-belajar /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 9. HTTPS (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

Certbot akan otomatis mengatur SSL dan redirect HTTP→HTTPS.

---

## 10. Queue Worker (opsional, untuk job background)

Jika nantinya memakai queue/job, jalankan worker via systemd:

```bash
sudo nano /etc/systemd/system/teman-belajar-queue.service
```

```ini
[Unit]
Description=Teman Belajar Udinus - Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/teman-belajar/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable teman-belajar-queue
sudo systemctl start teman-belajar-queue
```

---

## 11. Cek Deploy

```bash
# Health check bawaan Laravel
curl http://localhost/up      # harus keluar "up"

# Lihat log aplikasi bila error
tail -f /var/www/teman-belajar/storage/logs/laravel.log
```

Buka domain di browser. Login dengan akun admin hasil seeder (lihat `database/seeders/AdminSeeder.php`).

---

## 🔄 Update Aplikasi (deploy ulang)

```bash
cd /var/www/teman-belajar

git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart worker jika ada
sudo systemctl restart teman-belajar-queue

# (opsional) zero-downtime restart php-fpm
sudo systemctl reload php8.2-fpm
```

---

## 🧰 Mode Maintenance

```bash
php artisan down                # matikan akses, tampilkan halaman 503
php artisan up                  # hidupkan kembali
```

---

## 🛡️ Checklist Keamanan Produksi

- [x] `APP_ENV=production`
- [x] `APP_DEBUG=false`
- [x] `APP_KEY` sudah di-generate (bukan kosong)
- [x] `DB_PASSWORD` kuat & user DB bukan `root`
- [x] HTTPS aktif (Let's Encrypt)
- [x] Firewall: hanya buka port 22 (SSH), 80 (HTTP), 443 (HTTPS)
- [x] `storage/` dan `bootstrap/cache/` writable oleh `www-data`
- [x] File `.env` TIDAK di-commit (sudah di `.gitignore`)

---

## ❓ Troubleshooting

| Gejala | Solusi |
|---|---|
| Halaman blank / 500 | Cek `storage/logs/laravel.log`. Pastikan permission `storage/` benar. |
| 500 setelah ubah `.env` | Jalankan `php artisan config:cache` ulang. |
| Asset CSS/JS tidak muncul | Jalankan `npm run build`, pastikan `public/build/` ada. |
| `php artisan` error "No application encryption key" | Jalankan `php artisan key:generate`. |
| Login/redirect aneh | Pastikan `APP_URL` & `SESSION_DOMAIN` sesuai domain. |
| 502 Bad Gateway | Cek `php8.2-fpm` berjalan: `sudo systemctl status php8.2-fpm`. |

---

## 🌐 Implementasi Domain (Subdomain)

Panduan khusus memasang aplikasi pada **subdomain** (mis. `temanbelajar.namadomain.com`) di VPS yang sudah ada/mungkin sudah menampung web lain.

> Contoh domain di bawah: `temanbelajar.namadomain.com`. Ganti dengan subdomain Anda.

### A. Arahkan DNS (di dashboard registrar/domain)

1. Login ke dashboard tempat Anda beli domain (Cloudflare, Niagahoster, Namecheap, dll).
2. Buka **DNS Management / Zone Editor**.
3. Tambah **A record** baru:

   | Type | Name / Host | Value / Target | TTL |
   |---|---|---|---|
   | A | `temanbelajar` | `IP_VPS_ANDA` | Auto / 3600 |

   - `Name` = bagian subdomain (`temanbelajar`), BUKAN full domain.
   - `IP_VPS_ANDA` = IP publik VPS Anda (cek: `curl ifconfig.me` di VPS).

4. (Opsional tapi disarankan) Tambah juga `AAAA` record kalau VPS punya IPv6.

5. Tunggu propagasi DNS (biasanya 1–15 menit). Cek dengan:

   ```bash
   # Jalankan di komputer lokal Anda (bukan di VPS)
   dig temanbelajar.namadomain.com +short
   # atau
   nslookup temanbelajar.namadomain.com
   ```

   Harus mengembalikan IP VPS Anda. Kalau belum, tunggu lagi.

> 💡 **Pakai Cloudflare?** Bisa tambah record tipe `CNAME` mengarah ke domain utama, atau `A` langsung ke IP. Kalau proxy Cloudflare (oranye) aktif, SSL otomatis dari Cloudflare — tapi tetap disarankan pasang Let's Encrypt di Nginx (lihat langkah D).

### B. Buat file Nginx untuk subdomain

Buat virtual host baru terpisah dari domain utama:

```bash
sudo nano /etc/nginx/sites-available/teman-belajar
```

Isi (perhatikan `server_name` = subdomain Anda):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name temanbelajar.namadomain.com;
    root /var/www/teman-belajar/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan & uji:

```bash
sudo ln -s /etc/nginx/sites-available/teman-belajar /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Cek HTTP dulu (sebelum pasang SSL):

```bash
curl -I http://temanbelajar.namadomain.com/up   # harus 200 OK dan keluar "up"
```

### C. Sesuaikan `.env` di server

Edit `.env` di VPS:

```bash
nano /var/www/teman-belajar/.env
```

Pastikan tiga baris ini sesuai subdomain:

```dotenv
APP_URL=https://temanbelajar.namadomain.com
SESSION_DOMAIN=temanbelajar.namadomain.com
APP_DEBUG=false
```

Lalu re-cache config:

```bash
php artisan config:cache
```

> ⚠️ Setelah ubah `.env`, **wajib** jalankan `php artisan config:cache` lagi, jika tidak perubahan tidak berlaku.

### D. Pasang SSL / HTTPS (Let's Encrypt)

Aplikasi Laravel perlu HTTPS agar cookie session aman & tidak ada warning browser. Pakai Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d temanbelajar.namadomain.com
```

Certbot akan:
- Mengambil sertifikat SSL gratis (Let's Encrypt).
- Otomatis mengubah blok Nginx agar listen 443 + redirect HTTP→HTTPS.
- Memasang auto-renewal (cek: `sudo certbot renew --dry-run`).

Cek hasil:

```bash
curl -I https://temanbelajar.namadomain.com/up
```

Harus `HTTP/2 200`. Buka subdomain di browser → ikon gembok 🔒 muncul.

### E. Kenapa Trusted Proxy sudah diatur?

Aplikasi berjalan di belakang Nginx (reverse proxy). Tanpa konfigurasi `trustProxies`, Laravel melihat request sebagai `http://` (bukan `https://`) sehingga:
- URL asset (CSS/JS) di-generate sebagai `http://` → browser blok (mixed content).
- Redirect setelah login bisa loop `http`↔`https`.

Kode sudah mengaturnya di `bootstrap/app.php`:

```php
$middleware->trustProxies(at: '*');
$middleware->trustHosts(at: ['*']);
```

Jadi Anda **tidak perlu** set `APP_URL` pakai forceScheme manual — cukup `APP_URL=https://...` di `.env` dan trusted proxy aktif.

### F. Bila subdomain tidak terbuka

| Gejala | Cek |
|---|---|
| `dig` tidak balik IP VPS | DNS belum propagate / record salah. Tunggu atau periksa record A. |
| Browser "connection refused" | Nginx belum reload / port 80/443 ditutup firewall. Buka: `sudo ufw allow 80,443/tcp`. |
| 404 Nginx default page | `server_name` tidak cocok / `root` salah path. Bandingkan dengan blok Nginx di atas. |
| 500 setelah deploy | `php artisan config:cache` belum dijalankan setelah ubah `.env`. |
| Mixed content (asset http) | Trusted proxy belum aktif (sudah default) ATAU `APP_URL` masih `http://`. |
| Redirect loop | Pastikan tidak ada dua blok Nginx sama-sama menangkap subdomain. Hapus duplikat di `sites-enabled/`. |
