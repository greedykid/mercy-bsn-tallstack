# Design System & Pedoman Antarmuka (Design System Master Specification)
## MERCY (Sistem Informasi Akreditasi & Surveilen LPK KAN / BSN)
### Berbasis TallStackUI v4, Tailwind CSS v4, dan Prinsip Antislop (Operate Mode)

---

## 1. Filosofi Desain & Karakter Visual

### 1.1 Mode Desain: *Operate*
MERCY adalah sistem kerja operasional (*mission-critical back-office & monitoring tool*) untuk Komite Akreditasi Nasional (KAN) dan Badan Standardisasi Nasional (BSN). Antarmuka dirancang murni untuk mode **Operate**:
* **Prioritas Utama**: Keterbacaan instan (*scanability*), efisiensi alur kerja PIC, konsistensi penempatan kontrol, dan ketenangan visual di tengah tingginya densitas data.
* **Karakter Visual**: Berwibawa (*institutional authority*), presisi laboratorium, tenang, berkontras tajam, bebas dari ornamen dekoratif tanpa fungsi (*zero visual noise*).
* **Bebas AI Slop**: Menolak segala bentuk klise antarmuka generik AI (gradasi ungu-biru neon, *glassmorphism* berlebihan, kartu melayang tanpa batas, emoji dekoratif, dan teks klise).

### 1.2 Aturan Baku Antislop UI (*The Antislop Filter*)
1. **Dilarang Gradasi Biru-Ungu & Neon Glow**: Palet warna berakar kuat pada warna netral institusi dan indikator status regulasi resmi.
2. **Dilarang Bentuk Pil untuk Seluruh Komponen**: Radius sudut didefinisikan secara hierarkis dan terukur. Bentuk *pill* (`rounded-full`) hanya diperkenankan untuk *badge* status regulasi dan avatar pengguna.
3. **Dilarang Bayangan Melayang Lembut (*Overly Soft Floating Shadows*)**: Permukaan data didefinisikan menggunakan batas garis subtil (`border border-neutral-200 dark:border-neutral-800`), bukan bayangan tebal.
4. **Dilarang Pola Latar Titik/Grid (*Background Grid/Dots*)**: Latar belakang aplikasi tetap bersih (`bg-neutral-50 dark:bg-neutral-950`).
5. **Dilarang Emoji Dekoratif pada Label & Tombol**: Ikon hanya digunakan bila mempercepat pengenalan aksi secara fungsional.
6. **Dilarang Status Dot Berkedip Tanpa Arti**: Titik indikator hanya digunakan jika merefleksikan status koneksi aktif atau sinkronisasi data riil.

---

## 2. Token Desain & Sistem Warna (Design Tokens & Color Palette)

### 2.1 Palet Warna Inti (Core Palette)
Menggunakan integrasi Tailwind CSS v4 `@theme` yang terpasang di `resources/css/theme.css`:

| Token | Nilai CSS (Light Mode) | Nilai CSS (Dark Mode) | Peruntukan & Karakter Visual |
| :--- | :--- | :--- | :--- |
| `--color-primary` | `neutral-800` (`#262626`) | `white` (`#ffffff`) | Warna utama elemen interaktif, tombol primer, dan teks judul utama. |
| `--color-primary-content` | `neutral-800` (`#262626`) | `white` (`#ffffff`) | Warna isi elemen navigasi aktif dan *brand identifier*. |
| `--color-primary-fg` | `white` (`#ffffff`) | `neutral-900` (`#171717`) | Kontras teks di atas permukaan warna primer. |
| `Canvas Background` | `neutral-50` (`#fafafa`) | `neutral-950` (`#0a0a0a`) | Latar belakang kanvas aplikasi yang tenang untuk mata operator. |
| `Surface Background` | `white` (`#ffffff`) | `neutral-900` (`#171717`) | Latar belakang kartu data, sidebar, navbar, dan modal dialog. |
| `Surface Subdued` | `neutral-100` (`#f5f5f5`) | `neutral-800/60` | Area header tabel, input background sekunder, dan pembungkus kode. |
| `Borders / Dividers` | `neutral-200` (`#e5e5e5`) | `neutral-800` (`#262626`) | Garis batas struktural penegas batas antar-seksi data. |
| `Text Primary` | `neutral-900` (`#171717`) | `neutral-100` (`#f5f5f5`) | Teks judul, label aktif, dan nilai data utama. |
| `Text Secondary` | `neutral-600` (`#525252`) | `neutral-400` (`#a3a3a3`) | Teks deskripsi, label pembantu, dan instruksi sekunder. |
| `Text Muted` | `neutral-400` (`#a3a3a3`) | `neutral-500` (`#737373`) | Placeholder form, teks timestamp nonaktif, dan border sekunder. |

### 2.2 Sistem Pewarnaan Status Kepatuhan Regulasi KAN (WCAG AAA/AA)
Status kepatuhan LPK dan Tindakan Perbaikan (TP) memiliki arti hukum yang tegas. Pewarnaan diwajibkan mengikuti sistem 6-kategori berikut:

| Status Kepatuhan | Nilai Enum | Token Warna Solid | Tampilan Badge TallStackUI | Rasio Kontras & Arti Regulasi |
| :--- | :--- | :--- | :--- | :--- |
| **Memenuhi / Aktif** | `ACTIVE`, `COMPLETED`, `SATISFIED` | `emerald-700` (`#047857`) | `<x-badge color="emerald" text="Memenuhi" />` | LPK patuh penuh; seluruh kewajiban surveilen & TP diselesaikan. |
| **Mendekati Batas / Proses** | `SURVEILLANCE_DUE`, `IN_PROGRESS` | `amber-600` (`#d97706`) | `<x-badge color="amber" text="Jatuh Tempo" />` | Memasuki jendela notifikasi (bln 14/35) atau TP sedang berjalan. |
| **Terlewat / Kedaluwarsa** | `SURVEILLANCE_OVERDUE`, `EXPIRED` | `red-600` (`#dc2626`) | `<x-badge color="red" text="Terlewat" />` | Target surveilen terlewat atau masa sertifikat 5 tahun habis. |
| **Dibekukan Sementara** | `SUSPENDED` | `purple-700` (`#6b21a8`) | `<x-badge color="purple" text="Dibekukan" />` | Pembekuan akreditasi; jendela penyelesaian 1 tahun berjalan. |
| **Dicabut Resmi** | `REVOKED` | `rose-900` (`#881337`) | `<x-badge color="rose" text="Dicabut" />` | Pencabutan permanen setelah 1 tahun pembekuan tidak diselesaikan. |
| **Tidak Aktif / Arsip** | `INACTIVE` | `neutral-500` (`#737373`) | `<x-badge color="neutral" outline text="Nonaktif" />` | Data riwayat nonaktif administratif. |

### 2.3 Sistem Border Radius (Spatial Tokens)
* **Kontrol Input & Tombol**: `--radius-field: 0.25rem` (`rounded-sm` / `rounded`). Sudut tajam dan rapi memberikan impresi presisi instrumen lab.
* **Kontainer & Kartu Permukaan**: `--radius-box: 0.5rem` (`rounded-lg`). Memberikan batas pemisah yang tegas tanpa membulat berlebihan.
* **Elemen Sirkular**: `rounded-full` khusus untuk Avatar pengguna, indikator bulat kecil, dan status badge.

### 2.4 Sistem Elevasi & Garis Batas (Elevation & Border Rules)
* **Aturan Datar (*Flat with Borders*)**: Komponen kartu data dan tabel menggunakan elevasi `shadow-none` atau `shadow-xs` dengan garis batas kontras `border border-neutral-200 dark:border-neutral-800`.
* **Elevasi Melayang Terbatas**: Hanya elemen yang menimpa kanvas secara nyata (Modal Dialog, Dropdown Menu Popover, dan Toast) yang berhak menggunakan bayangan medium `shadow-lg` atau `shadow-xl`.

---

## 3. Skala Spasial, Grid, & Layout Tokens

### 3.1 Skala Jarak Terstandarisasi (Spacing Scale)
Konsistensi jarak vertikal dan horizontal antar-elemen mematuhi kelipatan 4px:

