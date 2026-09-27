# Product Requirement Document (PRD)
## MERCY
### Komite Akreditasi Nasional (KAN) / Badan Standardisasi Nasional (BSN)

---

## 1. Dokumen Ringkasan Eksekutif (Executive Summary)

### 1.1 Latar Belakang & Identitas Sistem
**MERCY** adalah sistem informasi dan manajemen operasional terpadu yang dirancang khusus untuk memfasilitasi, memantau, dan memvalidasi siklus hidup akreditasi **Laboratorium Penguji (LP)** di bawah naungan Komite Akreditasi Nasional (KAN) dan Badan Standardisasi Nasional (BSN). Aplikasi difokuskan secara eksklusif hanya untuk skema akreditasi resmi KAN:
* **LP**: Laboratorium Penguji (SNI ISO/IEC 17025:2017: *Persyaratan Umum Kompetensi Laboratorium Pengujian*)

Seluruh siklus pengawasan dan manajemen kepatuhan akreditasi di dalam sistem ini didedikasikan secara khusus untuk entitas Laboratorium Penguji (LP), termasuk pelacakan bidang lingkup pengujian teknis (seperti Kimia, Biologi/Mikrobiologi, Fisika, Mekanik, Kelistrikan, dan Lingkungan). Aplikasi ini tidak melayani skema di luar Laboratorium Penguji.

Aplikasi ini mengintegrasikan seluruh siklus pengawasan berkala (Surveilen KAN), 8 proses asesmen resmi KAN, penegakan batas waktu Tindakan Perbaikan (TP) dan Verifikasi Tindakan Perbaikan (VTP), alur Evaluasi Hasil Asesmen (EHA), pencatatan SK KAN, kalender interaktif responsif multi-event, mesin pencarian live instan tanpa reload, hingga pelaporan data terpusat dengan fokus utama pada pemantauan (*monitoring*) dan mempermudah pekerjaan PIC internal unit kerja.

### 1.2 Tujuan Dokumen PRD
Dokumen ini berfungsi sebagai spesifikasi kebutuhan produk komprehensif sekaligus **cetak biru arsitektur teknis dan standar Clean Code**. PRD ini merangkum seluruh domain bisnis, arsitektur data, logika kalkulasi hukum akreditasi KAN, alur kerja antar-peran, kebutuhan fungsional, dan pedoman refaktorisasi struktur kode agar terstruktur rapi, *maintainable*, berkepala dingin, memiliki *type safety* tinggi, serta bebas dari *spaghetti code* dan *code slop*.

---

## 2. Profil Pengguna & Hak Akses (Role-Based Access Control)

Sistem MERCY dikhususkan untuk penggunaan internal dengan pemisahan hak akses berbasis **2 peran (*roles*)**: `admin` dan `pic` dengan otorisasi berbasis Laravel Policies:

| Peran (*Role*) | Akun Referensi | Deskripsi Tanggung Jawab | Hak Akses Utama | Batasan Keamanan |
| :--- | :--- | :--- | :--- | :--- |
| **Admin Unit Akreditasi Lab** (`admin`) | `admin@mercy.local` | Penanggung jawab administrasi operasional akreditasi, verifikasi kepatuhan, dan pemeliharaan teknis sistem BSN. | Akses penuh (*Full Control*) ke seluruh modul: Registrasi, ubah, dan hapus LPK; impor massal LPK dan Asesmen (CSV/XLSX/Google Sheets); penjadwalan 8 tipe asesmen KAN; pemrosesan EHA; penugasan PIC; pencatatan SK KAN; ekspor data Google Sheets live feed; manajemen pengguna; dan pemicu notifikasi surveilen. | Tanpa batasan hak akses di sistem. |
| **PIC Laboratorium / Unit Teknis** (`pic`) | `pic@mercy.local` | Narahubung & penanggung jawab operasional unit teknis yang mendampingi LPK binaan. | Akses penuh operasional pada LPK binaannya: <br>1. **Database LPK**: Menambah dan menghapus data LPK sesuai tanggung jawabnya; <br>2. **Edit Profil LPK**: Mengubah nama LPK, masa akreditasi, kontak, alamat, personel penghubung, dan lingkup LPK; <br>3. **Notifikasi Siklus**: Menerima notifikasi deadline proses akreditasi berjalan sesuai siklus (AA, S1, S2, PRL, STT, RA); <br>4. **Input Realisasi Asesmen**: Menginput proses nyata di lapangan (Jenis Proses dropdown, Tanggal Realisasi, Tim Asesmen text); <br>5. **Notifikasi Bertingkat TP**: Menerima Notifikasi 1 (AA: +2 bln, Lainnya: +1 bln) dan Notifikasi Final batas waktu TP/VTP (AA: 3 bln, Lainnya: 2 bln); <br>6. **Perpanjangan TP**: Menginput perpanjangan batas waktu TP (+1 bln) dan menerima notifikasi perpanjangan; <br>7. **Pemenuhan & Laporan**: Menginput tanggal dinyatakan memenuhi dan tanggal Laporan Asesmen; <br>8. **EHA & SK**: Menginput realisasi Evaluasi Hasil Asesmen (EHA) dan tanggal SK KAN. | Dibatasi secara ketat (*403 Forbidden* via Policy): Dibatasi hanya pada LPK yang menjadi tanggung jawabnya (`pic_user_id == auth()->id()`), dilarang mengimpor data LPK massal lintas unit, dan dilarang mengelola akun pengguna sistem. |

---

## 3. Spesifikasi Kebutuhan Fungsional & Aturan Domain (Functional & Domain Rules)

### 3.1 Modul 1: Manajemen Master Laboratorium Penguji (LP)
* **Karakteristik Data**:
  * Nomor Registrasi resmi KAN (`no_reg` dan `registration_number`), bersifat unik dengan format baku Laboratorium Penguji (contoh: `LP-001-IDN`, `LP-021-IDN`, `LP-1450-IDN`).
  * Nama Laboratorium Penguji, Skema Akreditasi KAN (`kan_schema` menggunakan `App\Enums\KanSchema`: `LP` / Laboratorium Penguji SNI ISO/IEC 17025:2017, dilengkapi klasifikasi bidang lingkup pengujian seperti Kimia, Biologi/Mikrobiologi, Fisika, Mekanik, Kelistrikan, Lingkungan).
  * Alamat Lengkap, Kota, Provinsi, Alamat Email PIC, Nomor Telepon/Kontak.
  * Penugasan PIC Pendamping (`pic_user_id`) terhubung ke model `User` ber-role `pic`.
  * Tanggal Terbit Sertifikat Akreditasi (`certificate_date`) dan Tanggal Kedaluwarsa Sertifikat (`expired_at`).
  * Aturan Otomasi Kedaluwarsa: Jika `expired_at` dikosongkan pada form pendaftaran, sistem otomatis menghitung `+5 tahun` dari `certificate_date`.
  * Integrasi Dokumen: Menggunakan tautan Google Drive terpadu (`drive_url`) untuk berkas sertifikat, lampiran ruang lingkup pengujian, dan SK akreditasi.
* **Mesin Status Akreditasi Dinamis (Smart Dynamic Status Engine)**:
  Status LPK dievaluasi secara dinamis melalui domain service (`SurveillanceEngineService`), menghasilkan nilai dari `App\Enums\LpkStatus`:
  1. `REVOKED` (Dicabut): Akreditasi dicabut karena telah melewati masa toleransi pembekuan 1 tahun tanpa penyelesaian asesmen surveilen.
  2. `SUSPENDED` (Dibekukan): Status LPK dibekukan sementara karena melewati batas toleransi pengisian surveilen atau melewati batas waktu tindakan perbaikan tanpa pemenuhan.
  3. `EXPIRED` (Kedaluwarsa): Masa berlaku sertifikat akreditasi telah habis (`now > expired_at`).
  4. `SURVEILLANCE_OVERDUE` (Surveilen Terlewat): LPK berstatus aktif, namun telah melewati target pelaksanaan Surveilen 1 (bulan 15) atau Surveilen 2 (bulan 36) tanpa agenda asesmen.
  5. `SURVEILLANCE_DUE` (Surveilen Mendekati Tenggat): LPK yang memasuki jendela notifikasi pengawasan aktif (bulan 14 untuk S1, bulan 35 untuk S2, atau 1 bulan sebelum habis untuk Re-Akreditasi).
  6. `ACTIVE` (Aktif): LPK aktif, seluruh siklus pengawasan terpenuhi atau terjadwal aman.
  7. `INACTIVE` (Tidak Aktif): Status LPK dinonaktifkan secara administratif oleh administrator.
* **Tampilan Tabel Master & Kartu Keterangan Bertumpuk (Dual Notes Stack)**:
  Pada daftar LPK (`lpks/index`) maupun halaman rincian (`lpks/show`), data disajikan dalam arsitektur tabel terstruktur 4 kolom utama:
  1. **Toolbar Atas Tabel**:
     * Pengatur jumlah baris: `Show [ 10 ⌄ ] entries` (pilihan 10, 25, 50, 100 entri).
     * Pengalih mode tampilan (*View Mode Switcher*): Tombol grup `[ Tabel ]` dan `[ Grid ]` untuk beralih antara tampilan tabel data densitas tinggi dan tampilan kartu grid responsif.
  2. **Kolom 1: NO. AKREDITASI** (Mendukung pengurutan / *sortable*):
     * Menampilkan Nomor Registrasi resmi KAN (misal: `LP-021-IDN`, `LP-1450-IDN`) dalam format monospace tebal.
     * Menampilkan badge peringatan proses/tahapan aktif tepat di bawah nomor (misal: badge amber peringatan `⚠️ Re-Akreditasi (Akreditasi Ulang)`).
  3. **Kolom 2: NAMA LPK** (Mendukung pengurutan / *sortable*):
     * Menampilkan Nama Lembaga Penilaian Kesesuaian dalam teks tebal (misal: `Balai Besar Logam dan Mesin`, `PT Guna Sukses Inti`).
     * Menampilkan deretan badge status dan PIC di bawah nama:
       - Badge status pengawasan: `Jatuh Tempo Surveilen` (amber outline), `Aktif` (emerald).
       - Badge PIC pendamping: `PIC: [Nama PIC]` (neutral outline).
  4. **Kolom 3: MASA BERLAKU (AWAL & AKHIR)** (Mendukung pengurutan / *sortable*):
     * Menampilkan tanggal terbit sertifikat: `Awal: DD/MM/YYYY` (label "Awal:" warna teks muted).
     * Menampilkan tanggal kedaluwarsa sertifikat: `Akhir: DD/MM/YYYY` (label "Akhir:" warna teks muted).
     * Menampilkan badge peringatan jika mendekati batas habis: `Mendekati Kedaluwarsa` (neutral outline).
  5. **Kolom 4: KETERANGAN (Area Kartu Keterangan Bertumpuk / Dual Notes Stack)**:
     Keterangan disajikan dalam bentuk kartu ringkasan visual berlatar warna tematik:
     * **Kartu Status Siklus Surveilen (Otomatis)**:
       - Header kartu: Badge nama proses di kiri (misal: `Re-Akreditasi (Akreditasi Ulang)`, `Surveilen 1`) dan indikator status proses di kanan dengan titik status (`● JATUH TEMPO` [Rose], `● PELAKSANAAN` [Emerald], `● BATAS TP` [Cyan], `● REMINDER` [Amber]).
       - Body kartu: Redaksi terhitung sistem secara presisi:
         - Jika dibekukan: `Dibekukan (toleransi pengisian DD/MM/YYYY terlampaui. Sisa X hari kesempatan penyelesaian s/d DD/MM/YYYY).`
         - Jika terjadwal: `Terjadwal DD/MM/YYYY.`
     * **Kartu Catatan Khusus PIC (Manual)**:
       - Ditampilkan tepat di bawah kartu status otomatis jika PIC telah mengisi catatan.
       - Header kartu: Label kecil uppercase `CATATAN PIC` (warna amber).
       - Body kartu: Catatan teknis koordinasi internal (misal: `ruang lingkup diperhatikan`).
       - Dilengkapi modal form edit interaktif yang memungkinkan PIC mengubah catatan atau menyalin ringkasan sistem.
