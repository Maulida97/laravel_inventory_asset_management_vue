# UI/UX GUIDELINES & INTERACTION SPECIFICATION
**Inventory & Asset Management System**  
*Document Version: 1.0.0 | Phase: 08 - UI/UX Guidelines | Date: 2026-09-18*

---

## 1. Global Layout Architecture

Aplikasi menggunakan pola **Enterprise Dashboard Layout** yang kokoh, responsif, dan ergonomis bagi staf gudang dan manajer.

```
+-------------------------------------------------------------------------------+
| TOP NAVBAR: [Toggle Sidebar] [Breadcrumbs]      [Search Ctrl+K] [Bell] [User] |
+---------------+---------------------------------------------------------------+
| SIDEBAR       | MAIN CONTENT AREA                                             |
| (260px / 64px)| +-----------------------------------------------------------+ |
|               | | PAGE HEADER: Title, Description       [Action Buttons]    | |
| - Dashboard   | +-----------------------------------------------------------+ |
| - Inventory v | | KPI / STAT CARDS (4-column grid)                          | |
|   - Items     | +-----------------------------------------------------------+ |
|   - Ledger    | | DATA TABLE / MAIN WORKSPACE CONTAINER                     | |
|   - Transfers | | [Search & Filter Bar]                       [Export / Add]| |
| - Assets v    | | [Table: TanStack Table with server-side pagination]       | |
|   - Register  | |                                                           | |
|   - Maint.    | | [Pagination Bar: Rows per page, page numbers]             | |
| - Approvals(3)| +-----------------------------------------------------------+ |
| - Reports     |                                                               |
| - Settings    |                                                               |
+---------------+---------------------------------------------------------------+
```

### 1.1 Sidebar Navigation (`Sidebar.vue`)
- **Lebar**: `w-64` (260px) saat diperluas, `w-16` (64px) saat diciutkan (collapsed).
- **Pengelompokan Menu**:
  1. *Utama*: Dashboard
  2. *Manajemen Stok*: Item Master, Ledger Mutasi, Stok Masuk, Stok Keluar, Penyesuaian, Opname
  3. *Manajemen Aset*: Register Aset, Serah Terima (Check In/Out), Pemeliharaan, Pelepasan (Disposal)
  4. *Alur Kerja*: Inbox Approval (dengan badge jumlah menunggu warna oranye/merah jika ada antrean)
  5. *Wawasan*: Laporan Inventaris, Laporan Aset, Audit Trail
  6. *Administrasi*: Manajemen Pengguna, Departemen & Lokasi, Pengaturan Sistem
- **State Preservation**: Status sidebar tersimpan di `localStorage` agar tidak reset saat navigasi.

### 1.2 Top Navigation Bar (`Navbar.vue`)
- **Tinggi**: `h-14` (56px), border bawah `border-b border-border`, background blur `backdrop-blur-md bg-background/80`.
- **Elemen Wajib**:
  - Tombol toggle sidebar hamburger.
  - Hierarki Breadcrumb navigasi dinamis (`Inventory > Items > ITM-2026-0001`).
  - Shortcut Pencarian Cepat Global (`Ctrl + K` / `Cmd + K`).
  - Notifikasi Bell dengan badge counter real-time (alert stok menipis, tugas approval baru).
  - Profil Pengguna: Avatar inisial, nama pegawai, badge role, tombol dark/light mode toggle, dan Logout.

---

## 2. Data Table Specification (TanStack Table + shadcn-vue)

Data tabular adalah tulang punggung operasional sistem ini. Seluruh tabel master dan transaksi wajib mematuhi standar berikut:

### 2.1 Toolbar & Filter Bar
1. **Search Bar Debounced**: Input pencarian dengan icon kaca pembesar. Debounce waktu tunggu **300ms** sebelum memicu request Inertia dengan opsi `{ preserveState: true, replace: true }`.
2. **Faceted Multi-Filters**: Dropdown filter untuk Kategori, Lokasi, dan Status menggunakan checkbox list interaktif.
3. **Date Range Picker**: Untuk tabel Ledger dan Transaksi, filter tanggal fleksibel (Hari Ini, 7 Hari Terakhir, Bulan Ini, Custom Range).
4. **Column Visibility Toggler**: Tombol dropdown untuk menyembunyikan/menampilkan kolom sesuai preferensi user.
5. **Reset Filter Button**: Tombol silang untuk membersihkan semua filter dalam 1 klik jika ada filter aktif.

### 2.2 Table Interaction Standards
- **Sorting**: Kolom yang dapat diurutkan memiliki icon panah indikator (Ascending, Descending, Inactive). Klik kolom mengubah urutan secara instan.
- **Row Hover & Selection**: Baris tabel berubah warna subtle saat cursor berada di atasnya (`hover:bg-muted/50`). Baris yang dipilih (bulk selection) diberi penanda latar biru lembut.
- **Loading State**: Menampilkan minimal 5 baris **Skeleton Shimmer Loader** saat data sedang diambil, bukan spinner kosong.
- **Empty State**: Jika data nihil, tampilkan ilustrasi atau icon folder kosong, teks deskripsi bersahabat, dan tombol aksi "Tambah Data Baru".

