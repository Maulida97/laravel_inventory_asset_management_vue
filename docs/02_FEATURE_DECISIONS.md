# FEATURE DECISIONS
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18
> **Sumber**: Phase 01 Business Brainstorming

---

## PANDUAN MEMBACA DOKUMEN INI

Dokumen ini mencatat semua **keputusan fitur** yang dibuat selama brainstorming, beserta alasan dan dampak teknisnya. Setiap keputusan bersifat **FINAL** kecuali ada revisi eksplisit.

---

## DOMAIN 1: LOCATION

| ID | Keputusan | Alasan |
|---|---|---|
| FD-L01 | Hierarki 2 level (parent → child) | Cukup untuk struktur lokasi perusahaan, tidak over-engineered |
| FD-L02 | Parent bisa digunakan langsung jika belum punya child | Fleksibilitas di awal setup sebelum struktur lokasi lengkap |
| FD-L03 | Warning saat child ditambahkan ke parent berisi barang | Transparansi tanpa memblokir operasional Admin |
| FD-L04 | Lokasi tidak bisa dinonaktifkan jika masih ada barang/aset | Data integrity — tidak ada stok/aset yang menggantung |
| FD-L05 | Lokasi tidak dapat dihapus, hanya dinonaktifkan | Audit trail — histori transaksi yang merujuk lokasi tetap valid |
| FD-L06 | Child bisa pindah parent hanya jika lokasi kosong | Mencegah data tidak konsisten saat perpindahan |
| FD-L07 | Super Admin dan Admin dapat mengelola lokasi | Delegasi operasional ke Admin, tidak harus Super Admin saja |

---

## DOMAIN 2: ITEM / PRODUCT

| ID | Keputusan | Alasan |
|---|---|---|
| FD-I01 | Satu master item, bisa inventory, aset, atau keduanya | Menghindari duplikasi data barang di dua tabel berbeda |
| FD-I02 | Kategori flat (satu level) | Cukup untuk kebutuhan, tidak perlu kompleksitas hierarki kategori |
| FD-I03 | Ada satuan dengan konversi satu arah | Menyesuaikan cara perusahaan menerima (besar) dan mengeluarkan (kecil) barang |
| FD-I04 | Item code auto-generated | Konsistensi kode, tidak bergantung input manual |
| FD-I05 | Item hanya bisa dinonaktifkan jika stok 0 dan tidak ada aset aktif | Data integrity — item yang digunakan tidak bisa "dihilangkan" |
| FD-I06 | Atribut: brand, deskripsi, minimum stok, foto, spesifikasi | Informasi yang cukup untuk identifikasi dan manajemen barang |
| FD-I07 | Spesifikasi disimpan sebagai key-value di tabel terpisah | Memungkinkan filter dan pencarian berdasarkan spesifikasi |
| FD-I08 | 1 foto per item, simpan lokal | Cukup untuk dokumentasi visual, implementasi sederhana |

---

## DOMAIN 3: INVENTORY

| ID | Keputusan | Alasan |
|---|---|---|
| FD-IN01 | Stock In via PO + Goods Receipt (partial allowed) | Alur formal pembelian dengan fleksibilitas penerimaan bertahap |
| FD-IN02 | Stock In via Direct Receipt | Untuk pembelian kecil/mendesak tanpa PO |
| FD-IN03 | Stock Out: Internal Usage, Stock Request, Transfer, Disposal | Mencakup semua skenario pengeluaran barang yang ada |
| FD-IN04 | Tidak ada retur ke supplier | Tidak ada kebutuhan bisnis tersebut saat ini |
| FD-IN05 | Stok tidak boleh negatif | Mencegah data tidak realistis, validasi di sistem |
| FD-IN06 | Stock Adjustment dengan alasan wajib, tanpa approval | Koreksi cepat dengan accountability melalui catatan alasan |
| FD-IN07 | Stock Opname per child location | Granular dan praktis untuk operasional harian |
| FD-IN08 | Transaksi diblokir selama opname berlangsung | Akurasi data opname terjaga |
| FD-IN09 | Full event-based stock ledger | Audit trail penuh, tidak ada data stok yang "hilang" |

---

## DOMAIN 4: ASSET

| ID | Keputusan | Alasan |
|---|---|---|
| FD-A01 | 10 atribut identitas aset (lengkap) | Informasi komprehensif untuk tracking dan audit aset |
| FD-A02 | 5 status aset: Active, Inactive, In Maintenance, Disposed, Lost | Mencakup semua kondisi operasional aset yang relevan |
| FD-A03 | 5 kondisi fisik aset | Gradasi kondisi yang jelas untuk keputusan maintenance/disposal |
| FD-A04 | Assignment aset ke karyawan wajib | Akuntabilitas jelas — setiap aset punya penanggung jawab |
| FD-A05 | Perpindahan aset via dokumen mutasi formal | Jejak lengkap setiap perpindahan, bukan sekadar update field |
| FD-A06 | Return aset via dokumen return formal | Kondisi saat return tercatat, bisa deteksi kerusakan selama pemakaian |
| FD-A07 | Maintenance dicatat lengkap (7 atribut) | Riwayat dan biaya maintenance per aset untuk keputusan disposal |
| FD-A08 | Disposal via dokumen formal (7 atribut) | Penghapusan aset yang dapat diaudit, ada jejak alasan dan metode |