| Token Spasi | Ukuran | Kelas Tailwind | Penggunaan Spesifik di Aplikasi |
| :--- | :--- | :--- | :--- |
| `space-1` | 4px | `p-1`, `gap-1` | Jarak mikro antar-ikon dan badge angka kecil. |
| `space-2` | 8px | `p-2`, `gap-2` | Jarak baris pada dropdown item, tag inline, dan tombol mini. |
| `space-3` | 12px | `p-3`, `gap-3` | Padding dalam sel tabel kompak, padding kartu kecil (widget ringkasan). |
| `space-4` | 16px | `p-4`, `gap-4` | Padding standar kontainer formulir, jarak antar-field input bertumpuk. |
| `space-6` | 24px | `p-6`, `gap-6` | Padding kartu utama dashboard, jarak antar-kartu statistik. |
| `space-8` | 32px | `p-8`, `gap-8` | Jarak pemisah vertikal antar-seksi data utama. |
| `space-12` | 48px | `py-12` | Padding atas-bawah halaman detail LPK dan asesmen lapangan. |

### 3.2 Lebar Kontainer (Container Constraints)
* **Kanvas Kerja Utama (Authenticated App)**: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8` (memberikan densitas data maksimal tanpa membuat baris teks melebar liar).
* **Modal Dialog Standar**: `max-w-lg` (form catatan PIC, konfirmasi hapus) dan `max-w-2xl` (impor berkas massal CSV/XLSX, form asesmen lengkap).

---

## 4. Tipografi & Hierarki Semantik (Typography Scale)

* **Font Utama Aplikasi**: `'Plus Jakarta Sans', 'Jakarta Sans', ui-sans-serif, system-ui, sans-serif`.
* **Font Khusus Identitas Registrasi**: `font-mono` (JetBrains Mono / monospace sistem).
  * **Hanya digunakan untuk**: Nomor registrasi Laboratorium Penguji (`LP-001-IDN`, `LP-021-IDN`, `LP-1450-IDN`), nomor SK akreditasi, kode klausul standar ISO/SNI, dan hash verifikasi BSrE.

| Elemen Semantik | Ukuran Font | Weight | Tracking & Line Height | Implementasi Semantic HTML & TallStackUI |
| :--- | :--- | :--- | :--- | :--- |
| **H1 (Judul Halaman Utama)** | `1.5rem` (24px) | `font-semibold` (600) | `tracking-tight leading-tight` | `<h1 class="text-2xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-100">` |
| **H2 (Judul Seksi / Tab)** | `1.25rem` (20px) | `font-semibold` (600) | `tracking-tight leading-snug` | `<h2 class="text-xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-100">` |
| **H3 (Judul Kartu / Modul)** | `1.0rem` (16px) | `font-medium` (500) | `leading-normal` | `<h3 class="text-base font-medium text-neutral-900 dark:text-neutral-100">` |
| **Body Teks Utama** | `0.875rem` (14px) | `font-normal` (400) | `leading-relaxed text-neutral-700 dark:text-neutral-300` | `<p class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">` |
| **Label Form & Kolom Tabel**| `0.75rem` (12px) | `font-medium` (500) | `uppercase tracking-wider text-neutral-500` | `<x-label>` / `<th class="text-xs uppercase font-medium">` |
| **Metadata & Helper Info** | `0.75rem` (12px) | `font-normal` (400) | `text-neutral-500 dark:text-neutral-400` | `<span class="text-xs text-neutral-500 dark:text-neutral-400">` |

---

## 5. Sistem Ikonografi Baku (Iconography Specification)

Aplikasi menggunakan komponen `<x-icon name="..." />` dengan ikon semantik terstandardisasi (Heroicons bawaan terintegrasi di TallStackUI). Dilarang memasukkan emoji dekoratif ke dalam antarmuka.

| Konsep Domain / Aksi | Nama Ikon TallStackUI (Heroicons) | Contoh Penggunaan |
| :--- | :--- | :--- |
| **Master LPK** | `building-office` / `building-office-2` | Menu navigasi LPK, header kartu profil LPK. |
| **Asesmen Lapangan** | `clipboard-document-check` | Menu Asesmen, tombol buat agenda asesmen. |
| **Kalender Siklus KAN** | `calendar` / `calendar-days` | Navigasi kalender, pemilih tanggal jadwal. |
| **Batas Waktu (Tenggat)** | `clock` | Indikator hitung mundur toleransi & batas waktu TP. |
| **Status Memenuhi / Sesuai**| `check-circle` | Badge status terpenuhi, notifikasi sukses. |
| **Peringatan / Jatuh Tempo** | `exclamation-triangle` | Badge jatuh tempo surveilen, banner peringatan pembekuan. |
| **Pelanggaran / Pembekuan**| `no-symbol` / `x-circle` | Indikator LPK dibekukan atau dicabut. |
| **Tim Asesor / PIC** | `user-group` / `user` | Kolom penugasan PIC, tim asesmen lapangan. |
| **Dokumen / SK KAN** | `document-text` / `shield-check` | Pencatatan SK akreditasi, unduh bukti sertifikat. |
| **Impor Data Massal** | `arrow-up-tray` / `document-arrow-up` | Tombol dan modal impor CSV / XLSX. |
| **Ekspor Data / Feed** | `arrow-down-tray` | Tombol unduh rekapitulasi data. |
| **Ubah / Catatan PIC** | `pencil-square` | Tombol edit catatan PIC, perbarui profil LPK. |
| **Hapus Data** | `trash` | Tombol aksi destruktif dengan konfirmasi modal. |
| **Pencarian Cepat** | `magnifying-glass` | Input pencarian instan pada tabel master. |
| **Penyaring / Filter** | `funnel` | Dropdown filter bidang pengujian LP dan status kepatuhan. |
| **Mode Terang / Gelap** | `sun` / `moon` | Terintegrasi otomatis dalam `<x-theme-switch />`. |

---

## 6. Matriks Komponen TallStackUI v4 (`tallstackui/tallstackui`)

Setiap tampilan antarmuka disusun secara terstandarisasi menggunakan komponen Blade resmi TallStackUI v4 yang terintegrasi penuh:

### 6.1 Matriks Varian Tombol (`<x-button>`, `<x-button.circle>`, `<x-button.group>`)

| Varian Komponen | Peruntukan Aksi | Sintaks & Karakteristik Visual |
| :--- | :--- | :--- |
| Primer (`color="primary"`) | Aksi utama halaman (Simpan, Terbitkan Asesmen, Impor). Mendukung loading state otomatis: `loading`. | `<x-button color="primary" loading>Simpan</x-button>` (Latar kontras `bg-neutral-900 text-white dark:bg-white dark:text-neutral-900`). |
| Sekunder (`color="neutral" light`) | Aksi sekunder (Batal, Filter, Reset Pencarian). | `<x-button color="neutral" light>Batal</x-button>` (Latar subtil `bg-neutral-100 dark:bg-neutral-800`). |
| Garis Batas (`outline`) | Aksi navigasi sekunder atau tombol ekspor berkas rekapitulasi. | `<x-button color="neutral" outline>Ekspor CSV</x-button>` (Garis batas `border border-neutral-300 dark:border-neutral-700`). |
| Datar / Subtle (`flat`) | Aksi inline tanpa gangguan visual (Aksi per baris tabel, tombol bantuan). | `<x-button color="neutral" flat>Detail</x-button>` (Transparan, highlight saat hover). |
| Destruktif (`color="red"`) | Aksi risiko tinggi (Hapus LPK, Batalkan Asesmen). | `<x-button color="red">Hapus LPK</x-button>` (Latar merah tegas `bg-red-600 hover:bg-red-700 text-white`). |
| Tombol Melingkar (`<x-button.circle>`) | Tombol aksi kompak satu karakter atau ikon per baris tabel. | `<x-button.circle icon="eye" size="sm" color="neutral" light />` |
| Grup Tombol (`<x-button.group>`) | Pengelompokan visual tombol filter periode waktu atau mode tampilan kalender. | `<x-button.group> ... </x-button.group>` |

### 6.2 Matriks Kontrol Form Dasar & Validasi (`<x-input>`, `<x-textarea>`, `<x-toggle>`, `<x-checkbox>`, `<x-radio>`, `<x-upload>`)
Dilarang menggunakan elemen input HTML mentah tanpa style. Gunakan komponen terintegrasi TallStackUI yang otomatis mengikat validasi `$errors` dan status reaktif Livewire:

