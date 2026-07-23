# Upgrade Aman v1.2 → v1.3

1. Backup database MySQL.
2. Backup `.env` dan `storage/app/public`.
3. Salin file project v1.3 ke folder project lama.
4. Pertahankan `.env` lama.
5. Jalankan:

```cmd
composer dump-autoload
php artisan migrate
php artisan db:seed --class=Database\Seeders\UpgradeV13Seeder
php artisan storage:link
php artisan optimize:clear
```

Setelah login admin, periksa:

- **Tampilan → Pengaturan & SEO**
- **Tampilan → Menu Navbar**
- **Konten → Infografis**

Tidak perlu menjalankan `migrate:fresh`.
