# Upgrade ke v1.8 — SSO SIMPEG Kemenag

Versi ini mengganti adapter OAuth generik dengan alur token SIMPEG sesuai tutorial resmi Kementerian Agama dan menambahkan relasi akun penulis dengan data GTK.

## Langkah Upgrade

Backup database, `.env`, dan `storage/app/public`, lalu jalankan:

```bash
composer dump-autoload
php artisan migrate
php artisan optimize:clear
php artisan test
```

Jangan menjalankan `php artisan migrate:fresh` pada database yang sudah berisi data.

## Konfigurasi

Tambahkan blok SSO terbaru dari `.env.example` ke `.env`, kemudian isi:

```dotenv
KEMENAG_SSO_ENABLED=true
KEMENAG_SSO_APP_ID="APP_ID_DARI_KEMENAG"
```

Endpoint resmi sudah memiliki nilai bawaan. Baca `SSO_KEMENAG.md` untuk pendaftaran callback dan pengaturan data GTK.

## Perubahan Database

- `staff.nip`: NIP unik untuk pencocokan identitas SIMPEG.
- `users.staff_id`: relasi satu akun ke satu data GTK.

Akun lokal yang berhasil ditautkan melalui SSO menggunakan provider `local_kemenag_sso`, sehingga login manual tetap berfungsi.
