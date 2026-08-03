from __future__ import annotations

import sys
from pathlib import Path

from docx import Document
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_TAB_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Inches, Pt, RGBColor

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / "deliverables" / "Panduan_Pengguna_Website_MAN_1_Lampung_Selatan.docx"
ASSET_DIR = Path(__file__).resolve().parent / "assets"
ASSET_DIR.mkdir(parents=True, exist_ok=True)
DOC_SKILL = Path(r"C:\Users\devDa\.codex\plugins\cache\openai-primary-runtime\documents\26.730.11710\skills\documents")
sys.path.insert(0, str(DOC_SKILL / "scripts"))
from table_geometry import apply_table_geometry  # noqa: E402

BLUE, BLUE_DARK, YELLOW = "0877C9", "045A9D", "F4CD00"
INK, MUTED, PANEL, LIGHT, BORDER = "20252B", "5E6873", "EAF5FC", "F6F8FA", "D7DEE5"
SUCCESS, WARNING = "E9F6EF", "FFF8DB"
FONT, MONO = "Calibri", "Consolas"
CONTENT_DXA = 9360


def rgb(value):
    return RGBColor.from_string(value)


def set_run_font(run, name=FONT, size=None, bold=None, color=INK, italic=None):
    run.font.name = name
    r_fonts = run._element.get_or_add_rPr().get_or_add_rFonts()
    r_fonts.set(qn("w:ascii"), name)
    r_fonts.set(qn("w:hAnsi"), name)
    run.font.color.rgb = rgb(color)
    if size is not None:
        run.font.size = Pt(size)
    if bold is not None:
        run.bold = bold
    if italic is not None:
        run.italic = italic


def set_fill(parent, color):
    pr = parent._tc.get_or_add_tcPr() if hasattr(parent, "_tc") else parent._p.get_or_add_pPr()
    shd = pr.find(qn("w:shd"))
    if shd is None:
        shd = OxmlElement("w:shd")
        pr.append(shd)
    shd.set(qn("w:fill"), color)


def set_border(paragraph, color=BLUE, side="left", size=18, space=8):
    p_pr = paragraph._p.get_or_add_pPr()
    p_bdr = p_pr.find(qn("w:pBdr"))
    if p_bdr is None:
        p_bdr = OxmlElement("w:pBdr")
        p_pr.append(p_bdr)
    border = OxmlElement(f"w:{side}")
    border.set(qn("w:val"), "single")
    border.set(qn("w:sz"), str(size))
    border.set(qn("w:space"), str(space))
    border.set(qn("w:color"), color)
    p_bdr.append(border)


def add_page_field(paragraph):
    run = paragraph.add_run()
    for kind, value in (("begin", None), ("instr", "PAGE"), ("separate", None), ("text", "1"), ("end", None)):
        if kind in ("begin", "separate", "end"):
            el = OxmlElement("w:fldChar")
            el.set(qn("w:fldCharType"), kind)
        elif kind == "instr":
            el = OxmlElement("w:instrText")
            el.set(qn("xml:space"), "preserve")
            el.text = value
        else:
            el = OxmlElement("w:t")
            el.text = value
        run._r.append(el)