```blade
<x-input 
    id="no_reg" 
    label="Nomor Registrasi KAN *" 
    wire:model="form.no_reg" 
    placeholder="Contoh: LP-001-IDN" 
    icon="identification"
    clearable 
/>

<x-textarea 
    id="pic_notes" 
    label="Catatan Koordinasi PIC" 
    wire:model="form.notes" 
    rows="3" 
    resize-auto 
    maxlength="500" 
    count 
    hint="Maksimal 500 karakter untuk catatan koordinasi teknis LPK"
/>

<x-toggle 
    id="is_active" 
    label="Status Operasional Aktif" 
    wire:model.live="form.is_active" 
    color="emerald" 
/>

<x-upload 
    wire:model="file" 
    hint="Format berkas yang didukung: .csv dan .xlsx (Maks. 10MB)" 
    accept=".csv,.xlsx" 
/>
```

---

### 6.3 Matriks Menu Aksi & Dropdown Terintegrasi (`<x-dropdown>`, `<x-select.styled>`, `<x-select.native>`)

Seluruh kebutuhan menu mengambang (*action dropdown*), seleksi data relasional, dan pemilih kuantitas entri wajib menggunakan komponen TallStackUI:

#### A. Menu Aksi Baris & Toolbar Mengambang (`<x-dropdown>`, `<x-dropdown.items>`)
Digunakan untuk aksi kontekstual per baris tabel, menu akun pengguna, dan toolbar aksi massal (*bulk actions*). Mengambang mulus via Alpine.js anchor tanpa terpotong batas tabel (*overflow safe*):

```blade
{{-- Dropdown Aksi Baris Tabel LPK --}}
<x-dropdown icon="ellipsis-vertical" position="bottom-end">
    <x-dropdown.items 
        text="Lihat Profil Lengkap" 
        icon="eye" 
        href="{{ route('lpks.show', $row->id) }}" 
    />
    <x-dropdown.items 
        text="Ubah Catatan PIC" 
        icon="pencil-square" 
        wire:click="editPicNote('{{ $row->id }}')" 
    />
    <x-dropdown.items 
        text="Salin Riwayat Siklus" 
        icon="clipboard-document" 
        wire:click="copyCycleSummary('{{ $row->id }}')" 
    />
    <x-dropdown.items 
        text="Unduh Salinan SK KAN" 
        icon="document-arrow-down" 
        wire:click="downloadCertificate('{{ $row->id }}')" 
        separator 
    />
    <x-dropdown.items 
        text="Bekukan Status Akreditasi" 
        icon="no-symbol" 
        wire:click="promptSuspend('{{ $row->id }}')" 
        class="text-rose-600 dark:text-rose-400" 
    />
</x-dropdown>
```

#### B. Dropdown Form Relasional & Pencarian Cepat (`<x-select.styled>`)
Digunakan untuk memilih data relasional berkategori, penugasan PIC/Asesor, bidang pengujian LP, dan filter LPK pada kalender dengan kemampuan pencarian instan (*searchable*), multi-pilihan (*multiple*), serta pemanggilan endpoint asinkron:

```blade
{{-- 1. Pilihan Tunggal dengan Fitur Pencarian (Bidang / Lingkup Pengujian LP) --}}
<x-select.styled 
    label="Bidang Pengujian LP *" 
    wire:model="form.testing_scope" 
    :options="$testingScopes" 
    select="label:name|value:code" 
    placeholder="Pilih Bidang Pengujian..." 
    searchable 
    clearable 
/>

{{-- 2. Multi-Pilihan Penugasan Asesor Lapangan --}}
<x-select.styled 
    label="Tim Asesor Lapangan *" 
    wire:model="form.assessor_ids" 
    :options="$assessors" 
    select="label:name|value:id" 
    placeholder="Pilih satu atau beberapa asesor..." 
    searchable 
    multiple 
/>

{{-- 3. Filter Lembaga (LPK) pada Kalender dengan Pencarian Cepat --}}
<x-select.styled 
    wire:model.live="filterLpk" 
    :options="$lpkOptions" 
    select="label:name|value:id" 
    placeholder="Semua Lembaga (LPK)" 
    searchable 
    clearable 
/>
```

#### C. Dropdown Asli Ringan (*High-Performance Native Select* - `<x-select.native>`)
Digunakan khusus untuk kontrol utilitas cepat berdensitas tinggi yang tidak memerlukan dialog pencarian, seperti pemilih jumlah baris entri tabel (*entries per page*) dan pemilih cepat bulan/tahun:

```blade
{{-- Kontrol Show [10] entries pada Header Tabel LPK --}}
<div class="flex items-center gap-2 text-xs text-neutral-600 dark:text-neutral-400">
    <span>Show</span>
    <x-select.native 
        wire:model.live="quantity" 
        :options="[10, 25, 50, 100]" 
        class="text-xs py-1 px-2 font-medium" 
    />
    <span>entries</span>
</div>
```

---

### 6.4 Matriks Pemilih Tanggal & Waktu Terintegrasi (`<x-date>`, `<x-calendar>`, `<x-time>`)

Seluruh input penanggalan siklus KAN U-01, toleransi surveilen, jadwal asesmen, dan batas waktu perbaikan menggunakan trio komponen waktu TallStackUI:

#### A. Pemilih Tanggal Mengambang (`<x-date>`)
Digunakan pada form input tanggal asesmen, sertifikat, dan filter rentang laporan. Mendukung pengetikan langsung (*typeable*) dengan masking otomatis, tombol pembantu cepat (*helpers: kemarin, hari ini, besok*), serta pembatasan tanggal (*min-date / max-date*):

```blade
{{-- 1. Input Tanggal Tunggal dengan Helper Hari Ini / Besok --}}
<x-date 
    label="Tanggal Terbit Sertifikat KAN *" 
    wire:model="form.certificate_date" 
    format="DD/MM/YYYY" 
    helpers 
/>

{{-- 2. Input Tanggal Berbasis Keyboard Langsung (Typeable dengan Auto-Masking) --}}
<x-date 
    label="Tanggal Kedaluwarsa Sertifikat *" 
    wire:model="form.expired_at" 
    format="DD/MM/YYYY" 
    typeable 
    hint="Ketik tanggal secara langsung (misal: 05/10/2026)" 
/>

{{-- 3. Pemilih Rentang Waktu (Date Range) untuk Filter Laporan Pengawasan --}}
<x-date 
    label="Periode Asesmen Lapangan" 
    wire:model.live="filters.assessment_range" 
    format="DD/MM/YYYY" 
    range 
    helpers 
/>
```

#### B. Kalender Grid Inline Tanpa Popover (`<x-calendar>`)
Komponen kalender berbasis kisi yang dirender langsung di kanvas halaman tanpa input popover. Dirancang khusus untuk modul kalender dan dashboard penjadwalan:

```blade
{{-- Mini Month Picker pada Panel Samping Kiri Google Calendar MERCY --}}
<div class="p-2 border border-neutral-200 dark:border-neutral-800 rounded-lg bg-white dark:bg-neutral-900">
    <x-calendar 
        wire:model.live="selectedCalendarDate" 
        helpers 
    />
</div>

{{-- Perencanaan Rentang Waktu Siklus (Dua Bulan Berdampingan) --}}
<x-calendar 
    wire:model.live="cycleDateRange" 
    range 
    double 
/>
```

#### C. Pemilih Waktu Presisi (`<x-time>`)
Digunakan pada agenda asesmen lapangan untuk menentukan jam mulai dan selesai opening/closing meeting atau sidang teknis:

```blade
{{-- Input Waktu Format 24 Jam dengan Rentang Slider Halus --}}
<x-time 
    label="Waktu Pelaksanaan Opening Meeting *" 
    wire:model="form.opening_time" 
    format="24" 
    step="15" 
    clearable 
    hint="Standar format 24 jam dengan kelipatan 15 menit" 
/>
```

---

### 6.5 Matriks Label Status Kepatuhan & Peran Pengguna (`<x-badge>`)
Standar tunggal representasi visual label status kepatuhan akreditasi KAN, tahapan siklus surveilen, dan penugasan peran pengguna. Mematuhi rasio kontras WCAG AAA/AA:

* **Tiga Varian Gaya Visual**:
  * `solid` (standar default): Latar warna pekat dengan teks putih kontras tinggi untuk status prioritas (misal: aktif, dibekukan, dicabut).
  * `light`: Latar warna lembut dengan teks sewarna yang kontras untuk tahapan proses berjalan atau chip agenda kalender.
  * `outline`: Garis batas warna dengan latar transparan untuk peran PIC, metadata pendamping, dan label nonaktif.

