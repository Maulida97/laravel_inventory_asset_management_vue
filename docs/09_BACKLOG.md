# BACKLOG
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18

---

## KETERANGAN STATUS

| Status | Arti |
|---|---|
| `BACKLOG` | Belum dijadwalkan |
| `PLANNED` | Dijadwalkan di fase tertentu |
| `IN_PROGRESS` | Sedang dikerjakan |
| `DONE` | Selesai dan terverifikasi |
| `BLOCKED` | Menunggu dependency |

---

## FASE 0 — FOUNDATION

| ID | Task | Status | Dependency |
|---|---|---|---|
| F0-01 | Phase 01 — Business Brainstorming | DONE | — |
| F0-02 | Phase 02 — Requirement Consolidation | DONE | F0-01 |
| F0-03 | Buat docs/00_PRD.md | DONE | F0-02 |
| F0-04 | Buat docs/01_PRODUCT_VISION.md | DONE | F0-02 |
| F0-05 | Buat docs/02_FEATURE_DECISIONS.md | DONE | F0-02 |
| F0-06 | Buat docs/03_MODULES.md | DONE | F0-02 |
| F0-07 | Buat docs/04_USER_FLOW.md | DONE | F0-02 |
| F0-08 | Buat docs/05_DATABASE.md | DONE | F0-02 |
| F0-09 | Buat docs/06_API.md (NOT_APPLICABLE) | DONE | — |
| F0-10 | Buat docs/07_PERMISSION_MATRIX.md | DONE | F0-02 |
| F0-11 | Buat docs/08_ROADMAP.md | DONE | F0-02 |
| F0-12 | Buat docs/09_BACKLOG.md | DONE | F0-02 |
| F0-13 | Buat architecture/SYSTEM_ARCHITECTURE.md | DONE | F0-02 |
| F0-14 | Buat architecture/MODULE_ARCHITECTURE.md | DONE | F0-02 |
| F0-15 | Buat architecture/DATABASE_GUIDELINE.md | DONE | F0-02 |
| F0-16 | Buat architecture/FOLDER_STRUCTURE.md | DONE | F0-02 |
| F0-17 | Buat uiux/DESIGN_SYSTEM.md | DONE | F0-02 |
| F0-18 | Buat uiux/UI_GUIDELINES.md | DONE | F0-02 |
| F0-19 | Buat ADR-001 s/d ADR-010 | DONE | F0-02 |
| F0-20 | Buat AGENT.md | DONE | Semua doc selesai |
| F0-21 | Git repository setup (local init & develop branch) | DONE | F0-20 |

---

## FASE 1 — CORE SETUP & AUTH

| ID | Task | Status | Dependency |
|---|---|---|---|
| F1-01 | Setup Docker (6 services) | DONE | F0-21 |
| F1-02 | Verifikasi Docker berjalan | DONE | F1-01 |
| F1-03 | Laravel 13 installation | PLANNED | F1-01 |
| F1-04 | Konfigurasi .env | BACKLOG | F1-03 |
| F1-05 | Database connection testing | BACKLOG | F1-04 |
| F1-06 | Install Inertia.js + Vue 3 | BACKLOG | F1-03 |
| F1-07 | Install Tailwind CSS + shadcn-vue | BACKLOG | F1-06 |
| F1-08 | Install Spatie Laravel Permission | BACKLOG | F1-03 |
| F1-09 | Install Laravel Telescope | BACKLOG | F1-03 |
| F1-10 | Install Pest PHP | BACKLOG | F1-03 |
| F1-11 | Migration: users (extended profile) | BACKLOG | F1-05 |
| F1-12 | Migration: departments | BACKLOG | F1-05 |
| F1-13 | Spatie role & permission migration | BACKLOG | F1-08 |
| F1-14 | Seeder: roles (6 role) | BACKLOG | F1-13 |
| F1-15 | Seeder: permissions (lengkap) | BACKLOG | F1-13 |
| F1-16 | Seeder: Super Admin user | BACKLOG | F1-14 |
| F1-17 | Login page (Vue + Inertia) | BACKLOG | F1-06 |
| F1-18 | Logout functionality | BACKLOG | F1-17 |
| F1-19 | Auth middleware | BACKLOG | F1-17 |
| F1-20 | Base layout (sidebar, navbar) | BACKLOG | F1-07 |
| F1-21 | Dashboard skeleton page | BACKLOG | F1-20 |
| F1-22 | Feature test: authentication | BACKLOG | F1-18 |

---

## FASE 2 — MASTER DATA