def configure_document(doc):
    section = doc.sections[0]
    section.page_width, section.page_height = Inches(8.5), Inches(11)
    section.top_margin = section.right_margin = section.bottom_margin = section.left_margin = Inches(1)
    section.header_distance = section.footer_distance = Inches(0.492)

    normal = doc.styles["Normal"]
    normal.font.name, normal.font.size, normal.font.color.rgb = FONT, Pt(11), rgb(INK)
    normal._element.rPr.rFonts.set(qn("w:ascii"), FONT)
    normal._element.rPr.rFonts.set(qn("w:hAnsi"), FONT)
    normal.paragraph_format.space_before = Pt(0)
    normal.paragraph_format.space_after = Pt(6)
    normal.paragraph_format.line_spacing = 1.25

    for name, size, color, before, after in (
        ("Heading 1", 16, BLUE, 18, 10),
        ("Heading 2", 13, BLUE, 14, 7),
        ("Heading 3", 12, BLUE_DARK, 10, 5),
    ):
        style = doc.styles[name]
        style.font.name, style.font.size, style.font.bold, style.font.color.rgb = FONT, Pt(size), True, rgb(color)
        style._element.rPr.rFonts.set(qn("w:ascii"), FONT)
        style._element.rPr.rFonts.set(qn("w:hAnsi"), FONT)
        style.paragraph_format.space_before, style.paragraph_format.space_after = Pt(before), Pt(after)
        style.paragraph_format.keep_with_next = True

    for name in ("List Bullet", "List Number"):
        style = doc.styles[name]
        style.font.name, style.font.size, style.font.color.rgb = FONT, Pt(11), rgb(INK)
        style.paragraph_format.left_indent = Inches(0.375)
        style.paragraph_format.first_line_indent = Inches(-0.188)
        style.paragraph_format.space_after = Pt(4)
        style.paragraph_format.line_spacing = 1.25

    header = section.header.paragraphs[0]
    run = header.add_run("MAN 1 LAMPUNG SELATAN  /  PANDUAN PENGGUNA")
    set_run_font(run, size=8.5, bold=True, color=MUTED)

    footer = section.footer.paragraphs[0]
    footer.paragraph_format.tab_stops.add_tab_stop(Inches(6.5), WD_TAB_ALIGNMENT.RIGHT)
    set_run_font(footer.add_run("Modern School CMS v1.9"), size=8.5, color=MUTED)
    footer.add_run("\t")
    set_run_font(footer.add_run("Halaman "), size=8.5, color=MUTED)
    add_page_field(footer)


def title(doc, text, size=28, color=BLUE_DARK, align=WD_ALIGN_PARAGRAPH.LEFT, after=8):
    p = doc.add_paragraph()
    p.alignment = align
    p.paragraph_format.space_after = Pt(after)
    p.paragraph_format.keep_with_next = True
    set_run_font(p.add_run(text), size=size, bold=True, color=color)
    return p


def subtitle(doc, text, size=13, color=MUTED, align=WD_ALIGN_PARAGRAPH.LEFT, after=12):
    p = doc.add_paragraph()
    p.alignment = align
    p.paragraph_format.space_after = Pt(after)
    set_run_font(p.add_run(text), size=size, color=color)
    return p


def heading(doc, text, level=1):
    return doc.add_paragraph(text, style=f"Heading {level}")


def body(doc, text):
    p = doc.add_paragraph()
    set_run_font(p.add_run(text))
    return p


def bullets(doc, items):
    for item in items:
        p = doc.add_paragraph(style="List Bullet")
        set_run_font(p.add_run(item))


def steps(doc, items):
    for item in items:
        p = doc.add_paragraph(style="List Number")
        set_run_font(p.add_run(item))


def callout(doc, label, text, kind="info"):
    fills = {"info": PANEL, "warning": WARNING, "success": SUCCESS}
    borders = {"info": BLUE, "warning": YELLOW, "success": "2D8A57"}
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = p.paragraph_format.right_indent = Inches(0.08)
    p.paragraph_format.space_before, p.paragraph_format.space_after = Pt(6), Pt(10)
    set_fill(p, fills[kind])
    set_border(p, borders[kind])
    set_run_font(p.add_run(label + "  "), bold=True, color=BLUE_DARK)
    set_run_font(p.add_run(text))


def code(doc, text):
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = p.paragraph_format.right_indent = Inches(0.15)
    p.paragraph_format.space_before, p.paragraph_format.space_after = Pt(4), Pt(8)
    p.paragraph_format.line_spacing = 1.1
    set_fill(p, LIGHT)
    set_border(p, BLUE_DARK, size=10, space=6)
    set_run_font(p.add_run(text), name=MONO, size=9.2)


