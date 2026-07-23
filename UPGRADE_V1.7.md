# Upgrade v1.6 ke v1.7

Versi 1.7 mencakup:

- Summernote untuk editor isi berita, artikel, dan halaman profil.
- Slug otomatis dengan pemisah underscore.
- Konversi slug data lama dan URL menu profil.
- Kompatibilitas tautan lama yang memakai tanda minus.
- Tata letak halaman baca berita yang lebih proporsional dan responsif.

## Cara upgrade

Backup database terlebih dahulu, kemudian jalankan:

```bash
composer dump-autoload
php artisan migrate
php artisan optimize:clear
```

Migration akan mengubah slug lama, misalnya `judul-berita` menjadi `judul_berita`. Jika ditemukan slug yang sama setelah normalisasi, sistem menambahkan nomor urut agar nilai tetap unik.

Summernote dimuat melalui CDN sehingga project tetap tidak memerlukan npm atau Vite.
