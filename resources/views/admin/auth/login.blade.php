<!doctype html>
<html lang="id">
<head>
    @php($loginLogo=\App\Models\Setting::mediaUrl($siteSettings['site_logo']??null,asset('images/logo.svg')))
    @php($loginFavicon=\App\Models\Setting::mediaUrl($siteSettings['site_favicon']??null,asset('images/logo.svg')))
    @php($loginPrimary=\App\Models\Setting::normalizeHex($siteSettings['theme_primary']??'#0877C9'))
    @php($loginPrimaryDark=\App\Models\Setting::normalizeHex($siteSettings['theme_primary_dark']??'#045A9D','#045A9D'))
    @php($loginAccent=\App\Models\Setting::normalizeHex($siteSettings['theme_accent']??'#F4CD00','#F4CD00'))
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard - {{ $siteSettings['site_name'] ?? 'MAN 1 Lampung Selatan' }}</title>
    <link rel="icon" href="{{ $loginFavicon }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
    <style>:root{--brand:{{ $loginPrimary }};--brand-rgb:{{ \App\Models\Setting::hexToRgb($loginPrimary) }};--brand-dark:{{ $loginPrimaryDark }};--brand-dark-rgb:{{ \App\Models\Setting::hexToRgb($loginPrimaryDark,'#045A9D') }};--accent:{{ $loginAccent }};--accent-rgb:{{ \App\Models\Setting::hexToRgb($loginAccent,'#F4CD00') }};}</style>
</head>
<body class="login-page"><div class="login-shell"><div class="login-visual"><div><img src="{{ $loginLogo }}" alt="Logo"><span>DASHBOARD MADRASAH</span><h1>Kelola informasi madrasah bersama-sama.</h1><p>Administrator mengelola seluruh portal, sedangkan guru dan pegawai dapat menulis artikel menggunakan akun manual atau SSO Kemenag.</p></div></div><div class="login-form-wrap"><div class="login-form"><a href="{{ route('home') }}" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke website</a><h2>Selamat datang</h2><p>Silahkan login menggunakan akun anda masing-masing.</p>
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(config('kemenag-sso.enabled') && filled(config('kemenag-sso.app_id')))
<a href="{{ route('admin.sso.redirect') }}" class="btn btn-primary w-100 btn-lg mb-3"><i class="bi bi-shield-lock me-2"></i>{{ config('kemenag-sso.label') }}</a>
<div class="text-center text-secondary small mb-3">Untuk ASN Kementerian Agama | MAN 1 Lampung Selatan</div>
<div class="d-flex align-items-center gap-3 mb-3"><hr class="flex-grow-1"><span class="small text-secondary">LOGIN MANUAL</span><hr class="flex-grow-1"></div>
@endif
<form method="post" action="{{ route('admin.login.store') }}">@csrf<div class="mb-3"><label class="form-label">Username</label><div class="input-icon"><i class="bi bi-envelope"></i><input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username"></div></div><div class="mb-3"><label class="form-label">Kata Sandi</label><div class="input-icon"><i class="bi bi-lock"></i><input type="password" name="password" class="form-control" required autocomplete="current-password"></div></div><div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Ingat saya</label></div><button class="btn btn-primary w-100 btn-lg">Masuk ke Dashboard <i class="bi bi-arrow-right ms-2"></i></button></form><div class="demo-login"><strong>Keterangan</strong><span>Username dan Kata Sandi Manual dibuat oleh Administrator.</span></div></div></div></div></body></html>
