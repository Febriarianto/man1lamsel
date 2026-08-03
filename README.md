# MAN 1 Lampung Selatan — Modern School CMS v2.0

Portal sekolah dan CMS modern berbasis **Laravel 12**, **PHP 8.2+**, dan **MySQL**. Versi 2.0 menyaring pegawai berdasarkan `KODE_SATKER_2 = 02090325000000`, menggabungkan data GTK dengan atribut SIMPEG berdasarkan NIP, serta menyediakan pencarian pada seluruh tabel utama dashboard.

## Fitur Utama

### Website Publik

- Beranda modern dengan banner dinamis.
- Berita, artikel, pengumuman, prestasi, agenda, pencarian, profil, guru/staf, galeri, dan infografis.
- Navbar bertingkat, branding dinamis, SEO global dan per konten, Open Graph, sitemap, dan robots.
- Halaman baca berita responsif dengan lebar teks, gambar sampul, tipografi, dan tombol berbagi yang proporsional.
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
- Summernote sebagai editor visual untuk seluruh isi berita, artikel, dan halaman profil.
- Sinkronisasi data pegawai SIMPEG dengan pagination, filter wajib `KODE_SATKER_2`, upsert NIP, riwayat proses, dan pembaruan GTK.
- Tabel GTK menggunakan `LEFT JOIN` dengan pegawai SIMPEG untuk menampilkan status pegawai, pangkat/golongan, pendidikan, kontak, jabatan, unit, dan waktu sinkron terakhir.
- Pencarian server-side tersedia pada seluruh tabel utama dashboard dan tetap aktif saat berpindah halaman.

### Ruang Penulis Guru/Pegawai

- Masuk menggunakan akun manual atau SSO Kemenag.
- Akun dibuat otomatis saat login pertama jika auto-provision aktif dan NIP terdaftar sebagai GTK aktif.
- Akun manual dapat ditautkan ke GTK lalu digunakan untuk login manual maupun SSO.
- Role awal `author` atau Penulis Artikel.
- Hanya dapat melihat artikel miliknya sendiri.
- Artikel selalu disimpan sebagai draft.
- Hanya dapat mengedit atau menghapus draft miliknya sendiri.
- Artikel yang telah diterbitkan hanya dapat diubah administrator.
- Nama penulis, NIP, email, foto, dan unit kerja disinkronkan dari profil SIMPEG.

## Login Manual Penulis

Administrator dapat membuka **Pengguna & Penulis → Tambah Penulis Manual**, kemudian mengisi nama, email, kata sandi, NIP, dan unit kerja. Akun baru otomatis memiliki role `author` dan dapat langsung memakai halaman login CMS yang sama dengan administrator.

Penulis manual hanya dapat melihat artikel miliknya sendiri. Artikel selalu disimpan sebagai draft dan baru dapat diterbitkan oleh administrator. Penulis dapat mengganti kata sandinya melalui **Akun Saya**, sedangkan administrator dapat meresetnya melalui halaman edit pengguna.

## Format Slug

Slug konten memakai underscore, misalnya:

```text
judul_berita_baru
```

Slug dikonversi otomatis dari judul maupun input manual. Tautan lama yang masih menggunakan tanda minus tetap dapat dibuka untuk menjaga kompatibilitas.

## Kebutuhan Server

- PHP 8.2 atau lebih baru.
- Ekstensi PHP: BCMath, Ctype, cURL, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer, XML.
- Composer 2.x.
- MySQL 5.7+ atau MySQL 8.x.
- Apache atau Nginx.
- APP ID aplikasi yang sudah disetujui pada portal API Kemenag.

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

Daftarkan callback berikut pada portal API Kemenag:

```text
https://domain.sch.id/admin/sso/kemenag/callback
```

Kemudian isi APP ID pada `.env`:

```dotenv
KEMENAG_SSO_ENABLED=true
KEMENAG_SSO_APP_ID="APP_ID_DARI_KEMENAG"
KEMENAG_SSO_SIGNIN_URL="https://sso.kemenag.go.id/auth/signin"
KEMENAG_SSO_VERIFY_URL="https://sso.kemenag.go.id/auth/verify"
KEMENAG_SSO_SIGNOUT_URL="https://sso.kemenag.go.id/auth/signout"
KEMENAG_SSO_VERIFY_METHOD=POST
KEMENAG_SSO_REQUIRE_STAFF_MATCH=true
```

Setelah mengubah `.env`, jalankan `php artisan optimize:clear`. Baca panduan lengkap di `SSO_KEMENAG.md`.

## Keamanan SSO

- Mengikuti alur APP ID, callback token, dan endpoint verify SIMPEG.
- Token diverifikasi sebagai Bearer token dari server dan tidak disimpan.
- Callback hanya diterima setelah sesi login dimulai dari website dan belum kedaluwarsa.
- NIP secara bawaan wajib cocok dengan data GTK aktif.
- Pengguna SSO baru otomatis menjadi penulis, bukan administrator.
- Akun administrator tidak pernah ditautkan otomatis.
- Admin dapat menonaktifkan akun dari menu **Pengguna & Penulis**.

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
