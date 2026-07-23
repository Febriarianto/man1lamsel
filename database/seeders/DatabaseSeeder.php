<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Link;
use App\Models\Menu;
use App\Models\Infographic;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@mansalase.sch.id'],
            ['name' => 'Administrator MAN 1 Lampung Selatan', 'password' => Hash::make('admin12345'), 'role' => 'admin', 'auth_provider' => 'local', 'active' => true]
        );

        $settings = [
            ['key'=>'site_name','value'=>'MAN 1 Lampung Selatan','group'=>'general','type'=>'text'],
            ['key'=>'site_tagline','value'=>'Madrasah Mandiri Berprestasi','group'=>'general','type'=>'text'],
            ['key'=>'site_description','value'=>'Portal informasi resmi MAN 1 Lampung Selatan yang menyajikan profil, berita, prestasi, layanan, dan kegiatan madrasah.','group'=>'general','type'=>'textarea'],
            ['key'=>'site_logo','value'=>'images/logo.svg','group'=>'branding','type'=>'image'],
            ['key'=>'site_favicon','value'=>'images/logo.svg','group'=>'branding','type'=>'image'],
            ['key'=>'theme_preset','value'=>'identity-card','group'=>'theme','type'=>'select'],
            ['key'=>'theme_primary','value'=>'#0877C9','group'=>'theme','type'=>'color'],
            ['key'=>'theme_primary_dark','value'=>'#045A9D','group'=>'theme','type'=>'color'],
            ['key'=>'theme_accent','value'=>'#F4CD00','group'=>'theme','type'=>'color'],
            ['key'=>'theme_background','value'=>'#FFFFFF','group'=>'theme','type'=>'color'],
            ['key'=>'theme_surface','value'=>'#F5F7FA','group'=>'theme','type'=>'color'],
            ['key'=>'theme_text','value'=>'#171717','group'=>'theme','type'=>'color'],
            ['key'=>'theme_muted','value'=>'#667085','group'=>'theme','type'=>'color'],
            ['key'=>'theme_border','value'=>'#E3E8EF','group'=>'theme','type'=>'color'],
            ['key'=>'theme_white_ratio','value'=>'75','group'=>'theme','type'=>'number'],
            ['key'=>'theme_primary_ratio','value'=>'18','group'=>'theme','type'=>'number'],
            ['key'=>'theme_accent_ratio','value'=>'5','group'=>'theme','type'=>'number'],
            ['key'=>'theme_neutral_ratio','value'=>'2','group'=>'theme','type'=>'number'],
            ['key'=>'theme_pattern_enabled','value'=>'1','group'=>'theme','type'=>'boolean'],
            ['key'=>'theme_apply_admin','value'=>'1','group'=>'theme','type'=>'boolean'],
            ['key'=>'seo_default_title','value'=>'MAN 1 Lampung Selatan — Madrasah Mandiri Berprestasi','group'=>'seo','type'=>'text'],
            ['key'=>'seo_default_description','value'=>'Portal resmi MAN 1 Lampung Selatan: informasi madrasah, berita, pengumuman, prestasi, galeri, infografis, dan layanan pendidikan.','group'=>'seo','type'=>'textarea'],
            ['key'=>'seo_default_keywords','value'=>'MAN 1 Lampung Selatan, MAN Kalianda, madrasah Lampung Selatan, sekolah Islam, Kementerian Agama','group'=>'seo','type'=>'textarea'],
            ['key'=>'seo_title_separator','value'=>'—','group'=>'seo','type'=>'text'],
            ['key'=>'seo_og_image','value'=>'images/demo/hero-1.svg','group'=>'seo','type'=>'image'],
            ['key'=>'seo_indexing','value'=>'1','group'=>'seo','type'=>'boolean'],
            ['key'=>'seo_google_verification','value'=>'','group'=>'seo','type'=>'text'],
            ['key'=>'seo_bing_verification','value'=>'','group'=>'seo','type'=>'text'],
            ['key'=>'seo_analytics_id','value'=>'','group'=>'seo','type'=>'text'],
            ['key'=>'phone','value'=>'(0727) 3320495','group'=>'contact','type'=>'text'],
            ['key'=>'email','value'=>'info@mansalase.sch.id','group'=>'contact','type'=>'email'],
            ['key'=>'address','value'=>'Jl. Soekarno Hatta, Way Urang, Kalianda, Lampung Selatan','group'=>'contact','type'=>'textarea'],
            ['key'=>'maps_url','value'=>'https://maps.google.com/?q=MAN+1+Lampung+Selatan','group'=>'contact','type'=>'url'],
            ['key'=>'instagram','value'=>'https://www.instagram.com/mansatu.lamsel/','group'=>'social','type'=>'url'],
            ['key'=>'youtube','value'=>'#','group'=>'social','type'=>'url'],
            ['key'=>'facebook','value'=>'#','group'=>'social','type'=>'url'],
            ['key'=>'rdm_url','value'=>'#','group'=>'services','type'=>'url'],
            ['key'=>'spmb_url','value'=>'#','group'=>'services','type'=>'url'],
            ['key'=>'student_count','value'=>'211','group'=>'statistics','type'=>'number'],
            ['key'=>'teacher_count','value'=>'49','group'=>'statistics','type'=>'number'],
            ['key'=>'achievement_count','value'=>'35','group'=>'statistics','type'=>'number'],
            ['key'=>'alumni_count','value'=>'2500+','group'=>'statistics','type'=>'text'],
        ];
        foreach ($settings as $setting) Setting::firstOrCreate(['key'=>$setting['key']], $setting);

        if (Menu::query()->count() === 0) {
            $upsertMenu = function (string $title, ?string $url, int $sort, ?Menu $parent = null, string $target = '_self', ?string $icon = null): Menu {
                return Menu::updateOrCreate(
                    ['title' => $title, 'parent_id' => $parent?->id],
                    ['url' => $url, 'sort_order' => $sort, 'target' => $target, 'icon' => $icon, 'active' => true]
                );
            };
            $upsertMenu('Beranda', '/', 1, null, '_self', 'bi-house');
            $profileMenu = $upsertMenu('Profil', null, 2, null, '_self', 'bi-building');
            $upsertMenu('Selayang Pandang', '/profil/selayang_pandang', 1, $profileMenu);
            $upsertMenu('Visi & Misi', '/profil/visi_dan_misi', 2, $profileMenu);
            $upsertMenu('Sejarah', '/profil/sejarah', 3, $profileMenu);
            $upsertMenu('Fasilitas', '/profil/fasilitas', 4, $profileMenu);
            $upsertMenu('Program Unggulan', '/profil/program_unggulan', 5, $profileMenu);
            $upsertMenu('Prestasi Madrasah', '/prestasi', 6, $profileMenu);
            $staffMenu = $upsertMenu('Guru & Staf', null, 3, null, '_self', 'bi-people');
            $upsertMenu('Dewan Guru', '/guru', 1, $staffMenu);
            $upsertMenu('Tenaga Kependidikan', '/tenaga-kependidikan', 2, $staffMenu);
            $infoMenu = $upsertMenu('Informasi', null, 4, null, '_self', 'bi-newspaper');
            $upsertMenu('Berita', '/berita', 1, $infoMenu);
            $upsertMenu('Artikel', '/artikel', 2, $infoMenu);
            $upsertMenu('Pengumuman', '/pengumuman', 3, $infoMenu);
            $upsertMenu('Infografis', '/infografis', 5, null, '_self', 'bi-file-earmark-bar-graph');
            $galleryMenu = $upsertMenu('Galeri', null, 6, null, '_self', 'bi-images');
            $upsertMenu('Galeri Foto', '/galeri-foto', 1, $galleryMenu);
            $upsertMenu('Galeri Video', '/galeri-video', 2, $galleryMenu);
            $upsertMenu('RDM', '#', 7, null, '_blank', 'bi-journal-check');
            $upsertMenu('Kontak', '/hubungi-kami', 8, null, '_self', 'bi-envelope');
        }

        $pages = [
            ['title'=>'Selayang Pandang','slug'=>'selayang_pandang','excerpt'=>'Mengenal MAN 1 Lampung Selatan lebih dekat.','content'=>'<p>MAN 1 Lampung Selatan merupakan madrasah aliyah negeri yang berkomitmen membangun generasi berakhlak, berilmu, terampil, dan siap menghadapi perubahan zaman.</p><p>Pembelajaran memadukan penguatan karakter keislaman, kecakapan akademik, literasi digital, kepemimpinan, serta kepedulian terhadap lingkungan.</p>','image'=>'demo/campus.svg'],
            ['title'=>'Visi dan Misi','slug'=>'visi_dan_misi','excerpt'=>'Arah pengembangan dan komitmen pendidikan madrasah.','content'=>'<h3>Visi</h3><p>Terwujudnya madrasah yang unggul, religius, berprestasi, berwawasan lingkungan, dan adaptif terhadap perkembangan teknologi.</p><h3>Misi</h3><ol><li>Menyelenggarakan pembelajaran aktif, inovatif, dan berorientasi pada capaian peserta didik.</li><li>Menguatkan budaya religius, disiplin, bersih, dan ramah lingkungan.</li><li>Mengembangkan potensi akademik dan nonakademik melalui pembinaan berkelanjutan.</li><li>Meningkatkan layanan digital dan tata kelola yang transparan.</li><li>Membangun kemitraan dengan orang tua, masyarakat, pemerintah, dan perguruan tinggi.</li></ol>','image'=>'demo/vision.svg'],
            ['title'=>'Sejarah','slug'=>'sejarah','excerpt'=>'Perjalanan MAN 1 Lampung Selatan sejak awal berdiri.','content'=>'<p>MAN 1 Lampung Selatan tumbuh dari semangat masyarakat untuk menghadirkan pendidikan menengah berciri khas Islam di wilayah Lampung Selatan. Madrasah terus berkembang melalui penguatan kelembagaan, peningkatan mutu tenaga pendidik, fasilitas pembelajaran, serta prestasi peserta didik.</p><p>Konten sejarah lengkap, daftar kepala madrasah, dan tonggak perkembangan dapat diperbarui melalui dashboard admin.</p>','image'=>'demo/history.svg'],
            ['title'=>'Fasilitas','slug'=>'fasilitas','excerpt'=>'Fasilitas untuk mendukung pembelajaran dan pengembangan siswa.','content'=>'<p>Fasilitas madrasah mencakup ruang kelas, perpustakaan, laboratorium komputer, laboratorium sains, aula, sarana ibadah, ruang organisasi siswa, area olahraga, dan lingkungan belajar hijau.</p><p>Daftar, foto, serta kondisi fasilitas dapat ditambahkan dan diperbarui sesuai kebutuhan.</p>','image'=>'demo/lab.svg'],
            ['title'=>'Program Unggulan','slug'=>'program_unggulan','excerpt'=>'Program pengembangan akademik, karakter, dan keterampilan.','content'=>'<p>Program unggulan diarahkan pada penguatan tahfidz, kelas unggul, pembinaan olimpiade, riset siswa, literasi digital, bahasa, kewirausahaan, dan pengembangan ekstrakurikuler.</p>','image'=>'demo/program.svg'],
        ];
        foreach($pages as $page) Page::updateOrCreate(['slug'=>$page['slug']], array_merge($page,['status'=>'published','published_at'=>now()]));

        Staff::updateOrCreate(['slug'=>'ahmad_musopa'],[
            'name'=>'Ahmad Musopa, S.Pd.I., M.Pd.','position'=>'Kepala Madrasah','subject'=>null,'type'=>'principal','photo'=>'demo/principal.svg',
            'bio'=>'Pendidikan merupakan ikhtiar bersama untuk membentuk generasi yang berkarakter, cerdas, kreatif, dan bertanggung jawab. Mari membangun madrasah yang aman, nyaman, berprestasi, dan memberi manfaat bagi masyarakat.','sort_order'=>0,'active'=>true
        ]);
        $teachers = [
            ['Siti Rahmawati, M.Pd.','Wakil Kepala Bidang Kurikulum','Matematika'],
            ['Dr. Muchlisin Soleh, M.Pd.I.','Wakil Kepala Bidang Kesiswaan','Pendidikan Agama Islam'],
            ['Rina Marlina, S.Pd.','Guru','Bahasa Indonesia'],
            ['Dimas Kurniawan, S.Pd.','Guru','Tahfidz'],
            ['Ulfa Triana, M.Pd.','Guru','Tahfidz'],
            ['Fajar Nugroho, S.Pd.','Guru','Informatika'],
            ['Nur Aini, S.Pd.','Guru','Bahasa Inggris'],
            ['M. Ridwan, S.Pd.','Guru','Fisika'],
        ];
        foreach($teachers as $i=>$teacher) Staff::updateOrCreate(['slug'=>(string) str($teacher[0])->slug('_')],['name'=>$teacher[0],'position'=>$teacher[1],'subject'=>$teacher[2],'type'=>'teacher','photo'=>'demo/person-'.(($i%4)+1).'.svg','bio'=>'Tenaga pendidik yang berdedikasi mendampingi perkembangan akademik dan karakter peserta didik.','sort_order'=>$i+1,'active'=>true]);
        foreach([['Sri Wahyuni, S.E.','Kepala Tata Usaha'],['Agus Setiawan','Staf Administrasi'],['Dewi Lestari','Operator Madrasah']] as $i=>$employee) Staff::updateOrCreate(['slug'=>(string) str($employee[0])->slug('_')],['name'=>$employee[0],'position'=>$employee[1],'type'=>'employee','photo'=>'demo/person-'.(($i%4)+1).'.svg','sort_order'=>$i+1,'active'=>true]);

        $banners = [
            ['title'=>'Tumbuh dalam Iman, Unggul dalam Prestasi','subtitle'=>'Lingkungan belajar modern yang memadukan karakter keislaman, akademik, teknologi, dan kepemimpinan.','button_text'=>'Jelajahi Madrasah','button_url'=>'/profil/selayang_pandang','image'=>'demo/hero-1.svg','sort_order'=>1],
            ['title'=>'SPMB Tahun Pelajaran 2026/2027','subtitle'=>'Saatnya menjadi bagian dari keluarga besar MAN 1 Lampung Selatan dan mengembangkan potensi terbaikmu.','button_text'=>'Informasi Pendaftaran','button_url'=>'/pengumuman','image'=>'demo/hero-2.svg','sort_order'=>2],
        ];
        foreach($banners as $banner) Banner::updateOrCreate(['title'=>$banner['title']],array_merge($banner,['active'=>true]));

        $posts = [
            ['title'=>'29 Siswa Ikuti Olimpiade Sains Tingkat Kabupaten','category'=>'berita','excerpt'=>'Peserta didik mengikuti seleksi secara daring dengan pendampingan guru pembina.','content'=>'<p>Sebanyak 29 peserta didik mengikuti Olimpiade Sains tingkat kabupaten secara daring. Kegiatan berlangsung tertib dengan dukungan laboratorium komputer dan pendampingan guru pembina.</p><p>Madrasah berharap kegiatan ini menjadi ruang tumbuh bagi budaya belajar, sportivitas, dan prestasi akademik.</p>','image'=>'demo/news-osn.svg','featured'=>true,'published_at'=>now()->subDays(20)],
            ['title'=>'Rapat Kenaikan Kelas Tahun Pelajaran 2025/2026','category'=>'berita','excerpt'=>'Dewan guru melakukan evaluasi menyeluruh terhadap capaian belajar dan perkembangan siswa.','content'=>'<p>Rapat kenaikan kelas membahas capaian akademik, kedisiplinan, kehadiran, serta perkembangan karakter peserta didik. Keputusan dilakukan secara objektif berdasarkan data dan hasil rapat dewan guru.</p>','image'=>'demo/news-meeting.svg','published_at'=>now()->subDays(28)],
            ['title'=>'Sosialisasi Mutu Budaya Madrasah: Disiplin, Kebersihan, dan Ekoteologi','category'=>'berita','excerpt'=>'Madrasah memperkuat budaya positif melalui kedisiplinan, kebersihan, dan kepedulian lingkungan.','content'=>'<p>Kegiatan sosialisasi mutu budaya madrasah menekankan pentingnya disiplin, kebersihan, dan ekoteologi sebagai kebiasaan sehari-hari seluruh warga madrasah.</p>','image'=>'demo/news-green.svg','published_at'=>now()->subDays(52)],
            ['title'=>'Penerimaan Murid Baru Jalur Reguler Gelombang 1','category'=>'pengumuman','excerpt'=>'Pendaftaran peserta didik baru tahun pelajaran 2026/2027 telah dibuka.','content'=>'<p>MAN 1 Lampung Selatan membuka penerimaan murid baru jalur reguler. Calon peserta didik dapat menyiapkan dokumen persyaratan dan mengikuti informasi jadwal melalui kanal resmi madrasah.</p>','image'=>'demo/spmb.svg','published_at'=>now()->subDays(8)],
            ['title'=>'Pramuka Raih Tiga Piala pada Perkemahan Tingkat Kabupaten','category'=>'prestasi','excerpt'=>'Tim Pramuka menorehkan prestasi membanggakan dalam kompetisi tingkat kabupaten.','content'=>'<p>Prestasi ini merupakan hasil latihan disiplin, kekompakan tim, dan dukungan pembina. Madrasah memberikan apresiasi kepada seluruh peserta.</p>','image'=>'demo/achievement.svg','published_at'=>now()->subMonths(2)],
            ['title'=>'Membangun Kebiasaan Belajar yang Efektif di Era Digital','category'=>'artikel','excerpt'=>'Strategi sederhana agar teknologi membantu proses belajar, bukan menjadi gangguan.','content'=>'<p>Belajar efektif dimulai dari tujuan yang jelas, jadwal realistis, lingkungan yang mendukung, dan evaluasi rutin. Gunakan teknologi untuk mengakses sumber belajar, membuat catatan, serta berkolaborasi secara sehat.</p><h3>Langkah praktis</h3><ol><li>Tentukan target harian.</li><li>Gunakan sesi fokus 25–45 menit.</li><li>Matikan notifikasi yang tidak diperlukan.</li><li>Tutup sesi dengan rangkuman singkat.</li></ol>','image'=>'demo/article.svg','published_at'=>now()->subDays(15)],
        ];
        foreach($posts as $post) Post::updateOrCreate(['slug'=>(string) str($post['title'])->slug('_')],array_merge($post,['author_id'=>$admin->id,'author_name'=>$admin->name,'slug'=>(string) str($post['title'])->slug('_'),'status'=>'published','featured'=>$post['featured']??false]));

        foreach([
            ['PPDBM dan Masa Taaruf Siswa','photo','demo/gallery-1.svg',null],
            ['Upacara Bendera','photo','demo/gallery-2.svg',null],
            ['Kegiatan Laboratorium Komputer','photo','demo/gallery-3.svg',null],
            ['Prestasi Siswa','photo','demo/gallery-4.svg',null],
            ['Profil MAN 1 Lampung Selatan','video','demo/gallery-video.svg','https://www.youtube.com/'],
        ] as $item) Gallery::updateOrCreate(['slug'=>(string) str($item[0])->slug('_')],['title'=>$item[0],'slug'=>(string) str($item[0])->slug('_'),'type'=>$item[1],'image'=>$item[2],'video_url'=>$item[3],'description'=>'Dokumentasi kegiatan MAN 1 Lampung Selatan.','published_at'=>now()->subDays(rand(3,60)),'active'=>true]);

        Infographic::updateOrCreate(
            ['slug' => 'profil_peserta_didik_2026'],
            [
                'title' => 'Profil Peserta Didik MAN 1 Lampung Selatan Tahun 2026',
                'meta_title' => 'Infografis Profil Peserta Didik MAN 1 Lampung Selatan 2026',
                'description' => 'Ringkasan visual data peserta didik. Data pada infografis contoh ini dapat disesuaikan melalui dashboard admin.',
                'meta_description' => 'Infografis profil dan jumlah peserta didik MAN 1 Lampung Selatan tahun 2026.',
                'meta_keywords' => 'infografis MAN 1 Lampung Selatan, data siswa, peserta didik madrasah',
                'image' => 'demo/infographic-students.svg',
                'source_name' => 'Data Referensi Pendidikan',
                'source_url' => null,
                'published_at' => now()->subDays(5),
                'featured' => true,
                'active' => true,
                'sort_order' => 1,
            ]
        );

        foreach([
            ['Kementerian Agama RI','https://kemenag.go.id','bi-building'],
            ['EMIS Madrasah','https://emis.kemenag.go.id','bi-database'],
            ['SIMPATIKA','https://simpatika.kemenag.go.id','bi-people'],
            ['Kanwil Kemenag Lampung','https://lampung.kemenag.go.id','bi-bank'],
            ['Rapor Digital Madrasah','#','bi-journal-check'],
            ['E-Kinerja BKN','https://kinerja.bkn.go.id','bi-graph-up-arrow'],
        ] as $i=>$link) Link::updateOrCreate(['name'=>$link[0]],['url'=>$link[1],'icon'=>$link[2],'sort_order'=>$i+1,'active'=>true]);

        foreach([
            ['Masa Taaruf Siswa Madrasah',now()->addDays(12)->setTime(7,30),'Aula dan lingkungan madrasah'],
            ['Rapat Komite dan Orang Tua',now()->addDays(18)->setTime(9,0),'Aula MAN 1 Lampung Selatan'],
            ['Pembinaan Olimpiade Sains',now()->addDays(25)->setTime(13,30),'Laboratorium Sains'],
        ] as $event) Event::updateOrCreate(['slug'=>(string) str($event[0])->slug('_')],['title'=>$event[0],'slug'=>(string) str($event[0])->slug('_'),'starts_at'=>$event[1],'location'=>$event[2],'description'=>'Agenda resmi madrasah. Jadwal dapat berubah dan akan diperbarui melalui portal ini.','active'=>true]);
    }
}
