# MODULES
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18

---

## DAFTAR MODUL

| # | Modul | Kode | Status |
|---|---|---|---|
| 1 | Location Management | MOD-LOC | Planned |
| 2 | Item Master | MOD-ITEM | Planned |
| 3 | Inventory Management | MOD-INV | Planned |
| 4 | Asset Management | MOD-ASSET | Planned |
| 5 | Approval Workflow | MOD-APPR | Planned |
| 6 | User & Role Management | MOD-USER | Planned |
| 7 | Reporting | MOD-RPT | Planned |
| 8 | Audit Log | MOD-AUDIT | Planned |
| 9 | Dashboard | MOD-DASH | Planned |

---

## MOD-LOC — Location Management

### Deskripsi
Modul untuk mengelola lokasi operasional perusahaan dengan struktur hierarki 2 level.

### Sub-fitur
| Fitur | Deskripsi | Role yang Bisa Akses |
|---|---|---|
| Daftar Lokasi | Tampilkan semua lokasi (parent + child) | Semua role |
| Buat Lokasi Parent | Tambah lokasi induk baru | Super Admin, Admin |
| Buat Lokasi Child | Tambah lokasi turunan di bawah parent | Super Admin, Admin |
| Edit Lokasi | Ubah nama dan detail lokasi | Super Admin, Admin |
| Aktifkan/Nonaktifkan Lokasi | Toggle status lokasi | Super Admin, Admin |
| Pindah Child ke Parent Lain | Reassign parent lokasi | Super Admin, Admin |

### Business Rules
- Parent bisa dipakai langsung selama belum punya child
- Warning saat child ditambahkan ke parent berisi barang
- Nonaktifkan hanya jika lokasi kosong
- Tidak bisa dihapus
- Pindah parent hanya jika lokasi kosong

### Dependency
- Digunakan oleh: MOD-INV, MOD-ASSET, MOD-RPT

---

## MOD-ITEM — Item Master

### Deskripsi
Modul untuk mengelola master data semua barang perusahaan (inventory maupun aset).

### Sub-fitur
| Fitur | Deskripsi | Role yang Bisa Akses |
|---|---|---|
| Daftar Item | Tampilkan semua item dengan filter | Semua role |
| Buat Item | Tambah item baru dengan semua atribut | Super Admin, Admin |
| Edit Item | Ubah detail item | Super Admin, Admin |
| Aktifkan/Nonaktifkan Item | Toggle status item | Super Admin, Admin |
| Kelola Kategori | CRUD kategori barang | Super Admin, Admin |
| Kelola Satuan | CRUD satuan barang | Super Admin, Admin |
| Upload Foto Item | Upload/ganti foto item | Super Admin, Admin |
| Kelola Spesifikasi | Tambah/ubah/hapus spesifikasi item | Super Admin, Admin |

### Business Rules
- Item code auto-generated
- Konversi satuan: 1 purchase unit = N base unit
- Nonaktifkan hanya jika stok 0 dan tidak ada aset aktif
- Item bisa dikonfigurasi sebagai inventory, aset, atau keduanya

### Dependency
- Digunakan oleh: MOD-INV, MOD-ASSET, MOD-RPT

---

## MOD-INV — Inventory Management

### Deskripsi
Modul untuk mengelola stok barang: penerimaan, pengeluaran, transfer, adjustment, dan opname.

### Sub-fitur

#### Stock In
| Fitur | Deskripsi | Role |
|---|---|---|
| Buat Purchase Order | Buat PO baru | Admin, Inventory Staff |
| Goods Receipt | Terima barang dari PO (partial allowed) | Admin, Inventory Staff |
| Direct Receipt | Terima barang langsung tanpa PO | Admin, Inventory Staff |

#### Stock Out
| Fitur | Deskripsi | Role |
|---|---|---|
| Internal Usage | Catat pemakaian barang internal | Admin, Inventory Staff |
| Stock Request | Ajukan permintaan barang | Requester, Inventory Staff |
| Transfer Barang | Pindah barang antar lokasi | Admin, Inventory Staff |
| Disposal Barang | Hapus/buang barang tidak layak | Admin, Inventory Staff |