```blade
{{-- 1. Status Regulasi KAN U-01 --}}
<x-badge color="emerald" text="Aktif" />
<x-badge color="amber" light text="Jatuh Tempo Surveilen" />
<x-badge color="red" text="Terlewat Toleransi" />
<x-badge color="purple" text="Dibekukan Sementara" />
<x-badge color="rose" text="Dicabut Resmi" />
<x-badge color="neutral" outline text="Nonaktif" />

{{-- 2. Penugasan Peran & Metadata --}}
<x-badge color="neutral" outline text="PIC: PIC Laboratorium Penguji" />
<x-badge color="cyan" light text="Asesor Kepala" />
<x-badge color="amber" light icon="exclamation-triangle" text="Re-Akreditasi" />
```

---

### 6.6 Matriks Tabel Data Terintegrasi (`<x-table>`)
Standar tunggal penyajian data tabular di seluruh aplikasi (Master LPK, Rekapitulasi Asesmen Lapangan, Riwayat Pengawasan, Log Audit). Menyediakan fitur pengurutan server-side (*server-side sorting*), filter pencarian reaktif, paginasi entri, indikator pemuatan (*loading spinner*), dan kustomisasi 4 kolom utama Daftar LPK via direktif `@interact`:

```blade
<x-table :headers="$headers" :rows="$rows" :sort="$sort" filter loading paginate striped>
    {{-- Kolom 1: No. Akreditasi --}}
    @interact('column_no_reg', $row)
        <div class="space-y-1">
            <span class="font-mono text-sm font-semibold text-neutral-900 dark:text-neutral-100">{{ $row->no_reg }}</span>
            @if($row->active_warning_badge)
                <div>
                    <x-badge color="amber" light text="{{ $row->active_warning_badge }}" />
                </div>
            @endif
        </div>
    @endinteract

    {{-- Kolom 2: Nama LPK --}}
    @interact('column_name', $row)
        <div class="space-y-1.5">
            <div class="font-bold text-sm text-neutral-900 dark:text-neutral-100">{{ $row->name }}</div>
            <div class="flex flex-wrap items-center gap-1.5">
                <x-badge :color="$row->status_badge_color" :outline="$row->status_badge_outline" :text="$row->status_label" />
                @if($row->pic)
                    <x-badge color="neutral" outline text="PIC: {{ $row->pic->name }}" />
                @endif
            </div>
        </div>
    @endinteract

    {{-- Kolom 3: Masa Berlaku (Awal & Akhir) --}}
    @interact('column_validity', $row)
        <div class="space-y-1 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="text-neutral-500">Awal:</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $row->certificate_date?->format('d/m/Y') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-neutral-500">Akhir:</span>
                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $row->expired_at?->format('d/m/Y') }}</span>
            </div>
            @if($row->is_near_expiry)
                <div>
                    <x-badge color="neutral" outline text="Mendekati Kedaluwarsa" />
                </div>
            @endif
        </div>
    @endinteract

    {{-- Kolom 4: Keterangan (Area Kartu Keterangan Bertumpuk & Dropdown Aksi) --}}
    @interact('column_notes', $row)
        <div class="flex items-start justify-between gap-2 max-w-md">
            <div class="space-y-2 flex-1">
                {{-- Kartu Status Siklus Otomatis --}}
                <div class="p-3 rounded-md border text-xs {{ $row->cycle_card_classes }}">
                    <div class="flex items-center justify-between gap-2 pb-1.5 border-b {{ $row->cycle_card_border_classes }}">
                        <x-badge :color="$row->cycle_badge_color" light :text="$row->cycle_process_name" />
                        <span class="font-bold tracking-wider uppercase flex items-center gap-1 {{ $row->cycle_status_text_color }}">
                            <span>&bull;</span> {{ $row->cycle_status_indicator }}
                        </span>
                    </div>
                    <p class="pt-2 text-xs leading-relaxed {{ $row->cycle_card_body_text_color }}">
                        {{ $row->cycle_description }}
                    </p>
                </div>

                {{-- Kartu Catatan PIC Manual --}}
                @if($row->notes)
                    <div class="p-2.5 rounded-md border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-800/50 text-xs">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">
                            CATATAN PIC
                        </span>
                        <p class="mt-0.5 text-xs text-amber-900 dark:text-amber-200 leading-normal">
                            {{ $row->notes }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Menu Aksi Baris Terpadu --}}
            <x-dropdown icon="ellipsis-vertical" position="bottom-end">
                <x-dropdown.items text="Lihat Profil Lengkap" icon="eye" href="{{ route('lpks.show', $row->id) }}" />
                <x-dropdown.items text="Ubah Catatan PIC" icon="pencil-square" wire:click="editPicNote('{{ $row->id }}')" />
                <x-dropdown.items text="Salin Riwayat Siklus" icon="clipboard-document" wire:click="copyCycleSummary('{{ $row->id }}')" />
                <x-dropdown.items text="Unduh Salinan SK KAN" icon="document-arrow-down" wire:click="downloadCertificate('{{ $row->id }}')" separator />
                <x-dropdown.items text="Bekukan Status Akreditasi" icon="no-symbol" wire:click="promptSuspend('{{ $row->id }}')" class="text-rose-600 dark:text-rose-400" />
            </x-dropdown>
        </div>
    @endinteract
</x-table>
```

---

### 6.7 Matriks Dialog Overlay & Modal (Mandat Baku: `<x-modal>`, `<x-dialog>`)

MERCY menetapkan kebijakan arsitektural baku: dilarang membangun jendela sembul buatan sendiri (*custom overlay*) atau menggunakan fungsi peramban mentah (`window.confirm()`).

#### A. Modal Form Overlay (`<x-modal>` - Mandat Baku untuk SEMUA Modal)
Digunakan untuk seluruh jendela dialog form sembul (tambah agenda kalender, impor massal berkas, edit catatan PIC, permohonan perpanjangan TP):

```blade
{{-- 1. Modal Tambah Agenda Baru Kalender --}}
<x-modal id="create-calendar-event" wire="showCreateEventModal" title="Tambah Agenda Asesmen Baru" size="2xl">
    <div class="space-y-4">
        <x-select.styled label="Lembaga Penilaian Kesesuaian (LPK) *" wire:model="eventForm.lpk_id" :options="$lpks" select="label:name|value:id" searchable />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-date label="Tanggal Pelaksanaan *" wire:model="eventForm.date" format="DD/MM/YYYY" helpers />
            <x-time label="Waktu Pelaksanaan *" wire:model="eventForm.time" format="24" step="15" />
        </div>
        <x-select.styled label="Jenis Agenda Asesmen *" wire:model="eventForm.type" :options="$assessmentTypes" select="label:label|value:value" />
    </div>
    <x-slot:footer class="flex justify-end gap-2">
        <x-button color="neutral" light wire:click="$set('showCreateEventModal', false)">Batal</x-button>
        <x-button color="primary" wire:click="saveCalendarEvent" loading>Simpan Agenda</x-button>
    </x-slot:footer>
</x-modal>

{{-- 2. Modal Impor Data Massal LPK --}}
<x-modal id="lpk-import-modal" wire="showImportModal" title="Impor Data Massal LPK" size="2xl">
    <div class="space-y-4">
        <x-upload wire:model="file" hint="Format berkas yang didukung: .csv dan .xlsx (Maks. 10MB)" accept=".csv,.xlsx" />
    </div>
    <x-slot:footer class="flex justify-end gap-2">
        <x-button color="neutral" light wire:click="$set('showImportModal', false)">Batal</x-button>
        <x-button color="primary" wire:click="import" loading>Proses Impor</x-button>
    </x-slot:footer>
</x-modal>
```

#### B. Dialog Konfirmasi Programatik (`<x-dialog>` - Mandat Baku untuk SEMUA Dialog Konfirmasi)
Diletakkan satu kali di layout utama aplikasi (`<x-dialog />`). Seluruh konfirmasi aksi kritis, destruktif, atau perubahan status regulasi WAJIB dipicu secara programatik via Livewire trait `Interactions`:

```php
// 1. Konfirmasi Pembekuan Status Akreditasi LPK
$this->dialog()
    ->confirm('Konfirmasi Pembekuan Status LPK', 'Masa toleransi surveilen telah terlampaui. Apakah Anda yakin ingin membekukan status akreditasi LPK ini secara resmi?')
    ->method('confirmSuspension', $lpkId)
    ->send();

// 2. Konfirmasi Pembatalan Jadwal Asesmen Lapangan
$this->dialog()
    ->confirm('Batalkan Jadwal Asesmen?', 'Tindakan ini akan membatalkan surat penugasan tim asesor.')
    ->method('cancelAssessment', $assessmentId)
    ->send();
```

---

