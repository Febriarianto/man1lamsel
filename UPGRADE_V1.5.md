# Upgrade MAN 1 Lampung Selatan CMS v1.4 ke v1.5

Versi 1.5 menambahkan Theme Color Manager dan mengganti komposisi visual awal menjadi:

- Putih: 75%
- Biru: 18%
- Kuning: 5%
- Hitam/Abu: 2%

## 1. Backup

Sebelum menyalin patch, cadangkan:

- Database MySQL.
- File `.env`.
- Folder `storage/app/public`.

## 2. Salin patch

Ekstrak isi patch ke root project v1.4, lalu pilih **Replace/Overwrite** ketika Windows meminta konfirmasi.

Jangan menimpa `.env` dan jangan menghapus folder upload.

## 3. Jalankan perintah upgrade

```cmd
composer dump-autoload
php artisan db:seed --class=Database\Seeders\UpgradeV15Seeder
php artisan optimize:clear
```

Tidak ada migration baru pada versi ini. Seeder hanya menambahkan key pengaturan tema yang belum tersedia.

## 4. Atur warna

Login sebagai administrator dan buka:

```text
Tampilan → Pengaturan, Tema & SEO
```

Pilih preset **Kartu Identitas — Biru Kuning**, atau ubah setiap warna secara manual. Total komposisi warna harus 100% sebelum dapat disimpan.

## 5. Jika tampilan lama masih tersimpan

Tekan `Ctrl + F5` pada browser. Pada server produksi yang memakai cache tambahan/CDN, bersihkan cache tersebut juga.

## Larangan

Jangan jalankan:

```cmd
php artisan migrate:fresh
```

Perintah tersebut akan menghapus semua data.
