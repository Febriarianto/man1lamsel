<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API SIMPEG Kementerian Agama
    |--------------------------------------------------------------------------
    |
    | Kredensial hanya dibaca dari .env. Request pegawai dikirim menggunakan
    | kode satker API, kemudian setiap baris respons wajib lolos filter
    | KODE_SATKER_2 sebelum disimpan.
    |
    */
    'base_url' => rtrim(env('SIMPEG_API_BASE_URL', 'https://api.kemenag.go.id/v1'), '/'),
    'email' => env('SIMPEG_API_EMAIL'),
    'password' => env('SIMPEG_API_PASSWORD'),
    'request_satker_code' => (string) env('SIMPEG_REQUEST_SATKER_CODE', '02090000000000'),
    'satker_2_code' => (string) env(
        'SIMPEG_KODE_SATKER_2',
        env('SIMPEG_SATKER_CODE', '02090325000000')
    ),
    'page_size' => min(400, max(1, (int) env('SIMPEG_PAGE_SIZE', 400))),
    'timeout' => max(10, (int) env('SIMPEG_API_TIMEOUT', 60)),
    'verify_ssl' => env('SIMPEG_VERIFY_SSL', true),
    'sync_staff' => env('SIMPEG_SYNC_STAFF', true),
];
