# MAN 1 Lampung Selatan — Modern School CMS v1.6

Portal sekolah dan CMS modern berbasis **Laravel 12**, **PHP 8.2+**, dan **MySQL**. Versi 1.6 menambahkan akun penulis manual dengan email dan kata sandi, tanpa menghilangkan login SSO Kemenag maupun Theme Color Manager.

## Fitur Utama

### Website Publik

- Beranda modern dengan banner dinamis.
- Berita, artikel, pengumuman, prestasi, agenda, pencarian, profil, guru/staf, galeri, dan infografis.
- Navbar bertingkat, branding dinamis, SEO global dan per konten, Open Graph, sitemap, dan robots.
- Nama penulis tampil pada halaman artikel dan tetap tersimpan walaupun data akun kemudian berubah.
- Tema publik dinamis dengan komposisi awal Putih 75%, Biru 18%, Kuning 5%, dan Hitam/Abu 2%.
- Motif geometris dan strip identitas mengikuti desain kartu identitas murid.

### Dashboard Administrator

- Mengelola seluruh konten, halaman, guru/staf, galeri, infografis, agenda, banner, tautan, menu, SEO, dan pesan.
- Melihat seluruh artikel dari guru/pegawai beserta nama penulis dan unit kerjanya.
- Meninjau draft artikel, mengubah, lalu menerbitkannya.
- Mengelola pengguna, role, status aktif, NIP, unit kerja, dan metode login.
- Membuat akun penulis manual serta menetapkan atau mereset kata sandinya.
- Login lokal administrator tetap tersedia.
- Theme Color Manager dengan live preview, preset warna, pengaturan komposisi, dan pilihan penerapan warna ke dashboard.

### Ruang Penulis Guru/Pegawai

- Masuk menggunakan akun manual atau SSO Kemenag.
- Akun dibuat otomatis saat login pertama jika auto-provision aktif.
- Role awal `author` atau Penulis Artikel.
- Hanya dapat melihat artikel miliknya sendiri.
- Artikel selalu disimpan sebagai draft.
- Hanya dapat mengedit atau menghapus draft miliknya sendiri.
- Artikel yang telah diterbitkan hanya dapat diubah administrator.
- Nama penulis, NIP, email, dan unit kerja disinkronkan dari claim SSO.

## Login Manual Penulis

Administrator dapat membuka **Pengguna & Penulis → Tambah Penulis Manual**, kemudian mengisi nama, email, kata sandi, NIP, dan unit kerja. Akun baru otomatis memiliki role `author` dan dapat langsung memakai halaman login CMS yang sama dengan administrator.

Penulis manual hanya dapat melihat artikel miliknya sendiri. Artikel selalu disimpan sebagai draft dan baru dapat diterbitkan oleh administrator. Penulis dapat mengganti kata sandinya melalui **Akun Saya**, sedangkan administrator dapat meresetnya melalui halaman edit pengguna.

## Kebutuhan Server

- PHP 8.2 atau lebih baru.
- Ekstensi PHP: BCMath, Ctype, cURL, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer, XML.
- Composer 2.x.
- MySQL 5.7+ atau MySQL 8.x.
- Apache atau Nginx.
- Kredensial dan endpoint aplikasi dari pengelola SSO Kemenag.

Frontend menggunakan Bootstrap melalui CDN sehingga tidak membutuhkan npm atau Vite.

## Instalasi Baru

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database:

```sql
CREATE DATABASE mansalase_modern CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atur koneksi database pada `.env`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

Akses:

- Website: `http://127.0.0.1:8000`
- CMS: `http://127.0.0.1:8000/admin/login`

Akun administrator awal:

```text
Email    : admin@mansalase.sch.id
Password : admin12345
```

Segera ubah kata sandi pada menu **Akun Saya**.

## Upgrade dari v1.4 ke v1.5

Backup database, `.env`, dan `storage/app/public`, lalu salin patch v1.5 ke root project. Jalankan:

```bash
composer dump-autoload
php artisan db:seed --class=Database\Seeders\UpgradeV15Seeder
php artisan optimize:clear
```

Tidak ada perubahan struktur tabel pada v1.5. Seeder hanya menambahkan pengaturan tema yang belum tersedia dan tidak menghapus data lama.

Setelah login, buka **Tampilan → Pengaturan, Tema & SEO**.

## Upgrade dari v1.3 ke v1.4

Backup database, `.env`, dan `storage/app/public`, lalu salin patch v1.4 ke root project. Jalankan:

```bash
composer dump-autoload
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\UpgradeV14Seeder
php artisan optimize:clear
```

