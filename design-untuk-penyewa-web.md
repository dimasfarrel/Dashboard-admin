# Sewa Kost — Design & Build Spec (Sisi Mahasiswa)

Brief lengkap untuk AI code generator. Copy seluruh file ini ke AI builder mana pun.

---

## 1. Produk & Pengguna

Aplikasi web pencarian dan penyewaan kost (boarding house) untuk **mahasiswa dan pelajar di Indonesia**.

- Pengguna utama: mahasiswa 18–24 tahun, mayoritas membuka dari **HP**.
- Tujuan utama: pengguna menemukan kost favorit dalam **kurang dari 3 klik** (Beranda → Card kost → Detail).
- Bahasa antarmuka: **Bahasa Indonesia**.
- Sisi admin/pemilik kost **tidak** termasuk dalam spec ini.

---

## 2. Prinsip Desain

- **Modern, minimalis, bersih.** Tanpa dekorasi berlebihan, tanpa gradasi ramai, tanpa ilustrasi acak.
- Hirarki dibangun dari **whitespace + ukuran font**, bukan dari garis dan kotak bertumpuk.
- **Micro-interaction halus**: transisi `150–200ms ease-out` untuk hover, focus, dan perubahan state. Card naik halus saat hover (`translate-y-[-2px]` + shadow lebih dalam). Tanpa animasi masuk yang panjang atau parallax.
- **Ikon konsisten: Lucide React saja.** Ukuran standar 20px (inline teks) dan 24px (aksi utama). Ikon dekoratif diberi `aria-hidden="true"`.
- **Mobile-first**: rancang lebar 375px dulu, lalu naikkan ke `sm` (640) → `md` (768) → `lg` (1024) → `xl` (1280).
- Satu warna aksen (biru) + satu warna CTA (amber). Tidak ada warna lain selain status.

---

## 3. Palet Warna

Semua warna dipakai lewat **token semantik**. Jangan pernah menulis `bg-white`, `text-black`, atau hex literal di komponen.

### Light Mode

| Token | Hex | Penggunaan |
|---|---|---|
| `--background` | `#ffffff` | background halaman |
| `--card` | `#f8fafc` | permukaan card, panel filter, input |
| `--border` | `#e2e8f0` | garis 1px, pemisah |
| `--foreground` | `#0f172a` | teks utama, judul, harga |
| `--muted-foreground` | `#475569` | teks sekunder, label, metadata |
| `--primary` | `#2563eb` | link, tab aktif, tombol sekunder, ikon aktif |
| `--primary-foreground` | `#ffffff` | teks di atas primary |
| `--cta` | `#f59e0b` | tombol "Sewa / Pesan" & "Hubungi Pemilik" |
| `--cta-foreground` | `#0f172a` | teks di atas CTA (**gelap**, bukan putih) |

### Dark Mode

| Token | Hex |
|---|---|
| `--background` | `#0f172a` |
| `--card` | `#1e293b` |
| `--border` | `#334155` |
| `--foreground` | `#f8fafc` |
| `--muted-foreground` | `#94a3b8` |
| `--primary` | `#60a5fa` |
| `--primary-foreground` | `#0f172a` |
| `--cta` | `#fbbf24` |
| `--cta-foreground` | `#0f172a` |

### Warna Status (label tipe & pembayaran)

| Token | Light | Dark | Penggunaan |
|---|---|---|---|
| `--badge-putra` | bg `#dbeafe` / teks `#1e40af` | bg `#1e3a8a` / teks `#dbeafe` | label "Putra" |
| `--badge-putri` | bg `#fce7f3` / teks `#9d174d` | bg `#831843` / teks `#fce7f3` | label "Putri" |
| `--badge-campur` | bg `#e2e8f0` / teks `#334155` | bg `#334155` / teks `#e2e8f0` | label "Campur" |
| `--success` | bg `#dcfce7` / teks `#166534` | bg `#14532d` / teks `#dcfce7` | "Tersedia", "Lunas" |
| `--danger` | bg `#fee2e2` / teks `#991b1b` | bg `#7f1d1d` / teks `#fee2e2` | "Penuh", "Belum bayar" |

### Aturan kontras (WCAG AA, wajib)

- Teks di atas tombol CTA amber **harus** `#0f172a`. Putih di atas `#f59e0b` gagal AA.
- `--muted-foreground` adalah batas paling terang yang boleh dipakai untuk teks. Jangan pakai opacity (`text-muted-foreground/50`) atau abu-abu lebih terang.
- Border bukan pembawa makna; status selalu punya **teks**, warna hanya penguat.
- Focus ring: `2px solid var(--primary)` + `offset 2px`, selalu terlihat di kedua mode.

