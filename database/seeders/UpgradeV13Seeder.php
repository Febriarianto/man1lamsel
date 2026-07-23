<?php

namespace Database\Seeders;

use App\Models\Infographic;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpgradeV13Seeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key'=>'site_logo','value'=>'images/logo.svg','group'=>'branding','type'=>'image'],
            ['key'=>'site_favicon','value'=>'images/logo.svg','group'=>'branding','type'=>'image'],
            ['key'=>'seo_default_title','value'=>'MAN 1 Lampung Selatan — Madrasah Mandiri Berprestasi','group'=>'seo','type'=>'text'],
            ['key'=>'seo_default_description','value'=>'Portal resmi MAN 1 Lampung Selatan: informasi madrasah, berita, pengumuman, prestasi, galeri, infografis, dan layanan pendidikan.','group'=>'seo','type'=>'textarea'],
            ['key'=>'seo_default_keywords','value'=>'MAN 1 Lampung Selatan, MAN Kalianda, madrasah Lampung Selatan, sekolah Islam, Kementerian Agama','group'=>'seo','type'=>'textarea'],
            ['key'=>'seo_title_separator','value'=>'—','group'=>'seo','type'=>'text'],
            ['key'=>'seo_og_image','value'=>'images/demo/hero-1.svg','group'=>'seo','type'=>'image'],
            ['key'=>'seo_indexing','value'=>'1','group'=>'seo','type'=>'boolean'],
            ['key'=>'seo_google_verification','value'=>'','group'=>'seo','type'=>'text'],
            ['key'=>'seo_bing_verification','value'=>'','group'=>'seo','type'=>'text'],
            ['key'=>'seo_analytics_id','value'=>'','group'=>'seo','type'=>'text'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        if (Menu::query()->count() === 0) {
            $make = function (string $title, ?string $url, int $sort, ?Menu $parent = null, string $target = '_self', ?string $icon = null): Menu {
                return Menu::create([
                    'title' => $title,
                    'url' => $url,
                    'parent_id' => $parent?->id,
                    'sort_order' => $sort,
                    'target' => $target,
                    'icon' => $icon,
                    'active' => true,
                ]);
            };

            $make('Beranda', '/', 1, null, '_self', 'bi-house');
            $profile = $make('Profil', null, 2, null, '_self', 'bi-building');
            $make('Selayang Pandang', '/profil/selayang-pandang', 1, $profile);
            $make('Visi & Misi', '/profil/visi-dan-misi', 2, $profile);
            $make('Sejarah', '/profil/sejarah', 3, $profile);
            $make('Fasilitas', '/profil/fasilitas', 4, $profile);
            $make('Program Unggulan', '/profil/program-unggulan', 5, $profile);
            $make('Prestasi Madrasah', '/prestasi', 6, $profile);
            $staff = $make('Guru & Staf', null, 3, null, '_self', 'bi-people');
            $make('Dewan Guru', '/guru', 1, $staff);
            $make('Tenaga Kependidikan', '/tenaga-kependidikan', 2, $staff);
            $information = $make('Informasi', null, 4, null, '_self', 'bi-newspaper');
            $make('Berita', '/berita', 1, $information);
            $make('Artikel', '/artikel', 2, $information);
            $make('Pengumuman', '/pengumuman', 3, $information);
            $make('Infografis', '/infografis', 5, null, '_self', 'bi-file-earmark-bar-graph');
            $gallery = $make('Galeri', null, 6, null, '_self', 'bi-images');
            $make('Galeri Foto', '/galeri-foto', 1, $gallery);
            $make('Galeri Video', '/galeri-video', 2, $gallery);
            $make('RDM', '#', 7, null, '_blank', 'bi-journal-check');
            $make('Kontak', '/hubungi-kami', 8, null, '_self', 'bi-envelope');
        }

        Infographic::firstOrCreate(
            ['slug' => 'profil-peserta-didik-2026'],
            [
                'title' => 'Profil Peserta Didik MAN 1 Lampung Selatan Tahun 2026',
                'meta_title' => 'Infografis Profil Peserta Didik MAN 1 Lampung Selatan 2026',
                'description' => 'Ringkasan visual data peserta didik. Data pada infografis contoh ini dapat disesuaikan melalui dashboard admin.',
                'meta_description' => 'Infografis profil dan jumlah peserta didik MAN 1 Lampung Selatan tahun 2026.',
                'meta_keywords' => 'infografis MAN 1 Lampung Selatan, data siswa, peserta didik madrasah',
                'image' => 'demo/infographic-students.svg',
                'source_name' => 'Data Referensi Pendidikan',
                'published_at' => now()->subDays(5),
                'featured' => true,
                'active' => true,
                'sort_order' => 1,
            ]
        );
    }
}