#### Stock Management
| Fitur | Deskripsi | Role |
|---|---|---|
| Stock Adjustment | Koreksi stok dengan alasan | Admin, Inventory Staff |
| Buat Sesi Opname | Mulai stock opname per lokasi | Admin, Inventory Staff |
| Input Qty Fisik | Isi qty fisik per item saat opname | Admin, Inventory Staff |
| Konfirmasi Opname | Finalisasi hasil opname | Admin |
| Lihat Stok Terkini | Cek stok per item per lokasi | Semua role |
| Riwayat Ledger | Lihat histori perubahan stok | Admin, Inventory Staff |

### Business Rules
- Stok tidak boleh negatif
- Transaksi diblokir di lokasi yang sedang opname
- Stock adjustment wajib disertai alasan
- Semua transaksi butuh approval (kecuali view)
- Konversi satuan otomatis saat Stock In

### Dependency
- MOD-LOC (lokasi), MOD-ITEM (item), MOD-APPR (approval), MOD-USER (user)

---

## MOD-ASSET — Asset Management

### Deskripsi
Modul untuk mengelola aset perusahaan sebagai unit individual.

### Sub-fitur

#### Asset Lifecycle
| Fitur | Deskripsi | Role |
|---|---|---|
| Registrasi Aset | Daftarkan aset baru | Admin, Asset Staff |
| Edit Aset | Ubah detail aset | Admin, Asset Staff |
| Lihat Detail Aset | Tampilkan semua info aset | Semua role |
| Daftar Aset | Tampilkan semua aset dengan filter | Semua role |

#### Assignment
| Fitur | Deskripsi | Role |
|---|---|---|
| Assign Aset ke Karyawan | Tugaskan aset ke user | Admin, Asset Staff |
| Return Aset | Proses pengembalian aset | Admin, Asset Staff |
| Riwayat Assignment | Lihat histori pemegang aset | Admin, Asset Staff |

#### Movement
| Fitur | Deskripsi | Role |
|---|---|---|
| Mutasi Aset | Pindah aset antar lokasi | Admin, Asset Staff |
| Riwayat Mutasi | Lihat histori perpindahan aset | Admin, Asset Staff |

#### Maintenance
| Fitur | Deskripsi | Role |
|---|---|---|
| Catat Maintenance | Buat record maintenance baru | Admin, Asset Staff |
| Selesaikan Maintenance | Update status maintenance selesai | Admin, Asset Staff |
| Riwayat Maintenance | Lihat histori maintenance per aset | Admin, Asset Staff |

#### Disposal
| Fitur | Deskripsi | Role |
|---|---|---|
| Disposal Aset | Proses penghapusan aset | Admin, Asset Staff |
| Daftar Disposed | Lihat semua aset yang sudah disposed | Admin, Asset Staff |

### Business Rules
- Setiap aset wajib di-assign ke karyawan
- Satu aset hanya satu pemegang aktif
- Aset In Maintenance tidak bisa di-assign atau dimutasi
- Status Disposed/Lost bersifat permanen
- Return mencatat kondisi saat dikembalikan

### Dependency
- MOD-LOC, MOD-ITEM, MOD-USER, MOD-APPR

---

## MOD-APPR — Approval Workflow

### Deskripsi
Modul untuk mengelola proses persetujuan semua transaksi.

### Sub-fitur
| Fitur | Deskripsi | Role |
|---|---|---|
| Inbox Approval | Daftar transaksi yang menunggu review/approval | Admin (L1), Manager (L2) |
| Review Transaksi | Lihat detail transaksi sebelum bertindak | Admin, Manager |
| Approve Transaksi | Setujui transaksi | Admin (L1), Manager (L2) |
| Reject Transaksi | Tolak transaksi dengan alasan | Admin (L1), Manager (L2) |
| Pending Saya | Daftar pengajuan yang dibuat user login | Semua pembuat |
| Revisi & Ajukan Ulang | Edit dan resubmit transaksi yang ditolak | Pembuat |
| Cancel Pengajuan | Batalkan pengajuan yang belum diproses | Pembuat |
| Konfigurasi Approval | Atur level per jenis transaksi | Super Admin |

### Business Rules
- 10 jenis transaksi wajib approval
- Default 2 level, configurable oleh Super Admin
- Revisi selalu mulai dari Level 1
- Cancel hanya sebelum ada action dari approver