### Catatan Tailwind v4 (bila memakai stack ini)

Token didefinisikan di `src/styles.css` dalam `:root` / `.dark`, lalu dipetakan di `@theme inline` sebagai `--color-<nama>: var(--<nama>)`. Bila project mengharuskan `oklch`, konversikan hex di atas ke `oklch` — nilai visualnya identik, misalnya:

```css
:root {
  --background: oklch(1 0 0);            /* #ffffff */
  --card: oklch(0.984 0.003 247);        /* #f8fafc */
  --border: oklch(0.929 0.008 245);      /* #e2e8f0 */
  --foreground: oklch(0.208 0.042 265);  /* #0f172a */
  --muted-foreground: oklch(0.446 0.03 256); /* #475569 */
  --primary: oklch(0.546 0.215 262);     /* #2563eb */
  --cta: oklch(0.769 0.163 70);          /* #f59e0b */
}
.dark {
  --background: oklch(0.208 0.042 265);
  --card: oklch(0.279 0.041 260);        /* #1e293b */
  --border: oklch(0.372 0.044 257);      /* #334155 */
  --foreground: oklch(0.984 0.003 247);
  --muted-foreground: oklch(0.704 0.04 257); /* #94a3b8 */
  --primary: oklch(0.707 0.165 254);     /* #60a5fa */
  --cta: oklch(0.852 0.153 86);          /* #fbbf24 */
}
```

---

## 4. Tipografi

- Font: **Inter**, dimuat lewat `<link>` Google Fonts (jangan `@import` URL di CSS). Fallback: `ui-sans-serif, system-ui, sans-serif`.
- Bobot yang dipakai: 400, 500, 600, 700.
- Base body: **16px** (mobile) / 17px (desktop opsional). Jangan lebih kecil dari 14px di mana pun.

| Elemen | Ukuran | Bobot |
|---|---|---|
| `h1` judul halaman | 28px mobile / 34px desktop | 700 |
| `h2` judul section | 22px | 600 |
| Nama kost di card | 18px | 600 |
| Harga di card | 20px | 700 |
| Harga di halaman detail | 30px | 700 |
| Body / deskripsi | 16px, `line-height: 1.65` | 400 |
| Label & metadata | 14px | 500 |

- Radius global: `--radius: 0.75rem` (card 12px, tombol 10px, badge pill penuh).
- Shadow: hanya dua level — `sm` untuk card idle, `md` untuk hover. Tanpa shadow berwarna.

---

## 5. Struktur Halaman & Rute

### Navigasi

- **Mobile (< `lg`)**: bottom tab bar fixed, 4 tab — Beranda, Cari, Favorit, Profil. Setiap tab = ikon Lucide + label teks di bawahnya (12–13px). Tinggi bar 64px, tiap tab minimal 44×44px. Tab aktif berwarna `--primary` dan membawa `aria-current="page"`.
- **Desktop (`lg+`)**: top nav sticky — logo kiri, search bar ringkas di tengah, link Beranda / Cari / Favorit, toggle tema, lalu avatar/Profil (atau tombol "Masuk" bila belum login).
- Toggle Light/Dark tersedia di kedua layout, sebagai tombol berikon **+ teks** ("Mode Gelap" / "Mode Terang"), membawa `aria-pressed`.

### `/` — Beranda

1. **Hero ringkas** (bukan hero besar bergambar): sapaan "Cari kost di dekat kampusmu", satu baris subjudul.
2. **Search bar besar** (tinggi 56px): ikon `Search`, placeholder "Cari kota atau kampus… (mis. UGM, Depok)". Di bawahnya chip saran cepat: `UGM`, `UI`, `ITB`, `Undip`, `Unair`, `Brawijaya`.
3. **Filter cepat**: 3 chip horizontal — Putra, Putri, Campur.
4. **Kost Rekomendasi**: grid `KostCard` — 1 kolom mobile, 2 kolom `sm`, 3 kolom `lg`, 4 kolom `xl`. Setiap card: foto, nama, harga per bulan, jarak ke kampus, label tipe, tombol favorit.
5. **Kost Terdekat / Baru Ditambahkan**: satu section serupa (boleh horizontal scroll di mobile).
6. Tombol "Lihat semua kost" → `/search`.

### `/search` — Pencarian & Filter