def table(doc, headers, rows, widths):
    tbl = doc.add_table(rows=1, cols=len(headers))
    tbl.style = "Table Grid"
    tbl.allow_autofit = False
    tr_pr = tbl.rows[0]._tr.get_or_add_trPr()
    repeat = OxmlElement("w:tblHeader")
    repeat.set(qn("w:val"), "true")
    tr_pr.append(repeat)
    for idx, label in enumerate(headers):
        cell = tbl.rows[0].cells[idx]
        set_fill(cell, PANEL)
        cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
        p = cell.paragraphs[0]
        p.paragraph_format.space_after = Pt(0)
        set_run_font(p.add_run(label), size=9.5, bold=True, color=BLUE_DARK)
    for values in rows:
        row = tbl.add_row()
        for idx, value in enumerate(values):
            cell = row.cells[idx]
            cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
            p = cell.paragraphs[0]
            p.paragraph_format.space_after = Pt(0)
            p.paragraph_format.line_spacing = 1.1
            set_run_font(p.add_run(str(value)), size=9.3)
    apply_table_geometry(
        tbl,
        widths,
        table_width_dxa=CONTENT_DXA,
        indent_dxa=120,
        cell_margins_dxa={"top": 80, "bottom": 80, "start": 120, "end": 120},
    )
    doc.add_paragraph().paragraph_format.space_after = Pt(2)


def new_page(doc):
    doc.add_page_break()


def logo_png():
    return ASSET_DIR / "logo.png"


def add_cover(doc):
    doc.add_paragraph().paragraph_format.space_after = Pt(24)
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    inline = p.add_run().add_picture(str(logo_png()), width=Inches(1.25))
    inline._inline.docPr.set("name", "Logo MAN 1 Lampung Selatan")
    inline._inline.docPr.set("descr", "Logo madrasah pada sampul panduan pengguna")
    p.paragraph_format.space_after = Pt(22)
    subtitle(doc, "MAN 1 LAMPUNG SELATAN", 11, BLUE, WD_ALIGN_PARAGRAPH.CENTER, 14)
    title(doc, "Panduan Pengguna Website dan CMS", 29, BLUE_DARK, WD_ALIGN_PARAGRAPH.CENTER, 10)
    subtitle(
        doc,
        "Publikasi konten, pengelolaan GTK, login penulis, SSO Kemenag, dan sinkronisasi SIMPEG",
        14,
        BLUE_DARK,
        WD_ALIGN_PARAGRAPH.CENTER,
        48,
    )
    subtitle(doc, "Versi 1.9  |  Agustus 2026", 11, MUTED, WD_ALIGN_PARAGRAPH.CENTER, 5)
    subtitle(
        doc,
        "Untuk Administrator, Guru/Pegawai Penulis, dan Pengelola Teknologi Informasi",
        10.5,
        MUTED,
        WD_ALIGN_PARAGRAPH.CENTER,
        0,
    )


