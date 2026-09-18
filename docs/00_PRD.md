# PRD — Product Requirements Document
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18
> **Phase**: 03 — PRD
> **Sumber**: Phase 02 Requirement Consolidation

---

## 1. PRODUCT OVERVIEW

### 1.1 Nama Sistem
**Inventory & Asset Management System**

### 1.2 Deskripsi
Sistem informasi berbasis web untuk mengelola **inventaris barang (stok)** dan **aset perusahaan** secara terpusat di seluruh lokasi operasional perusahaan.

Sistem ini dirancang sebagai **single-company application** — bukan SaaS multi-tenant — yang menyediakan satu sumber data terpercaya untuk seluruh kegiatan pengelolaan barang dan aset perusahaan.

### 1.3 Latar Belakang
Perusahaan memiliki beberapa lokasi operasional (office, gudang, ruangan, dan area lainnya) dengan barang dan aset yang tersebar. Tanpa sistem yang terintegrasi, perusahaan mengalami kesulitan dalam:

- Mengetahui ketersediaan barang di setiap lokasi secara real-time
- Melacak keberadaan dan pemegang aset perusahaan
- Merekam riwayat perpindahan barang dan aset
- Mendeteksi selisih antara data sistem dan kondisi fisik
- Memastikan akuntabilitas atas setiap barang dan aset

### 1.4 Tujuan Produk
1. Menyediakan sistem terpusat untuk manajemen inventory (stok/kuantitas)
2. Menyediakan sistem terpusat untuk manajemen aset (unit individual)
3. Memungkinkan tracking barang dan aset di multi-lokasi
4. Menyediakan riwayat lengkap seluruh transaksi
5. Memastikan akuntabilitas melalui assignment dan approval workflow
6. Menyediakan laporan yang akurat untuk pengambilan keputusan

---

## 2. SCOPE

### 2.1 Dalam Scope
- Manajemen lokasi (multi-lokasi, hierarki 2 level)
- Master data barang (item catalog)
- Manajemen inventory (stok masuk, keluar, transfer, opname)
- Manajemen aset (registrasi, assignment, mutasi, maintenance, disposal)
- Approval workflow untuk semua transaksi
- Manajemen user, role, dan permission
- Laporan inventory dan aset
- Audit log lengkap
- Export laporan (PDF, Excel, CSV)

### 2.2 Luar Scope
- Multi-tenant / SaaS
- API publik / integrasi eksternal
- Manajemen keuangan / akuntansi penuh
- Procurement / pengadaan barang (hanya Purchase Order dasar)
- Manajemen supplier secara lengkap
- Barcode / QR Code scanning (bisa ditambahkan di masa mendatang)
- Aplikasi mobile
- Deployment ke VPS/cloud (hanya development/local)

---

## 3. PENGGUNA SISTEM

### 3.1 Daftar Role

| Role | Deskripsi | Akses Utama |
|---|---|---|
| **Super Admin** | Administrator tertinggi sistem | Semua akses, konfigurasi sistem, kelola approval config |
| **Admin** | Administrator operasional | Kelola data, laporan, user management, review transaksi (Level 1 approver) |
| **Inventory Staff** | Staf pengelola inventory | Transaksi stok (in, out, transfer, opname, adjustment) |
| **Asset Staff** | Staf pengelola aset | Registrasi, mutasi, maintenance, disposal aset |
| **Requester** | Pemohon barang | Mengajukan permintaan barang dari stok |
| **Manager** | Penyetuju transaksi | Approve/reject transaksi (Level 2 approver — final) |

**Catatan**: Satu user dapat memiliki lebih dari satu role.

### 3.2 Persyaratan User
- Setiap pengguna wajib memiliki akun sistem
- Setiap karyawan yang memegang aset wajib memiliki akun sistem
- User memiliki data profil karyawan: nama, nomor karyawan, departemen, jabatan, telepon, foto, tanggal bergabung, status karyawan

---

## 4. MODUL SISTEM

### 4.1 Modul Location Management
Pengelolaan lokasi operasional perusahaan.

**Fitur:**
- CRUD lokasi (nama, parent, status aktif)
- Hierarki 2 level: parent → child
- Parent dapat digunakan sebagai lokasi langsung selama belum punya child
- Validasi: lokasi tidak bisa dinonaktifkan jika masih ada barang/aset
- Lokasi tidak dapat dihapus, hanya dinonaktifkan
- Pindah child ke parent lain (hanya jika lokasi kosong)
- Dikelola oleh Super Admin dan Admin

---

### 4.2 Modul Item Master
Master data semua barang perusahaan.

**Fitur:**
- CRUD item (nama, kode auto-generate, kategori, satuan, merek, deskripsi, foto, minimum stok)
- Satu item dapat dikonfigurasi sebagai: inventory, aset, atau keduanya
- Satuan dengan konversi satu arah (purchase unit → base unit)
- Spesifikasi opsional per item (key-value pairs)
- Nonaktifkan item (hanya jika stok nol dan tidak ada aset aktif)
- CRUD kategori barang (flat, satu level)
- CRUD satuan barang

---

