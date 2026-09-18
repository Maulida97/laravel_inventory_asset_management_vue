# AGENT.MD: MASTER INSTRUCTION BLUEPRINT
**Inventory & Asset Management System**  
*Single Source of Truth for Autonomous AI Coding Agents & Engineers*  
*Last Updated: 2026-09-18 | Status: Active Specification*

---

## 1. Project Mission & Technology Stack

Sistem Enterprise Inventory & Asset Management yang dirancang menggunakan pendekatan **Documentation-First** yang ketat. Seluruh keputusan fungsional dan teknis telah dibakukan dalam direktori `docs/`, `architecture/`, `uiux/`, dan `decisions/ADR/`.

### Core Technology Stack:
- **Backend**: Laravel 13 + PHP 8.4 (Action-Service Layered Pattern, Thin Controllers)
- **Frontend**: Vue 3 (Composition API `<script setup>`) + Inertia.js (Monolithic Single-Page App)
- **Styling & UI Primitives**: Tailwind CSS + shadcn-vue + Lucide Vue Icons
- **Data Tables**: TanStack Table (Vue Table) dengan server-side debounced search, faceted filter, dan pagination
- **Database**: MySQL 8.0 (InnoDB, `utf8mb4_unicode_ci`, Zero Soft-Deletes)
- **Cache, Session & Queues**: Redis 7.2 Alpine
- **Authorization & RBAC**: Spatie Laravel Permission (6 Roles: Super Admin, Admin, Inventory Staff, Asset Staff, Requester, Manager)
- **Testing Engine**: Pest PHP v3 (Feature, Unit, and Architecture Tests)
- **Containerization**: Docker Compose (6 services: `app`, `webserver`, `db`, `cache`, `mailpit`, `phpmyadmin`)

---

## 2. Inviolable Core Architecture Rules

Setiap agen atau engineer yang bekerja pada repository ini **WAJIB** mematuhi aturan baku berikut tanpa pengecualian:

1. **Strict Documentation-First**: DILARANG memulai instalasi (`composer create-project`, `npm install`, dsb.) atau coding fitur sebelum seluruh dokumen perancangan (Phases 03–08) selesai diverifikasi dan berstatus GREEN.
2. **Event-Based Immutable Stock Ledger**:
   - Saldo stok barang dihitung secara agregat: $\text{Current Stock} = \sum \text{quantity}$ dari tabel `stock_ledger` per `item_id` dan `location_id`.
   - Dilarang membuat kolom saldo mutabel pada tabel `items` yang di-update secara ad-hoc.
   - Tabel `stock_ledger` bersifat **INSERT-ONLY** (Dilarang melakukan `UPDATE` atau `DELETE`).
3. **Zero Soft-Deletes**:
   - Tidak menggunakan trait `SoftDeletes` bawaan Laravel (`deleted_at`).
   - Gunakan kolom boolean `is_active` (default: `true`) untuk master data (`items`, `locations`, `departments`, `users`).
   - Data transaksi historis dilarang dihapus (dilindungi FK `ON DELETE RESTRICT`).
4. **Thin Controllers & Layered Architecture**:
   - Controller hanya bertugas: Menerima request -> Memanggil FormRequest validation -> Mendelegasikan logika ke `Action` / `Service` -> Mengembalikan `Inertia::render()` atau redirect.
   - Tidak ada query database kompleks atau kalkulasi bisnis langsung di Controller.
5. **Pessimistic Locking on Stock Operations**:
   - Setiap transaksi pengeluaran atau penyesuaian stok wajib dijalankan dalam `DB::transaction()` dengan klausa `lockForUpdate()` untuk mencegah race condition.
6. **Immutable Audit Trail**:
   - Tabel `audit_logs` merekam 9 domain kategori dengan format snapshot JSON `old_values` dan `new_values`.
   - Data kredensial sensitif (`password`, `token`) wajib disaring (masked).
7. **Pest PHP for All Tests**:
   - Seluruh pengujian wajib ditulis menggunakan Pest PHP v3 sintaks ekspresif.

---

## 3. Documentation Sitemap & Knowledge Map