- Baris atas: search input (terisi dari query) + tombol "Filter" berikon `SlidersHorizontal` (mobile membuka sheet dari bawah; `lg+` filter tampil sebagai sidebar kiri lebar 300px yang selalu terlihat).
- Isi panel filter:
  - **Rentang harga** — slider ganda, Rp 300.000 – Rp 3.000.000, langkah 50.000, nilai terpilih tampil sebagai teks di atas slider.
  - **Jarak ke kampus** — slider tunggal 0–10 km, langkah 0,5.
  - **Fasilitas** — checkbox: WiFi, AC, Kamar Mandi Dalam, Dapur Umum, Laundry. Setiap checkbox ikon + teks, area klik minimal 44px.
  - **Tipe kost** — radio: Semua, Putra, Putri, Campur.
  - Tombol "Terapkan Filter" (primary) dan "Reset" (ghost).
- Ringkasan hasil: "24 kost ditemukan" + dropdown urutkan (Termurah, Terdekat, Terbaru).
- Hasil: grid `KostCard` (1 / 2 / 3 kolom).
- **Empty state**: ikon `SearchX`, teks "Belum ada kost yang cocok", saran "Coba perlebar rentang harga atau jarak", tombol "Reset filter".
- Loading state: skeleton card, bukan spinner penuh halaman.

### `/kost/$id` — Detail Kost

1. **Image slider galeri**: rasio 4:3 (mobile) / 16:9 (desktop), tombol prev/next `ChevronLeft`/`ChevronRight` berukuran 44px dengan `aria-label`, indikator dot + teks "3 / 8", swipe di mobile, strip thumbnail di desktop.
2. **Header info**: `h1` nama kost, baris metadata — ikon `MapPin` + alamat, ikon `Navigation` + "0,8 km dari UGM", badge tipe, badge ketersediaan ("Tersedia" / "Penuh").
3. **Harga**: 30px bold + "/bulan", di bawahnya catatan kecil "Deposit 1 bulan · Minimal sewa 3 bulan".
4. **Deskripsi**: paragraf, maksimal ~4 baris lalu tombol "Baca selengkapnya".
5. **Fasilitas**: grid 2 kolom (mobile) / 3 kolom (desktop) berisi ikon Lucide + teks — `Wifi` WiFi, `AirVent` AC, `ShowerHead` Kamar Mandi Dalam, `CookingPot` Dapur Umum, `WashingMachine` Laundry, `Bike` Parkir, `ShieldCheck` Keamanan 24 Jam.
6. **Aturan kost**: daftar bullet singkat (jam malam, tamu, hewan).
7. **Lokasi**: embed `<iframe>` Google Maps, tinggi 320px, `loading="lazy"`, `title="Lokasi kost"`, sudut membulat, border 1px.
8. **Pemilik**: nama, foto kecil, "Biasanya membalas dalam 1 jam".
9. **Aksi utama** — sticky bottom bar di mobile, card sticky di kolom kanan pada `lg+`:
   - "Pesan / Sewa Sekarang" — background `--cta`, teks `--cta-foreground`, tinggi 56px, lebar penuh, ikon `CalendarCheck`.
   - "Hubungi Pemilik (WhatsApp)" — outline dengan border `--cta`, ikon `MessageCircle`, membuka `https://wa.me/<nomor>?text=<pesan terisi>`.
   - Ikon favorit (`Heart`) sebagai tombol 44×44px dengan `aria-label="Simpan ke favorit"`.
10. Section "Kost serupa di sekitar" di bagian bawah.

### `/profil` — Profil User

- Kartu identitas: foto profil bulat 88px, nama (20px semibold), email (muted), tombol "Edit Profil" (outline).
- **Riwayat Pemesanan**: daftar card — nama kost, tanggal masuk, durasi, total, badge status (Menunggu Pembayaran / Lunas / Selesai / Dibatalkan). Klik → detail kost.
- **Kost Favorit**: grid `KostCard` versi ringkas, dengan tombol hapus favorit.
- Menu bawah: Pengaturan, Mode Gelap/Terang, Keluar (`LogOut`, teks merah `--danger`).
- Empty state tiap section: teks jelas + tombol "Cari kost sekarang".

### `/login` dan `/register`

- Layout satu kolom terpusat, maksimal lebar 400px, tanpa gambar besar.
- `/login`: judul "Masuk", input Email, input Password (tombol mata `Eye`/`EyeOff` untuk lihat/sembunyikan, 44px), link "Lupa password?", tombol "Masuk" (primary, tinggi 52px), teks "Belum punya akun? Daftar".
- `/register`: Nama Lengkap, Email, Password, Konfirmasi Password, checkbox setuju syarat, tombol "Daftar".
- Setiap input punya `<label>` terlihat di atasnya (bukan hanya placeholder), pesan error di bawah field dengan teks + ikon `AlertCircle`.

