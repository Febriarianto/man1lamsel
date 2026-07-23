<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kemenag Single Sign-On
    |--------------------------------------------------------------------------
    |
    | Endpoint dan claim SSO dapat berbeda pada setiap lingkungan Kementerian
    | Agama. Seluruh parameter dibuat melalui .env agar source code tidak perlu
    | diubah ketika aplikasi didaftarkan ke Identity Provider resmi.
    |
    */
    'enabled' => env('KEMENAG_SSO_ENABLED', false),
    'label' => env('KEMENAG_SSO_LABEL', 'Masuk dengan SSO Kemenag'),

    'client_id' => env('KEMENAG_SSO_CLIENT_ID'),
    'client_secret' => env('KEMENAG_SSO_CLIENT_SECRET'),
    'redirect_uri' => env('KEMENAG_SSO_REDIRECT_URI'),

    'authorization_url' => env('KEMENAG_SSO_AUTHORIZATION_URL'),
    'token_url' => env('KEMENAG_SSO_TOKEN_URL'),
    'userinfo_url' => env('KEMENAG_SSO_USERINFO_URL'),
    'logout_url' => env('KEMENAG_SSO_LOGOUT_URL'),
    'scopes' => array_values(array_filter(array_map('trim', explode(',', env('KEMENAG_SSO_SCOPES', 'openid,profile,email'))))),
    'token_auth_method' => env('KEMENAG_SSO_TOKEN_AUTH_METHOD', 'client_secret_post'),

    'auto_provision' => env('KEMENAG_SSO_AUTO_PROVISION', true),
    'default_role' => env('KEMENAG_SSO_DEFAULT_ROLE', 'author'),
    'allowed_email_domains' => array_values(array_filter(array_map('trim', explode(',', env('KEMENAG_SSO_ALLOWED_EMAIL_DOMAINS', ''))))),

    'claims' => [
        'id' => env('KEMENAG_SSO_CLAIM_ID', 'sub'),
        'name' => env('KEMENAG_SSO_CLAIM_NAME', 'name'),
        'email' => env('KEMENAG_SSO_CLAIM_EMAIL', 'email'),
        'nip' => env('KEMENAG_SSO_CLAIM_NIP', 'nip'),
        'unit' => env('KEMENAG_SSO_CLAIM_UNIT', 'unit_name'),
        'avatar' => env('KEMENAG_SSO_CLAIM_AVATAR', 'picture'),
    ],

    'http' => [
        'timeout' => (int) env('KEMENAG_SSO_TIMEOUT', 15),
        'verify_ssl' => env('KEMENAG_SSO_VERIFY_SSL', true),
    ],
];