| Sub-Direktori | Dokumen | Ringkasan Isi |
|---|---|---|
| **`docs/`** | [`00_PRD.md`](file:///d:/Project/Inventory_Asset%20Management/docs/00_PRD.md) | Product Requirement Document menyeluruh |
| | [`01_PRODUCT_VISION.md`](file:///d:/Project/Inventory_Asset%20Management/docs/01_PRODUCT_VISION.md) | Visi produk, persona pengguna, dan nilai bisnis |
| | [`02_FEATURE_DECISIONS.md`](file:///d:/Project/Inventory_Asset%20Management/docs/02_FEATURE_DECISIONS.md) | 63 keputusan fitur dari Phase 01 Brainstorming |
| | [`03_MODULES.md`](file:///d:/Project/Inventory_Asset%20Management/docs/03_MODULES.md) | Rincian fungsional 8 modul utama |
| | [`04_USER_FLOW.md`](file:///d:/Project/Inventory_Asset%20Management/docs/04_USER_FLOW.md) | Alur langkah pengguna (permintaan, approval, checkout) |
| | [`05_DATABASE.md`](file:///d:/Project/Inventory_Asset%20Management/docs/05_DATABASE.md) | Spesifikasi skema tabel, indeks, FK, dan data dictionary |
| | [`06_API.md`](file:///d:/Project/Inventory_Asset%20Management/docs/06_API.md) | Justifikasi status NOT_APPLICABLE (Inertia monolithic) |
| | [`07_PERMISSION_MATRIX.md`](file:///d:/Project/Inventory_Asset%20Management/docs/07_PERMISSION_MATRIX.md) | Matriks hak akses 6 role & Spatie permission mapping |
| | [`08_ROADMAP.md`](file:///d:/Project/Inventory_Asset%20Management/docs/08_ROADMAP.md) | Milestone pengerjaan proyek |
| | [`09_BACKLOG.md`](file:///d:/Project/Inventory_Asset%20Management/docs/09_BACKLOG.md) | Daftar backlog terperinci dari F0 hingga F15 |
| **`architecture/`** | [`SYSTEM_ARCHITECTURE.md`](file:///d:/Project/Inventory_Asset%20Management/architecture/SYSTEM_ARCHITECTURE.md) | Arsitektur sistem, infrastruktur, docker, keamanan |
| | [`MODULE_ARCHITECTURE.md`](file:///d:/Project/Inventory_Asset%20Management/architecture/MODULE_ARCHITECTURE.md) | Interaksi antar-modul, dependensi, event flow |
| | [`DATABASE_GUIDELINE.md`](file:///d:/Project/Inventory_Asset%20Management/architecture/DATABASE_GUIDELINE.md) | Standar query, indexing, transaksi, dan locking |
| | [`FOLDER_STRUCTURE.md`](file:///d:/Project/Inventory_Asset%20Management/architecture/FOLDER_STRUCTURE.md) | Struktur pohon folder Laravel 13, Vue 3, & Docker |
| **`uiux/`** | [`DESIGN_SYSTEM.md`](file:///d:/Project/Inventory_Asset%20Management/uiux/DESIGN_SYSTEM.md) | Token warna HSL Tailwind, tipografi, dan primitives |
| | [`UI_GUIDELINES.md`](file:///d:/Project/Inventory_Asset%20Management/uiux/UI_GUIDELINES.md) | Standar datatable TanStack, form, modal, responsivitas |
| **`decisions/ADR/`**| `ADR-001` s/d `ADR-010` | 10 Dokumen Rekam Keputusan Arsitektur resmi |

---

## 4. 15-Phase Development Lifecycle Status

| Fase | Deskripsi | Status |
|---|---|---|
| **Phase 01** | Business Brainstorming (63 Keputusan) | ✅ COMPLETED |
| **Phase 02** | Requirement Consolidation | ✅ COMPLETED |
| **Phase 03** | PRD & System Documentation | ✅ COMPLETED |
| **Phase 04** | Architectural Blueprint | ✅ COMPLETED |
| **Phase 05** | Database Schema Specification | ✅ COMPLETED |
| **Phase 06** | API Specification (NOT_APPLICABLE) | ✅ COMPLETED |
| **Phase 07** | Authorization Matrix & Policies | ✅ COMPLETED |
| **Phase 08** | UI/UX & Design System Guidelines | ✅ COMPLETED |
| **Phase 09** | Project Setup (Docker, Laravel 13, Vue 3, Inertia, Git) | 🔄 NEXT PHASE |
| **Phase 10** | Core Modules Development | ⏳ PENDING |
| **Phase 11** | Automated Testing Suite (Pest PHP) | ⏳ PENDING |
| **Phase 12** | Performance Optimization & Caching | ⏳ PENDING |
| **Phase 13** | Security Hardening & Penetration Check | ⏳ PENDING |
| **Phase 14** | Code Review & Architectural Refactoring | ⏳ PENDING |
| **Phase 15** | Final Verification & Delivery | ⏳ PENDING |

---

## 5. Execution Workflow for Phase 09 (Next Phase)

Ketika pengguna memberi instruksi untuk masuk ke **Phase 09 (Project Setup)**:
1. Jalankan inisialisasi Git repository lokal dan remote GitHub.
2. Siapkan file konfigurasi Docker di `docker/` dan `compose.yaml`.
3. Jalankan container Docker (`docker compose up -d`).
4. Eksekusi setup Laravel 13 + PHP 8.4 di dalam container.
5. Konfigurasikan `.env` (MySQL, Redis, Mailpit).
6. Pasang dependencies: Inertia.js, Vue 3, Tailwind CSS, shadcn-vue, Spatie Permission, Pest PHP.
7. Jalankan tes konektivitas awal database dan redis.
