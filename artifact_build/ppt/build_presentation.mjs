import fs from "node:fs/promises";
import { Presentation, PresentationFile } from "@oai/artifact-tool";
import { buildSlide01 } from "./layouts/slide-01.mjs";
import { buildSlide05 } from "./layouts/slide-05.mjs";
import { buildSlide07 } from "./layouts/slide-07.mjs";
import { buildSlide09 } from "./layouts/slide-09.mjs";
import { buildSlide10 } from "./layouts/slide-10.mjs";
import { buildSlide11 } from "./layouts/slide-11.mjs";
import { buildSlide13 } from "./layouts/slide-13.mjs";
import { buildSlide17 } from "./layouts/slide-17.mjs";
import { buildSlide18 } from "./layouts/slide-18.mjs";
import { buildSlide26 } from "./layouts/slide-26.mjs";

const ROOT = "D:/tester/MAN1Lamsel";
const OUTPUT = ROOT + "/deliverables/Presentasi_Website_MAN_1_Lampung_Selatan.pptx";
const PREVIEW_DIR = ROOT + "/artifact_build/ppt/rendered";
const LOGO = ROOT + "/artifact_build/docx/assets/logo.png";
const BLUE = "#0877C9";
const YELLOW = "#F4CD00";

async function writeBlob(path, blob) {
  await fs.writeFile(path, new Uint8Array(await blob.arrayBuffer()));
}

async function imageBytes(path) {
  const bytes = await fs.readFile(path);
  return bytes.buffer.slice(bytes.byteOffset, bytes.byteOffset + bytes.byteLength);
}

function brand(slide, number, notes, sources) {
  slide.shapes.add({
    geometry: "rect",
    name: "brand-blue-" + number,
    position: { left: 0, top: 712, width: 1150, height: 8 },
    fill: BLUE,
    line: { fill: "none", width: 0 },
  });
  slide.shapes.add({
    geometry: "rect",
    name: "brand-yellow-" + number,
    position: { left: 1150, top: 712, width: 130, height: 8 },
    fill: YELLOW,
    line: { fill: "none", width: 0 },
  });
  const sourceText = sources.map((source) => "- " + source).join("\n");
  slide.speakerNotes.textFrame.setText(notes + "\n\n[Sources]\n" + sourceText);
  slide.speakerNotes.setVisible(true);
}

function addLogo(slide, blob, name) {
  slide.images.add({
    blob,
    contentType: "image/png",
    alt: "Logo MAN 1 Lampung Selatan",
    fit: "contain",
    position: { left: 1090, top: 38, width: 120, height: 120 },
    name,
  });
}

