# Panduan Integrasi SSO Kemenag

## Arsitektur

Integrasi v1.4 menggunakan OAuth 2.0 Authorization Code Flow dengan endpoint UserInfo yang lazim digunakan pada OpenID Connect. Endpoint tidak ditulis permanen di source code karena alamat, scope, dan nama claim dapat berbeda sesuai layanan SSO yang diberikan kepada aplikasi.

## Data yang Perlu Diminta kepada Pengelola SSO

1. Client ID.
2. Client Secret.
3. Authorization endpoint.
4. Token endpoint.
5. UserInfo endpoint.
6. Scope yang diizinkan.
7. Nama claim untuk ID unik, nama, email, NIP, unit kerja, dan foto.
8. Metode autentikasi token: `client_secret_post` atau `client_secret_basic`.
9. Redirect URI yang harus didaftarkan.

## Redirect URI

```text
https://domain.sch.id/admin/sso/kemenag/callback
```

Nilainya harus sama persis pada `.env` dan konfigurasi aplikasi di server SSO, termasuk skema HTTPS, domain, port, dan path.

## Contoh `.env`

```dotenv
KEMENAG_SSO_ENABLED=true
KEMENAG_SSO_LABEL="Masuk dengan SSO Kemenag"
KEMENAG_SSO_CLIENT_ID="..."
KEMENAG_SSO_CLIENT_SECRET="..."
KEMENAG_SSO_REDIRECT_URI="https://domain.sch.id/admin/sso/kemenag/callback"
KEMENAG_SSO_AUTHORIZATION_URL="https://sso.example/authorize"
KEMENAG_SSO_TOKEN_URL="https://sso.example/token"
KEMENAG_SSO_USERINFO_URL="https://sso.example/userinfo"
KEMENAG_SSO_SCOPES="openid,profile,email"
KEMENAG_SSO_TOKEN_AUTH_METHOD=client_secret_post
KEMENAG_SSO_AUTO_PROVISION=true
KEMENAG_SSO_DEFAULT_ROLE=author
KEMENAG_SSO_ALLOWED_EMAIL_DOMAINS=
KEMENAG_SSO_CLAIM_ID=sub
KEMENAG_SSO_CLAIM_NAME=name
KEMENAG_SSO_CLAIM_EMAIL=email
KEMENAG_SSO_CLAIM_NIP=nip
KEMENAG_SSO_CLAIM_UNIT=unit_name
KEMENAG_SSO_CLAIM_AVATAR=picture
KEMENAG_SSO_TIMEOUT=15
KEMENAG_SSO_VERIFY_SSL=true
```

## Auto-Provision

Saat `KEMENAG_SSO_AUTO_PROVISION=true`, pengguna yang berhasil diautentikasi akan dibuat otomatis pada tabel `users`. Role baru selalu mengikuti `KEMENAG_SSO_DEFAULT_ROLE`, yang disarankan tetap `author`.

Akun yang sudah ada dicocokkan berdasarkan:

1. Kombinasi `auth_provider` dan `provider_id`.
2. Email, sebagai mekanisme penghubung akun lama.

Role akun lama tidak ditimpa ketika profil disinkronkan ulang.

## Pembatasan Email

Untuk menerima domain tertentu saja:

```dotenv
KEMENAG_SSO_ALLOWED_EMAIL_DOMAINS=kemenag.go.id,man1lamsel.sch.id
```

Kosongkan jika Identity Provider menggunakan domain email yang beragam atau claim email bukan domain organisasi.

## Claim Bertingkat

Pembacaan claim menggunakan dot notation:

```dotenv
KEMENAG_SSO_CLAIM_ID=data.user.id
KEMENAG_SSO_CLAIM_NAME=data.user.nama
KEMENAG_SSO_CLAIM_EMAIL=data.user.email
KEMENAG_SSO_CLAIM_NIP=data.pegawai.nip
KEMENAG_SSO_CLAIM_UNIT=data.pegawai.satker.nama
```

## Alur Artikel

1. Guru/pegawai login melalui SSO.
2. Sistem menyimpan atau memperbarui profil lokal.
3. Pengguna membuka **Artikel Saya**.
4. Artikel disimpan sebagai draft.
5. Administrator melihat draft beserta nama penulis dan unit kerja.
6. Administrator melakukan review dan mengubah status menjadi Published.
7. Website publik menampilkan nama penulis dari kolom snapshot `author_name`.

## Troubleshooting

### Tombol SSO tidak tampil

Pastikan:

```dotenv
KEMENAG_SSO_ENABLED=true
```

Kemudian jalankan:

```bash
php artisan optimize:clear
```

### Callback ditolak

Periksa kesamaan `KEMENAG_SSO_REDIRECT_URI` dengan redirect URI yang didaftarkan pada Identity Provider.

### Profil tidak memiliki email atau ID

Sesuaikan `KEMENAG_SSO_CLAIM_ID` dan `KEMENAG_SSO_CLAIM_EMAIL` dengan respons UserInfo sebenarnya.

### Sertifikat SSL gagal saat pengembangan lokal

Gunakan sertifikat yang valid. `KEMENAG_SSO_VERIFY_SSL=false` hanya boleh digunakan sementara untuk pengujian internal, bukan produksi.

### SSO menggunakan CAS atau SAML

Controller v1.4 disiapkan untuk OAuth 2.0/OpenID Connect. Bila dokumen resmi menyatakan protokol CAS atau SAML, adapter autentikasi perlu disesuaikan dengan metadata dan prosedur dari Identity Provider tersebut.
