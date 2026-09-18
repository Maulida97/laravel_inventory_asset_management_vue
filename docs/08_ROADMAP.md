# ROADMAP
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18

---

## FASE DEVELOPMENT

### FASE 0 — Foundation (Pre-coding)
**Goal**: Semua dokumentasi dan setup selesai sebelum coding dimulai.

| # | Task | Status |
|---|---|---|
| 1 | Phase 01 — Business Brainstorming | ✅ DONE |
| 2 | Phase 02 — Requirement Consolidation | ✅ DONE |
| 3 | Phase 03 — PRD & Documentation | 🔄 IN PROGRESS |
| 4 | Phase 04 — Architecture Design | NOT_STARTED |
| 5 | Phase 05 — Database Design | NOT_STARTED |
| 6 | Phase 07 — Authorization Design | NOT_STARTED |
| 7 | Phase 08 — UI/UX Guidelines | NOT_STARTED |
| 8 | Phase 09 — Project Setup (Docker + Laravel) | NOT_STARTED |

---

### FASE 1 — Core Setup & Auth
**Goal**: Proyek Laravel berjalan, Docker up, auth berfungsi.

| # | Task |
|---|---|
| 1 | Docker setup (6 services) |
| 2 | Laravel 13 installation |
| 3 | Database connection |
| 4 | Base migration (users, roles, permissions) |
| 5 | Authentication (login, logout) |
| 6 | RBAC setup (Spatie Permission) |
| 7 | Role & permission seeding |
| 8 | Base layout (Inertia + Vue 3 + Tailwind + shadcn-vue) |
| 9 | Dashboard skeleton |

---

### FASE 2 — Master Data
**Goal**: Semua master data bisa dikelola.

| # | Task |
|---|---|
| 1 | Location Management (CRUD, hierarki 2 level) |
| 2 | Department Management |
| 3 | User Management |
| 4 | Category Management |
| 5 | Unit Management |
| 6 | Item Master (CRUD + spesifikasi + foto) |

---

### FASE 3 — Approval Workflow
**Goal**: Sistem approval berfungsi sebelum transaksi dibangun.

| # | Task |
|---|---|
| 1 | Approval flow configuration (Super Admin) |
| 2 | Approval request & steps table |
| 3 | Inbox approval (Admin — Level 1) |
| 4 | Inbox approval (Manager — Level 2) |
| 5 | Approve / Reject / Cancel / Revisi |
| 6 | Notifikasi in-app |

---

### FASE 4 — Inventory Module
**Goal**: Semua fitur inventory berfungsi dengan approval.

| # | Task |
|---|---|
| 1 | Stock Ledger foundation |
| 2 | Purchase Order (buat, submit, partial receipt) |
| 3 | Goods Receipt |
| 4 | Direct Receipt |
| 5 | Internal Usage |
| 6 | Stock Request |
| 7 | Transfer Barang |
| 8 | Stock Disposal |
| 9 | Stock Adjustment |
| 10 | Stock Opname |
| 11 | View Stok Terkini |

---

### FASE 5 — Asset Module
**Goal**: Semua fitur asset lifecycle berfungsi dengan approval.

| # | Task |
|---|---|
| 1 | Asset Registration |
| 2 | Asset Assignment |
| 3 | Asset Return |
| 4 | Asset Mutation |
| 5 | Asset Maintenance |
| 6 | Asset Disposal |
| 7 | Asset detail & history view |

---

### FASE 6 — Reporting
**Goal**: Semua laporan tersedia dan bisa di-export.

| # | Task |
|---|---|
| 1 | 9 Laporan Inventory |
| 2 | 10 Laporan Asset |
| 3 | Export PDF |
| 4 | Export Excel |
| 5 | Export CSV |

---

### FASE 7 — Audit Log & Dashboard
**Goal**: Audit log lengkap dan dashboard informatif.

| # | Task |
|---|---|
| 1 | Audit Log observer/listener setup |
| 2 | Audit Log viewer (filter, search) |
| 3 | Dashboard widgets |

---

### FASE 8 — Testing & Quality
**Goal**: Test coverage memadai, tidak ada N+1, tidak ada masalah security.

| # | Task |
|---|---|
| 1 | Feature tests per modul |
| 2 | Unit tests untuk service/logic |
| 3 | N+1 detection & fix |
| 4 | Slow query review |
| 5 | Security review |
| 6 | Load testing (k6) untuk endpoint kritis |

---

### FASE 9 — Refactoring & Finalisasi
**Goal**: Code bersih, terdokumentasi, siap diteruskan.

| # | Task |
|---|---|
| 1 | Service layer refactoring (jika controller terlalu fat) |
| 2 | Clean code review (naming, DRY, SOLID) |
| 3 | AGENT.md finalisasi |
| 4 | README.md |
| 5 | Final checklist verification |

---

## PRIORITAS

| Prioritas | Modul | Alasan |
|---|---|---|
| P0 (Wajib) | Auth, Location, Item Master | Fondasi semua modul lain |
| P0 (Wajib) | Approval Workflow | Semua transaksi bergantung padanya |
| P1 (Tinggi) | Inventory Management | Kebutuhan operasional utama |
| P1 (Tinggi) | Asset Management | Kebutuhan operasional utama |
| P2 (Menengah) | Reporting | Kebutuhan manajemen |
| P2 (Menengah) | Audit Log | Kebutuhan compliance |
| P3 (Rendah) | Load Testing | Optimasi performa |
| P3 (Rendah) | Advanced Refactoring | Code quality |

---

## FITUR YANG BISA DITAMBAHKAN DI MASA DEPAN

- Barcode / QR Code scanning untuk aset
- Notifikasi email (trigger dari approval, stok minimum, garansi berakhir)
- API publik (untuk integrasi dengan sistem lain)
- Aplikasi mobile
- Import barang dari Excel/CSV
- Jadwal maintenance berkala (preventive maintenance)
- Laporan total biaya maintenance per aset