### 2.3 Pagination Footer
- Menampilkan info kuantitas data: `"Menampilkan 1-25 dari 1.420 data"`.
- Pemilih jumlah per halaman: `[10, 25, 50, 100]` baris per halaman.
- Tombol navigasi halaman: `First`, `Previous`, `Page Number Buttons`, `Next`, `Last`.

---

## 3. Forms & Data Entry Guidelines

### 3.1 Layout & Grid
- Form standar menggunakan sistem **2-Column Responsive Grid** pada layar desktop (`grid grid-cols-1 md:grid-cols-2 gap-6`), dan jatuh ke 1 kolom pada layar ponsel/tablet.
- Bagian form yang kompleks dibagi menggunakan visual **Card Section** (misal: "Informasi Umum", "Spesifikasi Finansial", "Pengaturan Lokasi").

### 3.2 Label & Input Field Rules
- Setiap input wajib memiliki `<label>` yang jelas dengan tanda bintang merah `*` untuk kolom yang wajib diisi (required).
- Input angka/uang wajib diformat otomatis (contoh: pemisah ribuan titik pada rupiah, prefix `Rp`).
- Input serial number dan kode aset wajib otomatis diubah menjadi huruf besar (`uppercase`) dan bebas spasi awal/akhir (`trim`).

### 3.3 Validation & Error Presentation
- Validasi sisi client berjalan instan untuk format dasar.
- Pesan kesalahan dari server (Laravel validation error) ditampilkan langsung di bawah input terkait dengan teks merah `text-destructive text-xs mt-1 font-medium`.
- Input yang bermasalah otomatis mendapatkan outline merah `border-destructive`.
- Saat submit ditekan, tombol utama beralih ke kondisi **Loading / Disabled** dengan spinner animasi untuk mencegah double-submit:
  ```html
  <Button :disabled="form.processing">
    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
    Simpan Data
  </Button>
  ```

---

## 4. Modal Dialogs, Drawers & Feedback

### 4.1 Destructive Action Dialog (`ConfirmDialog.vue`)
Untuk aksi berisiko tinggi seperti **Disposal Aset**, **Penolakan Pengajuan**, atau **Nonaktifkan Lokasi/User**:
- Gunakan Modal Dialog bertema Destructive (Header merah / icon peringatan `AlertTriangle`).
- Wajib menyertakan penjelasan dampak aksi.
- Untuk penolakan (Reject), sediakan input wajib `textarea` alasan penolakan.
- Tombol konfirmasi berwarna merah `bg-destructive` dengan teks tegas (misal: `"Ya, Lakukan Disposal"` alih-alih `"OK"`).

### 4.2 Quick Inspection Drawer / Sheet (`Sheet.vue`)
- Mengklik baris aset atau item di tabel dapat membuka **Slide-over Drawer dari sisi kanan** (lebar 450px - 600px).
- Menampilkan foto aset, status, barcode/QR code, dan riwayat pemegang terakhir tanpa harus meninggalkan halaman daftar tabel.

### 4.3 Toast Notification System
- Notifikasi mengambang (Floating Toast) menggunakan **Sonner / shadcn toast** di pojok kanan atas (`top-right`).
- **Success Toast**: Latar putih/gelap dengan aksen hijau, pesan ringkas (contoh: *"Stok barang berhasil disesuaikan"*), durasi 3 detik.
- **Error Toast**: Aksen merah tegas, tetap tampil sampai ditutup manual jika menyangkut kegagalan transaksi penting.

---

## 5. Responsive Behavior & Accessibility (a11y)

### 5.1 Breakpoint Strategy
- **Mobile (< 768px)**:
  - Sidebar disembunyikan sepenuhnya di balik overlay drawer.
  - Tabel dibungkus dalam kontainer `overflow-x-auto` dengan scroll horizontal halus.
  - Kartu KPI statistik bertumpuk menjadi 1 kolom vertikal.
- **Tablet (768px - 1024px)**:
  - Sidebar dalam mode icon-only (collapsed 64px).
  - Grid form 2 kolom.
- **Desktop (>= 1024px)**:
  - Full workspace dengan sidebar 260px terbuka.

### 5.2 Accessibility Standard (WCAG 2.1 AA)
- Rasio kontras teks terhadap latar belakang minimal 4.5:1 untuk teks normal dan 3:1 untuk teks besar.
- Semua elemen interaktif dapat diakses melalui tombol keyboard (`Tab`, `Enter`, `Esc` untuk menutup modal dialog).
- Atribut ARIA terpasang pada modal (`role="dialog"`), dropdown (`aria-expanded`), dan tooltip.

---
*Phase 08 UI/UX Specifications Complete.*