---

## DOMAIN 5: USER & ROLE

| ID | Keputusan | Alasan |
|---|---|---|
| FD-U01 | 6 role: Super Admin, Admin, Inventory Staff, Asset Staff, Requester, Manager | Sesuai struktur tanggung jawab yang ada di perusahaan |
| FD-U02 | Satu user bisa memiliki lebih dari satu role | Fleksibilitas untuk karyawan dengan tanggung jawab ganda |
| FD-U03 | User = karyawan (satu entitas, bukan dua tabel terpisah) | Simplisitas — semua karyawan yang relevan punya akun |
| FD-U04 | Karyawan pemegang aset wajib punya akun | Tidak ada aset yang ter-assign ke entitas tanpa akun sistem |
| FD-U05 | User nonaktif + aset: warning, return manual | Offboarding cepat tanpa mengorbankan akurasi data aset |
| FD-U06 | Departemen sebagai master data | Konsistensi data, laporan per departemen yang akurat |

---

## DOMAIN 6: APPROVAL

| ID | Keputusan | Alasan |
|---|---|---|
| FD-AP01 | Semua 10 jenis transaksi butuh approval | Kontrol penuh atas semua perubahan inventory dan aset |
| FD-AP02 | Default 2 level (Admin → Manager) | Admin melakukan review teknis, Manager keputusan final |
| FD-AP03 | Level configurable oleh Super Admin | Fleksibilitas sesuai kebijakan perusahaan per jenis transaksi |
| FD-AP04 | Jika ditolak: revisi dan ajukan ulang dari Level 1 | User-friendly, tidak perlu input ulang dari awal |
| FD-AP05 | Cancel sebelum ada action dari approver | Pembuat punya kendali atas pengajuan yang belum diproses |

---

## DOMAIN 7: REPORTING

| ID | Keputusan | Alasan |
|---|---|---|
| FD-R01 | 9 laporan inventory | Mencakup semua kebutuhan monitoring stok dan transaksi |
| FD-R02 | 10 laporan aset | Mencakup semua kebutuhan monitoring aset dan lifecycle-nya |
| FD-R03 | Export: PDF, Excel, CSV | Fleksibilitas penggunaan laporan untuk berbagai kebutuhan |

---

## DOMAIN 8: AUDIT LOG

| ID | Keputusan | Alasan |
|---|---|---|
| FD-AL01 | 9 kategori aktivitas direkam | Cakupan audit yang komprehensif |
| FD-AL02 | Retensi permanen | Tidak ada batas kedalaman audit trail |
| FD-AL03 | INSERT ONLY | Integritas log — tidak ada manipulasi histori |

---

## DOMAIN 9: API

| ID | Keputusan | Alasan |
|---|---|---|
| FD-API01 | Tidak ada API publik | Tidak ada kebutuhan integrasi eksternal saat ini |
| FD-API02 | Session-based authentication | Web app biasa, tidak perlu token-based auth |

**Status**: NOT_APPLICABLE

---

## DOMAIN 10: TECH STACK

| ID | Keputusan | Alasan |
|---|---|---|
| FD-T01 | Laravel 13 + PHP 8.4 | Versi terbaru, fitur modern, dukungan jangka panjang |
| FD-T02 | MySQL 8.0 | Mature, familiar, ekosistem luas, bawaan Laravel |
| FD-T03 | Docker (6 services) | Environment konsisten, tidak bergantung pada host OS |
| FD-T04 | Redis (cache, session, queue, rate limiting) | Performa lebih baik dari database-based session/cache |
| FD-T05 | Inertia.js + Vue 3 | SPA-like experience tanpa API, sambil belajar Vue |
| FD-T06 | Tailwind CSS + shadcn-vue | Highly customizable, DataTable via TanStack Table |
| FD-T07 | Pest PHP | Testing framework modern, sintaks ekspresif |
| FD-T08 | OPcache aktif di development | Performa lebih baik, aman dengan validate_timestamps=1 |
| FD-T09 | Spatie Laravel Permission | Battle-tested RBAC, tidak perlu buat dari nol |
| FD-T10 | Laravel Telescope + Manual debugging | Visibilitas lengkap + pemahaman teknis mendalam |
| FD-T11 | Gitflow + GitHub | Structured branching untuk development yang terorganisir |

---

## FITUR YANG TIDAK DIIMPLEMENTASI (NOT_APPLICABLE)

| Fitur | Alasan Tidak Diimplementasi |
|---|---|
| REST API publik | Tidak ada kebutuhan integrasi eksternal |
| Sanctum token auth | Tidak ada API yang perlu diproteksi dengan token |
| Multi-tenant | Single company application |
| Retur ke supplier | Tidak ada kebutuhan bisnis tersebut |
| Aplikasi mobile | Bukan dalam scope |
| Barcode/QR scanning | Bisa ditambahkan di masa mendatang |
| Deployment production | Bukan dalam scope (hanya development/local) |
| Sentry error monitoring | Bukan dalam scope |
| DigitalOcean/VPS setup | Bukan dalam scope |