### 4.3 Modul Inventory Management
Pengelolaan stok barang berdasarkan kuantitas.

**4.3.1 Stock In**

| Jenis | Deskripsi |
|---|---|
| Purchase Order → Goods Receipt | Pembelian formal dengan dokumen PO. PO bisa diterima sebagian (partial receipt). PO status: draft → confirmed → partial → completed → cancelled |
| Direct Receipt | Penerimaan langsung tanpa PO untuk pembelian kecil/mendesak |

**4.3.2 Stock Out**

| Jenis | Deskripsi |
|---|---|
| Internal Usage | Pemakaian barang oleh departemen/karyawan |
| Stock Request | Permintaan barang oleh Requester (butuh approval) |
| Transfer | Perpindahan barang antar lokasi (out di asal, in di tujuan) |
| Disposal | Penghapusan barang rusak/tidak layak pakai |

**4.3.3 Stock Adjustment**
- Koreksi selisih stok dengan alasan wajib (tanpa approval)

**4.3.4 Stock Opname**
- Per child location
- Transaksi diblokir selama opname berlangsung
- Flow: buat sesi → input fisik → review selisih → konfirmasi → stok diupdate

**4.3.5 Stock Ledger**
- Full event-based ledger (INSERT ONLY)
- Stok terkini = SUM(quantity) dari ledger per item per lokasi
- Stok negatif tidak diizinkan

---

### 4.4 Modul Asset Management
Pengelolaan aset sebagai unit individual.

**4.4.1 Asset Registration**
- Daftarkan aset baru dengan semua atribut identitas
- Asset code auto-generated (format: AST-XXXXX)
- Status default: Active, Kondisi default: Good

**4.4.2 Asset Assignment**
- Assign aset ke karyawan (satu aset = satu pemegang)
- Histori assignment tersimpan permanen

**4.4.3 Asset Return**
- Formal melalui dokumen return
- Kondisi saat return dicatat
- Setelah return: aset kembali ke pool (tidak ter-assign)

**4.4.4 Asset Mutation**
- Perpindahan aset antar lokasi melalui dokumen mutasi
- Histori mutasi tersimpan permanen

**4.4.5 Asset Maintenance**
- Pencatatan maintenance/perbaikan aset
- Atribut: tanggal mulai/selesai, deskripsi, biaya, vendor, kondisi setelah, notes
- Status aset = In Maintenance selama proses

**4.4.6 Asset Disposal**
- Penghapusan aset melalui dokumen formal
- Status disposed bersifat permanen
- Metode: Dibuang / Dijual / Dihibahkan / Dihancurkan

**Status Aset:**
`Active` | `Inactive` | `In Maintenance` | `Disposed` | `Lost`

**Kondisi Aset:**
`Good` | `Minor Damage` | `Major Damage` | `Needs Repair` | `Scrapped`

---

### 4.5 Modul Approval Workflow
Sistem persetujuan untuk semua transaksi.

**Transaksi yang membutuhkan approval (10):**
Inventory: Purchase Order, Direct Receipt, Stock Request, Stock Disposal, Stock Adjustment
Asset: Asset Registration, Asset Mutation, Asset Return, Asset Maintenance, Asset Disposal

**Default Flow (2 Level):**
```
Pembuat → Admin (Level 1: review) → Manager (Level 2: approve final) → Executed
```

**Konfigurasi:** Super Admin dapat mengubah per transaction type menjadi 1 level.

**Status:** `draft` → `pending` → `approved` / `rejected` / `cancelled`

**Aturan:**
- Jika ditolak: pembuat bisa revisi dan ajukan ulang dari Level 1
- Pembuat bisa cancel selama belum ada action dari approver

---

### 4.6 Modul User & Role Management
Pengelolaan pengguna, role, dan permission.

**Fitur:**
- CRUD user dengan data profil karyawan lengkap
- Assign/cabut role dari user
- Kelola departemen (master data)
- Nonaktifkan user (dengan peringatan jika masih pegang aset)
- Histori aset yang masih dipegang user nonaktif tetap terlacak

---

### 4.7 Modul Reporting
Laporan untuk pengambilan keputusan.

**Laporan Inventory (9):** Stok terkini, riwayat mutasi, stock in, stock out, stock request, stock opname, stock adjustment, stok minimum, purchase order

**Laporan Aset (10):** Daftar aset, per lokasi, per karyawan, riwayat mutasi, riwayat assignment, maintenance, disposal, garansi akan berakhir, per status, per kondisi

**Export:** PDF, Excel (.xlsx), CSV

---

### 4.8 Modul Audit Log
Pencatatan seluruh aktivitas sistem.

**Aktivitas yang direkam (9 kategori):** Login/logout, gagal login, CRUD master data, transaksi inventory, transaksi aset, approval actions, perubahan user, perubahan konfigurasi, export laporan

**Ketentuan:** Permanen (tidak ada penghapusan), INSERT ONLY

---

## 5. BUSINESS RULES

