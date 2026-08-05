@extends('admin.layout')
@section('title','Pengaturan | Tema')
@section('page_title','Pengaturan | Tema')
@section('page_subtitle','Kelola identitas, warna dinamis, logo, favicon, metadata mesin pencari, kontak, dan layanan')
@section('content')
@php
$labels = [
'site_name'=>'Deskripsi','site_tagline'=>'Tagline','site_description'=>'Nama Madrasah','site_logo'=>'Logo Madrasah','site_favicon'=>'Favicon',
'seo_default_title'=>'Judul SEO Utama','seo_default_description'=>'Deskripsi SEO Utama','seo_default_keywords'=>'Kata Kunci Utama','seo_title_separator'=>'Pemisah Judul','seo_og_image'=>'Gambar Default Saat Dibagikan','seo_indexing'=>'Izinkan Mesin Pencari Mengindeks','seo_google_verification'=>'Kode Verifikasi Google Search Console','seo_bing_verification'=>'Kode Verifikasi Bing Webmaster','seo_analytics_id'=>'Google Analytics Measurement ID',
'phone'=>'Nomor Telepon','email'=>'Email','address'=>'Alamat','maps_url'=>'URL Google Maps','instagram'=>'Instagram','youtube'=>'YouTube','facebook'=>'Facebook','rdm_url'=>'URL RDM','spmb_url'=>'URL SPMB','student_count'=>'Jumlah Siswa','teacher_count'=>'Jumlah Guru','achievement_count'=>'Jumlah Prestasi','alumni_count'=>'Jumlah Alumni'
];
$groupLabels=['general'=>'Identitas Website','branding'=>'Gambar','seo'=>'SEO | Analitik','contact'=>'Kontak','social'=>'Media Sosial','services'=>'Layanan','statistics'=>'Statistik'];
$icons=['general'=>'bi-building','branding'=>'bi-image','seo'=>'bi-search','contact'=>'bi-telephone','social'=>'bi-share','services'=>'bi-grid','statistics'=>'bi-bar-chart'];
$theme = $settings->get('theme', collect())->keyBy('key');
$themeValue = fn(string $key, string $default='') => old($key, optional($theme->get($key))->value ?? $default);
$colorFields = [
    'theme_primary' => ['Biru Utama', '#0877C9', 'Navbar aktif, tombol, statistik, footer'],
    'theme_primary_dark' => ['Biru Gelap', '#045A9D', 'Overlay hero dan bidang kontras'],
    'theme_accent' => ['Kuning Aksen', '#F4CD00', 'Garis, ikon, badge, dan call-to-action'],
    'theme_background' => ['Putih/Latar Utama', '#FFFFFF', 'Latar dominan website'],
    'theme_surface' => ['Latar Sekunder', '#F5F7FA', 'Bagian selang-seling dan kartu lembut'],
    'theme_text' => ['Teks Utama', '#171717', 'Judul dan isi utama'],
    'theme_muted' => ['Teks Sekunder', '#667085', 'Deskripsi dan metadata'],
    'theme_border' => ['Garis/Batas', '#E3E8EF', 'Border kartu dan pemisah'],
];
$ratioFields = [
    'theme_white_ratio' => ['Putih', 75],
    'theme_primary_ratio' => ['Biru', 18],
    'theme_accent_ratio' => ['Kuning', 5],
    'theme_neutral_ratio' => ['Hitam/Abu', 2],
];
@endphp
<form method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" id="settingsForm">@csrf @method('put')
<div class="row g-4">
    @foreach($settings as $group=>$items)
        @if($group === 'theme')
            <div class="col-12">
                <div class="admin-card theme-manager-card">
                    <div class="theme-manager-head">
                        <div>
                            <span class="theme-kicker"><i class="bi bi-palette2"></i> THEME COLOR MANAGER</span>
                            <h3>Warna identitas website yang dapat diubah kapan saja</h3>
                            <p>Preset awal mengikuti kartu identitas murid: putih dominan, biru tegas, kuning sebagai aksen, dan hitam/abu untuk teks.</p>
                        </div>
                        <div class="theme-preset-select">
                            <label class="form-label" for="themePreset">Preset Tema</label>
                            <select class="form-select" name="theme_preset" id="themePreset">
                                <option value="identity-card" @selected($themeValue('theme_preset','identity-card')==='identity-card')>Kartu Identitas Murid— Biru Kuning</option>
                                <option value="ocean" @selected($themeValue('theme_preset')==='ocean')>Ocean Blue</option>
                                <option value="emerald" @selected($themeValue('theme_preset')==='emerald')>Emerald Madrasah</option>
                                <option value="maroon" @selected($themeValue('theme_preset')==='maroon')>Maroon Gold</option>
                                <option value="custom" @selected($themeValue('theme_preset')==='custom')>Warna Kustom</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-4 p-4 pt-2">
                        <div class="col-xl-7">
                            <h5 class="mb-3">Palet Warna</h5>
                            <div class="theme-color-grid">
                                @foreach($colorFields as $key => [$label,$default,$help])
                                    @php($value = strtoupper($themeValue($key,$default)))
                                    <div class="theme-color-field">
                                        <label for="{{ $key }}">{{ $label }}</label>
                                        <div class="theme-color-control">
                                            <input type="color" name="{{ $key }}" id="{{ $key }}" value="{{ $value }}" class="form-control form-control-color js-theme-color" data-text="{{ $key }}_text">
                                            <input type="text" id="{{ $key }}_text" value="{{ $value }}" class="form-control js-theme-color-text" data-color="{{ $key }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}">
                                        </div>
                                        <small>{{ $help }}</small>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-4 mb-2 gap-3 flex-wrap">
                                <div><h5 class="mb-1">Komposisi Warna</h5><p class="text-secondary small mb-0">Total wajib 100%. Nilai ini juga membentuk strip identitas di bawah navbar.</p></div>
                                <strong class="ratio-total" id="ratioTotal">100%</strong>
                            </div>
                            <div class="composition-bar" id="compositionBar">
                                <span data-segment="theme_white_ratio"></span><span data-segment="theme_primary_ratio"></span><span data-segment="theme_accent_ratio"></span><span data-segment="theme_neutral_ratio"></span>
                            </div>
                            <div class="row g-3 mt-1">
                                @foreach($ratioFields as $key => [$label,$default])
                                    <div class="col-sm-6 col-lg-3">
                                        <label class="form-label" for="{{ $key }}">{{ $label }}</label>
                                        <div class="input-group">
                                            <input type="number" min="0" max="100" name="{{ $key }}" id="{{ $key }}" value="{{ $themeValue($key,(string)$default) }}" class="form-control js-theme-ratio">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <div class="form-check form-switch theme-switch">
                                        <input type="checkbox" name="theme_pattern_enabled" value="1" id="theme_pattern_enabled" class="form-check-input" @checked($themeValue('theme_pattern_enabled','1')==='1')>
                                        <label class="form-check-label" for="theme_pattern_enabled"><strong>Motif geometris</strong><small>Tampilkan garis dan bidang diagonal seperti kartu identitas.</small></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch theme-switch">
                                        <input type="checkbox" name="theme_apply_admin" value="1" id="theme_apply_admin" class="form-check-input" @checked($themeValue('theme_apply_admin','1')==='1')>
                                        <label class="form-check-label" for="theme_apply_admin"><strong>Terapkan ke dashboard</strong><small>Warna utama ikut digunakan pada area administrator.</small></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5">
                            <div class="theme-preview-sticky">
                                <div class="theme-preview" id="themePreview">
                                    <div class="preview-top"><span class="preview-logo"><i class="bi bi-mortarboard-fill"></i></span><div><strong>MAN 1 LAMPUNG SELATAN</strong><small>Madrasah Mandiri Berprestasi</small></div><span class="preview-quote">”</span></div>
                                    <div class="preview-yellow-line"></div>
                                    <div class="preview-nav"><span>Beranda</span><span>Profil</span><span>Informasi</span><b><i class="bi bi-search"></i></b></div>
                                    <div class="preview-hero"><div><small>PORTAL RESMI MADRASAH</small><h4>Mendidik generasi unggul dan berkarakter</h4><p>Preview akan mengikuti pilihan warna Anda secara langsung.</p><button type="button">Selengkapnya <i class="bi bi-arrow-right"></i></button></div></div>
                                    <div class="preview-content"><span></span><span></span><span></span></div>
                                </div>
                                <p class="small text-secondary mt-3 mb-0"><i class="bi bi-info-circle me-1"></i> Perubahan baru tampil pada website setelah tombol simpan ditekan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-xl-6"><div class="admin-card p-4 h-100"><h4 class="mb-1"><i class="bi {{ $icons[$group]??'bi-gear' }} me-2"></i>{{ $groupLabels[$group]??Str::headline($group) }}</h4>
            <p class="text-secondary small mb-4">@switch($group)@case('branding')Gunakan logo transparan berkualitas baik dan favicon berbentuk persegi.@break @case('seo')Metadata ini menjadi nilai bawaan untuk Google dan saat tautan dibagikan ke media sosial.@break @default Perbarui data sesuai informasi resmi madrasah. @endswitch</p>
            <div class="row g-3">
            @foreach($items as $setting)
            <div class="col-12"><label class="form-label">{{ $labels[$setting->key]??Str::headline($setting->key) }}</label>
            @if($setting->type==='textarea')
            <textarea name="{{ $setting->key }}" class="form-control" rows="3">{{ old($setting->key,$setting->value) }}</textarea>
            @elseif($setting->type==='image')
            @if($setting->value)<img src="{{ \App\Models\Setting::mediaUrl($setting->value) }}" class="setting-image-preview mb-2" alt="Preview">@endif
            <input type="file" name="{{ $setting->key }}" class="form-control" accept="image/*,.ico,.svg"><small class="text-secondary">Kosongkan bila tidak ingin mengganti file saat ini.</small>
            @elseif($setting->type==='boolean')
            <div class="form-check form-switch"><input type="checkbox" name="{{ $setting->key }}" value="1" id="{{ $setting->key }}" class="form-check-input" @checked(old($setting->key,$setting->value)==='1'||old($setting->key,$setting->value)===1)><label class="form-check-label" for="{{ $setting->key }}">Aktif</label></div>
            @else
            <input type="{{ in_array($setting->type,['email','url','number'])?$setting->type:'text' }}" name="{{ $setting->key }}" value="{{ old($setting->key,$setting->value) }}" class="form-control" @if($setting->key==='seo_analytics_id') placeholder="G-XXXXXXXXXX" @endif>
            @endif
            @if($setting->key==='seo_default_keywords')<small class="text-secondary">Pisahkan setiap kata kunci dengan koma.</small>@endif
            @if($setting->key==='seo_google_verification'||$setting->key==='seo_bing_verification')<small class="text-secondary">Masukkan hanya nilai content dari meta tag verifikasi.</small>@endif
            </div>
            @endforeach
            </div></div></div>
        @endif
    @endforeach
