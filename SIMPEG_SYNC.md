# Sinkronisasi Data Pegawai SIMPEG

Modul ini mengambil data pegawai dari API Kemenag, melakukan pagination maksimal 400 data per request, lalu menyimpan atau memperbarui data berdasarkan NIP baru.

## Keamanan

- Email dan kata sandi API hanya disimpan pada `.env`.
- Token API hanya berada di memori selama sinkronisasi.
- Token dan kata sandi tidak disimpan ke database atau log.
- Hanya administrator yang dapat menjalankan sinkronisasi dari dashboard.
- Sinkronisasi bersamaan dicegah dengan application lock.
- Data dari kode satuan kerja lain tetap ditolak walaupun API mengirimkannya.

Kredensial yang pernah ditulis langsung dalam source code atau dibagikan melalui pesan sebaiknya segera diganti.

## Konfigurasi `.env`

```dotenv
SIMPEG_API_BASE_URL="https://api.kemenag.go.id/v1"
SIMPEG_API_EMAIL="EMAIL_API_ANDA"
SIMPEG_API_PASSWORD="KATA_SANDI_API_ANDA"
SIMPEG_SATKER_CODE="02090325000000"
SIMPEG_PAGE_SIZE=400
SIMPEG_API_TIMEOUT=60
SIMPEG_VERIFY_SSL=true
SIMPEG_SYNC_STAFF=true
```

Setelah mengubah `.env`:

```bash
php artisan optimize:clear
```

`SIMPEG_SATKER_CODE` diperlakukan sebagai string agar angka nol di depan tidak hilang.

## Menjalankan dari Dashboard

1. Masuk sebagai administrator.
2. Buka **Sinkron SIMPEG** pada sidebar.
3. Pastikan kode satuan kerja tampil `02090325000000`.
4. Tekan **Sinkronkan Sekarang**.
5. Periksa ringkasan dan riwayat sinkronisasi.

URL halaman:

```text
/admin/simpeg
```

## Menjalankan dari Terminal

```bash
php artisan simpeg:sync
```

Perintah menampilkan jumlah data yang dilaporkan API, diambil, sesuai filter, ditambahkan, diperbarui, dilewati, serta hasil sinkronisasi GTK.

## Filter Dua Lapis

Request ke endpoint `/v1/pegawai` selalu membawa:

```text
satker=02090325000000
```

Sebelum penyimpanan, aplikasi memeriksa kembali `KODE_SATUAN_KERJA` atau `KODE_SATKER_1`. Baris yang tidak sama dengan `02090325000000` dihitung sebagai dilewati dan tidak masuk database.

## Sinkronisasi GTK

Jika:

```dotenv
SIMPEG_SYNC_STAFF=true
```

pegawai yang lolos filter akan dibuat atau diperbarui pada menu GTK:

- NIP baru menjadi identitas utama.
- Nama dan jabatan diperbarui dari SIMPEG.
- Jenis GTK ditentukan dari jabatan: kepala madrasah, guru, atau pegawai.
- Data GTK diaktifkan.
- Akun pengguna tidak dibuat oleh proses ini.

Ketika pemilik NIP login melalui SSO Kemenag, sistem dapat menautkan atau membuat akun penulis sesuai kebijakan SSO yang sudah tersedia.

## Data dan Log

- `simpeg_employees`: arsip data pegawai terakhir.
- `simpeg_sync_logs`: riwayat berhasil/gagal beserta jumlah data.
- `staff`: data publik GTK yang digunakan website dan SSO.

Sinkronisasi tidak menghapus pegawai yang tidak lagi muncul di API. Penonaktifan tetap dilakukan administrator agar data publik tidak berubah tanpa peninjauan.
