# ADR-002: Event-Based Immutable Stock Ledger

- **Status**: Accepted
- **Deciders**: Software Architect, Domain Experts
- **Date**: 2026-09-18
- **Technical Story**: Strategy for tracking stock balances, warehouse movements, and preventing phantom discrepancies.

---

## Context and Problem Statement

Sistem manajemen inventaris tradisional sering menyimpan kolom `current_stock` langsung pada tabel master `items`. Praktik ini rentan terhadap *race condition*, ketidaksesuaian saldo saat pembatalan transaksi, dan ketidakmampuan untuk mengaudit mengapa suatu stok berkurang pada tanggal tertentu. Diperlukan arsitektur pencatatan stok yang tahan uji audit finansial dan anti-tampering.

## Decision Drivers

- Auditabilitas mutlak: setiap pergerakan barang (masuk, keluar, transfer, penyesuaian, opname) harus dapat ditelusuri ke dokumen asalnya.
- Pencegahan modifikasi historis: catatan mutasi masa lalu tidak boleh bisa diubah (`UPDATE`) atau dihapus (`DELETE`).
- Konsistensi matematis: saldo stok di lokasi mana pun adalah hasil kalkulasi agregat `SUM(quantity)`.

## Considered Options

1. **Option A**: Kolom `stock` mutabel pada tabel `items` / `item_locations` yang di-update dengan increment/decrement.
2. **Option B**: Full Event-Based Ledger (`stock_ledger`) bersifat INSERT-ONLY, di mana saldo stok adalah `SUM(quantity)` dari baris ledger per item per lokasi.
3. **Option C**: Hybrid: Tabel ledger transaksi ditambah kolom snapshot saldo di tabel master yang di-update via trigger database.

## Decision Outcome

**Chosen Option**: **Option B (Full Event-Based Immutable Stock Ledger)**.

Seluruh transaksi stok HANYA menulis baris baru ke tabel `stock_ledger`. Kolom `quantity` bertanda positif (`+`) untuk barang masuk dan negatif (`-`) untuk barang keluar. Tidak ada operasi `UPDATE` atau `DELETE` pada tabel ledger.

### Consequences

- **Good**: 100% audit trail compliance. Tidak ada data stok yang hilang tanpa jejak historis.
- **Good**: Rollback transaksi salah dilakukan dengan membukukan jurnal pembalik (reversing entry), bukan mengedit baris historis.
- **Good**: Integritas data terjamin pada level database locking (pessimistic locking via `lockForUpdate()`).
- **Bad**: Perhitungan saldo membutuhkan agregasi `SUM()`. Dimitigasi dengan composite index `(item_id, location_id, created_at)` dan Redis caching ber-TTL 60 detik.