</div>
<div class="sticky-save"><button class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-2"></i>Simpan Semua Pengaturan</button></div>
</form>
@endsection
@push('scripts')
<script>
(() => {
    const presets = @json($themePresets);
    const presetSelect = document.getElementById('themePreset');
    const colorInputs = [...document.querySelectorAll('.js-theme-color')];
    const ratioInputs = [...document.querySelectorAll('.js-theme-ratio')];
    const preview = document.getElementById('themePreview');

    const normalizeHex = value => /^#[0-9a-f]{6}$/i.test(value || '') ? value.toUpperCase() : null;

    function refreshPreview() {
        const colors = Object.fromEntries(colorInputs.map(input => [input.id, input.value]));
        preview.style.setProperty('--preview-primary', colors.theme_primary);
        preview.style.setProperty('--preview-primary-dark', colors.theme_primary_dark);
        preview.style.setProperty('--preview-accent', colors.theme_accent);
        preview.style.setProperty('--preview-bg', colors.theme_background);
        preview.style.setProperty('--preview-surface', colors.theme_surface);
        preview.style.setProperty('--preview-text', colors.theme_text);
        preview.style.setProperty('--preview-muted', colors.theme_muted);
        preview.style.setProperty('--preview-border', colors.theme_border);
        const compositionBar = document.getElementById('compositionBar');
        compositionBar.style.setProperty('--preview-primary', colors.theme_primary);
        compositionBar.style.setProperty('--preview-accent', colors.theme_accent);
        compositionBar.style.setProperty('--preview-bg', colors.theme_background);
        compositionBar.style.setProperty('--preview-text', colors.theme_text);

        const ratios = Object.fromEntries(ratioInputs.map(input => [input.id, Math.max(0, Math.min(100, Number(input.value) || 0))]));
        const total = Object.values(ratios).reduce((sum, value) => sum + value, 0);
        const totalElement = document.getElementById('ratioTotal');
        totalElement.textContent = `${total}%`;
        totalElement.classList.toggle('invalid', total !== 100);
        document.querySelectorAll('[data-segment]').forEach(segment => {
            segment.style.width = `${ratios[segment.dataset.segment] || 0}%`;
        });
    }

    presetSelect?.addEventListener('change', () => {
        const preset = presets[presetSelect.value];
        if (!preset) return;
        Object.entries(preset).forEach(([key, value]) => {
            const color = document.getElementById(key);
            const text = document.getElementById(`${key}_text`);
            if (color) color.value = value;
            if (text) text.value = value;
        });
        refreshPreview();
    });

    colorInputs.forEach(input => input.addEventListener('input', () => {
        const text = document.getElementById(input.dataset.text);
        if (text) text.value = input.value.toUpperCase();
        presetSelect.value = 'custom';
        refreshPreview();
    }));

    document.querySelectorAll('.js-theme-color-text').forEach(input => input.addEventListener('input', () => {
        const value = normalizeHex(input.value);
        if (value) {
            document.getElementById(input.dataset.color).value = value;
            presetSelect.value = 'custom';
            refreshPreview();
        }
    }));

    ratioInputs.forEach(input => input.addEventListener('input', refreshPreview));
    refreshPreview();
})();
</script>
@endpush
