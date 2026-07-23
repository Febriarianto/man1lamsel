# Theme Color Manager

Theme Color Manager memungkinkan administrator mengubah identitas warna website tanpa membuka atau mengedit file CSS.

## Lokasi menu

```text
Admin → Tampilan → Pengaturan, Tema & SEO
```

## Preset

1. **Kartu Identitas — Biru Kuning**
   - Putih sebagai bidang dominan.
   - Biru untuk hero, footer, statistik, tombol, dan navigasi aktif.
   - Kuning untuk garis, badge, ikon, dan tombol utama.
   - Hitam/abu untuk teks.
2. **Ocean Blue**
3. **Emerald Madrasah**
4. **Maroon Gold**
5. **Warna Kustom**

Saat color picker atau kode HEX diubah manual, preset otomatis menjadi **Warna Kustom**.

## Warna yang dapat diatur

- Warna utama.
- Warna utama gelap.
- Warna aksen.
- Latar utama.
- Latar sekunder.
- Teks utama.
- Teks sekunder.
- Border/pemisah.

Gunakan format HEX enam digit, misalnya:

```text
#0877C9
```

## Komposisi

Nilai awal:

```text
Putih       75%
Biru        18%
Kuning       5%
Hitam/Abu    2%
```

Total wajib tepat 100%. Komposisi membentuk strip identitas dinamis di bawah navbar dan menjadi panduan proporsi visual website.

## Pilihan tambahan

- **Motif geometris**: menampilkan garis dan bidang diagonal seperti pada kartu identitas murid.
- **Terapkan ke dashboard**: menggunakan warna utama dan aksen pada sidebar, tombol, serta elemen dashboard administrator.

## Cache

Setelah menyimpan warna, perubahan langsung dibaca dari database. Jika browser masih menampilkan warna lama:

```cmd
php artisan optimize:clear
```

Lalu tekan `Ctrl + F5` pada browser.
