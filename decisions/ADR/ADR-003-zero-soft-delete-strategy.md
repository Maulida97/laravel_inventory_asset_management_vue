# ADR-003: Zero Soft-Delete Strategy

- **Status**: Accepted
- **Deciders**: Software Architect, Database Administrator
- **Date**: 2026-09-18
- **Technical Story**: Handling deactivation and record deletion lifecycle across all application models.

---

## Context and Problem Statement

Fitur bawaan `SoftDeletes` pada Laravel menyuntikkan kolom `deleted_at` ke dalam tabel dan secara otomatis memfilter query dengan `WHERE deleted_at IS NULL`. Namun, pada sistem audit, inventaris, dan akuntansi, soft delete sering kali menimbulkan masalah:
1. Kerusakan integritas unik indeks (contoh: tidak bisa membuat item dengan kode yang sama jika item lama di-soft delete, kecuali index diubah menjadi composite dengan `deleted_at`).
2. Foreign key cascade yang tidak terduga atau ambigu ketika relasi anak membaca data induk yang terhapus secara logis.
3. Inkonsistensi data laporan historis: transaksi masa lalu yang merujuk pada entitas terhapus dapat menimbulkan query error atau tampilan kosong (orphan references).

## Decision Drivers

- Integritas data relasional yang ketat (`ON DELETE RESTRICT` pada semua master FK).
- Kejelasan status bisnis: barang/lokasi yang tidak digunakan lagi tidaklah "terhapus", melainkan berstatus "Nonaktif" (`is_active = false`).
- Menghindari kerumitan composite unique index pada MySQL 8.0.

## Considered Options

1. **Option A**: Menggunakan trait bawaan Laravel `SoftDeletes` (`deleted_at` timestamp).
2. **Option B**: Zero Soft-Delete. Menggunakan kolom boolean `is_active` default `true` untuk master data; dan pelarangan hard delete untuk data transaksi/ledger.

## Decision Outcome

**Chosen Option**: **Option B (Zero Soft-Delete Strategy)**.

- Semua tabel master (`items`, `locations`, `departments`, `categories`, `users`) menggunakan kolom boolean `is_active`.
- Menghapus item/lokasi yang memiliki riwayat transaksi dilarang di level aplikasi dan dicegah oleh foreign key `ON DELETE RESTRICT`. Pengguna hanya diizinkan mengubah status menjadi `is_active = false`.
- Entitas transaksional (`stock_ledger`, `audit_logs`, `asset_assignments`) bersifat permanen.

### Consequences

- **Good**: Index unik (`item_code`, `asset_code`, `email`, `location_code`) tetap bersih dan sederhana.
- **Good**: Semua laporan historis tetap dapat menampilkan nama lokasi atau nama barang tanpa trik `withTrashed()`.
- **Bad**: Developer harus secara sadar menambahkan scope filter `where('is_active', true)` pada dropdown pilihan formulir saat membuat transaksi baru.
