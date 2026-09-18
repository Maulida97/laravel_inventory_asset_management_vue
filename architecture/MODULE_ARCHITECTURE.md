# MODULE ARCHITECTURE
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18

---

## OVERVIEW

Sistem ini menggunakan arsitektur **Laravel MVC + Service Layer** dengan modifikasi:

```
Request
  └── Route
        └── Middleware (Auth, Permission)
              └── Controller (thin — hanya orchestrate)
                    └── Form Request (validation)
                          └── Service / Action (business logic)
                                └── Model / Repository (data access)
                                      └── Database (MySQL)
```

---

## LARAVEL FOLDER ARCHITECTURE

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Location/
│   │   ├── Item/
│   │   ├── Inventory/
│   │   ├── Asset/
│   │   ├── Approval/
│   │   ├── User/
│   │   ├── Report/
│   │   └── AuditLog/
│   │
│   ├── Requests/                   ← Form Request (validation)
│   │   ├── Location/
│   │   ├── Item/
│   │   ├── Inventory/
│   │   ├── Asset/
│   │   └── User/
│   │
│   └── Middleware/
│       ├── HandleInertiaRequests.php
│       └── (Spatie middleware via alias)
│
├── Models/
│   ├── User.php
│   ├── Department.php
│   ├── Location.php
│   ├── Category.php
│   ├── Unit.php
│   ├── Item.php
│   ├── ItemSpecification.php
│   ├── StockLedger.php
│   ├── StockOpname.php
│   ├── StockOpnameItem.php
│   ├── PurchaseOrder.php
│   ├── PurchaseOrderItem.php
│   ├── GoodsReceipt.php
│   ├── GoodsReceiptItem.php
│   ├── DirectReceipt.php
│   ├── StockRequest.php
│   ├── StockTransfer.php
│   ├── StockDisposal.php
│   ├── StockAdjustment.php
│   ├── Asset.php
│   ├── AssetAssignment.php
│   ├── AssetMutation.php
│   ├── AssetReturn.php
│   ├── AssetMaintenance.php
│   ├── AssetDisposal.php
│   ├── ApprovalRequest.php
│   ├── ApprovalStep.php
│   ├── ApprovalFlowConfig.php
│   └── AuditLog.php
│
├── Services/                       ← Business Logic
│   ├── LocationService.php
│   ├── ItemService.php
│   ├── StockLedgerService.php
│   ├── StockCalculationService.php
│   ├── InventoryTransactionService.php
│   ├── AssetService.php
│   ├── ApprovalService.php
│   ├── ReportService.php
│   └── AuditLogService.php
│
├── Observers/                      ← Audit Log Triggers
│   ├── ItemObserver.php
│   ├── LocationObserver.php
│   ├── AssetObserver.php
│   └── UserObserver.php
│
├── Events/
│   ├── StockTransactionCreated.php
│   ├── AssetStatusChanged.php
│   ├── ApprovalActioned.php
│   └── ReportExported.php
│
├── Listeners/
│   ├── RecordStockTransaction.php
│   ├── RecordApprovalAction.php
│   └── RecordReportExport.php
│
├── Policies/
│   ├── LocationPolicy.php
│   ├── ItemPolicy.php
│   ├── AssetPolicy.php
│   ├── ApprovalPolicy.php
│   └── UserPolicy.php
│
├── Exceptions/
│   ├── InsufficientStockException.php
│   ├── LocationNotEmptyException.php
│   ├── AssetNotAvailableException.php
│   └── ApprovalException.php
│
└── Enums/
    ├── AssetStatus.php
    ├── AssetCondition.php
    ├── AssetDisposalMethod.php
    ├── ApprovalStatus.php
    ├── TransactionType.php
    └── EmploymentStatus.php
```

---

## MODULE BREAKDOWN

### MOD-LOC: Location Module

```
Route::prefix('locations')->group(function() {
    GET    /           → LocationController@index
    GET    /create     → LocationController@create
    POST   /           → LocationController@store
    GET    /{id}       → LocationController@show
    GET    /{id}/edit  → LocationController@edit
    PUT    /{id}       → LocationController@update
    PATCH  /{id}/toggle-status → LocationController@toggleStatus
    PUT    /{id}/move  → LocationController@move
});