* **Kewenangan Database LPK oleh PIC**:
  * PIC memiliki hak penuh untuk **menambah data LPK baru** dan **mengurangi/menghapus data LPK** yang menjadi tanggung jawab binaannya (`pic_user_id == auth()->id()`).
  * PIC berwenang melakukan **edit database LPK binaan**, meliputi: Nama LPK, masa akreditasi LPK (`certificate_date` dan `expired_at`), kontak LPK (email dan telepon), alamat lengkap LPK, personel penghubung LPK, serta ruang lingkup akreditasi LPK (`scope`).
* **Impor Massal Data Master (Smart Upsert)**:
  * Mendukung unggah berkas `.csv` dan `.xlsx` melalui Action `UpsertLpkBatchAction`.
  * Mendukung penarikan langsung dari tautan publik Google Sheets via HTTP Client.
  * Logika *Smart Upsert*: Jika nomor registrasi sudah ada di basis data, perbarui data profilnya; jika belum ada, buat entri baru secara transaksional.

---

### 3.2 Modul 2: Siklus Pengawasan & 8 Tipe Proses Asesmen KAN (KAN U-01)
* **8 Tipe Proses Asesmen Resmi KAN (`App\Enums\AssessmentType`)**:
  1. `INITIAL`: Akreditasi Awal (AA)
  2. `SURVEILLANCE`: Surveilen 1 (S1)
  3. `SURVEILLANCE_PRL`: Surveilen 1 + Perluasan Ruang Lingkup (S1 + PRL)
  4. `SURVEILLANCE_2`: Surveilen 2 (S2)
  5. `SURVEILLANCE_2_PRL`: Surveilen 2 + Perluasan Ruang Lingkup (S2 + PRL)
  6. `UNSCHEDULED_SURVEILLANCE`: Surveilen Tidak Terjadwal (STT)
  7. `SCOPE_EXTENSION`: Perluasan Ruang Lingkup (PRL)
  8. `REASSESSMENT`: Re-Akreditasi / Akreditasi Ulang (RA)
* **Notifikasi Deadline Siklus Akreditasi**:
  * PIC otomatis menerima notifikasi pengingat batas waktu (*deadline*) proses akreditasi yang harus berjalan sesuai tonggak siklus KAN: Akreditasi Awal (AA), Survailen 1 (S1), Survailen 2 (S2), Perluasan Ruang Lingkup (PRL), Survailen Tidak Terjadwal (STT), dan Re-Akreditasi (RA).
* **Input Realisasi Pelaksanaan Proses Akreditasi oleh PIC (Real-World Execution)**:
  * PIC berwenang penuh untuk menginput realisasi pelaksanaan proses asesmen sesuai dengan fakta riil proses yang dijalankan oleh laboratorium di lapangan (**fleksibel dan tidak kaku terikat pada siklus yang telah ditetapkan sebelumnya**).
  * Data spesifik yang diinput oleh PIC:
    1. **Jenis Proses** (`dropdown`: Akreditasi Awal, Survailen 1, Survailen 1 + PRL, Survailen 2, Survailen 2 + PRL, Perluasan Ruang Lingkup, Survailen Tidak Terjadwal, Re-Akreditasi).
    2. **Tanggal Realisasi** (`tanggal`: tanggal riil kunjungan/pelaksanaan asesmen lapangan).
    3. **Tim Asesmen** (`text`: nama-nama tim asesor yang ditugaskan, termasuk Asesor Kepala/Lead Assessor dan Anggota).

---

### 3.3 Modul 3: Toleransi Pengisian & Siklus Hidup 3 Tahap Pengawasan
Sistem memberlakukan aturan siklus hidup bertingkat (*Three-Stage Surveillance Lifecycle*):
1. **Tahap 1: Toleransi Kunjungan Berjalan (Normal / In Progress)**:
   * Batas waktu pengisian dokumen asesmen surveilen dihitung sampai dengan **akhir bulan tanggal kunjungan** (`submission_due_date = end_at->endOfMonth()`).
   * Selama tanggal saat ini belum melewati batas akhir bulan kunjungan, status asesmen tetap `PLANNED` atau `IN_PROGRESS`.
2. **Tahap 2: Pembekuan Akreditasi & Jendela Penyelesaian 1 Tahun (Suspended)**:
   * Jika batas toleransi pengisian (`submission_due_date`) telah terlewati dan asesmen belum dinyatakan selesai, status asesmen dan status LPK seketika beralih secara otomatis menjadi **DIBEKUKAN** (`SUSPENDED`).
   * LPK diberikan hak dan kesempatan untuk menyelesaikan pembekuannya selama **1 tahun penuh** terhitung sejak tanggal toleransi (`submission_due_date + 1 year`).
   * Antarmuka menyajikan informasi hitung mundur masa penyelesaian pembekuan (jumlah hari dan bulan tersisa).
3. **Tahap 3: Pencabutan Status Akreditasi (Revoked)**:
   * Jika kesempatan 1 tahun telah habis (`is_suspension_expired = true`) dan LPK belum juga menyelesaikan kewajiban asesmennya, status akreditasi LPK seketika beralih otomatis menjadi **DICABUT** (`REVOKED`).
4. **Aturan Auto-Realisasi Data Historis LPK Aktif**:
   * Apabila status akreditasi LPK saat ini tercatat AKTIF (`ACTIVE`), maka seluruh data surveilen, tindakan perbaikan, dan asesmen dari tahun-tahun sebelumnya (`end_at < now()->startOfYear()`) secara otomatis diakui telah selesai dan memenuhi (`COMPLETED` dan `SATISFIED`).

---

### 3.4 Modul 4: Pelacakan Tindakan Perbaikan (TP & VTP) Berbasis Batas Waktu & Notifikasi Bertingkat
* **Batas Waktu TP & VTP (Tindakan Perbaikan & Verifikasi Tindakan Perbaikan)**:
  * **Akreditasi Awal (AA)**: **3 Bulan Kalender** setelah tanggal realisasi asesmen.
  * **Lainnya (Sr1, Sr2, PRL, RA)**: **2 Bulan Kalender** setelah tanggal realisasi asesmen.
* **Sistem Notifikasi Bertingkat Tindakan Perbaikan**:
  * **Notifikasi 1 (Early Reminder Asesmen)**:
    * **AA**: Dikirimkan otomatis pada **2 bulan setelah tanggal realisasi**.
    * **Lainnya (Sr1, Sr2, PRL, RA)**: Dikirimkan otomatis pada **1 bulan setelah tanggal realisasi**.
  * **Notifikasi Final (Jatuh Tempo Batas TP VTP)**:
    * **AA**: Tepat pada bulan ke-3 batas TP VTP.
    * **Lainnya (Sr1, Sr2, PRL, RA)**: Tepat pada bulan ke-2 batas TP VTP.
* **Perpanjangan Batas Waktu Tindakan Perbaikan oleh PIC (+1 Bulan)**:
  * **Kewenangan PIC**: PIC berwenang menyetujui dan menginput perpanjangan batas waktu tindakan perbaikan selama **1 bulan kalender** (`tp_extension_months = 1`).
  * **Syarat Mutlak Perpanjangan (Progress Check)**:
    * Laboratorium **wajib telah mengunggah/melakukan perbaikan sebagian** atas temuan ketidaksesuaian asesmen (`tp_findings_closed > 0` dan `tp_findings_closed < tp_findings_total`).
    * *Contoh riil*: Jika dari **10 temuan ketidaksesuaian** baru terselesaikan **8 temuan**, maka sisa **2 temuan** yang belum sesuai diberikan kesempatan perpanjangan batas waktu selama 1 bulan.
  * **Larangan Perpanjangan (Zero Progress Rejection & Penghentian Proses)**:
    * Apabila laboratorium **tidak ada perbaikan sama sekali (progres kosong / 0 temuan diselesaikan)** selama masa normal (2 bulan untuk S1/S2/PRL/STT/RA atau 3 bulan untuk AA), maka laboratorium **TIDAK BOLEH mengajukan perpanjangan**.
    * Proses penyelesaian tindakan perbaikan **wajib langsung dihentikan**, pengajuan perpanjangan ditolak sistem melalui `InvalidTpExtensionException`, dan status LPK seketika beralih menjadi **DIBEKUKAN** (`SUSPENDED`).
  * **Akumulasi Total Batas Waktu Pasca-Perpanjangan**:
    * **Akreditasi Awal (AA)**: 3 bulan batas normal + 1 bulan perpanjangan = **Total 4 Bulan Kalender**.
    * **Lainnya (Survailen 1, Survailen 2, Perluasan Ruang Lingkup, Survailen Tidak Terjadwal, Re-Akreditasi)**: 2 bulan batas normal + 1 bulan perpanjangan = **Total 3 Bulan Kalender**.
  * **Notifikasi Pengingat Perpanjangan**:
    * Sistem secara otomatis mengirimkan notifikasi pengingat batas waktu perpanjangan saat jendela perpanjangan mendekati jatuh tempo.