| ID | Task | Status | Dependency |
|---|---|---|---|
| F2-01 | Migration: locations | BACKLOG | F1-05 |
| F2-02 | Model: Location (relationships) | BACKLOG | F2-01 |
| F2-03 | Location CRUD (Controller + Vue pages) | BACKLOG | F2-02 |
| F2-04 | Location business rule validations | BACKLOG | F2-03 |
| F2-05 | Feature test: location | BACKLOG | F2-04 |
| F2-06 | Migration: categories | BACKLOG | F1-05 |
| F2-07 | Category CRUD | BACKLOG | F2-06 |
| F2-08 | Migration: units | BACKLOG | F1-05 |
| F2-09 | Unit CRUD | BACKLOG | F2-08 |
| F2-10 | Department CRUD | BACKLOG | F1-12 |
| F2-11 | Migration: items + item_specifications | BACKLOG | F2-06 |
| F2-12 | Model: Item (relationships) | BACKLOG | F2-11 |
| F2-13 | Item CRUD (with photo upload) | BACKLOG | F2-12 |
| F2-14 | Item specification management | BACKLOG | F2-13 |
| F2-15 | Item business rule validations | BACKLOG | F2-13 |
| F2-16 | Feature test: item | BACKLOG | F2-15 |
| F2-17 | User CRUD (Admin manages users) | BACKLOG | F1-11 |
| F2-18 | User role assignment | BACKLOG | F2-17 |
| F2-19 | Feature test: user management | BACKLOG | F2-18 |

---

## FASE 3 — APPROVAL WORKFLOW

| ID | Task | Status | Dependency |
|---|---|---|---|
| F3-01 | Migration: approval_flow_configs | BACKLOG | F1-05 |
| F3-02 | Migration: approval_requests | BACKLOG | F3-01 |
| F3-03 | Migration: approval_steps | BACKLOG | F3-02 |
| F3-04 | Model: ApprovalRequest, ApprovalStep | BACKLOG | F3-03 |
| F3-05 | Service: ApprovalService (create, advance, reject, cancel) | BACKLOG | F3-04 |
| F3-06 | Approval config page (Super Admin) | BACKLOG | F3-04 |
| F3-07 | Inbox approval - Level 1 (Admin) | BACKLOG | F3-05 |
| F3-08 | Inbox approval - Level 2 (Manager) | BACKLOG | F3-05 |
| F3-09 | Approve / Reject / Cancel actions | BACKLOG | F3-07 |
| F3-10 | Revisi & resubmit | BACKLOG | F3-09 |
| F3-11 | Notifikasi in-app (pending count) | BACKLOG | F3-09 |
| F3-12 | Feature test: approval workflow | BACKLOG | F3-10 |

---

## FASE 4 — INVENTORY MODULE

| ID | Task | Status | Dependency |
|---|---|---|---|
| F4-01 | Migration: stock_ledger | BACKLOG | F1-05 |
| F4-02 | Migration: stock_opnames + items | BACKLOG | F1-05 |
| F4-03 | Migration: purchase_orders + items | BACKLOG | F1-05 |
| F4-04 | Migration: goods_receipts + items | BACKLOG | F1-05 |
| F4-05 | Migration: direct_receipts + items | BACKLOG | F1-05 |
| F4-06 | Migration: stock_requests + items | BACKLOG | F1-05 |
| F4-07 | Migration: stock_transfers | BACKLOG | F1-05 |
| F4-08 | Migration: stock_disposals | BACKLOG | F1-05 |
| F4-09 | Migration: stock_adjustments | BACKLOG | F1-05 |
| F4-10 | Service: StockLedgerService | BACKLOG | F4-01 |
| F4-11 | Service: StockCalculationService (get current stock) | BACKLOG | F4-10 |
| F4-12 | Purchase Order CRUD + approval | BACKLOG | F3-05 |
| F4-13 | Goods Receipt + partial receipt logic | BACKLOG | F4-12 |
| F4-14 | Direct Receipt + approval | BACKLOG | F3-05 |
| F4-15 | Internal Usage + approval | BACKLOG | F3-05 |
| F4-16 | Stock Request + approval | BACKLOG | F3-05 |
| F4-17 | Transfer Barang + approval | BACKLOG | F3-05 |
| F4-18 | Stock Disposal + approval | BACKLOG | F3-05 |
| F4-19 | Stock Adjustment (no approval) | BACKLOG | F4-10 |
| F4-20 | Stock Opname flow | BACKLOG | F4-10 |
| F4-21 | Stok terkini view (per item per lokasi) | BACKLOG | F4-11 |
| F4-22 | N+1 check semua query inventory | BACKLOG | F4-21 |
| F4-23 | Feature test: inventory | BACKLOG | F4-21 |