### 6.8 Matriks Notifikasi Toast & Banner Peringatan Regulasi (`<x-toast>`, `<x-alert>`)

#### A. Notifikasi Toast Mengambang (`<x-toast>` - Mandat Baku untuk SEMUA Notifikasi)
Diletakkan satu kali di layout utama aplikasi (`<x-toast />`). Seluruh notifikasi sukses simpan, peringatan toleransi, dan kegagalan aksi mutasi WAJIB menggunakan `<x-toast>` via trait `Interactions`. Dilarang keras menggunakan session flash HTML banner sementara untuk pesan aksi:

```php
// Sukses
$this->toast()->success('Jadwal asesmen LP-001-IDN berhasil diterbitkan.')->send();

// Peringatan Regulasi
$this->toast()->warning('Masa toleransi surveilen berakhir 5 hari lagi.')->send();

// Gagal / Penolakan Aturan Bisnis
$this->toast()->error('Perpanjangan ditolak: tidak ada progres perbaikan temuan.')->send();

// Informasi
$this->toast()->info('Siklus pengawasan LPK disinkronkan dengan basis data KAN.')->send();
```

#### B. Banner Peringatan Statis Persisten (`<x-alert>`)
Digunakan khusus untuk peringatan kondisi berisiko tinggi yang bersifat persisten (menempel pada halaman) dan membutuhkan perhatian berkelanjutan dari PIC:

```blade
{{-- 1. Peringatan Pembekuan Akreditasi (Border Tebal Kiri) --}}
<x-alert 
    title="Status LPK Dibekukan Sementara" 
    text="Masa toleransi surveilen telah terlampaui. Jendela kesempatan penyelesaian 1 tahun aktif s/d 30/04/2027 sebelum status dicabut permanen." 
    color="purple" 
    bordered="left:purple" 
    icon="no-symbol" 
/>

{{-- 2. Banner Peringatan Persisten KAN pada Kalender Kegiatan --}}
<x-alert 
    title="Peringatan Siklus Pengawasan Akreditasi: 2 Kunjungan Memerlukan Tindak Lanjut!" 
    text="Sesuai siklus KAN, notifikasi aktif di Bulan ke-14 (S1) dan Bulan ke-35 (S2). Notifikasi ini persisten hingga jadwal kunjungan diagendakan." 
    color="red" 
    light 
    icon="exclamation-triangle" 
>
    <x-slot:footer>
        <div class="mt-2 flex gap-2">
            <x-button color="red" size="sm" wire:click="filterOverdueLpk">Tinjau LPK Jatuh Tempo &gt;</x-button>
        </div>
    </x-slot:footer>
</x-alert>
```

---

### 6.9 Matriks Garis Waktu & Pelacak Milestone Siklus KAN (`<x-timeline>`, `<x-progress.bar>`, `<x-step>`)

#### A. Garis Waktu 5 Tahun Siklus Akreditasi KAN (`<x-timeline>`, `<x-timeline.items>`)
Komponen server-side murni tanpa ketergantungan JavaScript untuk memvisualisasikan tahapan 5 tahun siklus pengawasan regulasi KAN U-01 (Akreditasi Awal -> Surveilen 1 -> Surveilen 2 -> Re-Akreditasi):

```blade
{{-- Garis Waktu Siklus Hidup Akreditasi LPK (Mode Horizontal / Vertikal) --}}
<x-timeline horizontal color="emerald" style="light">
    <x-timeline.items 
        title="Akreditasi Awal (AA)" 
        description="Penerbitan SK Pertama KAN" 
        date="Bulan 0 (05/10/2021)" 
        icon="shield-check" 
        color="emerald" 
    />
    <x-timeline.items 
        title="Surveilen 1 (S1)" 
        description="Target asesmen berkala pertama" 
        date="Bulan 14 (Desember 2022)" 
        icon="clipboard-document-check" 
        color="emerald" 
    />
    <x-timeline.items 
        title="Surveilen 2 (S2)" 
        description="Target asesmen berkala kedua" 
        date="Bulan 35 (September 2024)" 
        icon="clock" 
        color="amber" 
    />
    <x-timeline.items 
        title="Re-Akreditasi (RA)" 
        description="Akreditasi ulang siklus berikutnya" 
        date="Bulan 59 (September 2026)" 
        icon="arrow-path" 
        color="cyan" 
    />
</x-timeline>
```

#### B. Pelacak Batas Waktu Temuan Perbaikan (`<x-progress.bar>`)
Memantau persentase pemenuhan tindakan perbaikan (TP) asesmen lapangan:

```blade
<div class="space-y-1.5">
    <div class="flex items-center justify-between text-xs">
        <span class="font-medium text-neutral-700 dark:text-neutral-300">Kemajuan Temuan Tertutup</span>
        <span class="font-bold text-neutral-900 dark:text-neutral-100">8 / 10 Temuan Selesai</span>
    </div>
    <x-progress.bar :percent="80" color="emerald" size="sm" />
</div>
```

#### C. Wizard Alur Permohonan Akreditasi (`<x-step>`, `<x-step.items>`)
Menyajikan tahapan alur verifikasi berkas hingga evaluasi hasil asesmen:

```blade
<x-step wire:model="currentStep">
    <x-step.items step="1" title="Permohonan" description="Pemeriksaan kelengkapan dokumen" />
    <x-step.items step="2" title="Asesmen Lapangan" description="Kunjungan tim asesor KAN" />
    <x-step.items step="3" title="Tindakan Perbaikan" description="Penyelesaian temuan ketidaksesuaian" />
    <x-step.items step="4" title="Sidang Panitia Teknis" description="Evaluasi hasil asesmen & penerbitan SK" />
</x-step>
```

---

### 6.10 Matriks Bantuan Kontekstual & Tooltip (`<x-tooltip>`)
Digunakan untuk menyajikan petunjuk informasi teknis atau kepatuhan regulasi secara ringkas saat pengguna mengarahkan kursor (*hover*), fokus keyboard, atau ketukan mobile tanpa mengotori ruang visual kanvas kerja:

```blade
{{-- 1. Tooltip Ikon Bantuan pada Formula Perhitungan Masa Berlaku --}}
<div class="flex items-center gap-1.5 text-xs font-medium text-neutral-500">
    <span>MASA BERLAKU</span>
    <x-tooltip text="Masa berlaku siklus akreditasi KAN berlangsung tepat 5 tahun terhitung sejak tanggal penetapan SK." position="top" />
</div>

{{-- 2. Tooltip Klausul Standar Akreditasi ISO/SNI --}}
<div class="flex items-center gap-1">
    <span class="font-semibold text-xs text-neutral-800 dark:text-neutral-200">ISO/IEC 17025:2017</span>
    <x-tooltip text="Persyaratan Umum Kompetensi Laboratorium Pengujian" position="right" color="blue" />
</div>

{{-- 3. Tooltip pada Tombol Aksi Melekat (Directives) --}}
<x-button.circle icon="eye" size="sm" color="neutral" light tooltip="Lihat rincian riwayat pengawasan LPK" />
<x-button.circle icon="arrow-down-tray" size="sm" color="neutral" light tooltip="Unduh salinan sertifikat digital KAN" />
```

---

### 6.11 Matriks Pengubah Tema Aplikasi (Toggle Ganti Tema - `<x-theme-switch>`)
MERCY mengintegrasikan komponen pengubah tema resmi TallStackUI `<x-theme-switch />` pada bilah atas aplikasi (`<x-layout.header>`):

* **Dukungan Dua Variasi Tampilan**:
  * **Variasi Sederhana (Toggle Switch - Direkomendasikan untuk Header)**:
    ```blade
    {{-- Toggle ikon matahari/bulan ringkas pada top header --}}
    <x-theme-switch simple only-icons />
    ```
  * **Variasi Tersegmentasi (Segmented Control)**:
    ```blade
    {{-- Tiga opsi eksplisit: Terang, Gelap, atau Mengikuti Sistem OS --}}
    <x-theme-switch />
    ```
* **Karakteristik Teknis**:
  * **Persistensi Otomatis**: Preferensi tema disimpan di *local storage* browser secara otomatis.
  * **Zero Flicker**: Menggunakan inisialisasi skrip inline TallStackUI di `<head>` dokumen untuk mencegah kedipan kontras saat halaman dimuat ulang.
  * **Transisi Halus**: Transisi perubahan warna berlangsung dalam durasi 200ms (`transition-colors duration-200`).

---