* **Pencatatan Tanggal Pemenuhan & Laporan Asesmen oleh PIC**:
  * PIC menginput **Tanggal Dinyatakan Memenuhi** (`tp_satisfied_at`) setelah perbaikan diverifikasi memuaskan.
  * PIC menginput **Tanggal Laporan Asesmen** (`assessment_report_date`) sebagai bukti penyelesaian dokumen asesmen lapangan.
* **Otomasi Status Tindakan Perbaikan (`App\Enums\TpStatus`)**:
  * Dihitung otomatis oleh `TpDeadlineService`:
    * Jika `tp_satisfied_at` belum terisi:
      - Sebelum jatuh tempo: `IN_PROGRESS` (Sedang Berlangsung).
      - Melewati batas waktu: `SUSPENDED` (Dibekukan, badge ungu).
    * Jika `tp_satisfied_at` telah terisi: `COMPLETED` / `SATISFIED` (Memenuhi, badge hijau).

---

### 3.5 Modul 5: Alur Evaluasi Hasil Asesmen (EHA) & Penerbitan SK KAN
* **Pencatatan Evaluasi Hasil Asesmen (EHA) oleh PIC**:
  * PIC berwenang menginput dan memproses Evaluasi Hasil Asesmen (EHA):
    * Tanggal Realisasi EHA (`eha_completed_at`) dan Tanggal Rencana EHA (`eha_scheduled_date`).
    * Catatan dan rekomendasi sidang panitia teknis / tim evaluasi (`eha_notes`).
* **Pencatatan SK Akreditasi KAN oleh PIC**:
  * PIC berwenang menginput **Tanggal Terbit SK** (`sk_issued_at`) dan Nomor SK resmi KAN (`sk_number`).
  * Lead time penerbitan SK (`sk_lead_time_days`) dihitung otomatis oleh sistem dari tanggal pemenuhan TP ke tanggal SK.

---

### 3.6 Modul 6: Penjadwalan & Kalender Interaktif Multi-Event (Google Calendar Layout)
Aplikasi mengadopsi arsitektur antarmuka dua kolom bergaya **Google Calendar** untuk mengelola seluruh jadwal asesmen, batas waktu surveilen, dan tenggat tindakan perbaikan secara komprehensif:

* **Banner Peringatan Persisten KAN (Top Alert Bar)**:
  * Menampilkan peringatan mendesak siklus surveilen: *"Peringatan Siklus Pengawasan Akreditasi: X Kunjungan Memerlukan Tindak Lanjut!"*.
  * Penjelasan regulasi: Notifikasi aktif pada Bulan ke-14 (S1), Bulan ke-35 (S2), dan 1 Bulan sebelum kedaluwarsa (Re-Akreditasi), bersifat persisten dan tidak dapat diabaikan hingga jadwal diagendakan.
  * Tombol aksi langsung: *"Tinjau LPK Jatuh Tempo"* untuk navigasi cepat ke daftar LPK terkait.

* **Panel Kontrol Samping (Left Calendar Sidebar)**:
  1. **Tombol "+ Tambah Agenda Baru ⌄"**:
     * Tombol aksi pembuatan jadwal asesmen atau agenda pengawasan baru dengan menu popover/modal input (`<x-modal>`).
  2. **Kalender Mini Inline (Mini Month Picker)**:
     * Menyajikan navigasi cepat tanggal dengan tombol panah `<` dan `>`, label bulan dan tahun aktif, deretan header hari (`S S R K J S M`), dan penanda tanggal aktif hari ini.
     * Mengklik tanggal pada kalender mini secara reaktif memindahkan fokus dan rentang tanggal pada kalender utama di sebelah kanan.
  3. **Filter Kategori Kalender Reaktif**:
     * Deretan *checkbox* filter multi-pilih dilengkapi indikator titik warna (*color legend dot*):
       - `● Pelaksanaan` (Emerald / Hijau): Kunjungan asesmen lapangan resmi KAN.
       - `● Batas TP` (Cyan / Biru): Batas waktu penyelesaian Tindakan Perbaikan.
       - `● Reminder` (Amber / Kuning): Pengingat jendela surveilen bulan 14/35.
       - `● Jatuh Tempo` (Rose / Merah): Toleransi terlampaui / LPK berstatus dibekukan atau kedaluwarsa.
       - `● Agenda Internal` (Purple / Ungu): Sidang panitia teknis / evaluasi hasil asesmen (EHA).
  4. **Filter Lembaga (LPK)**:
     * Selector dropdown berbasis `<x-select.styled>` yang mendukung pencarian live untuk menyaring event berdasarkan satu LPK binaan tertentu atau menampilkan seluruh LPK (`Semua LPK`).

* **Grid Kalender Utama (Main Interactive Grid Panel)**:
  1. **Bilah Navigasi Kalender (Header Bar)**:
     * Tombol *"Hari ini"* untuk melompat seketika ke tanggal hari ini.
     * Tombol panah navigasi `<` dan `>` untuk berpindah bulan/minggu/hari secara instan.
     * Dropdown cepat pemilihan Bulan (Januari sampai Desember) dan Tahun dinamis.
     * Pengalih 4 mode tampilan (*4-View Segmented Control*): **Bulan** (*Month View*), **Minggu** (*Week View*), **Hari** (*Day View*), dan **Agenda** (*Agenda View*).
  2. **Tampilan Bulan (Month Grid View)**:
     * Header 7 kolom hari kerja dan akhir pekan (`Senin`, `Selasa`, `Rabu`, `Kamis`, `Jumat`, `Sabtu`, `Minggu`).
     * Kotak sel tanggal proporsional dengan penanda lingkaran warna primer pada tanggal saat ini.
     * *Event Chips* horizontal di dalam sel tanggal dengan warna tematik sesuai kategori event (misal chip merah muda bertuliskan `Kedaluwarsa`, chip hijau bertuliskan nama asesmen).
     * Klik pada sel tanggal membuka form pembuatan agenda baru pada tanggal tersebut, dan klik pada event chip membuka modal rincian agenda asesmen.
  3. **Pola Deep-Link & Auto-Focus Kalender**:
     * Format URL: `/calendar?view=month&date=YYYY-MM-DD&highlight={event_id}&selected=1`.
     * Resolusi otomatis tanggal di backend jika hanya menyertakan parameter `highlight`.
     * Penanda visual penyorotan target dan auto-focus modal popover.

---

### 3.7 Modul 7: Mesin Pencarian & Filter Reaktif Instan
* **Pencarian & Filter Terkelola Reaktif**:
  * Diterapkan pada tabel Master LPK dan Daftar Asesmen menggunakan Livewire 4 reactive components.
  * Input teks dengan *debounce* 280 ms dan pemantauan filter (*status*, *assessment_type*, *tp_status*, *kan_schema*, *pic_user_id*).
  * Pengatur jumlah baris per halaman: selector dropdown `Show [ 10 ⌄ ] entries` (10, 25, 50, 100 entri) yang mengendalikan pagination query secara reaktif.
  * Pengalih mode tampilan dwimuka (*Dual-View Mode Switcher*): tombol beralih `[ Tabel ]` dan `[ Grid ]` tersinkronisasi URL (`#[Url] public string $view = 'table'`).
  * Sinkronisasi query string URL otomatis (`#[Url]`) untuk *shareable search state*.
  * Tampilan badge filter aktif (*filter pills*) dengan kemampuan *single-click remove* dan tombol *reset all*.

---

### 3.8 Modul 8: Impor Massal Asesmen & LPK
* Fitur unggah CSV / XLSX terstandarisasi dengan pemetaan otomatis kolom.
* Eksekusi melalui Batch Action yang terlindung transaksi basis data (`DB::transaction`) dengan laporan detail baris sukses dan baris gagal (DTO `ImportResultDto`).

---

### 3.9 Modul 9: Live Feed Google Sheets & Laporan
* Endpoint data terlindungi parameter API key rahasia:
  * `/feeds/lpks.csv?key={api_key}`
  * `/feeds/assessments.csv?key={api_key}`
* Ekspor CSV terotentikasi dengan encoding UTF-8 BOM untuk kompatibilitas penuh dengan Microsoft Excel.

---

## 4. Arsitektur Kode & Cetak Biru Clean Code (Code Architecture & Clean Code Blueprint)