Jangan menjalankan `php artisan migrate:fresh` pada database yang sudah berisi data.

## Theme Color Manager

Pengaturan tema tersedia pada **Admin → Tampilan → Pengaturan, Tema & SEO**.

Fitur yang tersedia:

- Preset Kartu Identitas, Ocean Blue, Emerald Madrasah, dan Maroon Gold.
- Delapan warna dapat diubah dengan color picker atau kode HEX.
- Komposisi warna dapat disesuaikan, dengan total wajib 100%.
- Preview tema berubah langsung sebelum disimpan.
- Motif geometris dapat diaktifkan atau dimatikan.
- Tema dapat diterapkan atau tidak diterapkan ke dashboard administrator.
- Warna disimpan di database, sehingga tidak perlu mengedit CSS.

Baca panduan lengkap di `THEME_MANAGER.md`.

## Konfigurasi SSO Kemenag

Salin blok `KEMENAG_SSO_*` dari `.env.example` ke `.env`, lalu isi nilai yang diberikan pengelola Identity Provider Kemenag:

```dotenv
KEMENAG_SSO_ENABLED=true
KEMENAG_SSO_CLIENT_ID=client-id-aplikasi
KEMENAG_SSO_CLIENT_SECRET=client-secret-aplikasi
KEMENAG_SSO_REDIRECT_URI="https://domain.sch.id/admin/sso/kemenag/callback"
KEMENAG_SSO_AUTHORIZATION_URL="https://alamat-sso/authorize"
KEMENAG_SSO_TOKEN_URL="https://alamat-sso/token"
KEMENAG_SSO_USERINFO_URL="https://alamat-sso/userinfo"
KEMENAG_SSO_SCOPES="openid,profile,email"
KEMENAG_SSO_DEFAULT_ROLE=author
```

Setelah mengubah `.env`:

```bash
php artisan optimize:clear
```

Callback URL yang harus didaftarkan pada SSO:

```text
https://domain.sch.id/admin/sso/kemenag/callback
```

Nama claim dapat disesuaikan tanpa mengubah kode:

```dotenv
KEMENAG_SSO_CLAIM_ID=sub
KEMENAG_SSO_CLAIM_NAME=name
KEMENAG_SSO_CLAIM_EMAIL=email
KEMENAG_SSO_CLAIM_NIP=nip
KEMENAG_SSO_CLAIM_UNIT=unit_name
KEMENAG_SSO_CLAIM_AVATAR=picture
```

Claim bertingkat juga didukung, misalnya:

```dotenv
KEMENAG_SSO_CLAIM_NIP="pegawai.nip"
KEMENAG_SSO_CLAIM_UNIT="pegawai.satuan_kerja.nama"
```

Baca panduan lengkap di `SSO_KEMENAG.md`.

## Keamanan SSO

- Menggunakan Authorization Code Flow.
- Memvalidasi parameter `state` untuk mencegah pemalsuan callback.
- Profil pengguna diambil menggunakan access token dari endpoint UserInfo.
- Client secret hanya disimpan di `.env` dan tidak dimasukkan ke database.
- Pengguna SSO baru otomatis menjadi penulis, bukan administrator.
- Admin dapat menonaktifkan akun dari menu **Pengguna & Penulis**.
- Pembatasan domain email dapat diaktifkan melalui `KEMENAG_SSO_ALLOWED_EMAIL_DOMAINS`.

## Data Baru v1.4

Tabel `users` memperoleh kolom:

- `auth_provider`
- `provider_id`
- `nip`
- `unit_name`
- `avatar`
- `active`
- `last_login_at`

Tabel `posts` memperoleh kolom `author_name` sebagai snapshot nama penulis. Relasi `author_id` tetap digunakan untuk menghubungkan artikel dengan akun penulis.

## Data Baru v1.5

Tidak ada tabel atau kolom baru. Versi ini menambahkan key pengaturan pada tabel `settings`, meliputi palet warna, komposisi, preset, motif geometris, dan opsi penerapan tema ke dashboard.

## Deploy Produksi

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\UpgradeV14Seeder --force
php artisan db:seed --class=Database\\Seeders\\UpgradeV15Seeder --force
php artisan storage:link
php artisan optimize
```

Pastikan:

- `APP_URL` memakai HTTPS dan domain produksi.
- `APP_DEBUG=false`.
- Redirect URI SSO sama persis dengan yang didaftarkan.
- `KEMENAG_SSO_VERIFY_SSL=true` pada produksi.
- Folder `storage` dan `bootstrap/cache` writable.