---

## 6. Anatomi Komponen

**KostCard**
- Struktur: foto (rasio 4:3, `object-cover`, `loading="lazy"`, alt = "Foto <nama kost>") → badge tipe overlay kiri-atas → tombol favorit overlay kanan-atas (44×44, backdrop semi-transparan) → body: nama (2 baris maks, ellipsis), baris jarak (ikon `Navigation` + teks), baris fasilitas utama (maks 3 ikon + teks), harga bold + "/bln".
- Surface `--card`, border 1px `--border`, radius 12px, shadow `sm`.
- Hover: shadow `md`, `-translate-y-0.5`, transisi 180ms. Seluruh card adalah link; tombol favorit menghentikan propagasi.

**FilterPanel** — sheet bawah di mobile (dengan handle + tombol "Tutup" berlabel), sidebar sticky di desktop. Setiap grup filter punya heading `h3` 15px semibold.

**ImageSlider** — geser dengan tombol dan swipe, keyboard `ArrowLeft`/`ArrowRight`, tanpa autoplay.

**FacilityChip** — pill `--card` + border, ikon 20px + teks 14px, tinggi 40px, tidak interaktif di detail; versi filter berupa toggle dengan state terpilih berlatar `--primary` 10% dan border `--primary`.

**BookingCTA** — grup dua tombol; di mobile jadi bar sticky `--card` + border atas + `pb-safe`.

**AuthForm** — input tinggi 48px, radius 10px, background `--card`, border `--border`, focus border `--primary` + ring.

**Badge** — pill, padding `px-2.5 py-1`, 13px semibold, selalu berisi teks.

---

## 7. Data Mock (UI saja, tanpa backend)

```ts
export type TipeKost = "Putra" | "Putri" | "Campur";
export type Fasilitas =
  | "WiFi" | "AC" | "Kamar Mandi Dalam" | "Dapur Umum"
  | "Laundry" | "Parkir" | "Keamanan 24 Jam";

export type Kost = {
  id: string;
  nama: string;
  kota: string;
  alamat: string;
  kampusTerdekat: string;   // "UGM"
  jarakKm: number;          // 0.8
  hargaPerBulan: number;    // 950000
  tipe: TipeKost;
  fasilitas: Fasilitas[];
  foto: string[];           // URL, minimal 4
  deskripsi: string;
  aturan: string[];
  tersedia: boolean;
  sisaKamar: number;
  pemilik: { nama: string; whatsapp: string; foto: string };
  mapsEmbedUrl: string;
};

export type Booking = {
  id: string;
  kostId: string;
  namaKost: string;
  tanggalMasuk: string;     // "01 Sep 2026"
  durasiBulan: number;
  total: number;
  status: "Menunggu Pembayaran" | "Lunas" | "Selesai" | "Dibatalkan";
};

export type User = {
  nama: string;
  email: string;
  foto: string;
  favoritIds: string[];
  bookings: Booking[];
};
```

Contoh isi (buat 8–12 kost agar filter terasa nyata):

```ts
export const kostList: Kost[] = [
  {
    id: "1",
    nama: "Kost Melati Residence",
    kota: "Yogyakarta",
    alamat: "Jl. Kaliurang No. 21, Sleman",
    kampusTerdekat: "UGM",
    jarakKm: 0.8,
    hargaPerBulan: 950000,
    tipe: "Putri",
    fasilitas: ["WiFi", "AC", "Kamar Mandi Dalam", "Laundry"],
    foto: ["/kost-1-a.jpg", "/kost-1-b.jpg", "/kost-1-c.jpg", "/kost-1-d.jpg"],
    deskripsi: "Kost putri bersih dan tenang, 10 menit jalan kaki ke kampus UGM…",
    aturan: ["Jam malam 22.00", "Tamu tidak menginap", "Tidak untuk hewan"],
    tersedia: true,
    sisaKamar: 3,
    pemilik: { nama: "Bu Sri", whatsapp: "6281234567890", foto: "/owner-1.jpg" },
    mapsEmbedUrl: "https://www.google.com/maps?q=UGM+Yogyakarta&output=embed",
  },
  // Kost Bahagia Depok · UI · 1,4 km · Rp 1.250.000 · Campur
  // Kost Putra Sejahtera · ITB · 0,5 km · Rp 800.000 · Putra
  // Kost Anggrek Semarang · Undip · 2,1 km · Rp 700.000 · Putri  (tersedia: false)
];
```