LocationController (thin):
  index()   → return Inertia::render('Location/Index', [...])
  store()   → LocationService::create(LocationStoreRequest $request)
  update()  → LocationService::update(location, LocationUpdateRequest)
  toggleStatus() → LocationService::toggleStatus(location)
  move()    → LocationService::moveToParent(location, new_parent_id)

LocationService:
  create(data) → validate + create location
  toggleStatus(location) → cek barang/aset → toggle is_active
  moveToParent(location, parent) → cek kosong → update parent_id
  canDeactivate(location) → bool
  canMoveParent(location) → bool

LocationPolicy:
  create() → hasPermission('location.create')
  update() → hasPermission('location.edit')
  toggleStatus() → hasPermission('location.toggle-status')
```

---

### MOD-INV: Inventory Module

```
StockLedgerService:
  record(item_id, location_id, type, ref_type, ref_id, qty, notes, user)
    → INSERT ke stock_ledger
    → Invalidate Redis cache untuk (item_id, location_id)

StockCalculationService:
  getCurrentStock(item_id, location_id) → decimal
    → Check Redis cache dulu
    → Jika miss: SELECT SUM(quantity) FROM stock_ledger WHERE ...
    → Set Redis cache
  
  validateSufficientStock(item_id, location_id, requested_qty)
    → DB::transaction dengan lockForUpdate()
    → Throw InsufficientStockException jika tidak cukup

InventoryTransactionService:
  createStockIn(type, data) → buat transaksi + initiate approval
  createStockOut(type, data) → validate stock + buat transaksi + initiate approval
  executeStockIn(transaction) → StockLedgerService::record (qty positif)
  executeStockOut(transaction) → StockLedgerService::record (qty negatif)
  executeTransfer(transfer) → record out di asal + record in di tujuan
```

---

### MOD-ASSET: Asset Module

```
AssetService:
  register(data) → buat asset + initiate approval
  assign(asset, user, data) → validasi + buat assignment + initiate approval
  return(asset, assignment, data) → buat return doc + initiate approval
  mutate(asset, location, data) → validasi + buat mutasi + initiate approval
  startMaintenance(asset, data) → buat maintenance + update status
  completeMaintenance(maintenance, data) → update status asset + kondisi
  dispose(asset, data) → validasi + buat disposal + initiate approval

  executeAssign(assignment) → update assets.assigned_to
  executeReturn(return) → update assets.assigned_to = null, update kondisi
  executeMutation(mutation) → update assets.location_id
  executeDisposal(disposal) → update assets.status = 'disposed'

AssetPolicy:
  assign() → hasPermission('asset.assign') && asset.isAvailable()
  dispose() → hasPermission('asset.dispose') && !asset.isAssigned()
```

---

### MOD-APPR: Approval Module

```
ApprovalService:
  initiate(model, transaction_type)
    → Baca ApprovalFlowConfig untuk transaction_type
    → Buat ApprovalRequest (pending, level: 1)
    → Buat ApprovalSteps sesuai level count
    → Return approval_request

  approve(approval_request, approver, notes)
    → Validasi approver punya role yang sesuai untuk level ini
    → Update ApprovalStep level ini → approved
    → Cek apakah level terakhir:
        Jika ya → approval_request.status = approved
                  → Execute transaction (dispatch event)
        Jika tidak → advance ke level berikutnya

  reject(approval_request, approver, reason)
    → Update ApprovalStep → rejected
    → approval_request.status = rejected
    → Notifikasi ke pembuat

  cancel(approval_request, user)
    → Validasi: user adalah pembuat && belum ada action
    → approval_request.status = cancelled

  resubmit(approval_request, user)
    → Validasi: status = rejected && user adalah pembuat
    → Reset ke level 1
    → approval_request.status = pending
    → approval_request.revision_count++
```

---

### MOD-RPT: Report Module

```
ReportService:
  generateInventoryReport(type, filters) → Collection
  generateAssetReport(type, filters) → Collection

ReportController:
  index(type) → return Inertia dengan data report + filters
  export(type, format, filters)
    → Dispatch ExportReportJob ke queue (untuk export besar)
    → Atau langsung generate untuk export kecil
    → Return file download

ExportReportJob (Queue):
  handle() → ReportService::generate() → format → simpan ke storage → notif user