### 6.12 Matriks Kartu Permukaan Data & Ringkasan Metrik (`<x-card>`, `<x-stats>`)
* Kontainer kartu data menggunakan elevasi datar dengan batas kontras subtil (`border border-neutral-200 dark:border-neutral-800 shadow-none`).
* Mendukung slot `header` dan `footer`, serta fitur bawaan `minimize`, `close`, dan `loading`:

```blade
{{-- Kartu Ringkasan Informasi Pengawasan --}}
<x-card class="border border-neutral-200 dark:border-neutral-800 shadow-none">
    <x-slot:header class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
        <h3 class="text-base font-medium text-neutral-900 dark:text-neutral-100">Informasi Surveilen 1</h3>
        <x-badge color="amber" text="Jatuh Tempo Bulan 14" />
    </x-slot:header>
    <div class="pt-4 space-y-3">
        <!-- Rincian Milestone Asesmen -->
    </div>
</x-card>

{{-- Kartu Statistik Metrik Dashboard --}}
<x-stats number="142" title="Total LPK Aktif" icon="building-office-2" color="emerald" light />
```

---

### 6.13 Matriks Tata Letak Shell & Navigasi Aplikasi (`<x-layout>`, `<x-layout.header>`, `<x-sidebar>`, `<x-sidebar.item>`, `<x-tab>`, `<x-clipboard>`)
Struktur shell aplikasi MERCY dibangun menggunakan kerangka tata letak terpadu TallStackUI:
* `<x-layout>`: Shell halaman responsif terpusat.
* `<x-layout.header>`: Sticky header dengan tombol hamburger mobile, breadcrumbs, switch tema terang/gelap (`<x-theme-switch simple only-icons />`), dan profil dropdown (`<x-dropdown>`).
* `<x-sidebar>` & `<x-sidebar.item>`: Panel navigasi samping desktop & drawer mobile dengan deteksi route aktif otomatis.
* `<x-tab>` & `<x-tab.items>`: Tab navigasi antar-modul detail LPK (Informasi Umum, Siklus Pengawasan, Asesmen Lapangan, Riwayat Sertifikat).
* `<x-clipboard>`: Tombol salin satu-klik untuk nomor registrasi LPK dan nomor SK KAN.

---

## 7. Status Interaktivitas Livewire & Alpine.js (State Management & Feedback)

Untuk menjamin kenyamanan operasional, setiap interaksi asinkron Livewire memiliki penanganan feedback visual yang jelas dan terstandarisasi:

### 7.1 Status Loading (*Loading States*)
1. **Tombol Pengirim Form**:
   * Setiap tombol submit mutasi menyertakan atribut `loading` bawaan TallStackUI:
     ```blade
     <x-button type="submit" color="primary" loading>
         Simpan Data Asesmen
     </x-button>
     ```
2. **Pencarian Instan Tabel Master**:
   * Tabel TallStackUI menangani loading state secara otomatis melalui atribut `loading` pada `<x-table loading>` dengan indikator spinner elegan tanpa sentakan layout.

### 7.2 Notifikasi Toast Mengambang (`<x-toast>` - Mandat Baku SEMUA Notifikasi)
* Notifikasi sistem ditempatkan satu kali di layout utama via tag `<x-toast />`.
* Seluruh respons aksi Livewire (sukses, peringatan, error) WAJIB dipicu via trait `TallStackUi\Traits\Interactions`:
  * **Sukses**: `$this->toast()->success('Jadwal asesmen LP-001-IDN berhasil diterbitkan.')->send();`
  * **Peringatan**: `$this->toast()->warning('Masa toleransi surveilen berakhir 5 hari lagi.')->send();`
  * **Gagal**: `$this->toast()->error('Perpanjangan ditolak: tidak ada progres temuan perbaikan.')->send();`

### 7.3 Dialog Konfirmasi Programatik (`<x-dialog>` - Mandat Baku SEMUA Dialog Konfirmasi)
* Dialog konfirmasi ditempatkan satu kali di layout utama via tag `<x-dialog />`.
* Seluruh konfirmasi aksi risiko tinggi WAJIB dipicu via trait `TallStackUi\Traits\Interactions` (`$this->dialog()->confirm()`). Larangan menggunakan alert/confirm JavaScript browser.

### 7.4 Modal Form Overlay (`<x-modal>` - Mandat Baku SEMUA Modal)
* Seluruh popup form (tambah agenda kalender, edit catatan PIC, unggah berkas massal) WAJIB menggunakan komponen `<x-modal id="..." wire="...">`.

### 7.5 Empty States (*Placeholder Data Kosong*)
Ketika tabel atau kueri pencarian menghasilkan 0 baris, dilarang membiarkan layar kosong. Gunakan kontainer state bersih yang informatif:
* Ikon pencarian netral: `<x-icon name="magnifying-glass" class="w-8 h-8 text-neutral-400 mx-auto" />`.
* Judul ringkas: *"Data LPK Tidak Ditemukan"*.
* Deskripsi: *"Tidak ada data yang cocok dengan kata kunci atau filter yang Anda pilih."*
* Tombol aksi: `<x-button color="neutral" light wire:click="resetFilters">Reset Filter</x-button>`.

---

## 8. Pola Antarmuka Baku (Screen Blueprints)

### 8.1 Pola 1: Tampilan Daftar Master LPK (Table View & Dual Notes Stack)

Sesuai spesifikasi visual operasional resmi MERCY, tampilan daftar LPK menyajikan kendali toolbar fleksibel dan struktur 4 kolom presisi:

* **Toolbar Atas (Header Controls)**:
  * **Kiri (Entries Paginator)**: Kontrol paginasi instan menggunakan `<x-select.native wire:model.live="quantity" :options="[10, 25, 50, 100]" />` terbungkus dalam `Show [ 10 ⌄ ] entries` yang terikat langsung ke properti Livewire `$quantity`.
  * **Kanan (View Mode Switcher)**: Pengalih mode tampilan menggunakan `<x-button.group>` TallStackUI:
    * Mode Tabel: `<x-button color="primary" flat> [⊞ Tabel] </x-button>` (aktif, memiliki garis batas penegas dan latar kontras terpilih).
    * Mode Grid: `<x-button color="neutral" flat> [⸬ Grid] </x-button>` (inaktif, beralih ke mode tata letak kartu ringkas).

