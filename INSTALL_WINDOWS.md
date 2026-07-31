# Panduan Instalasi Windows — v1.5

## Instalasi Baru

Buka Command Prompt pada folder project:

```cmd
FIX_WINDOWS.bat
copy .env.example .env
php artisan key:generate
```

Buat database MySQL dan isi konfigurasi pada `.env`, lalu:

```cmd
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

Akses `http://127.0.0.1:8000/admin/login`.

## Upgrade dari v1.4 ke v1.5

Cadangkan database, `.env`, dan `storage\app\public`. Salin patch v1.5 ke folder project dan pilih **Replace/Overwrite**.

```cmd
composer dump-autoload
php artisan db:seed --class=Database\Seeders\UpgradeV15Seeder
php artisan optimize:clear
```

Buka **Admin → Tampilan → Pengaturan, Tema & SEO** untuk mengubah warna.

## Upgrade dari v1.3

Cadangkan database, `.env`, serta `storage\app\public`. Salin patch v1.4 ke folder project dan pilih Replace/Overwrite.

```cmd
composer dump-autoload
php artisan migrate
php artisan db:seed --class=Database\Seeders\UpgradeV14Seeder
php artisan optimize:clear
```

## Mengaktifkan SSO

Daftarkan callback aplikasi pada portal API Kemenag, lalu edit `.env` dan isi APP ID:

```dotenv
KEMENAG_SSO_ENABLED=true
KEMENAG_SSO_APP_ID="APP_ID_DARI_KEMENAG"
KEMENAG_SSO_SIGNIN_URL="https://sso.kemenag.go.id/auth/signin"
KEMENAG_SSO_VERIFY_URL="https://sso.kemenag.go.id/auth/verify"
KEMENAG_SSO_SIGNOUT_URL="https://sso.kemenag.go.id/auth/signout"
KEMENAG_SSO_VERIFY_METHOD=POST
KEMENAG_SSO_REQUIRE_STAFF_MATCH=true
```

Kemudian:

```cmd
php artisan optimize:clear
```

Daftarkan callback produksi berikut pada aplikasi Kemenag:

```text
https://domain-sekolah.sch.id/admin/sso/kemenag/callback
```

SSO nyata memerlukan callback HTTPS yang dapat diakses oleh server Kemenag. Baca `SSO_KEMENAG.md` untuk panduan lengkap.

## Jika Folder Cache Tidak Dapat Ditulis

```cmd
mkdir bootstrap\cache
mkdir storage\framework\cache\data
mkdir storage\framework\sessions
mkdir storage\framework\views
mkdir storage\logs
attrib -R bootstrap\cache /S /D
attrib -R storage /S /D
composer dump-autoload
```

Jika masih gagal, buka Command Prompt sebagai Administrator:

```cmd
icacls bootstrap\cache /grant "%USERNAME%:(OI)(CI)F" /T
icacls storage /grant "%USERNAME%:(OI)(CI)F" /T
```

## Jangan Gunakan pada Database Produksi

```cmd
php artisan migrate:fresh --seed
```

Perintah tersebut menghapus seluruh tabel dan data.
