# Upgrade v2.0

Versi ini memperbaiki filter sinkronisasi SIMPEG menjadi `KODE_SATKER_2`, menambahkan pencarian pada semua tabel utama dashboard, dan menggabungkan tampilan GTK dengan data pegawai SIMPEG berdasarkan NIP.

## Konfigurasi SIMPEG

Tambahkan atau sesuaikan nilai berikut pada `.env`:

```dotenv
SIMPEG_REQUEST_SATKER_CODE="02090000000000"
SIMPEG_KODE_SATKER_2="02090325000000"
```

- `SIMPEG_REQUEST_SATKER_CODE` dikirim sebagai parameter `satker` ke endpoint `/v1/pegawai`.
- `SIMPEG_KODE_SATKER_2` menjadi filter wajib pada setiap baris respons sebelum disimpan.
- Konfigurasi lama `SIMPEG_SATKER_CODE` masih dibaca sebagai fallback, tetapi sebaiknya diganti dengan nama baru di atas.

## Perintah Upgrade

```bash
php artisan migrate
php artisan optimize:clear
```

Setelah itu, buka **Admin → Sinkron SIMPEG** dan jalankan sinkronisasi ulang agar seluruh data GTK memperoleh atribut SIMPEG terbaru.

Jangan menjalankan `php artisan migrate:fresh` pada website yang sudah berisi data produksi.