* **Struktur 4 Kolom Utama Tabel**:
  * **Kolom 1 (`NO. AKREDITASI` - Sortable `⇅`)**:
    * Nilai utama: Nomor Akreditasi resmi KAN (misal: `LP-021-IDN`, `LP-1450-IDN`) menggunakan font monospace tebal (`font-mono text-sm font-semibold text-neutral-900 dark:text-neutral-100`).
    * Indikator peringatan tahapan: Badge amber muda dengan ikon tanda seru jika LPK memiliki tahapan aktif mendesak (misal: `<x-badge color="amber" light text="⚠️ Re-Akreditasi (Akreditasi Ulang)" />`).
  * **Kolom 2 (`NAMA LPK` - Sortable `⇅`)**:
    * Nilai utama: Nama institusi Lembaga Penilaian Kesesuaian dalam teks tebal (`font-bold text-sm text-neutral-900 dark:text-neutral-100`, misal: `Balai Besar Logam dan Mesin`, `PT Guna Sukses Inti`).
    * Deretan badge status & PIC di bawah nama:
      - Badge status kepatuhan/surveilen: `<x-badge color="amber" outline text="Jatuh Tempo Surveilen" />` atau `<x-badge color="emerald" light text="Aktif" />`.
      - Badge penugasan PIC: `<x-badge color="neutral" outline text="PIC: PIC Laboratorium Penguji" />`.
  * **Kolom 3 (`MASA BERLAKU` - Sub-label `AWAL & AKHIR`, Sortable `⇅`)**:
    * Tanggal Terbit Sertifikat: `Awal: 05/10/2021` (label `Awal:` abu-abu netral, tanggal format `DD/MM/YYYY`).
    * Tanggal Kedaluwarsa Sertifikat: `Akhir: 05/10/2026` (label `Akhir:` abu-abu netral, tanggal format `DD/MM/YYYY`).
    * Badge masa berlaku: `<x-badge color="neutral" outline text="Mendekati Kedaluwarsa" />` jika sisa masa berlaku memasuki jendela 1 tahun sebelum habis.
  * **Kolom 4 (`KETERANGAN` - Area Kartu Keterangan Bertumpuk & Menu Aksi)**:
    Menampilkan kartu ringkasan visual (*Structured Status Cards*) berlatar warna tematik serta dropdown aksi kontekstual:
    1. **Kartu Status Siklus Surveilen (Otomatis)**:
       * Kartu latar warna tematik status regulasi:
         - Jatuh Tempo / Dibekukan: Latar rose muda (`bg-rose-50 border border-rose-200 text-rose-900 dark:bg-rose-950/30 dark:border-rose-800/50`).
         - Pelaksanaan / Terjadwal: Latar emerald muda (`bg-emerald-50 border border-emerald-200 text-emerald-900 dark:bg-emerald-950/30 dark:border-emerald-800/50`).
         - Batas Tindakan Perbaikan: Latar cyan muda (`bg-cyan-50 border border-cyan-200 text-cyan-900 dark:bg-cyan-950/30 dark:border-cyan-800/50`).
         - Reminder Pengawasan: Latar amber muda (`bg-amber-50 border border-amber-200 text-amber-900 dark:bg-amber-950/30 dark:border-amber-800/50`).
       * Header kartu: Badge proses di kiri (misal: `<x-badge color="rose" light text="Re-Akreditasi (Akreditasi Ulang)" />` atau `<x-badge color="emerald" light text="Surveilen 1" />`), dan indikator status proses di kanan dengan titik status (`● JATUH TEMPO` [Rose], `● PELAKSANAAN` [Emerald], `● BATAS TP` [Cyan], `● REMINDER` [Amber]).
       * Body kartu: Redaksi terhitung sistem secara presisi:
         - Kasus Dibekukan: `Dibekukan (toleransi pengisian 30/04/2026 terlampaui. Sisa 215 hari kesempatan penyelesaian s/d 30/04/2027).`
         - Kasus Pelaksanaan: `Terjadwal 29/03/2027.`
    2. **Kartu Catatan Khusus PIC (Manual)**:
       * Ditampilkan tepat di bawah kartu status otomatis jika PIC telah menginput catatan koordinasi.
       * Kontainer latar amber muda (`bg-amber-50 border border-amber-200 dark:bg-amber-950/30 dark:border-amber-800/50 rounded-md p-2 mt-2`).
       * Header kartu: Label kecil uppercase `CATATAN PIC` (warna amber pekat).
       * Body kartu: Teks catatan internal (misal: `ruang lingkup diperhatikan`).
    3. **Menu Aksi Baris Terpadu (`<x-dropdown>`)**:
       * Setiap baris tabel dilengkapi tombol aksi melayang `<x-dropdown icon="ellipsis-vertical" position="bottom-end">` memuat `<x-dropdown.items>` untuk Detail, Edit Catatan, Cetak Lembar Verifikasi QR, dan Pembekuan Status Akreditasi.

* **Mode Grid (Kartu Grid Responsif)**:
  * Ketika tombol `[ Grid ]` dipilih, daftar LPK bertransformasi menjadi susunan kartu grid (`grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4`).
  * Setiap kartu memuat header nomor registrasi dan nama LPK, badge status dan penugasan PIC, masa berlaku awal/akhir, serta tumpukan kartu keterangan di bagian bawah kartu.

### 8.2 Pola 2: Tampilan Detail LPK & Kartu Keterangan Bertumpuk (*Dual Notes Stack*)
Halaman rincian LPK (`lpks/show`) menerapkan layout 2-kolom berdampingan:
1. **Kolom Kiri - Status Siklus Otomatis**:
   * Dihitung murni oleh `SurveillanceEngineService`.
   * Menampilkan tahap surveilen aktif (S1, S2, RA), batas waktu toleransi kunjungan, dan status pembekuan jika terlambat.
   * Dilengkapi badge indikator status resmi KAN menggunakan `<x-badge>`.
2. **Kolom Kanan - Catatan Khusus PIC**:
   * Area catatan manual narahubung PIC pendamping.
   * Dilengkapi tombol *"Ubah Catatan"* yang membuka dialog modal interaktif `<x-modal id="edit-pic-note-modal">`.
   * Terdapat opsi *"Salin Keterangan Sistem"* untuk menyalin ringkasan kalkulasi otomatis ke draft catatan PIC.

### 8.3 Pola 3: Pelacak Batas Waktu Tindakan Perbaikan (TP Tracker & Stepper)
Pada rincian asesmen lapangan, status pemenuhan temuan ketidaksesuaian disajikan dalam komponen visual progres:
* **Kartu Ringkasan Temuan**: Menampilkan progres temuan yang telah diselesaikan menggunakan `<x-progress.bar>`: `tp_findings_closed` dari `tp_findings_total` (contoh: *"8 dari 10 Temuan Telah Diperbaiki"*).
* **Indikator Batas Waktu**:
  * Tipe Akreditasi Awal (AA): Batas normal 3 bulan kalender.
  * Tipe Lainnya (S1, S2, PRL, STT, RA): Batas normal 2 bulan kalender.
* **Jalur Perpanjangan (+1 Bulan)**:
  * **Kondisi Layak (`tp_findings_closed > 0`)**: Tombol perpanjangan aktif; saat disetujui menampilkan badge perpanjangan aktif `<x-badge color="cyan" text="Perpanjangan Disetujui" />` dengan nomor surat resmi KAN, serta tanggal jatuh tempo baru (total 4 bulan untuk AA, total 3 bulan untuk lainnya).
  * **Kondisi Dilarang (`tp_findings_closed == 0`)**: Tombol perpanjangan dinonaktifkan dengan peringatan tegas bahwa perpanjangan dilarang bagi laboratorium tanpa progres perbaikan (0 temuan tertutup), dan proses langsung dialihkan ke sanksi pembekuan.

### 8.4 Pola 4: Kalender Multi-Event KAN & Deep-Linking (Google Calendar Architecture)

Mengadopsi tata letak dua kolom terpadu bergaya **Google Calendar** untuk memudahkan PIC memantau dan mengatur agenda asesmen, toleransi surveilen, serta batas waktu perbaikan:

* **Banner Peringatan Persisten KAN (Top Alert Bar)**:
  * Membentang di bagian atas halaman dengan aksen merah peringatan:
    * Ikon dan Label Tag: `<x-badge color="red" light text="⚠️ PERINGATAN KAN" />`.
    * Judul: *"Peringatan Siklus Pengawasan Akreditasi: 2 Kunjungan Memerlukan Tindak Lanjut!"*.
    * Keterangan Regulasi: *"Sesuai siklus KAN, notifikasi aktif di Bulan ke-14 (S1), Bulan ke-35 (S2), dan 1 Bulan sebelum kedaluwarsa (Re-Akreditasi). Notifikasi ini persisten dan tidak dapat diabaikan hingga jadwal kunjungan diagendakan."*.
    * Tombol Tindakan Langsung: `<x-button color="red" size="sm">Tinjau LPK Jatuh Tempo &gt;</x-button>`.

* **Panel Kontrol Samping (Left Sidebar Panel - Lebar ~280px)**:
  1. **Tombol "+ Tambah Agenda Baru ⌄"**:
     * Menggunakan tombol primer `<x-button color="primary" icon="plus" wire:click="openCreateEventModal">+ Tambah Agenda Baru</x-button>` atau `<x-dropdown text="+ Tambah Agenda Baru">`, membuka dialog modal penambahan agenda asesmen (`<x-modal id="create-calendar-event">`) yang memuat input tanggal `<x-date format="DD/MM/YYYY" helpers />` dan waktu `<x-time format="24" step="15" />`.
  2. **Kalender Mini Inline (Mini Month Picker - `<x-calendar>`)**:
     * Menggunakan komponen kalender inline bawaan TallStackUI: `<x-calendar wire:model.live="selectedCalendarDate" helpers />` yang dirender langsung di kanvas tanpa pembungkus popover input.
     * Menampilkan navigasi bulan aktif (misal `September 2026`) dengan panah navigasi bawaan.
     * Grid tanggal mini interaktif: Klik pada suatu tanggal memicu perpindahan fokus kalender utama secara reaktif via Livewire.
     * Hari aktif saat ini disorot dengan lingkaran warna primer (`rounded-full bg-primary text-white`).
  3. **Filter Kategori Kalender Reaktif (Checkbox & Legend)**:
     * Label header kecil uppercase: `KATEGORI KALENDER`.
     * Checkbox list reaktif dengan titik indikator warna (*color legend dots*):
       - `[x] ● Pelaksanaan` (Dot emerald `#059669`): Jadwal kunjungan asesmen lapangan.
       - `[x] ● Batas TP` (Dot cyan `#0891b2`): Tenggat penyelesaian tindakan perbaikan.
       - `[x] ● Reminder` (Dot amber `#d97706`): Pengingat surveilen bulan 14/35.
       - `[x] ● Jatuh Tempo` (Dot rose `#e11d48`): Toleransi terlampaui / status dibekukan.
       - `[x] ● Agenda Internal` (Dot purple `#7c3aed`): Sidang panitia teknis dan EHA.
  4. **Filter Lembaga (LPK)**:
     * Label header kecil uppercase: `FILTER LEMBAGA (LPK)`.
     * Dropdown filter `<x-select.styled wire:model.live="filterLpk" :options="$lpks" select="label:name|value:id" placeholder="Semua Lembaga (LPK)" searchable clearable />` dengan fitur pencarian live (*searchable*): pilihan *"Semua Lembaga (LPK)"* atau spesifik nama/no_reg LPK.
  5. **Kartu Profil Pengguna & Versi Sistem**:
     * Terletak di bagian bawah sidebar: Avatar inisial (`PL`), nama PIC pendamping, badge peran `<x-badge color="cyan" outline text="PIC LAB" />`, dan tombol logout.