Format tampilan:
- Mata uang: `Rp 950.000` — `new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 })`.
- Jarak: `0,8 km` (koma desimal).
- Tanggal: `01 Sep 2026`.

Favorit & sesi login disimpan di state React + `localStorage` (kunci `kost-favorit`, `kost-user`). Form login/register hanya validasi tampilan lalu redirect — tidak ada autentikasi nyata di versi ini.

---

## 8. Aksesibilitas (tidak bisa dinegosiasikan)

- Semua kombinasi teks/latar memenuhi **WCAG AA** (4.5:1 teks normal, 3:1 teks ≥24px) di light **dan** dark mode.
- Target sentuh minimal **44×44px** untuk semua tombol, tab, checkbox, dan panah slider.
- Tepat satu `<h1>` per halaman, satu `<main>`, setiap section pakai `aria-labelledby`.
- Setiap ikon punya teks berdampingan; ikon murni dekoratif diberi `aria-hidden="true"`; tombol ikon-saja (favorit, prev/next) wajib `aria-label`.
- Setiap input punya `<label>` terkait; error field terhubung via `aria-describedby`, pakai teks + ikon (bukan hanya warna merah).
- Slider harga/jarak: gunakan komponen yang mendukung keyboard dan `aria-valuetext` dalam bahasa Indonesia ("Rp 950.000").
- `<iframe>` maps punya `title`. Semua `<img>` punya `alt` deskriptif.
- Focus ring selalu terlihat; jangan `outline: none` tanpa pengganti.
- Status ("Tersedia", "Penuh", "Lunas") selalu berupa teks.

---

## 9. Catatan Teknis

### Versi Next.js (sesuai permintaan)

- Next.js App Router + React + Tailwind CSS.
- Dark/Light mode dengan **`next-themes`**: `<ThemeProvider attribute="class" defaultTheme="system" enableSystem>` di `app/layout.tsx`; kelas `dark` pada `<html>`; tambahkan `suppressHydrationWarning` pada `<html>`.
- Font Inter lewat `next/font/google`.
- Struktur rute: `app/page.tsx`, `app/search/page.tsx`, `app/kost/[id]/page.tsx`, `app/profil/page.tsx`, `app/login/page.tsx`, `app/register/page.tsx`.
- Metadata per halaman lewat `export const metadata` (title unik, description, og:title, og:description).

### Versi TanStack Start + Tailwind v4 (stack project ini)

- Rute: `src/routes/index.tsx`, `search.tsx`, `kost.$id.tsx`, `profil.tsx`, `login.tsx`, `register.tsx`. String `createFileRoute` memakai garis miring (`"/kost/$id"`).
- `next-themes` **tidak** dipakai; gunakan provider tema sendiri yang menoggle kelas `dark` pada `<html>` dan menyimpan pilihan di `localStorage` (kunci `kost-theme`), default mengikuti `prefers-color-scheme`. Baca `localStorage` di dalam `useEffect` agar tidak terjadi hydration mismatch.
- Font Inter dimuat lewat `<link>` di `head()` pada `src/routes/__root.tsx`. Jangan `@import` URL font di `src/styles.css`.
- Token warna di `src/styles.css`: nilai di `:root` / `.dark`, dipetakan di `@theme inline`. Utilitas kustom pakai `@utility`, varian pakai `@custom-variant` (bukan `tailwind.config.js` — file itu tidak dibaca di v4).
- Navigasi memakai `<Link to=... params=...>`, bukan `<a href>`.
- SEO: setiap rute punya `head()` sendiri dengan title & description unik ("Sewa Kost Mahasiswa · <Nama Aplikasi>", dsb.), plus `og:type` dan `twitter:card`.

### Upgrade path (opsional, di luar versi mock)

Bila nanti butuh data nyata: pindahkan `Kost`, `Booking`, `User` ke tabel database, ganti login tampilan dengan autentikasi email/password sungguhan, dan tabel favorit/pemesanan per pengguna dengan aturan akses per-baris. Bentuk tipe data di Bagian 7 sengaja dibuat agar bisa dipetakan 1:1 ke tabel database.

---

## 10. Yang Tidak Boleh Ada di Versi 1

Tanpa chat in-app, tanpa payment gateway, tanpa peta interaktif custom (cukup embed iframe), tanpa review/rating, tanpa notifikasi push, tanpa panel admin/pemilik, tanpa carousel autoplay, tanpa animasi scroll berat.
