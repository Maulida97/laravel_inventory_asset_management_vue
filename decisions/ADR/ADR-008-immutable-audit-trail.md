# ADR-008: INSERT-ONLY Immutable Audit Trail

- **Status**: Accepted
- **Deciders**: Software Architect, Compliance & Security Officer
- **Date**: 2026-09-18
- **Technical Story**: System-wide activity logging, integrity verification, and sensitive data masking.

---

## Context and Problem Statement

Sistem enterprise yang mengelola barang bernilai tinggi dan aset vital rentan terhadap manipulasi atau sengketa pertanggungjawaban jika tidak dilengkapi jejak audit (*audit trail*) yang tidak dapat disangkal (*non-repudiation*). Pengguna dengan hak akses tinggi (seperti Super Admin atau Database Administrator nakal) berpotensi menghapus log aktivitas untuk menutupi kesalahan.

## Decision Drivers

- Integritas data log yang mutlak: log tidak boleh dapat diubah (`UPDATE`) atau dihapus (`DELETE`).
- Kemampuan merekonstruksi kondisi sebelum dan sesudah perubahan data (*diff visualization*).
- Perlindungan privasi dan keamanan: data sensitif (seperti password atau token) tidak boleh masuk ke log.

## Considered Options

1. **Option A**: File-based logging (monolog text files di `storage/logs/laravel.log`).
2. **Option B**: Database INSERT-ONLY Table (`audit_logs`) dengan snapshot JSON `old_values` dan `new_values`.
3. **Option C**: Audit Log SaaS eksternal (Datadog / Logstash / AWS CloudWatch).

## Decision Outcome

**Chosen Option**: **Option B (Database INSERT-ONLY Immutable Table)**.

Semua aktivitas penting (9 kategori: `auth`, `inventory`, `asset`, `approval`, `master`, `system`, `user_management`, `report`, `setting`) dicatat ke tabel `audit_logs` via `AuditLoggerService`.

### Karakteristik Teknis:
1. **Immutable**: Model Eloquent `AuditLog` menonaktifkan method `update()`, `save()` setelah create, dan `delete()`.
2. **Payload Snapshot**: Kolom `old_values` dan `new_values` bertipe `JSON`, merekam atribut yang berubah.
3. **Sensitive Masking**: Atribut seperti `password`, `remember_token` otomatis disaring/dimasker menjadi `********`.
4. **Metadata Konteks**: Merekam IP address, user agent, dan timestamp `created_at`.
5. **Access Control**: Hanya Super Admin yang memiliki wewenang membaca data detail perubahan JSON.

### Consequences

- **Good**: Bukti audit sah, transparan, dan mudah disaring langsung dari antarmuka web.
- **Good**: Mengeliminasi ketergantungan pada vendor SaaS pihak ketiga.
- **Bad**: Ukuran tabel `audit_logs` akan tumbuh cepat dari waktu ke waktu; diperlukan strategi partisi tabel atau cold-storage archiving jika data melebihi jutaan baris di masa mendatang.