---

## FASE 5 — ASSET MODULE

| ID | Task | Status | Dependency |
|---|---|---|---|
| F5-01 | Migration: assets | BACKLOG | F1-05 |
| F5-02 | Migration: asset_assignments | BACKLOG | F5-01 |
| F5-03 | Migration: asset_mutations | BACKLOG | F5-01 |
| F5-04 | Migration: asset_returns | BACKLOG | F5-01 |
| F5-05 | Migration: asset_maintenances | BACKLOG | F5-01 |
| F5-06 | Migration: asset_disposals | BACKLOG | F5-01 |
| F5-07 | Model: Asset + relationships | BACKLOG | F5-06 |
| F5-08 | Asset Registration + approval | BACKLOG | F3-05 |
| F5-09 | Asset Assignment + approval | BACKLOG | F5-08 |
| F5-10 | Asset Return + approval | BACKLOG | F5-09 |
| F5-11 | Asset Mutation + approval | BACKLOG | F5-08 |
| F5-12 | Asset Maintenance (create + complete) | BACKLOG | F5-08 |
| F5-13 | Asset Disposal + approval | BACKLOG | F5-08 |
| F5-14 | Asset detail view + history | BACKLOG | F5-13 |
| F5-15 | N+1 check semua query asset | BACKLOG | F5-14 |
| F5-16 | Feature test: asset | BACKLOG | F5-14 |

---

## FASE 6 — REPORTING

| ID | Task | Status | Dependency |
|---|---|---|---|
| F6-01 | Install Laravel Excel (Maatwebsite) | BACKLOG | F1-03 |
| F6-02 | Install DomPDF | BACKLOG | F1-03 |
| F6-03 | 9 Laporan Inventory (query + view) | BACKLOG | F4-23 |
| F6-04 | 10 Laporan Asset (query + view) | BACKLOG | F5-16 |
| F6-05 | Export PDF per laporan | BACKLOG | F6-02 |
| F6-06 | Export Excel per laporan | BACKLOG | F6-01 |
| F6-07 | Export CSV per laporan | BACKLOG | F6-01 |
| F6-08 | Audit log: catat setiap export | BACKLOG | F6-07 |

---

## FASE 7 — AUDIT LOG & DASHBOARD

| ID | Task | Status | Dependency |
|---|---|---|---|
| F7-01 | Migration: audit_logs | BACKLOG | F1-05 |
| F7-02 | Setup Observer / Event Listener | BACKLOG | F7-01 |
| F7-03 | Audit log semua aktivitas (9 kategori) | BACKLOG | F7-02 |
| F7-04 | Audit Log viewer UI | BACKLOG | F7-03 |
| F7-05 | Dashboard: widget stok minimum | BACKLOG | F4-21 |
| F7-06 | Dashboard: widget aset per status | BACKLOG | F5-14 |
| F7-07 | Dashboard: widget approval pending | BACKLOG | F3-11 |
| F7-08 | Dashboard: widget garansi berakhir | BACKLOG | F5-14 |
| F7-09 | Dashboard: widget transaksi terbaru | BACKLOG | F4-21 |

---

## FASE 8 — TESTING & QUALITY

| ID | Task | Status | Dependency |
|---|---|---|---|
| F8-01 | Review semua N+1 query (checklist) | BACKLOG | F7-09 |
| F8-02 | Slow query analysis (Telescope + EXPLAIN) | BACKLOG | F8-01 |
| F8-03 | Index review per tabel penting | BACKLOG | F8-02 |
| F8-04 | Security review (mass assignment, XSS, CSRF) | BACKLOG | F7-09 |
| F8-05 | Permission test (role-based testing) | BACKLOG | F7-09 |
| F8-06 | Load test k6 (endpoint kritis) | BACKLOG | F8-03 |

---

## FASE 9 — REFACTORING & FINALISASI

| ID | Task | Status | Dependency |
|---|---|---|---|
| F9-01 | Review Fat Controller | BACKLOG | F8-06 |
| F9-02 | Service layer (jika diperlukan) | BACKLOG | F9-01 |
| F9-03 | Clean Code review (naming, DRY, SOLID) | BACKLOG | F9-02 |
| F9-04 | AGENT.md finalisasi | BACKLOG | F9-03 |
| F9-05 | README.md | BACKLOG | F9-04 |
| F9-06 | Final checklist verification | BACKLOG | F9-05 |