```

---

## PATTERN YANG DIGUNAKAN

### 1. Thin Controller
Controller hanya bertugas:
- Menerima request
- Memanggil Service
- Mengembalikan Inertia response

Tidak ada business logic di Controller.

### 2. Service Layer
Semua business logic ada di Service:
- Validasi business rule
- Orchestrate multiple model operations
- Handle database transaction
- Dispatch events

### 3. Form Request
Validasi input ada di Form Request:
- Rule validation (required, max, unique, dll.)
- Authorization cek permission

### 4. Policy
Otorisasi granular berbasis kondisi bisnis:
- Hanya pembuat yang bisa cancel
- Aset harus available sebelum di-assign
- Lokasi harus kosong sebelum dinonaktifkan

### 5. Eloquent Observer
Untuk side effect otomatis:
- Audit log saat model dibuat/diubah
- Auto-generate kode (item_code, asset_code)

### 6. Enum (PHP 8.1+)
Untuk konstanta yang terdefinisi:
- AssetStatus::Active, Inactive, dll.
- ApprovalStatus::Pending, Approved, dll.
- TransactionType::StockInPo, StockInDirect, dll.

---

## VUE + INERTIA ARCHITECTURE

```
resources/js/
├── app.js                    ← Entry point (setup Vue + Inertia)
├── bootstrap.js
│
├── layouts/
│   ├── AppLayout.vue         ← Main authenticated layout (sidebar + navbar)
│   └── AuthLayout.vue        ← Layout untuk halaman auth (login)
│
├── components/
│   ├── ui/                   ← shadcn-vue components (auto-generated)
│   │   ├── button/
│   │   ├── table/
│   │   ├── dialog/
│   │   ├── form/
│   │   ├── badge/
│   │   └── ...
│   │
│   ├── shared/               ← Komponen yang digunakan di banyak halaman
│   │   ├── DataTable.vue     ← Wrapper TanStack Table
│   │   ├── PageHeader.vue
│   │   ├── StatusBadge.vue
│   │   ├── ConfirmDialog.vue
│   │   ├── Pagination.vue
│   │   └── FilterBar.vue
│   │
│   └── domain/               ← Komponen per domain
│       ├── location/
│       ├── item/
│       ├── inventory/
│       ├── asset/
│       └── approval/
│
├── pages/                    ← Inertia Pages (1 file per halaman)
│   ├── Auth/
│   │   └── Login.vue
│   ├── Dashboard/
│   │   └── Index.vue
│   ├── Location/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Edit.vue
│   ├── Item/
│   ├── Inventory/
│   │   ├── Stock/
│   │   ├── PurchaseOrder/
│   │   ├── StockRequest/
│   │   └── Opname/
│   ├── Asset/
│   │   ├── Index.vue
│   │   ├── Show.vue
│   │   ├── Create.vue
│   │   └── Maintenance/
│   ├── Approval/
│   │   ├── Inbox.vue
│   │   └── Show.vue
│   ├── Report/
│   │   ├── Inventory/
│   │   └── Asset/
│   ├── User/
│   └── AuditLog/
│
├── composables/              ← Vue Composables (reusable logic)
│   ├── usePermission.js      ← Cek permission dari props
│   ├── useToast.js
│   └── useConfirm.js
│
└── utils/
    ├── formatters.js         ← Format tanggal, angka, currency
    └── constants.js
```

---

## NAMING CONVENTIONS

### PHP (Backend)
```
Controller: LocationController, ItemController (PascalCase + Controller suffix)
Service:    LocationService, ApprovalService (PascalCase + Service suffix)
Model:      Location, Item, Asset (PascalCase, singular)
Migration:  create_locations_table, add_parent_id_to_locations_table
Request:    StoreLocationRequest, UpdateItemRequest
Policy:     LocationPolicy, AssetPolicy
Event:      AssetStatusChanged, StockTransactionCreated
Listener:   RecordAssetStatusChange
Observer:   AssetObserver
Enum:       AssetStatus, ApprovalStatus
Exception:  InsufficientStockException
```

### JavaScript/Vue (Frontend)
```
Component:  DataTable.vue, StatusBadge.vue (PascalCase)
Page:       Location/Index.vue, Asset/Show.vue
Composable: usePermission.js, useToast.js (camelCase dengan prefix use)
Utility:    formatters.js, constants.js (camelCase)
```

### Database
```
Table:       locations, items, stock_ledger (snake_case, plural)
Column:      parent_id, is_active, created_at (snake_case)
Index:       idx_locations_parent_id, idx_stock_ledger_item_location
FK:          fk_locations_parent_id, fk_items_category_id
```
