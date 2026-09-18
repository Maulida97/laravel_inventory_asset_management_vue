# USER FLOW
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18

---

## 1. AUTHENTICATION FLOW

```
User membuka aplikasi
    ↓
Halaman Login
    ↓
Input email + password
    ↓
Validasi kredensial
    ├── GAGAL → Tampilkan error, catat di audit log (failed login)
    └── BERHASIL → Catat di audit log (login) → Redirect ke Dashboard
    
Logout
    ↓
Hapus session → Catat audit log → Redirect ke Login
```

---

## 2. LOCATION MANAGEMENT FLOW

### 2.1 Buat Lokasi Parent
```
Admin/Super Admin → Menu Location → Buat Lokasi
    ↓
Pilih: Parent Location
    ↓
Isi: Nama Lokasi
    ↓
Simpan → Lokasi parent aktif dibuat
```

### 2.2 Buat Lokasi Child
```
Admin/Super Admin → Menu Location → Buat Lokasi
    ↓
Pilih: Child Location → Pilih Parent
    ↓
[SISTEM CEK] Apakah parent punya barang/aset?
    ├── YA → Tampilkan warning "Parent ini masih memiliki barang"
    │         Admin konfirmasi lanjut → Child dibuat
    │         Parent tidak lagi bisa terima transaksi baru
    └── TIDAK → Child dibuat langsung
```

### 2.3 Nonaktifkan Lokasi
```
Admin/Super Admin → Pilih Lokasi → Nonaktifkan
    ↓
[SISTEM CEK] Ada barang/aset aktif di lokasi?
    ├── YA → ERROR: "Lokasi masih memiliki barang/aset. Kosongkan dulu."
    └── TIDAK → Lokasi dinonaktifkan (is_active = false)
```

---

## 3. INVENTORY FLOW

### 3.1 Purchase Order → Goods Receipt Flow
```
Inventory Staff → Buat Purchase Order
    ↓
Isi: Supplier, Item list (qty, unit), tanggal, notes
    ↓
Submit → Status: DRAFT
    ↓
[APPROVAL] Submit ke approval → Status: PENDING
    ↓
Admin (Level 1) → Review → Approve/Reject
    ├── REJECT → Pembuat notifikasi → Bisa revisi & resubmit
    └── APPROVE → Lanjut ke Level 2
    ↓
Manager (Level 2) → Review → Approve/Reject
    ├── REJECT → Pembuat notifikasi → Bisa revisi & resubmit
    └── APPROVE → PO Status: CONFIRMED → Stok BELUM berubah
    
... Barang tiba ...

Inventory Staff → Buat Goods Receipt untuk PO yang confirmed
    ↓
Pilih PO → Isi qty yang diterima per item (boleh partial)
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
GR Approved → Stok bertambah di lokasi tujuan
    ↓
[SISTEM UPDATE] PO.qty_received += qty_received
    ├── qty_received < qty_ordered → PO Status: PARTIAL
    └── qty_received = qty_ordered → PO Status: COMPLETED
```

### 3.2 Direct Receipt Flow
```
Inventory Staff → Buat Direct Receipt
    ↓
Isi: Item, Qty (dalam purchase unit), lokasi tujuan, tanggal, notes
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
Approved → Stok bertambah (qty × conversion_factor dalam base unit)
```

### 3.3 Stock Request Flow
```
Requester → Buat Stock Request
    ↓
Pilih Item, Isi Qty (dalam base unit), Lokasi sumber, Alasan
    ↓
[SISTEM VALIDASI] Cek stok tersedia di lokasi
    ├── TIDAK CUKUP → ERROR: "Stok tidak mencukupi"
    └── CUKUP → Submit → [APPROVAL]
    ↓
Admin (Level 1) → Review → Approve/Reject
    ↓
Manager (Level 2) → Approve/Reject
    ↓
APPROVED → Stok berkurang → Barang diserahkan ke Requester
```

### 3.4 Stock Opname Flow
```
Admin → Buat Sesi Opname → Pilih Lokasi (child)
    ↓
Status: IN_PROGRESS
    ↓
[SISTEM] Blokir semua transaksi di lokasi ini
    ↓
[SISTEM] Ambil snapshot qty_system per item di lokasi
    ↓
Inventory Staff → Input qty_physical per item
    ↓
[SISTEM] Hitung selisih: difference = qty_physical - qty_system
    ↓
Admin → Review selisih → Konfirmasi / Cancel
    ├── CANCEL → Status: CANCELLED → Lokasi terbuka kembali
    └── KONFIRMASI → 
          Untuk setiap item dengan difference ≠ 0:
          Catat ke stock_ledger (type: opname_adjustment)
          Status: COMPLETED → Lokasi terbuka kembali
```

---

## 4. ASSET FLOW

