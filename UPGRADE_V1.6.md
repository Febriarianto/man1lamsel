# Upgrade v1.5 ke v1.6

Versi 1.6 menambahkan login manual bagi penulis artikel.

## Fitur baru

- Administrator dapat membuat akun penulis lokal.
- Penulis masuk menggunakan email dan kata sandi melalui halaman login CMS.
- Administrator dapat mereset kata sandi akun lokal.
- Akun SSO Kemenag tetap dikelola terpisah dan tidak menerima password lokal.
- Pembatasan artikel penulis tetap berlaku: hanya artikel sendiri dan selalu berstatus draft.

## Cara upgrade

Tidak ada migration atau perubahan struktur database pada versi ini. Setelah menyalin file versi 1.6 ke project, jalankan:

```bash
composer dump-autoload
php artisan optimize:clear
```

Login sebagai administrator, lalu buka:

```text
Pengguna & Penulis → Tambah Penulis Manual
```