### Dependency
- Digunakan oleh: semua modul transaksi

---

## MOD-USER — User & Role Management

### Deskripsi
Modul untuk mengelola pengguna, role, dan konfigurasi akses.

### Sub-fitur
| Fitur | Deskripsi | Role |
|---|---|---|
| Daftar User | Tampilkan semua user | Super Admin, Admin |
| Buat User | Tambah user baru | Super Admin, Admin |
| Edit User | Ubah data profil user | Super Admin, Admin |
| Assign Role | Berikan/cabut role dari user | Super Admin |
| Aktifkan/Nonaktifkan User | Toggle akses user ke sistem | Super Admin, Admin |
| Kelola Departemen | CRUD master departemen | Super Admin, Admin |
| Profil Saya | Edit profil sendiri | Semua user |
| Ganti Password | Ubah password sendiri | Semua user |

### Business Rules
- Nonaktifkan user: warning jika masih pegang aset
- User dengan aset aktif tidak bisa dihapus
- Multi-role per user diizinkan

### Dependency
- Digunakan oleh: semua modul

---

## MOD-RPT — Reporting

### Deskripsi
Modul untuk menghasilkan laporan inventory dan aset dengan kemampuan export.

### Laporan Inventory
| Laporan | Filter Tersedia |
|---|---|
| Stok Terkini per Lokasi | Lokasi, Item, Kategori |
| Riwayat Mutasi Stok | Periode, Item, Lokasi, Jenis Transaksi |
| Laporan Stock In | Periode, Jenis (PO/Direct), Lokasi |
| Laporan Stock Out | Periode, Jenis, Lokasi, Item |
| Laporan Stock Request | Periode, Status, Requester |
| Laporan Stock Opname | Periode, Lokasi |
| Laporan Stock Adjustment | Periode, Lokasi, User |
| Laporan Stok Minimum | Kategori, Lokasi |
| Laporan Purchase Order | Periode, Status |

### Laporan Aset
| Laporan | Filter Tersedia |
|---|---|
| Daftar Aset Keseluruhan | Status, Kondisi, Lokasi, Kategori |
| Aset per Lokasi | Lokasi, Status |
| Aset per Karyawan | Departemen, User |
| Riwayat Mutasi Aset | Periode, Aset |
| Riwayat Assignment | Periode, Aset, User |
| Laporan Maintenance | Periode, Aset, Vendor |
| Laporan Disposal | Periode, Metode |
| Garansi Akan Berakhir | Periode (dalam N hari ke depan) |
| Aset Berdasarkan Status | Status |
| Aset Berdasarkan Kondisi | Kondisi |

### Export Format
PDF | Excel (.xlsx) | CSV

---

## MOD-AUDIT — Audit Log

### Deskripsi
Modul untuk melihat dan mencari riwayat aktivitas sistem.

### Sub-fitur
| Fitur | Deskripsi | Role |
|---|---|---|
| Daftar Audit Log | Tampilkan semua aktivitas dengan filter | Super Admin, Admin |
| Filter Log | Filter berdasarkan user, event, tanggal, entitas | Super Admin, Admin |
| Detail Log | Lihat old_values dan new_values | Super Admin, Admin |

### Dependency
- Menerima data dari: semua modul

---

## MOD-DASH — Dashboard

### Deskripsi
Halaman ringkasan utama yang menampilkan data penting secara visual.

### Widget
| Widget | Deskripsi | Role |
|---|---|---|
| Total Aset Aktif | Jumlah aset berstatus Active | Semua role |
| Total Item Inventory | Jumlah item yang dikelola sebagai stok | Semua role |
| Stok Minimum Alert | Item yang stoknya di bawah minimum | Admin, Inventory Staff |
| Approval Menunggu | Jumlah transaksi yang perlu diproses | Admin, Manager |
| Aset Garansi Berakhir | Aset dengan garansi hampir habis (30 hari) | Admin, Asset Staff |
| Aset per Status | Grafik distribusi status aset | Admin, Asset Staff, Manager |
| Stok per Lokasi | Ringkasan stok di setiap lokasi | Admin, Inventory Staff |
| Transaksi Terbaru | Daftar transaksi terbaru | Admin, Manager |

### Dependency
- MOD-INV, MOD-ASSET, MOD-APPR