### 4.1 Asset Registration Flow
```
Asset Staff → Registrasi Aset Baru
    ↓
Pilih Item (yang can_be_asset = true)
Isi semua atribut identitas aset
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
APPROVED → Aset dibuat (status: Active, kondisi: Good)
    ↓
[LANGKAH BERIKUTNYA] Assign ke karyawan
```

### 4.2 Asset Assignment Flow
```
Asset Staff → Pilih Aset → Assign
    ↓
[SISTEM VALIDASI]
    ├── Aset sudah di-assign? → ERROR: "Return dulu aset dari pemegang sekarang"
    ├── Aset In Maintenance? → ERROR: "Aset sedang dalam maintenance"
    └── Valid → Lanjut
    ↓
Pilih User (karyawan), Isi tanggal assign, notes
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
APPROVED → assets.assigned_to = user_id
             Catat di asset_assignments
```

### 4.3 Asset Return Flow
```
Asset Staff → Pilih Aset → Return
    ↓
Isi: Tanggal return, Kondisi saat return, Catatan, Penerima
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
APPROVED →
    asset_assignments.returned_at = return_date
    assets.assigned_to = null
    assets.condition = condition_on_return
    Catat di asset_returns
```

### 4.4 Asset Mutation Flow
```
Asset Staff → Pilih Aset → Mutasi
    ↓
[SISTEM VALIDASI]
    ├── Aset In Maintenance? → ERROR
    └── Valid → Lanjut
    ↓
Isi: Lokasi tujuan, Tanggal mutasi, Alasan, Notes
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
APPROVED →
    assets.location_id = to_location_id
    Catat di asset_mutations
```

### 4.5 Asset Maintenance Flow
```
Asset Staff → Pilih Aset → Buat Maintenance
    ↓
Isi: Tanggal mulai, Deskripsi, Vendor, Notes awal
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
APPROVED →
    assets.status = 'in_maintenance'
    Catat di asset_maintenances (end_date masih null)
    
... Maintenance selesai ...

Asset Staff → Update Maintenance → Isi end_date, kondisi after, biaya, notes
    ↓
assets.status = 'active'
assets.condition = condition_after
```

### 4.6 Asset Disposal Flow
```
Asset Staff → Pilih Aset → Disposal
    ↓
[SISTEM VALIDASI]
    ├── Aset masih di-assign? → ERROR: "Return aset dulu"
    └── Valid → Lanjut
    ↓
Isi semua 7 atribut disposal
    ↓
Submit → [APPROVAL] → Admin → Manager → Approve
    ↓
APPROVED →
    assets.status = 'disposed' (PERMANEN)
    Catat di asset_disposals
```

---

## 5. APPROVAL FLOW (GENERAL)

```
Pembuat → Buat Transaksi → Submit
    ↓
approval_requests dibuat (status: PENDING, level: 1)
    ↓
Admin mendapat notifikasi → Buka Inbox Approval
    ↓
Admin review detail transaksi
    ├── REJECT → Isi rejection_reason → approval_requests.status = REJECTED
    │             Pembuat notifikasi
    │             Pembuat bisa: Edit → Resubmit (mulai dari Level 1 lagi)
    │                           ATAU: Cancel pengajuan
    └── APPROVE → approval_steps Level 1 = APPROVED
                  current_level = 2
                  ↓
                  Manager mendapat notifikasi
                  ↓
                  Manager review
                  ├── REJECT → status = REJECTED → Pembuat notifikasi
                  └── APPROVE → status = APPROVED → Transaksi DIEKSEKUSI

CANCEL (oleh Pembuat):
Hanya bisa jika status = PENDING dan belum ada action dari approver manapun
    ↓
approval_requests.status = CANCELLED
Transaksi tidak dapat dilanjutkan
```

---

## 6. REPORTING FLOW

```
User → Menu Laporan → Pilih Jenis Laporan
    ↓
Isi Parameter/Filter (periode, lokasi, item, dll.)
    ↓
Generate → Tampilkan di layar (table/chart)
    ↓
Export (opsional) → Pilih Format: PDF / Excel / CSV
    ↓
File ter-download
    ↓
Catat di Audit Log: user X mengeksport laporan Y
```

---

## 7. USER MANAGEMENT FLOW

### 7.1 Buat User Baru
```
Super Admin/Admin → Menu Users → Buat User
    ↓
Isi: Nama, Email, Password, Nomor Karyawan, Departemen, Jabatan, dll.
    ↓
Assign Role (bisa lebih dari satu)
    ↓
Simpan → User aktif dibuat → Email welcome (via Mailpit di dev)
```

### 7.2 Nonaktifkan User
```
Super Admin/Admin → Pilih User → Nonaktifkan
    ↓
[SISTEM CEK] User masih pegang aset?
    ├── YA → Tampilkan warning + daftar aset
    │         Admin konfirmasi lanjut → User dinonaktifkan
    │         Aset masih tercatat atas nama user ini
    │         Dashboard menampilkan flag "aset dipegang user nonaktif"
    └── TIDAK → User dinonaktifkan langsung
```
