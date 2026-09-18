# PERMISSION MATRIX
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18

---

## KETERANGAN

| Simbol | Arti |
|---|---|
| ✅ | Boleh / Punya Akses |
| ❌ | Tidak Boleh / Tidak Punya Akses |
| 👁️ | Read Only (hanya lihat) |
| 🔧 | Konfigurasi / Admin Only |

**Role:** SA = Super Admin | ADM = Admin | INV = Inventory Staff | ASSET = Asset Staff | REQ = Requester | MGR = Manager

---

## 1. LOCATION MANAGEMENT

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Lihat daftar lokasi | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Buat lokasi parent | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Buat lokasi child | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit lokasi | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Aktifkan/Nonaktifkan lokasi | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Pindah child ke parent lain | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 2. ITEM MASTER

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Lihat daftar item | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Lihat detail item | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Buat item baru | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit item | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Nonaktifkan item | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Kelola kategori | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Kelola satuan | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 3. INVENTORY — STOCK IN

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Buat Purchase Order | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Submit PO ke approval | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Buat Goods Receipt | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Buat Direct Receipt | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Lihat daftar PO | ✅ | ✅ | ✅ | ❌ | ❌ | 👁️ |
| Lihat daftar GR | ✅ | ✅ | ✅ | ❌ | ❌ | 👁️ |

---

## 4. INVENTORY — STOCK OUT

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Buat Internal Usage | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Buat Stock Request | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Buat Transfer Barang | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Buat Disposal Barang | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Lihat daftar Stock Out | ✅ | ✅ | ✅ | ❌ | 👁️ | 👁️ |

---

## 5. INVENTORY — STOCK MANAGEMENT

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Buat Stock Adjustment | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Buat Sesi Stock Opname | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Input Qty Fisik (Opname) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Konfirmasi/Cancel Opname | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Lihat Stok Terkini | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Lihat Riwayat Ledger | ✅ | ✅ | ✅ | ❌ | ❌ | 👁️ |

---

## 6. ASSET MANAGEMENT

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Lihat daftar aset | ✅ | ✅ | 👁️ | ✅ | ❌ | ✅ |
| Lihat detail aset | ✅ | ✅ | 👁️ | ✅ | ❌ | ✅ |
| Registrasi aset baru | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Edit detail aset | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Assign aset ke karyawan | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Return aset | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Mutasi aset | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Catat Maintenance | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Selesaikan Maintenance | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Disposal aset | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Lihat riwayat assignment | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Lihat riwayat mutasi | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Lihat riwayat maintenance | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |

---

## 7. APPROVAL WORKFLOW

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Lihat inbox approval (Level 1) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Approve/Reject (Level 1) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Lihat inbox approval (Level 2) | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Approve/Reject (Level 2) | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Lihat status pengajuan sendiri | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Cancel pengajuan sendiri | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Revisi & resubmit pengajuan | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Konfigurasi approval level | 🔧 | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 8. USER & ROLE MANAGEMENT

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Lihat daftar user | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Buat user baru | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit data user | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Assign/Cabut role | 🔧 | ❌ | ❌ | ❌ | ❌ | ❌ |
| Aktifkan/Nonaktifkan user | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Kelola departemen | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit profil sendiri | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Ganti password sendiri | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 9. REPORTING

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Laporan Stok Terkini | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Riwayat Mutasi Stok | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Stock In | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Stock Out | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Stock Request | ✅ | ✅ | ✅ | ❌ | 👁️ | ✅ |
| Laporan Stock Opname | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Stock Adjustment | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Stok Minimum | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Purchase Order | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Laporan Aset Keseluruhan | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Laporan Aset per Lokasi | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Laporan Aset per Karyawan | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Laporan Riwayat Mutasi Aset | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Laporan Riwayat Assignment | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Laporan Maintenance | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Laporan Disposal | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Laporan Garansi Berakhir | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Export laporan (PDF/Excel/CSV) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |

---

## 10. AUDIT LOG

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Lihat Audit Log | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Filter & Cari Audit Log | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Lihat Detail Log (old/new values) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 11. DASHBOARD

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Lihat Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Widget: Total Aset Aktif | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Widget: Stok Minimum Alert | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| Widget: Approval Menunggu | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Widget: Garansi Berakhir | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Widget: Transaksi Terbaru | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |

---

## 12. SYSTEM CONFIGURATION (Super Admin Only)

| Permission | SA | ADM | INV | ASSET | REQ | MGR |
|---|---|---|---|---|---|---|
| Konfigurasi Approval Flow | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Akses Laravel Telescope | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Lihat Audit Log Detail | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Assign/Cabut Role User | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## CATATAN IMPLEMENTASI

### Spatie Permission Mapping
Setiap baris dalam tabel di atas akan dipetakan menjadi **permission string** di Spatie Laravel Permission.

Contoh naming convention:
```
location.view          → Lihat daftar lokasi
location.create        → Buat lokasi
location.edit          → Edit lokasi
location.toggle-status → Aktifkan/Nonaktifkan

item.view
item.create
item.edit

inventory.stock-in.create
inventory.stock-out.create
inventory.adjustment.create
inventory.opname.create
inventory.opname.confirm

asset.view
asset.create
asset.assign
asset.return
asset.mutate
asset.maintenance
asset.dispose

approval.review.level-1
approval.approve.level-1
approval.review.level-2
approval.approve.level-2
approval.configure

report.inventory.view
report.asset.view
report.export

audit.view
audit.view-detail

user.view
user.create
user.edit
user.toggle-status
user.assign-role

department.manage
```

### Policy vs Middleware
- **Middleware**: Cek role/permission untuk akses route
- **Policy**: Cek business rule tambahan (contoh: hanya pembuat yang bisa cancel)