Untuk memastikan kode MERCY terbebas dari *code bloat*, *fat controllers*, duplikasi formula domain, dan *AI code slop*, arsitektur aplikasi direfaktor menjadi **arsitektur berlapis yang bersih (*Clean Layered Architecture*)**:

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    PRESENTATION / UI LAYER (Livewire 4)                 │
│  - Livewire Components (Thin, reactive, UI-state only)                  │
│  - Livewire Form Objects (app/Livewire/Forms/* - validation & inputs)   │
│  - Blade / TallStackUI Components (Atomic UI, zero inline logic)        │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │ Calls / Dispatches
                                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    APPLICATION / ACTION LAYER (app/Actions)             │
│  - Single-Responsibility Invokable Action Classes                       │
│  - Database Transactions (DB::transaction) & Event Dispatching          │
│  - Input Handling via DTOs (app/DTOs/*)                                 │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │ Uses / Coordinates
                                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    DOMAIN & SERVICE LAYER (app/Services & Enums)        │
│  - SurveillanceEngineService (Milestone & lifecycle calculator)         │
│  - TpDeadlineService (Batas waktu TP/VTP & aturan perpanjangan)         │
│  - Backed Enums with Domain Behaviors (app/Enums/*)                     │
│  - Custom Domain Exceptions (app/Exceptions/*)                          │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │ Queries / Persists
                                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    PERSISTENCE & SECURITY LAYER                         │
│  - Eloquent Models (Relationships, Enums Casting, Typed Attributes)     │
│  - Custom Query Scopes & Builders                                       │
│  - Laravel Policies (Granular authorization replacing ad-hoc checks)    │
└─────────────────────────────────────────────────────────────────────────┘
```

### 4.1 Prinsip Desain Clean Code (SOLID, KISS, DRY)
1. **Single Responsibility Principle (SRP)**:
   * **Livewire Component**: Hanya bertanggung jawab mengikat event antarmuka, meneruskan input ke Form Object, dan menampilkan view. Dilarang menjalankan kalkulasi batas waktu langsung di komponen.
   * **Form Object (`Livewire\Forms\*`)**: Bertanggung jawab penuh atas aturan validasi, penampungan atribut input, dan pembentukan DTO.
   * **Action Class (`App\Actions\*`)**: Bertanggung jawab mengeksekusi satu proses mutasi data spesifik secara transaksional (contoh: `CreateAssessmentAction`, `ExtendTpDeadlineAction`, `SatisfyTpAction`).
   * **Service Class (`App\Services\*`)**: Mengenkapsulasi algoritma kalkulasi domain yang murni dan dapat diuji secara independen tanpa bergantung pada konteks HTTP.
   * **Model Class (`App\Models\*`)**: Hanya mendefinisikan relasi, casts, query scopes, dan atribut tabel. Tidak berisi logika presentasi HTML atau notifikasi email.
2. **Open/Closed Principle (OCP)**:
   * Skema KAN berfokus penuh pada Laboratorium Penguji (`KanSchema::LP`) beserta klasifikasi bidang lingkup pengujian dan tipe asesmen (`AssessmentType`) dikelola via PHP 8.3+ Backed Enums dengan *pattern matching* (`match`). Penambahan sub-bidang pengujian baru cukup memperbarui Enum tanpa merombak logika di belasan file.
3. **Liskov Substitution & Interface Segregation (LSP & ISP)**:
   * Parser impor berkas (`LpkImportParserInterface`) menyediakan kontrak modular untuk CSV, XLSX, maupun HTTP feed.
4. **Dependency Inversion (DIP)**:
   * Seluruh dependensi layanan eksternal atau kalkulator diinjeksi melalui *constructor injection* atau *method injection* Laravel Service Container.
5. **Don't Repeat Yourself (DRY)**:
   * Seluruh formula perhitungan jatuh tempo surveilen (bulan 14, 15, 35, 36) dan batas waktu TP/VTP (2 bulan / 3 bulan / +1 bulan) hanya didefinisikan satu kali di dalam `TpDeadlineService` dan `SurveillanceEngineService`.

---

### 4.2 Struktur Direktori Baku (`app/`)
Struktur pembuatan kode distandarisasi secara ketat dan ramping (*lean & clean*) berfokus pada kebutuhan pemantauan PIC:

```
app/
├── Actions/                              # Single-responsibility mutation classes
│   ├── Assessments/
│   │   ├── CreateAssessmentAction.php
│   │   ├── UpdateAssessmentAction.php
│   │   ├── ExtendTpDeadlineAction.php
│   │   ├── SatisfyTpAction.php
│   │   ├── RecordAssessmentReportAction.php
│   │   ├── CompleteEhaAction.php
│   │   └── IssueSkAction.php
│   └── Lpks/
│       ├── CreateLpkAction.php
│       ├── UpdateLpkAction.php
│       ├── DeleteLpkAction.php
│       ├── UpdatePicNotesAction.php
│       └── UpsertLpkBatchAction.php
├── DTOs/                                 # Strictly typed data transfer objects
│   ├── AssessmentData.php
│   ├── ImportResultDto.php
│   └── LpkData.php
├── Enums/                                # PHP 8.3+ Backed Enums with domain methods
│   ├── AssessmentType.php
│   ├── CalendarEventType.php
│   ├── KanSchema.php
│   ├── LpkStatus.php
│   ├── TpStatus.php
│   └── UserRole.php
├── Exceptions/                           # Domain-specific business exceptions
│   ├── DomainException.php
│   ├── InvalidTpExtensionException.php
│   └── LpkSuspendedException.php
├── Livewire/                             # Thin presentation components
│   ├── Assessments/
│   │   ├── AssessmentIndex.php
│   │   ├── AssessmentShow.php
│   │   └── AssessmentForm.php
│   ├── Calendar/
│   │   ├── CalendarView.php
│   │   └── CalendarEventModal.php
│   ├── Forms/                            # Livewire Form Objects for validation
│   │   ├── AssessmentForm.php
│   │   ├── LpkForm.php
│   │   └── PicNoteForm.php
│   ├── Lpks/
│   │   ├── LpkIndex.php
│   │   ├── LpkShow.php
│   │   └── LpkForm.php
│   │   └── LpkImportModal.php
├── Models/                               # Eloquent models (data & relations only)
│   ├── Assessment.php
│   ├── CalendarEvent.php
│   ├── Lpk.php
│   └── User.php
├── Policies/                             # Authorization rules per entity
│   ├── AssessmentPolicy.php
│   ├── CalendarEventPolicy.php
│   └── LpkPolicy.php
├── Providers/
│   └── AppServiceProvider.php
└── Services/                             # Domain calculation & business orchestration
    ├── CalendarFeedService.php
    ├── Importers/
    │   ├── CsvLpkImporter.php
    │   └── ExcelLpkImporter.php
    ├── TpDeadlineService.php
    └── SurveillanceEngineService.php
```

---

### 4.3 Standar Modern PHP 8.3+ & Laravel 13
1. **Strict Types & Static Analysis**:
   * Setiap berkas PHP diawali deklarasi `declare(strict_types=1);`.
   * Seluruh argumen method dan return type wajib memiliki typehint eksplisit (misal: `public function execute(AssessmentData $data): Assessment`).
   * Menghindari penggunaan tipe `mixed` dan array asosiatif tanpa struktur dengan menggantikannya menggunakan DTO atau Value Object.
2. **Backed Enums**:
   * Seluruh status, peran pengguna, skema KAN, dan tipe asesmen dikelola via PHP Backed Enums bertipe `string`.
   * Enum dilengkapi helper methods seperti `label()`, `colorBadge()`, dan atribut aturan bisnis.
   * Model Eloquent menggunakan *Model Casts* baru Laravel:
     ```php
     protected function casts(): array
     {
         return [
             'assessment_type' => AssessmentType::class,
             'status' => LpkStatus::class,
             'start_at' => 'date',
             'end_at' => 'date',
             'tp_due_date' => 'date',
         ];
     }
     ```
3. **Immutability & Readonly Properties**:
   * DTOs menggunakan `public readonly` properties atau `readonly class` untuk menjamin immutability data input di seluruh lapisan.

---

### 4.4 Pola Tindakan & Layanan Domain (Action & Domain Service Pattern)
* **Pola Action (`App\Actions\*`)**:
  * Setiap Action adalah kelas invokable (`public function __invoke(...)` atau `public function execute(...)`).
  * Action membungkus mutasi data dalam transaksi:
    ```php
    public function execute(Assessment $assessment, string $letterNo): Assessment
    {
        if (! $this->deadlineService->isEligibleForExtension($assessment)) {
            throw new InvalidTpExtensionException("Asesmen belum memenuhi syarat perpanjangan batas waktu.");
        }

        return DB::transaction(function () use ($assessment, $letterNo) {
            $extendedDate = $this->deadlineService->calculateExtendedDueDate($assessment);

            $assessment->update([
                'tp_has_extension' => true,
                'tp_extension_months' => 1,
                'tp_extension_letter_no' => $letterNo,
                'tp_extended_due_date' => $extendedDate,
            ]);

            return $assessment;
        });
    }
    ```
* **Pola Service (`App\Services\*`)**:
  * Berisi kalkulasi fungsional yang murni (*stateless / pure functions* jika memungkinkan).
  * Contoh: `TpDeadlineService` menghitung rentang bulan, tanggal batas waktu berdasarkan tipe asesmen, dan status kepatuhan tanpa melakukan query I/O langsung.

---

### 4.5 Pola Livewire 4 & Form Objects
* Komponen Livewire tidak boleh memuat ratusan baris aturan validasi atau logic kueri.
* Aturan validasi dan model binding form didelegasikan ke `Livewire\Forms\Form`:
  ```php
  class AssessmentForm extends Form
  {
      #[Validate('required|exists:lpks,id')]
      public int $lpk_id;

      #[Validate('required')]
      public AssessmentType $assessment_type = AssessmentType::SURVEILLANCE;

      #[Validate('required|date')]
      public string $start_at;

      #[Validate('required|date|after_or_equal:start_at')]
      public string $end_at;
  }
  ```
* Komponen Livewire hanya menjalankan:
  ```php
  public function save(CreateAssessmentAction $action): void
  {
      $this->form->validate();
      $action->execute($this->form->toDto());
      $this->toastSuccess('Jadwal asesmen berhasil diterbitkan.');
  }
  ```

---

### 4.6 Penanganan Kesalahan & Custom Domain Exceptions
* Larangan mengembalikan nilai boolean ambigu (`return false;`) saat terjadi kegagalan aturan bisnis.
* Gunakan Custom Domain Exceptions yang informatif:
  * `InvalidTpExtensionException`: Ditolak jika laboratorium tidak memiliki progres perbaikan sama sekali (0 temuan tertutup) selama masa normal 2/3 bulan, atau tanpa nomor surat.
  * `LpkSuspendedException`: Mencegah tindakan administratif tertentu saat LPK dalam masa pembekuan/pencabutan.
* Komponen UI / Livewire menangkap domain exception dan menampilkannya sebagai pesan toast / error alert yang ramah pengguna.

---

### 4.7 Standar Higienitas Komentar & Kode (Antislop Code Standards)
Mengadopsi aturan ketat *Antislop Code* untuk mencegah polusi komentar buatan AI yang tidak berguna:
1. **Dilarang Komentar Pemisah Dekoratif**:
   * Tidak boleh ada `// =====================`, `// ---------------- WORKFLOW ----------------`, atau banner ALL CAPS.
2. **Dilarang Menarasikan Kode yang Sudah Jelas (No Restating the Obvious)**:
   * Hapus komentar seperti `// Inisialisasi variabel`, `// User model`, `// Validasi data`, `// Return response`.
3. **Dilarang Menarasikan Alur Langkah (No Step-by-Step Workflow Narration)**:
   * Hapus `// Step 1: Ambil data`, `// Step 2: Proses data`. Kode yang ditulis rapi dengan penamaan method yang deskriptif adalah dokumentasi terbaik.
4. **Dilarang Emoji Dekoratif**:
   * Hapus `// ✅ Validasi berhasil` atau `// 🚀 Optimasi kueri`.
5. **Komentar yang Wajib Dipertahankan**:
   * Komentar hanya digunakan untuk mendokumentasikan **dasar hukum regulasi KAN** (misal rujukan pedoman KAN U-01), aturan batas waktu non-intuitif, atau *trade-off* teknis non-sepele.
   * Komentar ditulis singkat (1-2 baris), menggunakan bahasa natural pengembang.

---

## 5. Struktur Basis Data & Hubungan Antar-Entitas (Data Architecture & ERD)

Skema relasional MERCY disederhanakan (*streamlined*) dan dioptimalkan secara murni untuk pemantauan siklus akreditasi serta mempermudah pekerjaan PIC:

```
+-----------------------------------------------------------------------------------------+
|                                         USERS                                           |
| id (PK), name, email, password, role (admin / pic), timestamps                          |
+-----------------------------------------------------------------------------------------+
       │                                                                      │
       │ (1 to N) Penugasan PIC                                               │ (1 to N) Pembuat Event
       ▼                                                                      ▼
+-------------------------------------------------------+             +--------------------+
|                         LPKS                          |             |  CALENDAR_EVENTS   |
| id (PK), no_reg, registration_number, name,           |             | id (PK), lpk_id,   |
| kan_schema (KanSchema Enum), scope, address, email,   |             | assessment_id,     |
| status (LpkStatus Enum), expired_at, pic_user_id (FK),|             | event_type (Enum), |
| certificate_date, notes                               |             | title, start_at,   |
+-------------------------------------------------------+             | end_at, is_all_day |
       │                                                              +--------------------+
       │                                                                      ▲
       │ (1 to N)                                                             │
       ▼                                                                      │
+-----------------------------------------------------------------------------┴───────────+
|                                      ASSESSMENTS                                        |
| id (PK), lpk_id (FK), created_by (FK), title, assessment_type (AssessmentType Enum),     |
| start_at, end_at, location, lead_assessor, assessor_team, status,                       |
| tp_deadline_months (2/3), tp_due_date, tp_has_extension, tp_extension_months (max 1),    |
| tp_findings_total, tp_findings_closed,                                                  |
| tp_extension_letter_no, tp_extended_due_date, tp_submitted_at, tp_satisfied_at,        |
| assessment_report_date, eha_scheduled_date, eha_completed_at, eha_notes,                |
| sk_issued_at, sk_number, sk_lead_time_days                                              |
+-----------------------------------------------------------------------------------------+
```

---

## 6. Standar Antarmuka & Fondasi TallStackUI (UI/UX & TallStackUI Architecture)

Pengembangan antarmuka MERCY dibangun di atas fondasi resmi **TallStackUI v4** (`tallstackui/tallstackui`), sebuah pustaka komponen Blade modern dan terintegrasi penuh untuk ekosistem TALL Stack (Tailwind CSS 4, Alpine.js 3, Laravel 13, dan Livewire 4).

### 6.1 Filosofi & Prinsip Penerapan TallStackUI di MERCY
1. **Integrasi Ekosistem TALL Stack Asli**:
   * Komponen TallStackUI dirancang khusus untuk bekerja bersama Livewire 4 dan Alpine.js 3.
   * Tidak memerlukan penulisan ulang ratusan baris markup HTML mentah untuk kontrol form, tabel reaktif, atau jendela interaksi modal.
2. **Kustomisasi Bersih Melalui Soft Customization**:
   * Penyesuaian tampilan global maupun terarah dilakukan melalui mekanisme *Soft Customization* menggunakan `TallStackUi::customize()` di dalam `AppServiceProvider`.
   * Metode legacy v2 `TallStackUi::personalize()` sudah ditiadakan dan tidak boleh digunakan.
3. **Larangan Raw HTML Clutter**:
   * Dilarang menulis form controls manual (`<input>`, `<select>`, `<button>`) atau kontainer mentah dengan puluhan kelas utility berulang di dalam Blade view utama.
   * Seluruh antarmuka wajib memanfaatkan komponen resmi TallStackUI (`<x-input>`, `<x-select.*>`, `<x-button>`, `<x-card>`, `<x-modal>`, `<x-table>`, dan sejenisnya) untuk menjamin konsistensi visual, aksesibilitas, dan keterbacaan kode (*Clean Code UI*).
4. **Reaktivitas Alami Livewire 4 & Form Objects**:
   * Komponen input TallStackUI secara otomatis terikat dengan atribut `wire:model`, mendeteksi error validasi dari Form Objects (`$errors`), dan menampilkan label wajib dengan sintaks tanda bintang.
   * Interaktivitas mikro seperti modal, slide-over, dropdown, dan pemilih tanggal terisolasi dalam Alpine.js tanpa overhead JavaScript eksternal yang berat.
5. **Interaksi & Feedback Terprogram via Trait**:
   * Pengiriman notifikasi melayang dan konfirmasi aksi kritis ditangani secara programatik dari komponen Livewire menggunakan trait `TallStackUi\Traits\Interactions` (`$this->toast()` dan `$this->dialog()`).

---

### 6.2 Katalog & Pemetaan Komponen TallStackUI di MERCY

| Kategori | Komponen TallStackUI | Pemetaan Fitur di Aplikasi MERCY |
| :--- | :--- | :--- |
| **App Shell & Layout** | `<x-layout>` | Kerangka layout utama aplikasi dengan koordinasi viewport responsif. |
| | `<x-layout.header>` | Top bar aplikasi dengan slot kiri (toggle drawer mobile, tanggal aktif) dan kanan (user profile, theme switcher). |
| | `<x-sidebar collapsible>` | Panel navigasi samping yang dapat diciutkan (*collapsible*), responsif mobile drawer dan desktop fixed. |
| | `<x-sidebar.item>` | Menu navigasi per peran (`admin` & `pic`), mendukung deteksi rute aktif otomatis, ikon, dan badge counter. |
| | `<x-sidebar.separator>` | Pembatas visual antar-grup menu navigasi (contoh: Master Data, Monitoring Asesmen, Laporan). |
| | `<x-breadcrumbs>` | Penunjuk hierarki lokasi halaman saat navigasi detail LPK dan asesmen. |
| | `<x-theme-switch>` | Pengubah tema *Light Mode* dan *Dark Mode* otomatis dengan persistensi local storage bawaan. |
| **Containers & Surfaces** | `<x-card>` (props: header, footer, minimize, close, loading) | Kartu statistik dashboard, kartu rincian profil LPK, dan kartu milestone pengawasan. |
| | `<x-stats>` | Kartu ringkasan metrik angka dengan indikator tren, ikon, dan animasi hitung angka. |
| | `<x-clipboard>` | Tombol salin instan nomor registrasi LPK dan nomor SK KAN ke papan klip. |
| | `<x-kbd>` | Label visual penanda pintasan keyboard (contoh: `Ctrl+K`, `Esc`). |
| | `<x-tooltip>` | Balon petunjuk informasi saat kursor mengarah ke status atau kode skema akreditasi ISO/SNI. |
| **Formulir & Kontrol Data** | `<x-input>` | Input teks dan angka dengan fitur: `wire:model`, `icon`, `prefix`, `clearable`, dan penanganan error otomatis. |
| | `<x-select.styled>` | Dropdown dinamis dengan pencarian live untuk Skema KAN, pemilihan LPK, dan penugasan PIC. |
| | `<x-select.native>` | Dropdown pilihan native untuk filter cepat baris per halaman pada tabel data (Show entries) dan pemilih tahun/bulan. |
| | `<x-date>` / `<x-calendar>` / `<x-time>` | Pemilihan tanggal terbit sertifikat, jadwal kunjungan lapangan, batas waktu TP, jam asesmen, dan kalender mini inline. |
| | `<x-toggle>` & `<x-checkbox>` | Tombol toggle perpanjangan batas waktu, pengaturan pengingat, dan filter status tabel. |
| | `<x-textarea>` | Input teks multibaris dengan penghitung karakter untuk catatan PIC dan ringkasan sidang EHA. |
| | `<x-upload>` | Area unggah berkas massal CSV dan XLSX berbasis Livewire dengan indikator progres unggah. |
| | `<x-errors>` / `<x-error>` | Penampil ringkasan kesalahan validasi formulir dari Livewire Form Objects. |
| **Tabel & Visualisasi Data** | `<x-table>` | Tabel data terintegrasi untuk SEMUA penyajian data tabular (Master LPK, Rekapitulasi Asesmen, Log Audit) dengan sorting server-side, pagination, search, filter, dan `@interact`. |
| | `<x-badge>` | Indikator status kepatuhan akreditasi dengan sistem warna kontras tinggi KAN (solid, light, outline). |
| | `<x-avatar>` | Representasi profil pengguna PIC dan Admin dengan inisial nama atau foto pengguna. |
| | `<x-progress.bar>` | Pelacak persentase kemajuan penyelesaian temuan ketidaksesuaian Tindakan Perbaikan (TP). |
| | `<x-progress.circle>` | Indikator sirkular hitung mundur toleransi pengawasan surveilen. |
| | `<x-timeline>` | Garis waktu horizontal dan vertikal visualisasi siklus hidup akreditasi KAN (AA, S1, S2, RA). |
| **Aksi & Dialog Interaktif** | `<x-button>` (solid, outline, flat, subtle) | Tombol aksi dengan status loading bawaan (`loading`) dan penempatan ikon fungsional. |
| | `<x-button.circle>` | Tombol aksi ikonik melingkar untuk tindakan cepat per baris data (Detail, Edit, Kalender). |
| | `<x-button.group>` | Pengelompokan visual tombol filter periode waktu atau tombol aksi terkait. |
| | `<x-dropdown>` + `<x-dropdown.items>` | Menu popover aksi per baris tabel (Ubah Profil, Jadwalkan Asesmen, Cetak SK, Unduh CSV). |
| | `<x-modal>` (Mandat Semua Modal) | Dialog overlay terstruktur untuk SEMUA jendela pop-up modal: impor berkas massal, agenda kalender baru, perpanjangan TP, dan catatan PIC. |
| | `<x-dialog>` (Mandat Semua Dialog) | Dialog konfirmasi terprogram untuk SEMUA dialog konfirmasi aksi kritis (pembekuan LPK, pencabutan, hapus data) via Livewire `Interactions`. |
| | `<x-command-palette>` | Palet perintah keyboard cepat (Cmd+K / Ctrl+K) untuk pencarian nomor registrasi LPK secara instan. |
| | `<x-loading>` | Indikator overlay loading layar penuh saat proses pemrosesan batch atau impor data berlangsung. |
| **Notifikasi & Peringatan** | `<x-toast>` (Mandat Semua Notifikasi) | Notifikasi sistem melayang untuk SEMUA umpan balik mutasi data dan flash messages, dipicu via trait `Interactions` di komponen Livewire. |
| | `<x-alert>` | Banner peringatan status prioritas tinggi untuk LPK berstatus `SUSPENDED`, terlewat toleransi, atau peringatan siklus KAN. |

---

### 6.3 Standar Pewarnaan Badge Kepatuhan Regulasi KAN
Sistem status MERCY dipetakan secara presisi ke dalam komponen `<x-badge>` TallStackUI dengan kontras tinggi (WCAG AAA/AA):

| Status Kepatuhan | Nilai Enum (`LpkStatus` / `TpStatus`) | Implementasi TallStackUI Badge | Keterangan Visual |
| :--- | :--- | :--- | :--- |
| **Aktif / Memenuhi** | `ACTIVE`, `COMPLETED`, `SATISFIED` | `<x-badge color="emerald" text="Aktif" />` | Hijau segar; menandakan kepatuhan penuh regulasi. |
| **Jatuh Tempo Pengawasan** | `SURVEILLANCE_DUE`, `IN_PROGRESS` | `<x-badge color="amber" text="Jatuh Tempo" />` | Kuning peringatan; berada dalam jendela aksi notifikasi. |
| **Terlewat / Kedaluwarsa** | `SURVEILLANCE_OVERDUE`, `EXPIRED` | `<x-badge color="red" text="Terlewat" />` | Merah tegas; memerlukan tindakan perbaikan segera. |
| **Dibekukan Sementara** | `SUSPENDED` | `<x-badge color="purple" text="Dibekukan" />` | Ungu kontras tinggi; masa pembekuan 1 tahun sedang berjalan. |
| **Dicabut Resmi** | `REVOKED` | `<x-badge color="rose" text="Dicabut" />` | Merah tua pekat; status akreditasi berakhir permanen. |
| **Tidak Aktif** | `INACTIVE` | `<x-badge color="neutral" outline text="Nonaktif" />` | Abu-abu netral administratif. |

---

### 6.4 Penataan Komponen & Aksesibilitas Universal (Universal Accessibility)
1. **Penyelarasan Baris Asesmen & Kalender**:
   * Komponen kartu dan baris asesmen membungkus `<x-badge>` dan tombol aksi `<x-button.circle size="sm" color="neutral" />` secara sejajar horizontal (`flex items-center gap-2`).
   * Menghilangkan penumpukan elemen tautan bersarang (*no nested anchor tags*).
2. **Navigasi Keyboard Penuh**:
   * Seluruh komponen interaktif TallStackUI (modal dialog, dropdown menu, command palette, theme switch) dapat dioperasikan penuh melalui keyboard (`Tab`, `Shift+Tab`, `Enter`, `Escape`, panah navigasi).
3. **Standar Kontras Rasio**:
   * Seluruh paduan warna teks terhadap latar belakang memenuhi standar WCAG AA (minimal rasio 4.5:1 untuk teks normal dan 3:1 untuk teks tebal dan komponen grafis).

---

### 6.5 Kebijakan Standarisasi 11 Komponen Inti TallStackUI di Seluruh Aplikasi

Untuk menjamin konsistensi arsitektur antarmuka, aksesibilitas terpadu, dan kemudahan pemeliharaan kode (*maintainability*), MERCY menetapkan aturan implementasi wajib untuk 11 komponen inti TallStackUI berikut di seluruh modul aplikasi:

1. **`time` (`<x-time>`)**:
   * **Kebijakan**: Seluruh input waktu jam dan menit wajib menggunakan `<x-time>`. Dilarang menggunakan input waktu HTML mentah.
   * **Penerapan**: Penjadwalan jam kunjungan asesmen lapangan, waktu rapat pembukaan (opening meeting), rapat penutupan (closing meeting), dan sidang panitia teknis KAN dengan format 24 jam (`format="24" step="15"`).

2. **`calendar` (`<x-calendar>`)**:
   * **Kebijakan**: Digunakan untuk rendering kisi penanggalan inline langsung di halaman tanpa popover.
   * **Penerapan**: Mini Month Picker pada bilah navigasi samping kiri (*Left Sidebar*) Google Calendar MERCY (`<x-calendar wire:model.live="selectedCalendarDate" helpers />`) dan pemilihan rentang siklus pengawasan (`range double`).

3. **`table` (`<x-table>`)**:
   * **Kebijakan**: Seluruh penyajian data berulang berbentuk tabular (Master LPK, Rekapitulasi Asesmen, Riwayat Surveilen, Log Audit) wajib menggunakan `<x-table>`.
   * **Fitur Standar**: Server-side sorting (`:sort`), live filter (`filter`), loading state elegan (`loading`), paginasi terikat Livewire (`paginate`), dan kustomisasi 4 kolom utama Daftar LPK via direktif `@interact`.

4. **`toast` (`<x-toast>` - Mandat Tunggal untuk SEMUA Notifikasi)**:
   * **Kebijakan**: Seluruh notifikasi hasil mutasi data, umpan balik sistem, flash messages, dan pengingat aksi WAJIB menggunakan komponen `<x-toast />` tunggal di layout utama yang dipicu secara programatik dari komponen Livewire via trait `TallStackUi\Traits\Interactions`.
   * **Larangan**: Dilarang keras menggunakan session flash HTML banner sementara atau alert kustom ad-hoc untuk pesan aksi sementara.
   * **Metode Pemanggilan**: `$this->toast()->success()`, `$this->toast()->warning()`, `$this->toast()->error()`, dan `$this->toast()->info()`.

5. **`dialog` (`<x-dialog>` - Mandat Tunggal untuk SEMUA Dialog Konfirmasi)**:
   * **Kebijakan**: Seluruh dialog konfirmasi aksi kritis, aksi destruktif, atau perubahan status regulasi WAJIB menggunakan komponen `<x-dialog />` tunggal di layout utama yang dipicu via trait `TallStackUi\Traits\Interactions`.
   * **Larangan**: Dilarang menggunakan fungsi bawaan peramban (`window.confirm()`, `window.alert()`).
   * **Penerapan**: Konfirmasi pembekuan status akreditasi LPK (`confirmSuspension`), konfirmasi pencabutan akreditasi, konfirmasi pembatalan jadwal asesmen, dan konfirmasi hapus data permanen.

6. **`modal` (`<x-modal>` - Mandat Tunggal untuk SEMUA Modal Form & Popup)**:
   * **Kebijakan**: Seluruh jendela sembul modal yang menampung formulir atau konten detail cepat WAJIB menggunakan `<x-modal id="..." wire="...">`.
   * **Larangan**: Dilarang membangun modal kustom dengan manipulasi kelas Tailwind manual (`fixed inset-0 ...`).
   * **Penerapan**: Modal pembuatan agenda baru kalender (`create-calendar-event`), modal impor massal LPK (`lpk-import-modal`), modal ubah catatan PIC (`edit-pic-note-modal`), dan modal perpanjangan batas waktu TP.

7. **`tooltip` (`<x-tooltip>`)**:
   * **Kebijakan**: Digunakan untuk menyediakan teks bantuan atau deskripsi teknis singkat tanpa mengotori ruang visual utama kanvas kerja.
   * **Penerapan**: Header kolom tabel LPK (penjelasan formula perhitungan masa berlaku), status badge KAN (alasan jatuh tempo), nomor klausul standar akreditasi (ISO/IEC 17025, ISO/IEC 17020), dan tombol aksi per baris.

8. **`timeline` (`<x-timeline>`, `<x-timeline.items>`)**:
   * **Kebijakan**: Komponen server-side murni tanpa JavaScript untuk memvisualisasikan urutan tahapan kronologis secara terstruktur.
   * **Penerapan**: Garis waktu 5 tahun siklus hidup akreditasi KAN U-01 (Akreditasi Awal -> Surveilen 1 -> Surveilen 2 -> Re-Akreditasi), serta riwayat kronologis penyelesaian temuan ketidaksesuaian asesmen lapangan.

9. **`theme switch` (`<x-theme-switch>` - Toggle Ganti Tema)**:
   * **Kebijakan**: Menggunakan komponen resmi `<x-theme-switch />` yang ditempatkan pada bilah header aplikasi (`<x-layout.header>`).
   * **Fitur**: Mendukung varian toggle sederhana (`simple only-icons`) dan mode tersegmentasi (`block`), dengan penyimpanan otomatis preferensi tema gelap/terang di *local storage* peramban tanpa kedipan layar (*zero flicker*).

10. **`badge` (`<x-badge>`)**:
    * **Kebijakan**: Standar tunggal representasi visual label status kepatuhan, siklus surveilen, dan penugasan peran pengguna.
    * **Spesifikasi**: Mematuhi rasio kontras WCAG AAA/AA dengan varian solid, light, dan outline sesuai matriks warna domain KAN (emerald, amber, red, purple, rose, cyan, neutral).

11. **`alert` (`<x-alert>`)**:
    * **Kebijakan**: Komponen banner informasi dan peringatan statis yang menempel pada halaman untuk kondisi risiko tinggi yang membutuhkan perhatian berkelanjutan.
    * **Penerapan**: Banner peringatan LPK dibekukan (*suspended status alert*), peringatan batas toleransi surveilen bulan 14/35 terlampaui, dan bilah peringatan persisten KAN di bagian atas kalender kegiatan.

---

## 7. Kebutuhan Non-Fungsional & Kualitas Perangkat Lunak (Non-Functional Requirements)

1. **Performa & Responsivitas**:
   * Waktu render halaman utama di bawah 150 ms.
   * Pembaruan tabel live filter Livewire selesai dalam waktu di bawah 100 ms.
   * Penerapan Eager Loading (`with(['lpk', 'creator'])`) di seluruh query komponen untuk menjamin **0 (Nol) Masalah N+1 Query**.
2. **Keamanan & Integritas Data**:
   * Proteksi token CSRF pada seluruh mutasi POST/PUT/DELETE.
   * Otorisasi granular via Laravel Policies pada level Action dan Livewire Component.
   * Proteksi multi-tenancy PIC: User PIC hanya dapat melihat dan memperbarui LPK yang tercatat di bawah binaannya (`pic_user_id == auth()->id()`).
3. **Kepatuhan Audit & Standar Regulasi**:
   * Logika tahapan pengawasan dan penegakan batas waktu mengacu pada pedoman Komite Akreditasi Nasional (KAN U-01).
   * Keabsahan dokumen digital diverifikasi dengan enkripsi hash SHA-256 BSrE BSSN.

---

## 8. Strategi Pengujian Otomatis (Automated Testing Strategy with Pest PHP)

Aplikasi dibangun di atas standar pengujian ketat menggunakan **Pest PHP 5** dengan target *test coverage* di atas 90%:
1. **Unit Testing (`tests/Unit`)**:
   * `SurveillanceEngineTest`: Menguji kalkulasi bulan ke-14, 15, 35, 36, dan transisi status LPK (Active, Suspended, Revoked).
   * `TpDeadlineTest`: Menguji penentuan batas 2 bulan vs 3 bulan, kelayakan perpanjangan +1 bulan (total 4 bulan AA, total 3 bulan lainnya jika ada progres temuan tertutup), dan penolakan keras/pembekuan jika tanpa perbaikan (0 temuan tertutup).
2. **Feature Testing (`tests/Feature`)**:
   * `LpkManagementTest`: Pengujian CRUD LPK, otorisasi peran PIC vs Admin, dan smart upsert CSV/XLSX.
   * `AssessmentWorkflowTest`: Pengujian siklus hidup asesmen, auto-naming singkatan KAN, input realisasi, input laporan asesmen, EHA, hingga verifikasi SK.
   * `CalendarDeepLinkTest`: Pengujian resolusi parameter highlight, perpindahan bulan/tahun otomatis, dan modal event popover.

---

## 9. Arsitektur Penerapan & Infrastruktur Layanan (Deployment Architecture)

1. **Lingkungan Kontainer Docker**:
   * Layanan berjalan pada kontainer Docker mandiri (`composer:2` berbasis Alpine Linux dan PHP 8.3/8.4+).
   * Nama kontainer: `mercy-app` dengan port binding internal `8002:8000`.
   * Kebijakan restart: `unless-stopped`.
2. **Eksposur Domain Publik Aman (Cloudflare Zero Trust Tunnel)**:
   * Akses publik dienkripsi ujung-ke-ujung melalui Cloudflare Tunnel (`cloudflared`).
   * Domain resmi publik: `https://mercy2.drzzy.my.id`.
   * **Target Service URL untuk Ingress Cloudflare Tunnel**: `http://127.0.0.1:8002` (atau `http://localhost:8002`).
   * Ingress Tunnel mengarahkan lalu lintas domain `mercy2.drzzy.my.id` secara langsung ke container port `8002`.

---

## 10. Rencana Pelaksanaan Refaktorisasi Bertahap (Refactoring Execution Roadmap)

Pelaksanaan pembangunan dan refaktorisasi arsitektur MERCY dibagi ke dalam 6 fase terukur yang saling berkesinambungan. Setiap fase memiliki daftar berkas konkret, checklist tugas teknis, dan kriteria penyelesaian (*Definition of Done* / DoD) yang teruji:

### 10.1 Matriks Ikhtisar 6 Fase Implementasi

| Fase | Fokus Arsitektural | Deliverables Konkret | Kriteria Selesai (DoD) |
| :--- | :--- | :--- | :--- |
| **Fase 1: Fondasi Domain & Type Safety** | Enums KAN, Exceptions, dan DTOs. | `KanSchema.php`, `LpkStatus.php`, `AssessmentType.php`, `CycleProcess.php`, `TpStatus.php`, `AssessmentData.php`. | 100% strict typing, metode casting enum dan warna badge teruji unit test. |
| **Fase 2: Skema Basis Data & Kebijakan (RBAC)** | Migrasi tabel, relasi Eloquent, query scopes, policies, dan seeders. | Migrations (`lpks`, `surveillance_cycles`, `assessments`, `tp_deadlines`, `pic_notes`), Models, `LpkPolicy.php`, `KanSchemaSeeder.php`. | `migrate:fresh --seed` berjalan mulus tanpa error; relasi DB bebas error constraint. |
| **Fase 3: Layanan Domain & Lapisan Action** | Kalkulasi regulasi KAN U-01 & penegakan toleransi waktu. | `SurveillanceEngineService.php` (bulan 14, 15, 35, 36, 59), `TpDeadlineService.php` (2 vs 3 bln & kelayakan perpanjangan), Actions transaksional. | Unit Test Pest PHP meliput seluruh aturan bisnis KAN dengan cakupan 100%. |
| **Fase 4: Lapisan Presentasi Daftar LPK & Detail** | Livewire 4, Form Objects, dan TallStackUI. | `app/Livewire/Lpk/Index.php` & `Show.php`, Form Objects, `<x-table>` 4-kolom `@interact`, Dual Notes Stack, `<x-select.native>`, `<x-modal>`. | Paginasi `Show [10] entries`, filter bidang pengujian / status LP live, sorting server-side, dan mode switcher Tabel/Grid berfungsi responsif. |
| **Fase 5: Lapisan Presentasi Kalender Kegiatan** | Arsitektur Google Calendar 2-kolom. | `app/Livewire/Calendar/Index.php`, `<x-calendar>` inline sidebar, checkbox kategori dengan dot legend, `<x-select.styled>` LPK, deep-linking URL. | Navigasi bulan mini-calendar sinkron dengan grid utama; chip event dan modal agenda berjalan tanpa glitch. |
| **Fase 6: Pengujian Otomatis, Linting & Deploy** | Feature tests komprehensif, pembersihan kode, dan deployment container. | Feature Test Pest (`tests/Feature`), Laravel Pint, Docker port `8002`, Cloudflare Tunnel. | Seluruh Feature Test hijau, Pint 0 format issues, dan aplikasi aktif di `mercy2.drzzy.my.id`. |

---

### 10.2 Rincian Fase 1: Fondasi Domain & Type Safety

* **Tujuan**: Menghilangkan ketergantungan pada *magic strings*, menjamin validitas tipe data, dan meletakkan aturan dasar regulasi KAN U-01.
* **Daftar Berkas yang Dibuat**:
  * `app/Enums/KanSchema.php`: Kode skema KAN khusus Laboratorium Penguji (`LP`) dan sub-lingkup bidang pengujiannya dengan metode label dan nama lengkap.
  * `app/Enums/LpkStatus.php`: Status akreditasi (`ACTIVE`, `SURVEILLANCE_DUE`, `SURVEILLANCE_OVERDUE`, `SUSPENDED`, `REVOKED`, `INACTIVE`) dengan relasi warna badge `<x-badge>`.
  * `app/Enums/AssessmentType.php`: Jenis kunjungan (`AA`, `S1`, `S2`, `RA`, `PRL`, `STT`, `EHA`, `VTP`).
  * `app/Enums/AssessmentStatus.php`: Status pelaksanaan (`DRAFT`, `SCHEDULED`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`).
  * `app/Enums/CycleProcess.php`: Tahapan siklus reguler KAN (`SURVEILLANCE_1`, `SURVEILLANCE_2`, `RE_ACCREDITATION`, `EXTENSION`).
  * `app/Enums/TpStatus.php`: Status batas waktu tindakan perbaikan (`PENDING`, `IN_PROGRESS`, `EXTENDED`, `SATISFIED`, `OVERDUE`).
  * `app/Exceptions/InvalidTpExtensionException.php`: Pengecualian saat perpanjangan diajukan tanpa progres perbaikan (0 temuan tertutup).
  * `app/Exceptions/SurveillanceToleranceOverdueException.php`: Pengecualian batas toleransi surveilen terlampaui.
  * `app/DTOs/AssessmentData.php`: Data transfer object immutable untuk payload asesmen.
  * `app/DTOs/LpkImportData.php`: Validasi payload impor CSV/XLSX.
* **Checklist Teknis**:
  - [ ] Buat seluruh Backed Enums dengan tipe `string` dan casting methods terintegrasi.
  - [ ] Hubungkan metode `badgeColor()` dan `badgeOutline()` pada `LpkStatus` dan `TpStatus` agar konsisten dengan standar warna KAN.
  - [ ] Buat DTOs menggunakan fitur PHP 8.3 `readonly class` untuk menjamin immutability data.
* **Definition of Done (DoD)**: 100% strict typing, PHPStan/Larastan level 8 lulus, dan seluruh unit test enum method hijau.

---

### 10.3 Rincian Fase 2: Skema Basis Data, Eloquent Models, Query Scopes & Policies

* **Tujuan**: Membangun skema tabel relasional berindeks tinggi dan proteksi otorisasi multi-tenancy PIC.
* **Daftar Berkas yang Dibuat**:
  * Migrations:
    * `database/migrations/2026_01_01_000001_create_lpks_table.php` (indeks pada `no_reg`, `status`, `expired_at`, `pic_user_id`).
    * `database/migrations/2026_01_01_000002_create_surveillance_cycles_table.php` (tahapan siklus terhitung sistem).
    * `database/migrations/2026_01_01_000003_create_assessments_table.php` (agenda asesmen, tanggal realisasi, jam opening/closing).
    * `database/migrations/2026_01_01_000004_create_tp_deadlines_table.php` (pelacak batas waktu 2/3 bulan dan status perpanjangan).
    * `database/migrations/2026_01_01_000005_create_pic_notes_table.php` (catatan koordinasi teknis PIC per LPK).
  * Models:
    * `app/Models/Lpk.php`: Relasi ke cycles, assessments, picNotes, dan user PIC; casts Backed Enums.
    * `app/Models/SurveillanceCycle.php`: Status milestone, tanggal jatuh tempo, dan deskripsi regulasi.
    * `app/Models/Assessment.php`: Relasi tim asesor, agenda kalender, dan hasil laporan.
    * `app/Models/TpDeadline.php`: Perhitungan sisa hari, temuan tertutup vs total temuan, dan log perpanjangan.
    * `app/Models/PicNote.php`: Riwayat catatan manual PIC.
  * Policies:
    * `app/Policies/LpkPolicy.php`: Otorisasi RBAC (Admin akses global; PIC hanya kelola LPK yang dibina).
    * `app/Policies/AssessmentPolicy.php`: Otorisasi pembuatan jadwal dan penerbitan SK.
  * Seeders:
    * `database/seeders/UserSeeder.php` (akun Admin dan PIC pendamping laboratorium).
    * `database/seeders/KanSchemaSeeder.php` (data referensi skema akreditasi KAN khusus Laboratorium Penguji / LP).
    * `database/seeders/DemoLpkSeeder.php` (data uji realistis mencakup LPK aktif, jatuh tempo S1/S2, dibekukan, dan re-akreditasi).
* **Checklist Teknis**:
  - [ ] Definisikan foreign key constraints dengan cascade rules yang tepat.
  - [ ] Implementasikan query scopes: `scopeActive()`, `scopeDueSurveillance()`, `scopeOverdue()`, `scopeAssignedTo()`.
  - [ ] Uji relasi Eloquent dan verifikasi zero N+1 queries menggunakan `with()` eager loading.
* **Definition of Done (DoD)**: Perintah `php artisan migrate:fresh --seed` berjalan sukses tanpa error; integritas referensial dan otorisasi policy terverifikasi.

---

### 10.4 Rincian Fase 3: Layanan Domain & Lapisan Action (Core Engine)

* **Tujuan**: Mengimplementasikan logika kalkulasi regulasi KAN U-01 murni, terisolasi, dan aman secara transaksional.
* **Daftar Berkas yang Dibuat**:
  * Services:
    * `app/Services/SurveillanceEngineService.php`:
      * Kalkulasi bulan ke-14 (S1), bulan ke-35 (S2), dan bulan ke-59 (RA) dari `certificate_date`.
      * Evaluasi masa toleransi keterlambatan (+1 bulan) dan otomatisasi status `SUSPENDED`.
      * Penentuan jendela penyelesaian 1 tahun sebelum eksekusi status `REVOKED`.
    * `app/Services/TpDeadlineService.php`:
      * Penentuan batas normal: 3 bulan kalender untuk Akreditasi Awal (AA); 2 bulan kalender untuk tipe lainnya.
      * Evaluasi kelayakan perpanjangan +1 bulan: hanya jika `tp_findings_closed > 0`.
      * Penolakan mutlak perpanjangan dan pemicuan pembekuan jika `tp_findings_closed == 0`.
  * Actions:
    * `app/Actions/Lpk/ImportLpkAction.php`: Smart upsert berkas CSV/XLSX dengan validasi registrasi KAN.
    * `app/Actions/Lpk/CalculateSurveillanceStatusAction.php`: Rekalkulasi status siklus seluruh LPK.
    * `app/Actions/Lpk/SuspendLpkAction.php`: Eksekusi status pembekuan dengan pencatatan audit trail.
    * `app/Actions/Assessment/ScheduleAssessmentAction.php`: Penerbitan jadwal agenda asesmen baru.
    * `app/Actions/Assessment/ApproveTpExtensionAction.php`: Persetujuan perpanjangan batas waktu TP.
    * `app/Actions/Pic/SavePicNoteAction.php`: Penyimpanan catatan koordinasi teknis PIC.
* **Checklist Teknis**:
  - [ ] Pisahkan aturan bisnis murni ke dalam Service class tanpa keterikatan pada HTTP request atau UI state.
  - [ ] Bungkus seluruh mutasi data transaksional ke dalam `DB::transaction()`.
  - [ ] Buat Unit Test komprehensif menggunakan Pest PHP untuk menguji seluruh formula penanggalan dan kondisi edge-case.
* **Definition of Done (DoD)**: Unit test mencakup 100% jalur logika bisnis KAN U-01; seluruh skenario kelayakan dan penolakan perpanjangan teruji secara matematis.

---

### 10.5 Rincian Fase 4: Lapisan Presentasi Modul Master LPK & Detail (Livewire 4 & TallStackUI)

* **Tujuan**: Membangun tampilan daftar master LPK yang identik dengan mock-up operasional resmi MERCY.
* **Daftar Berkas yang Dibuat**:
  * Livewire Components:
    * `app/Livewire/Lpk/Index.php`: Thin component untuk pencarian, sorting, filter, paginasi, dan pengalih tampilan.
    * `app/Livewire/Lpk/Show.php`: Thin component rincian profil LPK 2-kolom berdampingan.
  * Form Objects:
    * `app/Livewire/Forms/LpkForm.php`: Validasi data LPK.
    * `app/Livewire/Forms/PicNoteForm.php`: Validasi catatan manual PIC.
  * Blade Views:
    * `resources/views/livewire/lpk/index.blade.php`: Tampilan utama daftar LPK.
    * `resources/views/livewire/lpk/partials/table-view.blade.php`: Implementasi `<x-table>` 4-kolom `@interact`.
    * `resources/views/livewire/lpk/partials/grid-view.blade.php`: Implementasi susunan kartu grid responsif.
    * `resources/views/livewire/lpk/show.blade.php`: Halaman detail 2-kolom.
    * `resources/views/livewire/lpk/partials/cycle-card.blade.php`: Kartu keterangan otomatis berlatar tematik.
* **Checklist Teknis**:
  - [ ] Terapkan tabel 4-kolom presisi:
    * Kolom 1 (`NO. AKREDITASI`): Font monospace tebal + badge peringatan tahapan mendesak.
    * Kolom 2 (`NAMA LPK`): Teks institusi tebal + deretan badge status & PIC di bawahnya.
    * Kolom 3 (`MASA BERLAKU`): Sub-label abu-abu `Awal:` dan `Akhir:` format `DD/MM/YYYY`.
    * Kolom 4 (`KETERANGAN`): Tumpukan kartu status otomatis bertema warna + kartu catatan PIC latar amber.
  - [ ] Terapkan kendali toolbar atas:
    * Kiri: Paginasi instan `Show [ 10 ⌄ ] entries` menggunakan `<x-select.native wire:model.live="quantity" :options="[10, 25, 50, 100]" />`.
    * Kanan: Pengalih mode tampilan menggunakan `<x-button.group>` (`[⊞ Tabel]` dan `[⸬ Grid]`).
  - [ ] Terapkan menu aksi baris melayang: `<x-dropdown icon="ellipsis-vertical" position="bottom-end">` memuat item detail, edit catatan, salin keterangan, dan cetak QR.
  - [ ] Terapkan modal form terstandarisasi: Modal impor massal `<x-modal id="lpk-import-modal">` dengan `<x-upload>` dan modal ubah catatan PIC `<x-modal id="edit-pic-note-modal">`.
  - [ ] Integrasikan visualisasi 5 tahun siklus akreditasi KAN pada halaman detail menggunakan `<x-timeline horizontal color="emerald" style="light">`.
* **Definition of Done (DoD)**: Tampilan visual identik dengan referensi mock-up; filter livewire responsif tanpa lag; interaktivitas modal dan dropdown berjalan mulus; notifikasi aksi terhubung ke `<x-toast>`.

---

### 10.6 Rincian Fase 5: Lapisan Presentasi Modul Kalender Kegiatan (Google Calendar Architecture)

* **Tujuan**: Mengimplementasikan antarmuka kalender multi-event dua kolom terpadu bergaya Google Calendar.
* **Daftar Berkas yang Dibuat**:
  * Livewire Components:
    * `app/Livewire/Calendar/Index.php`: Komponen pengelola state kalender, navigasi periode, dan filter reaktif.
    * `app/Livewire/Forms/CalendarEventForm.php`: Validasi formulir agenda asesmen.
  * Blade Views:
    * `resources/views/livewire/calendar/index.blade.php`: Layout 2-kolom terpadu.
    * `resources/views/livewire/calendar/partials/sidebar.blade.php`: Panel samping kiri (lebar ~280px).
    * `resources/views/livewire/calendar/partials/month-grid.blade.php`: Kisi kalender utama 7-hari.
    * `resources/views/livewire/calendar/partials/event-chip.blade.php`: Chip event horizontal bertumpuk.
    * `resources/views/livewire/calendar/partials/event-modal.blade.php`: Modal penambahan agenda baru.
* **Checklist Teknis**:
  - [ ] Pasang banner peringatan persisten KAN di bagian atas halaman menggunakan `<x-alert>` merah dengan tombol langsung tinjau LPK jatuh tempo.
  - [ ] Bangun panel kontrol samping kiri (lebar ~280px):
    * Tombol "+ Tambah Agenda Baru" membuka dialog modal `<x-modal id="create-calendar-event">` memuat input tanggal `<x-date>` dan waktu `<x-time>`.
    * Mini Month Picker inline menggunakan `<x-calendar wire:model.live="selectedCalendarDate" helpers />` yang merespon klik tanggal secara instan.
    * Filter kategori kalender reaktif: Checkbox dengan dot warna legend (Pelaksanaan, Batas TP, Reminder, Jatuh Tempo, Agenda Internal).
    * Filter Lembaga (LPK) menggunakan `<x-select.styled>` live search.
    * Kartu profil PIC dan versi sistem di bagian bawah sidebar.
  - [ ] Bangun panel kalender utama:
    * Header bilah navigasi: Tombol Hari Ini, tombol panah bulan/periode `<x-button.circle>`, dropdown cepat bulan dan tahun `<x-select.native>`, serta segmented button group 4 mode tampilan (`[ Bulan | Minggu | Hari | Agenda ]` via `<x-button.group>`).
    * Grid tampilan bulan: Header 7 kolom hari (`Senin` s.d. `Minggu`), sel tanggal proporsional dengan penanda nomor hari ini dalam lingkaran primer.
    * Event chips horizontal kompak bertumpuk di dalam tanggal dengan warna tematik kategori.
    * Pola deep-linking URL: `/calendar?view=month&date=YYYY-MM-DD&highlight={event_id}&selected=1` dengan resolusi otomatis ke bulan event terkait.
* **Definition of Done (DoD)**: Navigasi kalender dua arah antara mini calendar sidebar dan grid utama berjalan sinkron; popover event chip informatif; deep linking URL bekerja presisi.

---

### 10.7 Rincian Fase 6: Pengujian Otomatis Pest, Linting & Deployment

* **Tujuan**: Memastikan keandalan end-to-end, kualitas kode standar industri, dan kesiapan publikasi produksi internal unit kerja.
* **Daftar Berkas yang Dibuat/Dikonfigurasi**:
  * Automated Test Suite (Pest PHP):
    * `tests/Unit/SurveillanceEngineTest.php`: Uji komputasi siklus bulan 14, 15, 35, 36, 59.
    * `tests/Unit/TpDeadlineTest.php`: Uji batas 2 vs 3 bulan dan aturan kelayakan perpanjangan.
    * `tests/Feature/LpkManagementTest.php`: Uji CRUD LPK, otorisasi RBAC (Admin vs PIC), dan impor CSV/XLSX.
    * `tests/Feature/AssessmentWorkflowTest.php`: Uji siklus hidup penerbitan asesmen hingga EHA.
    * `tests/Feature/CalendarDeepLinkTest.php`: Uji parameter highlight dan navigasi URL kalender.
  * Konfigurasi Lingkungan & Deployment:
    * `Dockerfile` dan `docker-compose.yml` (port binding `8002:8000`).
    * Konfigurasi Cloudflare Tunnel (`cloudflared`) mengarah ke target `http://127.0.0.1:8002`.
* **Checklist Teknis**:
  - [ ] Jalankan pengujian menyeluruh: `vendor/bin/pest` dengan target cakupan kode di atas 90%.
  - [ ] Jalankan audit kebersihan kode: Hapus komentar AI slop sesuai aturan antislop-code.
  - [ ] Jalankan formatting kode otomatis menggunakan Laravel Pint: `vendor/bin/pint`.
  - [ ] Bangun dan jalankan kontainer Docker `mercy-app`, verifikasi akses melalui domain publik terenkripsi `https://mercy2.drzzy.my.id`.
* **Definition of Done (DoD)**: 100% tes Pest hijau; 0 masalah formatting pada Pint; aplikasi live, cepat (<150ms render), dan terproteksi di balik Cloudflare Tunnel.