### 5.1 Location Rules
- BR-L01: Lokasi parent bisa menyimpan barang selama belum punya child
- BR-L02: Saat child ditambahkan ke parent berisi barang, sistem memberi warning
- BR-L03: Lokasi tidak bisa dinonaktifkan jika masih ada barang/aset aktif
- BR-L04: Lokasi tidak bisa dihapus, hanya dinonaktifkan (is_active = false)
- BR-L05: Child hanya bisa dipindah ke parent lain jika lokasi kosong

### 5.2 Item Rules
- BR-I01: Item code di-generate otomatis saat item dibuat
- BR-I02: Item tidak bisa dinonaktifkan jika stok > 0 atau masih ada aset aktif
- BR-I03: Konversi satuan satu arah: Stock In dalam purchase unit, Stock Out dalam base unit
- BR-I04: Serial number aset bersifat unique (jika diisi)

### 5.3 Inventory Rules
- BR-IN01: Stok tidak boleh negatif — transaksi ditolak jika qty > stok tersedia
- BR-IN02: Stock adjustment wajib disertai alasan/reason
- BR-IN03: Semua transaksi stok di lokasi yang sedang opname diblokir
- BR-IN04: Stok tersimpan dalam base unit untuk konsistensi kalkulasi
- BR-IN05: PO dapat diterima sebagian (partial receipt)

### 5.4 Asset Rules
- BR-A01: Setiap aset harus di-assign ke satu user (pemegang)
- BR-A02: Satu aset hanya boleh punya satu assignment aktif
- BR-A03: Aset berstatus In Maintenance tidak bisa di-assign atau dimutasi
- BR-A04: Status Disposed dan Lost bersifat permanen
- BR-A05: Return aset menggunakan dokumen formal dengan kondisi saat return
- BR-A06: Disposal aset melalui dokumen formal, status disposed permanen

### 5.5 Approval Rules
- BR-AP01: Semua 10 jenis transaksi wajib melewati approval sebelum dieksekusi
- BR-AP02: Default 2 level, dapat dikonfigurasi menjadi 1 level oleh Super Admin
- BR-AP03: Jika ditolak, pembuat dapat merevisi dan mengajukan ulang dari Level 1
- BR-AP04: Pembuat dapat cancel pengajuan selama belum ada action dari approver
- BR-AP05: Revisi selalu mulai dari Level 1 (tidak bisa skip ke Level 2)

### 5.6 User Rules
- BR-U01: Karyawan yang memegang aset wajib memiliki akun sistem
- BR-U02: User yang dinonaktifkan tetap tercatat sebagai pemegang aset sampai return diproses
- BR-U03: Satu user dapat memiliki lebih dari satu role

---

## 6. TECH STACK

| Komponen | Teknologi |
|---|---|
| Backend Framework | Laravel 13 |
| PHP | 8.4 |
| Database | MySQL 8.0 |
| Frontend | Inertia.js + Vue 3 (Composition API) |
| Build Tool | Vite |
| CSS | Tailwind CSS |
| UI Components | shadcn-vue + TanStack Table |
| Cache/Session/Queue | Redis |
| Container | Docker (PHP-FPM, Nginx, MySQL, Redis, Mailpit, phpMyAdmin) |
| Testing | Pest PHP |
| RBAC | Spatie Laravel Permission |
| OPcache | Aktif (validate_timestamps=1 untuk development) |
| Debugging | Laravel Telescope + Manual methods |
| Version Control | Git + GitHub (Gitflow) |

---

## 7. CONSTRAINTS & ASSUMPTIONS

### 7.1 Constraints
- Sistem hanya untuk satu perusahaan (single-tenant)
- Environment target: development/local (Docker)
- Tidak ada deployment ke VPS/cloud dalam scope ini
- Tidak ada API publik
- Tidak ada aplikasi mobile

### 7.2 Assumptions
- Setiap karyawan pemegang aset memiliki akun sistem
- Departemen bersifat relatif stabil dan dikelola sebagai master data
- Approval Manager menggunakan role "Manager" dalam sistem
- Export laporan dilakukan on-demand (bukan scheduled)

---

## 8. SUCCESS METRICS

| Metrik | Target |
|---|---|
| Semua transaksi inventory tercatat di ledger | 100% |
| Semua aset teridentifikasi dengan pemegang dan lokasi | 100% |
| Semua transaksi melewati approval workflow | 100% |
| Seluruh aktivitas terekam di audit log | 100% |
| Laporan dapat di-export ke PDF/Excel/CSV | ✅ |
| Tidak ada stok negatif di sistem | 100% |
| Data lokasi selalu memiliki integritas referensial | 100% |

---

## 9. DOCUMENT STATUS

| Dokumen | Status |
|---|---|
| 00_PRD.md | ✅ DRAFT |
| 01_PRODUCT_VISION.md | NOT_STARTED |
| 02_FEATURE_DECISIONS.md | NOT_STARTED |
| 03_MODULES.md | NOT_STARTED |
| 04_USER_FLOW.md | NOT_STARTED |
| 05_DATABASE.md | NOT_STARTED |
| 06_API.md | NOT_APPLICABLE |
| 07_PERMISSION_MATRIX.md | NOT_STARTED |
| 08_ROADMAP.md | NOT_STARTED |
| 09_BACKLOG.md | NOT_STARTED |
