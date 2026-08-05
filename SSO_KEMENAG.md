# Panduan SSO SIMPEG Kementerian Agama

Integrasi ini mengikuti tutorial resmi:

https://api.kemenag.go.id/user_guide/v.1/simpeg/sso/

Alur pada tutorial tersebut bukan OAuth Authorization Code. Aplikasi mengirim `appid` ke halaman signin, menerima `token` pada callback, lalu memverifikasi token sebagai Bearer token ke endpoint verify.

## 1. Daftarkan Aplikasi

1. Masuk ke https://api.kemenag.go.id/.
2. Daftarkan aplikasi website.
3. Isi URL callback aplikasi:

   ```text
   https://domain-sekolah.sch.id/admin/sso/kemenag/callback
   ```

4. Setelah aplikasi disetujui, salin **APP ID** yang diberikan.

Callback harus memakai HTTPS pada produksi. Domain dan path harus sama persis dengan yang didaftarkan.

## 2. Isi `.env`

```dotenv
KEMENAG_SSO_ENABLED=true
KEMENAG_SSO_LABEL="Masuk dengan SSO Kemenag"
KEMENAG_SSO_APP_ID="APP_ID_DARI_KEMENAG"
KEMENAG_SSO_SIGNIN_URL="https://sso.kemenag.go.id/auth/signin"
KEMENAG_SSO_VERIFY_URL="https://sso.kemenag.go.id/auth/verify"
KEMENAG_SSO_SIGNOUT_URL="https://sso.kemenag.go.id/auth/signout"
KEMENAG_SSO_VERIFY_METHOD=POST
KEMENAG_SSO_CALLBACK_TOKEN_PARAM=token
KEMENAG_SSO_AUTO_PROVISION=true
KEMENAG_SSO_REQUIRE_STAFF_MATCH=true
KEMENAG_SSO_AUTO_LINK_BY_NIP=true
KEMENAG_SSO_LOGIN_ATTEMPT_TTL=600
KEMENAG_SSO_TIMEOUT=15
KEMENAG_SSO_VERIFY_SSL=true
```

Kemudian jalankan:

```bash
php artisan optimize:clear
```

Tombol SSO tampil hanya jika SSO diaktifkan dan APP ID sudah terisi.

## 3. Siapkan Data GTK

Konfigurasi aman bawaan hanya mengizinkan GTK aktif yang NIP-nya sudah terdaftar:

1. Buka **Admin → GTK**.
2. Tambahkan atau edit guru/pegawai.
3. Isi NIP tanpa spasi.
4. Pastikan status GTK aktif.

Jika guru sudah memiliki akun manual, buka **Admin → Pengguna & Penulis**, edit akun, lalu pilih data GTK yang sesuai. Saat login SSO pertama, akun lokal otomatis menjadi **Manual + SSO**. Kata sandi manual tidak dihapus.

## Alur Teknis

1. Pengguna menekan **Masuk dengan SSO Kemenag**.
2. Website mengarahkan ke `https://sso.kemenag.go.id/auth/signin?appid=APP_ID`.
3. Kemenag mengembalikan pengguna ke callback dengan parameter `token`.
4. Server mengirim token sebagai `Authorization: Bearer ...` ke endpoint verify.
5. Profil `pegawai`, terutama `NIP_BARU`, nama, email, foto, dan satuan kerja, dibaca.
6. `NIP_BARU` dicocokkan dengan data GTK dan akun penulis. `NIP` lama hanya digunakan sebagai fallback kompatibilitas.
7. Pengguna masuk sebagai penulis.

Token callback tidak disimpan ke database dan tidak ditulis ke log. Pengguna SSO baru selalu mendapat role `author`; administrator tidak pernah dibuat atau ditautkan otomatis.

Jika SIMPEG tidak mengirim email, sistem menggunakan alamat internal `sso.NIP_BARU@users.invalid`.

## Pilihan Verifikasi GET atau POST

Tutorial resmi menampilkan GET pada satu bagian dan POST pada contoh implementasi lengkap. Konfigurasi bawaan memakai POST. Jika pengelola API mengonfirmasi aplikasi memakai GET:

```dotenv
KEMENAG_SSO_VERIFY_METHOD=GET
```

## Pengaturan Pencocokan GTK

Untuk mengizinkan pegawai valid dari SIMPEG walaupun belum terdaftar pada menu GTK:

```dotenv
KEMENAG_SSO_REQUIRE_STAFF_MATCH=false
```

Pengaturan tersebut lebih longgar dan tidak disarankan untuk produksi.

## Troubleshooting

### Tombol SSO tidak tampil

Periksa `KEMENAG_SSO_ENABLED` dan `KEMENAG_SSO_APP_ID`, lalu jalankan `php artisan optimize:clear`.

### NIP belum terdaftar

Isi NIP pada menu **GTK**, pastikan hanya angka dan statusnya aktif. Nilai harus sama dengan `NIP_BARU` pada respons SIMPEG.

### Login kembali ke halaman masuk

Periksa `storage/logs/laravel.log`, URL callback yang didaftarkan, persetujuan aplikasi, APP ID, dan metode verifikasi.

### Pengujian lokal

SSO nyata biasanya memerlukan callback HTTPS yang dapat diakses dari internet. Gunakan domain staging HTTPS dan daftarkan callback staging itu pada aplikasi Kemenag.

### Produksi

```dotenv
APP_DEBUG=false
KEMENAG_SSO_VERIFY_SSL=true
```