async function main() {
  await fs.mkdir(PREVIEW_DIR, { recursive: true });
  await fs.mkdir(ROOT + "/deliverables", { recursive: true });
  const presentation = Presentation.create({ slideSize: { width: 1280, height: 720 } });
  const logo = await imageBytes(LOGO);

  const s1 = buildSlide01(presentation, {
    title: "MAN 1 LAMPUNG SELATAN",
    title2: "Website MAN 1 Lamsel",
    title3: "Portal publik, CMS, SSO Kemenag, dan sinkronisasi SIMPEG",
  });
  addLogo(s1, logo, "logo-cover");
  brand(s1, 1, "Buka dengan tujuan: satu portal yang mudah dikelola, aman, dan siap dipakai bersama.", [ROOT + "/README.md"]);

  const s2 = buildSlide05(presentation, {
    title: "Satu portal, tiga pengalaman",
    body1: {
      titleHere: "Untuk publik",
      loremIpsumDolorSitAmetConsecteturAdipiscing:
        "Informasi madrasah tersaji responsif: berita, artikel, profil, agenda, GTK, galeri, infografis, pengumuman, dan kontak.",
    },
    body2: {
      titleHere: "Untuk pengelola",
      loremIpsumDolorSitAmetConsecteturAdipiscing:
        "Administrator mengatur seluruh portal. Guru dan pegawai menulis artikel melalui ruang kerja yang dibatasi.",
    },
    footer1: "2",
  });
  brand(s2, 2, "Tekankan bahwa website publik dan dashboard memakai sumber data yang sama, tetapi hak aksesnya berbeda.", [ROOT + "/README.md", ROOT + "/routes/web.php"]);

  const s3 = buildSlide13(presentation, {
    title: "Cakupan sistem",
    body1: { titleGoesHere: "Konten", loremIpsumDolorSitAmetConsecteturAdipiscing: "Berita, artikel, pengumuman, prestasi, halaman profil, agenda, galeri, dan infografis." },
    body2: { titleGoesHere: "Tampilan", loremIpsumDolorSitAmetConsecteturAdipiscing: "Navbar bertingkat, logo, favicon, tema dinamis, banner, tautan layanan, dan desain responsif." },
    body3: { titleGoesHere: "Temuan & SEO", loremIpsumDolorSitAmetConsecteturAdipiscing: "Pencarian, metadata per konten, Open Graph, canonical URL, sitemap, dan robots." },
    body4: { titleGoesHere: "Integrasi", loremIpsumDolorSitAmetConsecteturAdipiscing: "Akun penulis, GTK, SSO Kemenag, sinkronisasi SIMPEG, dan pencatatan riwayat." },
    footer1: "3",
  });
  brand(s3, 3, "Gunakan slide ini sebagai peta kemampuan sebelum masuk ke peran dan proses.", [ROOT + "/README.md"]);

  const s4 = buildSlide07(presentation, {
    title: "Peran dibatasi dengan jelas",
    body1: "Pengunjung\nMembaca informasi publik dan mengirim pesan.",
    body2: "Penulis\nMembuat artikel sendiri. Semua artikel disimpan sebagai draft.",
    body3: "Administrator\nMeninjau, menerbitkan, dan mengelola seluruh portal serta akses.",
    footer1: "4",
  });
  brand(s4, 4, "Jelaskan prinsip akses minimum: penulis tidak dapat menerbitkan sendiri atau membuka menu administrator.", [ROOT + "/README.md", ROOT + "/app/Http/Middleware/AdminMiddleware.php"]);

  const s5 = buildSlide11(presentation, {
    title: "Dua jalur login, satu akun",
    body1: {
      topic: "Akun penulis dapat disiapkan secara manual lalu ditautkan ke data GTK.",
      loremIpsumDolorSitAmetConsecteturAdipiscing: "Ketika NIP yang sama berhasil masuk melalui SSO, akun menjadi hibrida.",
      loremIpsumDolorSitAmetConsecteturAdipiscing2: "Kata sandi manual tetap berfungsi.",
    },
    body2: "Login manual",
    body3: "SSO Kemenag",
    body4: { detailGoesHere: "Email + kata sandi lokal", detailGoesHere2: "Dibuat administrator", detailGoesHere3: "Bisa direset di CMS" },
    body5: { detailGoesHere: "Identitas resmi Kemenag", detailGoesHere2: "Diverifikasi melalui token", detailGoesHere3: "Wajib cocok dengan GTK aktif" },
    footer1: "5",
  });
  brand(s5, 5, "Akun hibrida memberi jalur cadangan tanpa melemahkan validasi identitas SSO.", [ROOT + "/SSO_KEMENAG.md", ROOT + "/app/Http/Controllers/Admin/AuthController.php", ROOT + "/app/Http/Controllers/Admin/SsoController.php"]);

  const s6 = buildSlide17(presentation, {
    title: "SSO hanya untuk GTK aktif",
    label1: "1. Autentikasi",
    label2: "2. Verifikasi",
    label3: "3. Pencocokan",
    body1: { titleHere: "Masuk di Kemenag", loremIpsumDolorSitAmetConsecteturAdipiscing: "Pengguna membuka signin resmi dengan APP ID aplikasi." },
    body2: { titleHere: "Token diperiksa", loremIpsumDolorSitAmetConsecteturAdipiscing: "Server memverifikasi Bearer token dan membaca profil pegawai." },
    body3: { titleHere: "NIP menentukan akses", loremIpsumDolorSitAmetConsecteturAdipiscing: "Login diterima hanya jika NIP ditemukan pada GTK yang aktif." },
    footer1: "6",
  });
  brand(s6, 6, "Jika NIP tidak ada atau GTK nonaktif, login ditolak. Akun baru selalu berperan sebagai penulis.", [ROOT + "/SSO_KEMENAG.md", ROOT + "/app/Http/Controllers/Admin/SsoController.php"]);

  const s7 = buildSlide18(presentation, {
    title: "Sinkron SIMPEG menjaga GTK siap pakai",
    body1: { titleHere: "Ambil", loremIpsumDolorSitAmetConsecteturAdipiscing: "API dipanggil per halaman, maksimal 400 pegawai, dengan parameter satker yang dikunci." },
    body2: { titleHere: "Saring & simpan", loremIpsumDolorSitAmetConsecteturAdipiscing: "Hanya kode 02090325000000 yang lolos. Upsert memakai NIP_BARU, lalu NIP." },
    body3: { titleHere: "Perbarui GTK", loremIpsumDolorSitAmetConsecteturAdipiscing: "Nama, NIP, jabatan, unit, jenis GTK, dan status aktif diperbarui." },
    label1: "API SIMPEG",
    label2: "VALIDASI",
    label3: "GTK & SSO",
    footer1: "7",
  });
  brand(s7, 7, "Tekankan filter dua lapis: parameter request dan pemeriksaan ulang setiap baris respons.", [ROOT + "/SIMPEG_SYNC.md", ROOT + "/app/Services/SimpegSynchronizer.php"]);

  const s8 = buildSlide10(presentation, {
    title: "Keamanan dibangun di setiap lapisan",
    body1: "Akses, identitas, dan operasi sensitif memiliki kontrol terpisah.",
    body2: {
      loremIpsumDolorSitAmetConsecteturAdipiscing: "Token SSO tidak disimpan. Kredensial API hanya berada di ENV.",
      loremIpsumDolorSitAmetConsecteturAdipiscing2: "Sinkronisasi ganda dicegah dengan application lock.",
    },
    label1: "HTTPS & verifikasi SSL",
    label2: "Role minimum untuk akun baru",
    label3: "NIP wajib cocok dengan GTK aktif",
    label4: "Riwayat sinkronisasi tercatat",
    label5: "Backup database, ENV, dan storage",
    footer1: "8",
  });
  brand(s8, 8, "Hubungkan kontrol teknis dengan kebiasaan operasional: kredensial tidak boleh masuk source code atau pesan publik.", [ROOT + "/SSO_KEMENAG.md", ROOT + "/SIMPEG_SYNC.md"]);

  const s9 = buildSlide09(presentation, {
    title: "Alur kerja administrator tetap sederhana",
    body1: {
      topic: "Siklus harian",
      loremIpsumDolorSitAmetConsecteturAdipiscing: "Pantau dashboard, tinjau draft, terbitkan konten, dan tindak lanjuti pesan masuk.",
      loremIpsumDolorSitAmetConsecteturAdipiscing2: "Setiap perubahan diperiksa kembali pada website publik.",
    },
    body2: { titleHere: "Konten", loremIpsumDolorSitAmetConsecteturAdipiscing: "Summernote, slug underscore, gambar sampul, status draft/published." },
    body3: { titleHere: "Identitas", loremIpsumDolorSitAmetConsecteturAdipiscing: "GTK, pengguna, login manual, SSO, logo, favicon, warna, dan SEO." },
    body4: { titleHere: "Operasi", loremIpsumDolorSitAmetConsecteturAdipiscing: "Sinkron SIMPEG, backup, log aplikasi, pemeriksaan ponsel, dan pembaruan sistem." },
    footer1: "9",
  });
  brand(s9, 9, "Tunjukkan bahwa banyak fitur tetap dirangkum dalam tiga rutinitas: konten, identitas, dan operasi.", [ROOT + "/README.md"]);

  const s10 = buildSlide13(presentation, {
    title: "Empat langkah menuju operasional penuh",
    body1: { titleGoesHere: "1. Konfigurasi", loremIpsumDolorSitAmetConsecteturAdipiscing: "Isi domain, database, APP ID SSO, dan kredensial API SIMPEG di ENV." },
    body2: { titleGoesHere: "2. Data awal", loremIpsumDolorSitAmetConsecteturAdipiscing: "Jalankan migration, storage link, sinkron SIMPEG, dan periksa data GTK." },
    body3: { titleGoesHere: "3. Uji akses", loremIpsumDolorSitAmetConsecteturAdipiscing: "Uji admin, penulis manual, SSO, draft artikel, publikasi, dan tampilan ponsel." },
    body4: { titleGoesHere: "4. Jaga sistem", loremIpsumDolorSitAmetConsecteturAdipiscing: "Backup rutin, nonaktifkan akun lama, pantau log, dan perbarui konten." },
    footer1: "10",
  });
  brand(s10, 10, "Gunakan sebagai checklist implementasi dan serah terima operasional.", [ROOT + "/README.md", ROOT + "/SIMPEG_SYNC.md", ROOT + "/SSO_KEMENAG.md"]);

  const s11 = buildSlide26(presentation, {
    title: "MAN 1 LAMPUNG SELATAN",
    title2: "Siap dikelola bersama",
    title3: { loremIpsumDetails: "Konten lebih tertata", loremIpsumDetails2: "Identitas pegawai tervalidasi", loremIpsumDetails3: "Akses tetap terkendali" },
  });
  addLogo(s11, logo, "logo-closing");
  brand(s11, 11, "Tutup dengan ajakan: tetapkan admin penanggung jawab, lengkapi kredensial produksi, lalu lakukan uji penerimaan.", [ROOT + "/README.md"]);

  for (const [index, slide] of presentation.slides.items.entries()) {
    const stem = "slide-" + String(index + 1).padStart(2, "0");
    const png = await presentation.export({ slide, format: "png", scale: 1.5 });
    await writeBlob(PREVIEW_DIR + "/" + stem + ".png", png);
    const layout = await slide.export({ format: "layout" });
    await fs.writeFile(PREVIEW_DIR + "/" + stem + ".layout.json", await layout.text());
  }

  const montage = await presentation.export({ format: "webp", montage: true, scale: 1 });
  await writeBlob(PREVIEW_DIR + "/montage.webp", montage);
  const pptx = await PresentationFile.exportPptx(presentation);
  await pptx.save(OUTPUT);
  console.log(OUTPUT);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
