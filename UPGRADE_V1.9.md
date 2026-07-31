# Upgrade ke v1.9 — Sinkronisasi Pegawai SIMPEG

Backup database dan `.env`, kemudian jalankan:

```bash
composer dump-autoload
php artisan migrate
php artisan optimize:clear
php artisan test
```

Tambahkan konfigurasi `SIMPEG_*` dari `.env.example`, isi kredensial API, dan pertahankan:

```dotenv
SIMPEG_SATKER_CODE="02090325000000"
```

Panduan lengkap tersedia pada `SIMPEG_SYNC.md`.
