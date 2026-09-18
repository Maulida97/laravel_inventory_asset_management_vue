# ADR-005: 2-Level Physical Location Hierarchy

- **Status**: Accepted
- **Deciders**: Software Architect, Operations Team
- **Date**: 2026-09-18
- **Technical Story**: Location data structure for inventory warehousing and asset placement.

---

## Context and Problem Statement

Aset fisik dan stok barang berada di berbagai tempat fisik: Gedung Kantor Pusat, Gudang Utama, Ruang Rapat Lt 2, Ruang Server, atau Rak Khusus. Struktur lokasi yang terlalu datar (flat) menyulitkan pelacakan, sementara struktur hirarki bersarang tanpa batas (infinite nested tree / adjacency list mendalam) memperkenalkan kompleksitas query rekursif (CTE) yang berlebihan pada sistem operasional harian.

## Decision Drivers

- Kemudahan navigasi dan pemilihan lokasi oleh staf gudang dan karyawan.
- Performa query SQL sederhana tanpa kebutuhan nested set model yang kompleks.
- Perlindungan integritas: lokasi tidak boleh dihapus jika masih ada barang atau aset di dalamnya.

## Considered Options

1. **Option A**: Flat Location List (1 level tanpa relasi induk-anak).
2. **Option B**: 2-Level Hierarchy (Parent-Child) menggunakan relasi self-referencing `parent_id` pada tabel `locations`.
3. **Option C**: Infinite Nested Hierarchy (Closure Table / Modified Preorder Tree Traversal).

## Decision Outcome

**Chosen Option**: **Option B (2-Level Hierarchy: Parent & Child)**.

Tabel `locations` memiliki kolom self-referencing `parent_id`:
- **Level 1 (Parent)**: Fasilitas Utama / Gedung / Gudang (e.g., *Gedung A*, *Gudang Logistik Cikarang*). `parent_id = NULL`.
- **Level 2 (Child)**: Ruangan / Rak / Lantai spesifik (e.g., *Ruang Server 01*, *Rak A-12*). `parent_id = ID Gedung`.

### Rules:
- Level child tidak boleh memiliki child lagi (dibatasi 2 tingkatan di tingkat validasi Form Request & Policy).
- Penghapusan dilarang jika ada aset/stok (`ON DELETE RESTRICT`).
- Lokasi dapat dinonaktifkan (`is_active = false`). Menonaktifkan lokasi parent otomatis menonaktifkan seluruh ruangan di bawahnya.

### Consequences

- **Good**: Query sangat cepat dan mudah (`Location::with('children')->whereNull('parent_id')`).
- **Good**: Struktur UI select dropdown rapi dan mudah dipahami (`Gedung A > Ruang Server 01`).
- **Bad**: Tidak mendukung skenario sub-rak 4 level (misal: Gedung > Lantai > Ruang > Rak > Bin), namun kebutuhan bisnis saat ini cukup terakomodasi oleh 2 level.
