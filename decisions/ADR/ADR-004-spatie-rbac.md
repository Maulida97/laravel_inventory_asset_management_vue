# ADR-004: Role-Based Access Control using Spatie Laravel Permission

- **Status**: Accepted
- **Deciders**: Software Architect, Security Lead
- **Date**: 2026-09-18
- **Technical Story**: Authorization engine and permission management strategy.

---

## Context and Problem Statement

Sistem memiliki 6 role pengguna dengan batasan wewenang ketat:
1. **Super Admin**: Konfigurasi sistem penuh, audit log menyeluruh, manajemen role.
2. **Admin**: Pengelolaan master data, approval level 1, manajemen pengguna.
3. **Inventory Staff**: Operasional stok masuk/keluar, penyesuaian, dan opname.
4. **Asset Staff**: Registrasi aset, pemeliharaan, serah terima, dan disposal.
5. **Requester**: Pengajuan kebutuhan barang / ATK, melihat status sendiri.
6. **Manager**: Approval level 2, review laporan eksekutif.

Diperlukan mekanisme otorisasi yang fleksibel, berkinerja tinggi, dan didukung penuh oleh ekosistem Laravel.

## Decision Drivers

- Standarisasi industri dan keamanan teruji.
- Dukungan granular permission strings (misal: `inventory.stock-in.create`, `asset.assign`).
- Integrasi mulus dengan Laravel Gate, Policy, Blade/Inertia Middleware, dan Redis caching.

## Considered Options

1. **Option A**: Implementasi RBAC kustom (tabel dan gate buatan sendiri).
2. **Option B**: Menggunakan paket standar industri `spatie/laravel-permission`.
3. **Option C**: Bouncer (`silber/bouncer`).

## Decision Outcome

**Chosen Option**: **Option B (`spatie/laravel-permission`)**.

Spatie Laravel Permission diadopsi secara resmi. Sebanyak 6 role bawaan dan ~60 granular permission string didaftarkan melalui seeder. Cache permission otomatis tersimpan di Redis untuk menghindari query berulang pada setiap request HTTP.

### Consequences

- **Good**: Fitur lengkap (Direct Permissions, Role Hierarchy, Middleware `role:`, `permission:`, Blade/Inertia directives).
- **Good**: Caching permission otomatis via cache driver (Redis).
- **Good**: Kemudahan pengecekan otorisasi di frontend melalui Inertia shared props (`auth.user.permissions`).
- **Bad**: Menambah dependensi paket pihak ketiga dan migrasi tabel bawaan (`roles`, `permissions`, pivot tables).