* **Panel Kalender Utama (Main Interactive Grid Panel)**:
  1. **Bilah Navigasi Header Kalender**:
     * Kiri:
       - Tombol `<x-button color="neutral" outline size="sm">Hari ini</x-button>`.
       - Tombol panah navigasi bulan/periode: `<x-button.circle icon="chevron-left" size="sm" />` dan `<x-button.circle icon="chevron-right" size="sm" />`.
       - Dropdown cepat Bulan dan Tahun menggunakan kontrol asli ringan `<x-select.native wire:model.live="calendarMonth" :options="$months" class="text-xs" />` dan `<x-select.native wire:model.live="calendarYear" :options="$years" class="text-xs" />`.
     - Kanan:
       - Pengalih 4 mode tampilan (*Segmented Button Group* via `<x-button.group>`):
         - `[ Bulan ]` (aktif, berlatar abu-abu netral terpilih)
         - `[ Minggu ]`
         - `[ Hari ]`
         - `[ Agenda ]`
  2. **Grid Tampilan Bulan (Month View Grid)**:
     * Header 7 kolom hari: `Senin`, `Selasa`, `Rabu`, `Kamis`, `Jumat`, `Sabtu`, `Minggu`.
     * Kotak sel tanggal proporsional dengan nomor tanggal di pojok kiri atas.
     * Hari ini ditandai secara visual dengan nomor di dalam lingkaran warna primer (seperti angka `27` pada gambar).
     * *Event Chips* horizontal bertumpuk di dalam tanggal:
       - Chip kompak berlatar warna kategori:
         - Chip `Kedaluwarsa`: Latar rose muda (`bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-200`).
         - Chip `Pelaksanaan`: Latar emerald muda (`bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200`).
         - Chip `Batas TP`: Latar cyan muda (`bg-cyan-50 text-cyan-800 border border-cyan-200 dark:bg-cyan-950/40 dark:text-cyan-200`).
       - Klik pada chip event membuka modal popover rincian agenda lengkap dengan tautan deep link ke detail LPK atau asesmen.
       - Klik pada sel tanggal kosong membuka modal pembuatan agenda baru dengan tanggal awal otomatis terisi.
  3. **Pola Deep-Link & Navigasi URL**:
     * Format URL: `/calendar?view=month&date=YYYY-MM-DD&highlight={event_id}&selected=1`.
     * Resolusi otomatis tanggal di backend jika hanya menyertakan parameter `highlight`, mengarahkan kalender langsung ke bulan event tersebut dan membuka popover terkait.

---

## 9. Standar Bahasa & Penulisan Teks UI (Copywriting & Tone of Voice)

1. **Gaya Bahasa Institusional & Netral**:
   * Menggunakan Bahasa Indonesia baku sesuai regulasi akreditasi KAN (Pedoman KAN U-01).
   * Singkat, jelas, tanpa basa-basi (*no buzzwords*).
2. **Standardisasi Istilah Kunci**:
   * Gunakan **"Batas Waktu"** (bukan *"SLA"*).
   * Gunakan **"Tindakan Perbaikan (TP)"** dan **"Verifikasi Tindakan Perbaikan (VTP)"**.
   * Gunakan **"Evaluasi Hasil Asesmen (EHA)"**.
   * Gunakan **"Surveilen 1"**, **"Surveilen 2"**, **"Akreditasi Awal"**, **"Re-Akreditasi"**.
   * Gunakan **"Dibekukan"** (`SUSPENDED`) dan **"Dicabut"** (`REVOKED`).
3. **Pesan Konfirmasi & Notifikasi Toast**:
   * Berorientasi fakta: *"Jadwal asesmen LP-001-IDN berhasil diterbitkan."* (Bukan *"Hebat! Data Anda berhasil disimpan!"*).
   * Penolakan aturan bisnis: *"Perpanjangan batas waktu ditolak: laboratorium belum menyelesaikan temuan perbaikan sama sekali."*

---

## 10. Aksesibilitas, Motion, & Responsivitas (A11y & Motion Standards)

1. **Area Sentuh Mobile (Touch Targets)**: Seluruh tombol dan kontrol form pada tampilan mobile memiliki ukuran minimal `44x44 px`.
2. **Navigasi Keyboard Penuh**: Modal dialog dapat ditutup dengan tombol `Escape`. Form dapat dipindahkan dengan tombol `Tab` secara berurutan. Dropdown dapat dinavigasi dengan tanda panah atas/bawah.
3. **Pemeriksaan Kontras Warna**: Seluruh teks putih pada badge status KAN (emerald, amber, red, purple, rose) memiliki rasio kontras di atas 4.5:1 terhadap latar belakangnya.
4. **Standar Animasi & Transisi Halus (*Smooth Animations & Micro-interactions*)**:
   * **Animasi Buka/Tutup Sidebar Dashboard (Open & Collapse)**:
     * Transisi lebar dan pergeseran sidebar berlangsung halus selama **300ms** menggunakan kurva `cubic-bezier(0.4, 0, 0.2, 1)`:
       - **Mode Terbuka (*Expanded*)**: Lebar 16rem (256px) dengan label menu lengkap.
       - **Mode Ciut (*Collapsed*)**: Lebar 4rem (64px) yang memadatkan menu menjadi deretan ikon terpusat dengan *instant tooltip* saat hover.
     * **Persistensi State**: Status ciut disimpan secara otomatis di *local storage* browser (`_x_collapsedSidebar`) via Alpine.js `$persist`, sehingga preferensi pengguna tetap terjaga saat berpindah halaman tanpa *flicker*.
     * **Tombol Aksesibilitas**: Tombol toggle drawer selalu tersedia di header layout `<x-layout.header>` pada seluruh ukuran layar (desktop, tablet, dan mobile).
     * **Mode Mobile (< 768px)**: Sidebar meluncur halus dari sisi kiri (*slide-in drawer*) dengan overlay backdrop gelap yang memudar (*fade-in/out*) selama 300ms.
   * **Transisi Halus Seluruh Antarmuka (*Global Smooth Transitions*)**:
     * **Pengubah Tema (*Dark / Light Mode Toggle*)**: Transisi warna latar belakang, batas garis (*border*), dan teks berlangsung halus selama **200ms** (`transition: background-color 200ms ease, border-color 200ms ease, color 200ms ease`), mengeliminasi kedipan (*harsh visual flash*). Menggunakan komponen bawaan `<x-theme-switch />`.
     * **Kontrol Interaktif**: Tombol (`<x-button>`), tautan navigasi, dan input form memiliki transisi hover/focus halus selama **150ms** (`transition-duration: 150ms`).
     * **Gulir Halaman**: Mengadopsi `scroll-behavior: smooth`.
     * **Dukungan Aksesibilitas Gerak**: Seluruh transisi otomatis dinonaktifkan jika sistem operasi pengguna mengaktifkan fitur sensitivitas gerak (`@media (prefers-reduced-motion: reduce)`).
5. **Desain Adaptif Multi-Device**:
   * **Mobile (`< 640px`)**: Sidebar beralih ke *off-canvas drawer* dengan animasi geser; tabel data bertransformasi menjadi tumpukan kartu ringkas.
   * **Tablet (`640px - 1024px`)**: Sidebar default dalam mode ciut (*collapsed icon-only* dengan tooltip interaktif).
   * **Desktop (`> 1024px`)**: Mode fleksibel (dapat diciutkan atau diperluas sesuai kenyamanan PIC) dengan densitas data maksimal.
