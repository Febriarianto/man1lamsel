<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO SIMPEG Kementerian Agama
    |--------------------------------------------------------------------------
    |
    | Alur ini mengikuti dokumentasi SIMPEG: aplikasi mengarahkan pengguna ke
    | endpoint signin dengan APP ID, menerima token pada callback, lalu
    | memverifikasi token tersebut ke endpoint verify menggunakan Bearer token.
    |
    */
    'enabled' => env('KEMENAG_SSO_ENABLED', false),
    'label' => env('KEMENAG_SSO_LABEL', 'Masuk dengan SSO Kemenag'),

    'app_id' => env('KEMENAG_SSO_APP_ID'),
    'signin_url' => env('KEMENAG_SSO_SIGNIN_URL', 'https://sso.kemenag.go.id/auth/signin'),
    'verify_url' => env('KEMENAG_SSO_VERIFY_URL', 'https://sso.kemenag.go.id/auth/verify'),
    'signout_url' => env('KEMENAG_SSO_SIGNOUT_URL', 'https://sso.kemenag.go.id/auth/signout'),
    'verify_method' => strtoupper(env('KEMENAG_SSO_VERIFY_METHOD', 'POST')),
    'callback_token_parameter' => env('KEMENAG_SSO_CALLBACK_TOKEN_PARAM', 'token'),

    'auto_provision' => env('KEMENAG_SSO_AUTO_PROVISION', true),
    'require_staff_match' => env('KEMENAG_SSO_REQUIRE_STAFF_MATCH', true),
    'auto_link_by_nip' => env('KEMENAG_SSO_AUTO_LINK_BY_NIP', true),
    'login_attempt_ttl' => (int) env('KEMENAG_SSO_LOGIN_ATTEMPT_TTL', 600),

    'http' => [
        'timeout' => (int) env('KEMENAG_SSO_TIMEOUT', 15),
        'verify_ssl' => env('KEMENAG_SSO_VERIFY_SSL', true),
    ],
];