def build():
    doc = Document()
    configure_document(doc)
    add_cover(doc)

    new_page(doc)
    heading(doc, "1. Tentang Sistem")
    body(doc, "Website MAN 1 Lampung Selatan adalah portal informasi publik dan CMS internal berbasis Laravel 12. Sistem memisahkan pengelolaan administrator dan ruang kerja penulis, tetapi menggunakan satu halaman login.")
    callout(doc, "Tujuan utama", "Memudahkan madrasah menerbitkan informasi, menjaga identitas visual, mengelola data GTK, dan menghubungkan akun penulis dengan identitas pegawai Kemenag berdasarkan NIP.", "success")
    heading(doc, "1.1 Ruang Lingkup", 2)
    bullets(doc, [
        "Website publik: beranda, berita, artikel, pengumuman, prestasi, profil, agenda, GTK, galeri, dan infografis.",
        "Dashboard administrator: konten, halaman profil, menu navbar, tema, SEO, pengguna, GTK, pesan, dan layanan.",
        "Ruang penulis: penulisan artikel dengan Summernote, penyimpanan draft, dan peninjauan administrator.",
        "Integrasi Kemenag: login SSO dan sinkronisasi pegawai SIMPEG khusus satuan kerja madrasah.",
    ])
    heading(doc, "1.2 Peta Menu Utama", 2)
    table(doc, ["Peran", "Menu yang Digunakan", "Hasil"], [
        ["Administrator", "Dashboard, Konten, GTK, Sinkron SIMPEG, Pengguna, Tampilan", "Mengendalikan portal dan akses"],
        ["Penulis", "Artikel Saya, Akun Saya", "Membuat dan memperbaiki draft artikel"],
        ["Pengunjung", "Website publik", "Membaca informasi dan mengirim pesan"],
    ], [1800, 3900, 3660])

    new_page(doc)
    heading(doc, "2. Masuk dan Keluar dari Dashboard")
    heading(doc, "2.1 Alamat Login", 2)
    code(doc, "https://domain-sekolah.sch.id/admin/login")
    body(doc, "Gunakan domain produksi madrasah. Untuk pengembangan lokal, alamat umumnya http://127.0.0.1:8000/admin/login.")
    heading(doc, "2.2 Login Manual", 2)
    steps(doc, [
        "Buka halaman login dashboard.",
        "Masukkan email akun dan kata sandi yang diberikan administrator.",
        "Aktifkan Ingat saya hanya pada perangkat pribadi.",
        "Tekan Masuk ke Dashboard.",
    ])
    heading(doc, "2.3 Login SSO Kemenag", 2)
    steps(doc, [
        "Tekan Masuk dengan SSO Kemenag pada bagian atas formulir login.",
        "Selesaikan autentikasi pada halaman resmi Kemenag.",
        "Sistem memverifikasi token dan membaca NIP pegawai.",
        "NIP harus ditemukan pada data GTK yang aktif.",
        "Jika valid, akun penulis dibuat atau ditautkan lalu dashboard dibuka.",
    ])
    callout(doc, "Syarat SSO", "Tombol SSO tampil setelah APP ID diisi. Pegawai tidak dapat masuk jika NIP tidak tersedia pada data GTK aktif.", "warning")
    heading(doc, "2.4 Keluar dengan Aman", 2)
    body(doc, "Buka menu akun di kanan atas, kemudian pilih Keluar. Sesi lokal akan dihapus. Untuk login SSO, pengguna juga diarahkan ke halaman signout Kemenag.")

    new_page(doc)
    heading(doc, "3. Peran dan Hak Akses")
    table(doc, ["Fungsi", "Administrator", "Penulis"], [
        ["Melihat dashboard", "Ya", "Ya"],
        ["Mengelola seluruh konten", "Ya", "Tidak"],
        ["Membuat artikel", "Ya", "Ya"],
        ["Menerbitkan artikel", "Ya", "Tidak"],
        ["Mengubah artikel terbit", "Ya", "Tidak"],
        ["Mengelola GTK dan pengguna", "Ya", "Tidak"],
        ["Menjalankan sinkronisasi SIMPEG", "Ya", "Tidak"],
        ["Mengatur tema, menu, dan SEO", "Ya", "Tidak"],
    ], [3900, 2730, 2730])
    callout(doc, "Prinsip akses minimum", "Akun baru dari SSO selalu memperoleh role Penulis Artikel. Role administrator hanya diberikan secara manual oleh administrator lain.")
    heading(doc, "3.1 Jenis Metode Login", 2)
    table(doc, ["Metode", "Keterangan"], [
        ["Login Manual", "Memakai email dan kata sandi lokal."],
        ["SSO Kemenag", "Identitas dan autentikasi berasal dari SSO; kata sandi tidak dikelola CMS."],
        ["Manual + SSO", "Akun lokal yang ditautkan ke NIP GTK; kedua metode tetap dapat digunakan."],
    ], [2700, 6660])

    new_page(doc)
    heading(doc, "4. Mengelola Berita dan Artikel")
    heading(doc, "4.1 Membuat Konten", 2)
    steps(doc, [
        "Buka Berita & Artikel sebagai admin, atau Artikel Saya sebagai penulis.",
        "Tekan Tambah Konten dan pilih jenis konten sesuai hak akses.",
        "Isi judul, ringkasan, kategori, gambar sampul, dan isi konten.",
        "Gunakan Summernote untuk format teks, daftar, tabel, tautan, gambar, atau video.",
        "Simpan. Artikel penulis selalu menjadi draft untuk ditinjau administrator.",
    ])
    heading(doc, "4.2 Aturan Slug", 2)
    body(doc, "Slug dibuat otomatis dari judul menggunakan underscore.")
    code(doc, "Judul: Kegiatan Literasi Madrasah\nSlug : kegiatan_literasi_madrasah")
    heading(doc, "4.3 Status Publikasi", 2)
    table(doc, ["Status", "Makna", "Tindakan"], [
        ["Draft", "Belum tampil di website", "Perbaiki dan tinjau"],
        ["Published", "Sudah tampil untuk publik", "Admin dapat memperbarui atau menarik publikasi"],
    ], [1800, 3600, 3960])
    callout(doc, "Nama penulis", "Nama penulis disimpan sebagai snapshot dan tetap tampil meskipun profil akun berubah atau dinonaktifkan.")

    new_page(doc)
    heading(doc, "5. Konten Publik, Menu, Tema, dan SEO")
    heading(doc, "5.1 Modul Konten Lain", 2)
    bullets(doc, [
        "Halaman Profil untuk visi-misi, sejarah, fasilitas, dan program unggulan.",
        "Galeri foto/video, infografis, agenda, banner utama, tautan layanan, dan pesan masuk.",
        "Semua editor isi panjang menggunakan Summernote.",
    ])
    heading(doc, "5.2 Menu Navbar Bertingkat", 2)
    steps(doc, [
        "Buka Tampilan > Menu Navbar dan tambahkan nama serta URL.",
        "Pilih menu induk untuk membuat submenu.",
        "Atur urutan, target tab, ikon, dan status aktif.",
        "Periksa tampilan desktop dan ponsel setelah disimpan.",
    ])
    heading(doc, "5.3 Tema dan Identitas", 2)
    body(doc, "Buka Tampilan > Pengaturan, Tema & SEO untuk mengubah logo, favicon, warna, motif, kontak, dan media sosial.")
    callout(doc, "Komposisi awal", "Putih 75%, Biru 18%, Kuning 5%, dan Hitam/Abu 2%. Total komposisi warna harus 100%.")
    heading(doc, "5.4 SEO", 2)
    bullets(doc, [
        "Isi judul SEO, meta description, gambar Open Graph, dan canonical URL.",
        "Sitemap tersedia pada /sitemap.xml dan robots pada /robots.txt.",
        "Berita, artikel, halaman, dan infografis memiliki metadata SEO khusus.",
    ])

    new_page(doc)
    heading(doc, "6. Mengelola GTK dan Akun Penulis")
    heading(doc, "6.1 Data GTK", 2)
    steps(doc, [
        "Buka menu GTK.",
        "Isi nama lengkap, NIP, jabatan, mata pelajaran/unit, jenis, foto, dan status.",
        "Masukkan NIP hanya dengan angka dan pastikan sama dengan NIP SIMPEG/SSO.",
        "Aktifkan GTK yang boleh menggunakan SSO.",
    ])
    heading(doc, "6.2 Membuat Akun Penulis Manual", 2)
    steps(doc, [
        "Buka Pengguna & Penulis, lalu tekan Tambah Penulis Manual.",
        "Pilih data GTK jika akun akan ditautkan ke identitas pegawai.",
        "Isi nama, email, NIP, unit kerja, kata sandi, dan aktifkan akun.",
        "Minta pengguna mengganti kata sandi melalui Akun Saya.",
    ])
    heading(doc, "6.3 Parameter Penautan", 2)
    table(doc, ["Urutan", "Parameter", "Penggunaan"], [
        ["1", "NIP_BARU", "Identitas utama hasil sinkronisasi SIMPEG"],
        ["2", "NIP", "Digunakan jika NIP_BARU kosong"],
        ["3", "Nama lengkap", "Hanya membantu mencocokkan GTK lama yang NIP-nya masih kosong"],
    ], [1200, 2400, 5760])
    callout(doc, "Penting", "NIP adalah kunci hubungan SIMPEG, GTK, akun penulis, dan login SSO. Nama bukan identitas utama.", "warning")

    new_page(doc)
    heading(doc, "7. Sinkronisasi Pegawai SIMPEG")
    callout(doc, "Filter terkunci", "Hanya data dengan KODE_SATUAN_KERJA = 02090325000000 yang boleh disimpan.", "success")
    heading(doc, "7.1 Menjalankan Sinkronisasi", 2)
    steps(doc, [
        "Pastikan kredensial API SIMPEG sudah diisi pada .env.",
        "Buka menu Sinkron SIMPEG dan periksa kode satuan kerja.",
        "Tekan Sinkronkan Sekarang dan tunggu proses selesai.",
        "Periksa jumlah sesuai filter, data baru, data diperbarui, data dilewati, dan hasil GTK.",
        "Tinjau riwayat sinkronisasi jika terjadi kegagalan.",
    ])
    heading(doc, "7.2 Cara Sistem Menyimpan Data", 2)
    bullets(doc, [
        "API dipanggil dengan parameter satker, start, dan limit maksimal 400.",
        "Setiap baris diperiksa ulang melalui KODE_SATUAN_KERJA atau KODE_SATKER_1.",
        "Upsert memakai NIP_BARU, dengan fallback NIP.",
        "Data satker lain dilewati dan tidak masuk database.",
        "Sinkronisasi berulang memperbarui data tanpa membuat duplikat.",
    ])
    heading(doc, "7.3 Sinkronisasi ke GTK", 2)
    table(doc, ["Data SIMPEG", "Data GTK"], [
        ["NIP_BARU / NIP", "NIP"],
        ["NAMA_LENGKAP / NAMA", "Nama"],
        ["TAMPIL_JABATAN", "Jabatan"],
        ["Satker paling spesifik", "Mata pelajaran/unit"],
        ["Jabatan", "Jenis kepala/guru/pegawai"],
    ], [3900, 5460])
    code(doc, "php artisan simpeg:sync")

    new_page(doc)
    heading(doc, "8. Alur SSO Kemenag dan Keamanannya")
    steps(doc, [
        "Website mengarahkan pengguna ke endpoint signin Kemenag dengan APP ID.",
        "Kemenag mengembalikan token ke callback website.",
        "Server memverifikasi token sebagai Bearer token ke endpoint verify.",
        "Sistem mengambil profil pegawai dan menormalisasi NIP.",
        "NIP dicocokkan dengan GTK aktif.",
        "Akun penulis dibuat atau ditautkan; administrator tidak dibuat otomatis.",
    ])
    heading(doc, "8.1 Kondisi Login Ditolak", 2)
    bullets(doc, [
        "APP ID atau endpoint belum lengkap.",
        "Sesi login kedaluwarsa atau token tidak valid.",
        "NIP tidak ada pada GTK aktif.",
        "NIP atau email terhubung ke lebih dari satu akun.",
        "Akun pengguna dinonaktifkan.",
    ])
    heading(doc, "8.2 Konfigurasi Inti", 2)
    code(doc, 'KEMENAG_SSO_ENABLED=true\nKEMENAG_SSO_APP_ID="APP_ID_DARI_KEMENAG"\nKEMENAG_SSO_REQUIRE_STAFF_MATCH=true\nKEMENAG_SSO_VERIFY_SSL=true')
    callout(doc, "Keamanan", "Token SSO tidak disimpan. Jangan menaruh APP ID, email API, atau kata sandi API di repository publik.", "warning")

    new_page(doc)
    heading(doc, "9. Pemeriksaan Website Publik")
    heading(doc, "9.1 Setelah Menerbitkan Konten", 2)
    bullets(doc, [
        "Periksa halaman dari desktop dan ponsel.",
        "Pastikan gambar, judul, tanggal, kategori, nama penulis, dan isi tampil proporsional.",
        "Uji tautan internal, eksternal, tombol berbagi, dan URL slug underscore.",
    ])
    heading(doc, "9.2 Pemeriksaan Berkala", 2)
    table(doc, ["Frekuensi", "Pemeriksaan"], [
        ["Harian", "Draft baru, pesan masuk, berita/pengumuman penting, tautan rusak"],
        ["Mingguan", "Agenda, banner, galeri, data GTK, akun nonaktif"],
        ["Bulanan", "Backup database, dependensi, log aplikasi, sitemap, dan SEO"],
        ["Setiap perubahan", "Tampilan ponsel, menu, favicon, metadata berbagi, dan hak akses"],
    ], [1800, 7560])

    new_page(doc)
    heading(doc, "10. Pemeliharaan dan Troubleshooting")
    heading(doc, "10.1 Perintah Pemeliharaan", 2)
    code(doc, "php artisan optimize:clear\nphp artisan migrate --force\nphp artisan storage:link\nphp artisan test")
    heading(doc, "10.2 Masalah Umum", 2)
    table(doc, ["Masalah", "Pemeriksaan Awal"], [
        ["Tombol SSO tidak tampil", "Periksa KEMENAG_SSO_ENABLED dan KEMENAG_SSO_APP_ID; bersihkan cache."],
        ["SSO menolak NIP", "Pastikan NIP ada pada GTK, hanya angka, dan status GTK aktif."],
        ["Sinkron SIMPEG gagal", "Periksa kredensial API, koneksi, SSL, dan riwayat sinkronisasi."],
        ["Gambar tidak tampil", "Periksa storage link dan file pada storage/app/public."],
        ["Tema belum berubah", "Jalankan optimize:clear dan lakukan hard refresh browser."],
        ["Artikel penulis tidak tampil", "Artikel penulis adalah draft; administrator harus menerbitkannya."],
    ], [3000, 6360])
    callout(doc, "Jangan gunakan", "php artisan migrate:fresh pada database produksi karena menghapus seluruh tabel dan data.", "warning")

    new_page(doc)
    heading(doc, "11. Checklist Keamanan Administrator")
    bullets(doc, [
        "Gunakan HTTPS dan APP_DEBUG=false pada produksi.",
        "Ganti kata sandi awal administrator dan gunakan kata sandi unik.",
        "Nonaktifkan akun yang tidak lagi membutuhkan akses.",
        "Jangan membagikan kredensial API melalui source code, chat publik, atau tangkapan layar.",
        "Aktifkan verifikasi SSL untuk SSO dan SIMPEG.",
        "Backup database, .env, dan storage/app/public secara berkala.",
        "Periksa log sinkronisasi dan storage/logs/laravel.log ketika terjadi kegagalan.",
        "Berikan role administrator hanya kepada petugas yang benar-benar memerlukan.",
    ])
    heading(doc, "11.1 Berkas Referensi Teknis", 2)
    bullets(doc, [
        "README.md - gambaran proyek dan instalasi.",
        "SSO_KEMENAG.md - konfigurasi dan alur SSO.",
        "SIMPEG_SYNC.md - sinkronisasi pegawai dan filter satker.",
        "THEME_MANAGER.md - pengaturan warna dan identitas.",
        "UPGRADE_V1.8.md dan UPGRADE_V1.9.md - langkah peningkatan versi.",
    ])
    callout(doc, "Dukungan", "Saat melaporkan masalah, sertakan waktu, akun/peran, URL, langkah, dan pesan kesalahan. Jangan menyertakan kata sandi atau token.")

    props = doc.core_properties
    props.title = "Panduan Pengguna Website dan CMS MAN 1 Lampung Selatan"
    props.subject = "Panduan administrator, penulis, SSO Kemenag, dan sinkronisasi SIMPEG"
    props.author = "MAN 1 Lampung Selatan"
    props.keywords = "MAN 1 Lampung Selatan, CMS, SSO Kemenag, SIMPEG, GTK"
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    doc.save(OUTPUT)
    print(OUTPUT)


if __name__ == "__main__":
    build()
