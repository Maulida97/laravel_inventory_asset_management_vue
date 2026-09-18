# ADR-006: Dynamic Multi-Level Approval Workflow

- **Status**: Accepted
- **Deciders**: Software Architect, Business Process Owners
- **Date**: 2026-09-18
- **Technical Story**: Multi-stage approval mechanism for requisitions, adjustments, and asset disposals.

---

## Context and Problem Statement

Operasi seperti pengeluaran barang bernilai tinggi, penyesuaian selisih stok fisik, dan pemusnahan/penjualan aset tetap memerlukan otorisasi bertingkat sebelum eksekusi mutasi buku dilakukan. Standar bisnis membutuhkan 2 tingkat persetujuan (Level 1: Admin Verifikator -> Level 2: Manajer Departemen/Keuangan), namun konfigurasi ini harus dapat disesuaikan (dinamis) di kemudian hari oleh Super Admin tanpa mengubah kode sumber.

## Decision Drivers

- Fleksibilitas konfigurasi alur per jenis transaksi.
- Rekam jejak audit transparan: siapa yang menyetujui/menolak, catatan penolakan, dan timestamp akurat.
- Eksekusi atomik: transaksi mutasi stok baru dibukukan setelah seluruh level yang diwajibkan disetujui secara tuntas.

## Considered Options

1. **Option A**: Hardcoded Approval Logic di masing-masing Controller (misal: `status = approved_by_admin`).
2. **Option B**: Dynamic Approval Engine dengan 3 tabel dedicated (`approval_flow_configs`, `approval_requests`, `approval_steps`).
3. **Option C**: State Machine eksternal (Workflow engine microservice).

## Decision Outcome

**Chosen Option**: **Option B (Dynamic Approval Engine)**.

Alur diatur melalui:
1. `approval_flow_configs`: Menentukan role approver dan urutan per `transaction_type`.
2. `approval_requests`: Menyimpan status siklus aktif dokumen (`pending`, `approved`, `rejected`).
3. `approval_steps`: Menyimpan riwayat verifikasi step-by-step per approver.

Jika salah satu approver memilih `reject`, seluruh alur berhenti, status dokumen menjadi `rejected`, dan alasan penolakan wajib diisi. Hanya ketika seluruh step selesai (`approved`), sistem memicu event domain untuk memproses pemotongan ledger stok atau perubahan status aset secara otomatis dalam `DB::transaction()`.

### Consequences

- **Good**: Sangat modular, aman dari bypass otorisasi.
- **Good**: Mengakomodasi kebutuhan perubahan kebijakan bisnis (misal: menaikkan menjadi 3 tingkat) melalui UI admin.
- **Bad**: Menambah tabel relasional dan membutuhkan event listener untuk mengeksekusi aksi final.
