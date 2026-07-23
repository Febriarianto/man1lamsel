# Upgrade v1.3 ke v1.4

## Backup

Backup sebelum menyalin patch:

- Database MySQL.
- File `.env`.
- Folder `storage/app/public`.

## Instalasi Patch

Ekstrak patch ke root project v1.3 lalu pilih Replace/Overwrite. Patch tidak berisi `.env`, sehingga konfigurasi aktif tidak tertimpa.

Jalankan:

```bash
composer dump-autoload
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\UpgradeV14Seeder
php artisan optimize:clear
```

## Mengaktifkan SSO

Tambahkan blok `KEMENAG_SSO_*` dari `.env.example` ke `.env`, isi kredensial dan endpoint resmi, kemudian:

```bash
php artisan optimize:clear
```

## Perubahan Database

Migration menambahkan informasi SSO pada `users` dan snapshot `author_name` pada `posts`. Artikel lama otomatis memperoleh nama penulis dari relasi `author_id`; jika relasi kosong, nama diisi `Administrator`.

## Pemeriksaan

1. Login admin lokal masih berhasil.
2. Menu **Pengguna & Penulis** tampil untuk administrator.
3. Tombol SSO tampil setelah `KEMENAG_SSO_ENABLED=true`.
4. Login SSO pertama membuat user role Penulis.
5. Penulis hanya melihat artikel sendiri.
6. Artikel baru berstatus Draft.
7. Administrator dapat menerbitkan artikel.
8. Nama penulis tampil pada artikel publik.

## Larangan

Jangan jalankan:

```bash
php artisan migrate:fresh
```

Perintah tersebut menghapus database.
