# FOLDER STRUCTURE & CODEBASE ORGANIZATION
**Inventory & Asset Management System**  
*Document Version: 1.0.0 | Date: 2026-09-18*

---

## 1. Overview & Architectural Principles

Aplikasi dibangun menggunakan pola **Layered / Action-Service Architecture** di atas Laravel 13, dengan frontend monolitik reaktif berbasis **Inertia.js + Vue 3 (Composition API)**.

Prinsip organisasi folder:
1. **Separation of Concerns (SoC)**: Controllers tipis (thin controllers), logika bisnis mutlak ada di `Services/` atau `Actions/`.
2. **Predictable & Modular**: Setiap modul bisnis (Inventory, Asset, Approval, Audit, Report) memiliki representasi terstruktur di Backend (`app/`) dan Frontend (`resources/js/Pages/`).
3. **Auditability & Integrity**: Ledger stok dan lifecycle aset dikontrol oleh dedicated service dan events.
4. **Vue SFC Best Practice**: Komponen frontend dibagi menjadi base primitives (shadcn-vue), shared UI/layout components, and domain-specific feature components.

---

## 2. Root Directory Tree

```
inventory-asset-management/
├── .agents/                       # Project AI assistant customizations & rules
├── .editorconfig
├── .env.example
├── .git/
├── .gitattributes
├── .gitignore
├── architecture/                  # Architecture documentation (Phase 04)
│   ├── DATABASE_GUIDELINE.md
│   ├── FOLDER_STRUCTURE.md
│   ├── MODULE_ARCHITECTURE.md
│   └── SYSTEM_ARCHITECTURE.md
├── decisions/                     # Architecture Decision Records (ADRs)
│   └── ADR/
│       ├── ADR-001-laravel-inertia-stack.md
│       ├── ADR-002-event-based-stock-ledger.md
│       └── ...
├── docker/                        # Docker container configurations
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   ├── Dockerfile
│   │   ├── local.ini
│   │   └── opcache.ini
│   └── mysql/
│       └── my.cnf
├── docs/                          # PRD & Functional Specs (Phase 03, 05, 07)
│   ├── 00_PRD.md
│   ├── 01_PRODUCT_VISION.md
│   ├── 02_FEATURE_DECISIONS.md
│   ├── 03_MODULES.md
│   ├── 04_USER_FLOW.md
│   ├── 05_DATABASE.md
│   ├── 06_API.md
│   ├── 07_PERMISSION_MATRIX.md
│   ├── 08_ROADMAP.md
│   └── 09_BACKLOG.md
├── uiux/                          # UI/UX Specifications (Phase 08)
│   ├── DESIGN_SYSTEM.md
│   └── UI_GUIDELINES.md
├── app/                           # Core Backend Application Logic
├── bootstrap/                     # Framework bootstrap & app configuration
├── config/                        # Laravel configuration files
├── database/                      # Migrations, seeders, factories
├── public/                        # Web root entry points and static assets
├── resources/                     # Views, Vue 3 components, styles, assets
├── routes/                        # Web route definitions (Inertia)
├── storage/                       # Logs, uploads, cache
├── tests/                         # Pest PHP automated test suites
├── compose.yaml                   # Docker Compose configuration (6 services)
├── package.json                   # Node.js dependencies (Vue, Tailwind, shadcn-vue)
├── phpunit.xml                    # PHPUnit / Pest configuration
├── postcss.config.js
├── tailwind.config.js
├── tsconfig.json (optional/jsconfig.json)
└── vite.config.js                 # Vite bundling configuration
```

---

## 3. Backend Directory Structure (`app/`)

```
app/
├── Actions/                       # Single-purpose business action classes
│   ├── Assets/
│   │   ├── CalculateDepreciationAction.php
│   │   ├── CheckInAssetAction.php
│   │   ├── CheckOutAssetAction.php
│   │   ├── CreateAssetAction.php
│   │   ├── DisposeAssetAction.php
│   │   └── ScheduleMaintenanceAction.php
│   ├── Audits/
│   │   └── LogActivityAction.php
│   ├── Auth/
│   │   ├── AuthenticateUserAction.php
│   │   └── ResetPasswordAction.php
│   └── Inventory/
│       ├── AdjustStockAction.php
│       ├── CheckLowStockAlertAction.php
│       ├── ReceiveGoodsAction.php
│       ├── RecordStockLedgerEntryAction.php
│       └── TransferStockAction.php
│
├── Enums/                         # Strict PHP 8.4 Enums for states and statuses
│   ├── AssetDisposalMethod.php    # discarded, sold, donated, destroyed
│   ├── AssetStatus.php            # available, in_use, maintenance, disposed, lost
│   ├── AuditCategory.php          # auth, inventory, asset, approval, system, etc.
│   ├── AuditEventType.php         # create, update, delete, approve, reject, export
│   ├── ItemTransactionType.php    # in_purchase, in_adjustment, out_requisition, out_loss, etc.
│   ├── MaintenanceStatus.php      # scheduled, in_progress, completed, cancelled
│   ├── RequestPriority.php        # low, medium, high, urgent
│   └── RequestStatus.php          # draft, pending_l1, pending_l2, approved, rejected, cancelled
│
├── Events/                        # Domain events dispatched upon critical operations
│   ├── AssetCheckedOut.php
│   ├── AssetDisposed.php
│   ├── LowStockDetected.php
│   ├── RequestApproved.php
│   ├── RequestRejected.php
│   ├── RequestSubmitted.php
│   └── StockLedgerChanged.php
│
├── Exceptions/                    # Custom domain exceptions
│   ├── InsufficientStockException.php
│   ├── InvalidApprovalTransitionException.php
│   ├── LockedLocationException.php
│   └── SerialNumberCollisionException.php
│
├── Exports/                       # Maatwebsite Excel / CSV export classes
│   ├── Assets/
│   │   ├── AssetRegisterExport.php
│   │   ├── DepreciationScheduleExport.php
│   │   └── MaintenanceLogExport.php
│   └── Inventory/
│       ├── CurrentStockBalanceExport.php
│       ├── StockLedgerDetailExport.php
│       └── StockValuationExport.php
│
├── Http/
│   ├── Controllers/               # Thin Controllers returning Inertia responses
│   │   ├── Approvals/
│   │   │   ├── ApprovalHistoryController.php
│   │   │   └── PendingApprovalController.php
│   │   ├── Assets/
│   │   │   ├── AssetCategoryController.php
│   │   │   ├── AssetCheckInOutController.php
│   │   │   ├── AssetController.php
│   │   │   ├── AssetDisposalController.php
│   │   │   └── AssetMaintenanceController.php
│   │   ├── Audits/
│   │   │   └── AuditLogController.php
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php
│   │   │   └── PasswordResetController.php
│   │   ├── Dashboard/
│   │   │   └── DashboardController.php
│   │   ├── Inventory/
│   │   │   ├── ItemCategoryController.php
│   │   │   ├── ItemController.php
│   │   │   ├── LocationController.php
│   │   │   ├── StockAdjustmentController.php
│   │   │   ├── StockLedgerController.php
│   │   │   └── StockTransferController.php
│   │   ├── Reports/
│   │   │   ├── AssetReportController.php
│   │   │   └── InventoryReportController.php
│   │   ├── Requisitions/
│   │   │   └── RequisitionController.php
│   │   └── Settings/
│   │       ├── RolePermissionController.php
│   │       ├── SystemConfigurationController.php
│   │       └── UserController.php
│   │
│   ├── Middleware/                # Custom HTTP middleware
│   │   ├── HandleInertiaRequests.php  # Inertia shared data (auth, flash, permissions)
│   │   ├── EnsureAccountIsActive.php  # Check is_active == true
│   │   └── LogUserActivity.php       # Automatic request tracking
│   │
│   ├── Requests/                  # FormRequest validation rules
│   │   ├── Assets/
│   │   │   ├── CheckOutAssetRequest.php
│   │   │   ├── DisposeAssetRequest.php
│   │   │   ├── StoreAssetCategoryRequest.php
│   │   │   ├── StoreAssetRequest.php
│   │   │   └── UpdateAssetRequest.php
│   │   ├── Inventory/
│   │   │   ├── StockAdjustmentRequest.php
│   │   │   ├── StockTransferRequest.php
│   │   │   ├── StoreItemCategoryRequest.php
│   │   │   ├── StoreItemRequest.php
│   │   │   └── UpdateItemRequest.php
│   │   ├── Requisitions/
│   │   │   ├── ProcessApprovalRequest.php
│   │   │   └── StoreRequisitionRequest.php
│   │   └── Settings/
│   │       ├── StoreUserRequest.php
│   │       └── UpdateUserRequest.php
│   │
│   └── Resources/                 # Inertia JsonResource / Data Transformers
│       ├── AssetResource.php
│       ├── AuditLogResource.php
│       ├── ItemResource.php
│       ├── RequisitionResource.php
│       ├── StockLedgerResource.php
│       └── UserResource.php
│
├── Listeners/                     # Handlers reacting to domain events
│   ├── CreateAuditLogRecord.php
│   ├── NotifyApproverOnSubmission.php
│   ├── NotifyRequesterOnStatusChange.php
│   ├── TriggerLowStockNotification.php
│   └── UpdateStockSummaryCache.php
│
├── Models/                        # Eloquent models (No soft delete, using is_active)
│   ├── ApprovalWorkflow.php
│   ├── Asset.php
│   ├── AssetCategory.php
│   ├── AssetCheckout.php
│   ├── AssetDepreciation.php
│   ├── AssetDisposal.php
│   ├── AssetMaintenance.php
│   ├── AuditLog.php               # INSERT-ONLY immutable table
│   ├── Item.php
│   ├── ItemCategory.php
│   ├── Location.php               # 2-level hierarchy (parent_id)
│   ├── Requisition.php
│   ├── RequisitionItem.php
│   ├── StockAdjustment.php
│   ├── StockLedger.php            # INSERT-ONLY financial ledger
│   ├── SystemSetting.php
│   ├── User.php
│   └── Vendor.php
│
├── Notifications/                 # Database & Mail notifications (Mailpit in dev)
│   ├── LowStockAlertNotification.php
│   ├── MaintenanceDueNotification.php
│   ├── RequisitionActionNotification.php
│   └── RequisitionSubmittedNotification.php
│
├── Policies/                      # Authorization policies integrated with Spatie
│   ├── AssetCategoryPolicy.php
│   ├── AssetPolicy.php
│   ├── AuditLogPolicy.php
│   ├── ItemCategoryPolicy.php
│   ├── ItemPolicy.php
│   ├── LocationPolicy.php
│   ├── RequisitionPolicy.php
│   └── UserPolicy.php
│
├── Providers/
│   ├── AppServiceProvider.php
│   └── EventServiceProvider.php
│
└── Services/                      # Multi-step business workflow coordinators
    ├── Approval/
    │   ├── ApprovalEngineService.php
    │   └── WorkflowStepResolver.php
    ├── Asset/
    │   ├── AssetDepreciationService.php
    │   ├── AssetLifecycleService.php
    │   └── MaintenanceSchedulerService.php
    ├── Audit/
    │   └── AuditLoggerService.php
    ├── Inventory/
    │   ├── InventoryValuationService.php
    │   ├── StockBalanceCalculator.php
    │   └── StockLedgerWriter.php
    └── Reporting/
        ├── AssetReportService.php
        ├── InventoryReportService.php
        └── PdfReportGeneratorService.php
```

---

## 4. Database Layer (`database/`)

```
database/
├── factories/                     # Model factories for testing and seeding
│   ├── AssetCategoryFactory.php
│   ├── AssetFactory.php
│   ├── ItemCategoryFactory.php
│   ├── ItemFactory.php
│   ├── LocationFactory.php
│   ├── RequisitionFactory.php
│   └── UserFactory.php
│
├── migrations/                    # Sequential database schema definition
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2026_09_19_000001_create_permission_tables.php     # Spatie RBAC
│   ├── 2026_09_19_000002_create_locations_table.php
│   ├── 2026_09_19_000003_create_item_categories_table.php
│   ├── 2026_09_19_000004_create_items_table.php
│   ├── 2026_09_19_000005_create_stock_ledgers_table.php   # Immutable
│   ├── 2026_09_19_000006_create_stock_adjustments_table.php
│   ├── 2026_09_19_000007_create_asset_categories_table.php
│   ├── 2026_09_19_000008_create_assets_table.php
│   ├── 2026_09_19_000009_create_asset_checkouts_table.php
│   ├── 2026_09_19_000010_create_asset_maintenances_table.php
│   ├── 2026_09_19_000011_create_asset_depreciations_table.php
│   ├── 2026_09_19_000012_create_asset_disposals_table.php
│   ├── 2026_09_19_000013_create_requisitions_table.php
│   ├── 2026_09_19_000014_create_requisition_items_table.php
│   ├── 2026_09_19_000015_create_approval_workflows_table.php
│   ├── 2026_09_19_000016_create_audit_logs_table.php      # Immutable
│   └── 2026_09_19_000017_create_system_settings_table.php
│
└── seeders/                       # Deterministic data seeding
    ├── DatabaseSeeder.php         # Master coordinator
    ├── RoleAndPermissionSeeder.php # 6 roles & exact permission strings
    ├── SuperAdminUserSeeder.php   # Initial super admin account
    ├── SystemSettingSeeder.php    # Default configs (approval levels, etc.)
    ├── LocationSeeder.php         # Default warehouse & building hierarchy
    ├── CategorySeeder.php         # Default Item & Asset categories
    └── DemoDataSeeder.php         # Optional demo catalog & historical stock
```

---

## 5. Frontend Directory Structure (`resources/`)

```
resources/
├── css/
│   └── app.css                    # Tailwind directives & CSS variables (shadcn theme)
│
├── views/
│   ├── app.blade.php              # Inertia root layout template
│   └── reports/
│       ├── asset_pdf.blade.php    # DomPDF / Browsershot template
│       └── inventory_pdf.blade.php
│
└── js/
    ├── app.js                     # Inertia Vue 3 entry point & plugin registration
    │
    ├── Components/                # Reusable UI Components
    │   ├── Common/                # Generic utility components
    │   │   ├── ConfirmDialog.vue
    │   │   ├── EmptyState.vue
    │   │   ├── FileUpload.vue
    │   │   ├── LoadingSpinner.vue
    │   │   ├── PageHeader.vue
    │   │   ├── SearchInput.vue
    │   │   └── StatusBadge.vue
    │   │
    │   ├── DataTable/             # TanStack Table based datatable system
    │   │   ├── DataTable.vue
    │   │   ├── DataTableColumnHeader.vue
    │   │   ├── DataTableFacetedFilter.vue
    │   │   ├── DataTablePagination.vue
    │   │   └── DataTableViewOptions.vue
    │   │
    │   └── ui/                    # shadcn-vue base primitives
    │       ├── alert/
    │       ├── badge/
    │       ├── button/
    │       ├── card/
    │       ├── dialog/
    │       ├── dropdown-menu/
    │       ├── form/
    │       ├── input/
    │       ├── select/
    │       ├── table/
    │       ├── tabs/
    │       └── tooltip/
    │
    ├── Composables/               # Vue Composition API reusable hooks
    │   ├── useAuth.js             # Current user & role check helpers
    │   ├── useDebounce.js
    │   ├── useFilter.js           # Query params & filter state syncing
    │   ├── useFormatters.js       # Currency (IDR), dates, numbers
    │   └── usePermission.js       # 'can()' helper aligned with Spatie
    │
    ├── Layouts/                   # Page Shell Layouts
    │   ├── AppLayout.vue          # Main layout (Sidebar, Navbar, Breadcrumb, Content)
    │   ├── AuthLayout.vue         # Login & password recovery container
    │   └── Components/
    │       ├── NotificationBell.vue
    │       ├── Sidebar.vue
    │       ├── SidebarItem.vue
    │       └── UserDropdown.vue
    │
    ├── Pages/                     # Inertia Page Components
    │   ├── Approvals/
    │   │   ├── History.vue
    │   │   ├── Index.vue          # Pending approvals queue
    │   │   └── Show.vue           # Detail view with Approve/Reject modal
    │   │
    │   ├── Assets/
    │   │   ├── Categories/
    │   │   │   ├── CreateEditDialog.vue
    │   │   │   └── Index.vue
    │   │   ├── CheckInOut/
    │   │   │   ├── CheckInModal.vue
    │   │   │   ├── CheckOutModal.vue
    │   │   │   └── Index.vue
    │   │   ├── Create.vue
    │   │   ├── Disposal/
    │   │   │   ├── CreateModal.vue
    │   │   │   └── Index.vue
    │   │   ├── Edit.vue
    │   │   ├── Index.vue          # Asset registry table
    │   │   ├── Maintenance/
    │   │   │   ├── LogModal.vue
    │   │   │   └── ScheduleModal.vue
    │   │   └── Show.vue           # Asset 360 overview (specs, QR, history)
    │   │
    │   ├── Audits/
    │   │   ├── Index.vue          # Filterable audit log explorer
    │   │   └── Show.vue           # Diff viewer (before vs after values)
    │   │
    │   ├── Auth/
    │   │   ├── ForgotPassword.vue
    │   │   ├── Login.vue
    │   │   └── ResetPassword.vue
    │   │
    │   ├── Dashboard/
    │   │   ├── AdminDashboard.vue
    │   │   ├── Index.vue          # Role-based dashboard dispatcher
    │   │   ├── ManagerDashboard.vue
    │   │   └── StaffDashboard.vue
    │   │
    │   ├── Inventory/
    │   │   ├── Adjustments/
    │   │   │   ├── Create.vue
    │   │   │   └── Index.vue
    │   │   ├── Categories/
    │   │   │   └── Index.vue
    │   │   ├── Create.vue
    │   │   ├── Edit.vue
    │   │   ├── Index.vue          # Item master list & stock summary
    │   │   ├── Ledger/
    │   │   │   └── Index.vue      # Historical stock ledger entries
    │   │   ├── Locations/
    │   │   │   └── Index.vue      # Location hierarchy tree view
    │   │   ├── Show.vue           # Item detail, movements, barcode
    │   │   └── Transfers/
    │   │       ├── Create.vue
    │   │       └── Index.vue
    │   │
    │   ├── Reports/
    │   │   ├── AssetReports.vue
    │   │   ├── Index.vue
    │   │   └── InventoryReports.vue
    │   │
    │   ├── Requisitions/
    │   │   ├── Create.vue
    │   │   ├── Index.vue
    │   │   └── Show.vue
    │   │
    │   └── Settings/
    │       ├── Locations/
    │       ├── Permissions/
    │       │   └── Index.vue      # Matrix management
    │       ├── System/
    │       │   └── Index.vue      # Workflow configuration & general settings
    │       └── Users/
    │           ├── CreateEditModal.vue
    │           └── Index.vue
    │
    └── lib/                       # Frontend helper utilities
        └── utils.js               # clsx, twMerge (shadcn utility)
```

---

## 6. Routes & Web Entry (`routes/`)

```
routes/
├── console.php                    # Artisan schedule & console commands
└── web.php                        # Main web application routes
```

### Route Organization Structure (`routes/web.php` breakdown):
```php
// 1. Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// 2. Authenticated & Active routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory Domain
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('items', ItemController::class);
        Route::resource('categories', ItemCategoryController::class);
        Route::resource('locations', LocationController::class);
        Route::get('ledger', [StockLedgerController::class, 'index'])->name('ledger.index');
        Route::resource('adjustments', StockAdjustmentController::class);
        Route::resource('transfers', StockTransferController::class);
    });

    // Asset Domain
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::resource('categories', AssetCategoryController::class);
        Route::resource('items', AssetController::class);
        Route::post('items/{asset}/checkout', [AssetCheckInOutController::class, 'checkOut'])->name('items.checkout');
        Route::post('items/{asset}/checkin', [AssetCheckInOutController::class, 'checkIn'])->name('items.checkin');
        Route::resource('maintenances', AssetMaintenanceController::class);
        Route::resource('disposals', AssetDisposalController::class);
    });

    // Approvals Domain
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [PendingApprovalController::class, 'index'])->name('index');
        Route::get('/history', [ApprovalHistoryController::class, 'index'])->name('history');
        Route::post('/{requisition}/approve', [PendingApprovalController::class, 'approve'])->name('approve');
        Route::post('/{requisition}/reject', [PendingApprovalController::class, 'reject'])->name('reject');
    });

    // Audit Log Domain
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{log}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Reports Domain
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('inventory', [InventoryReportController::class, 'index'])->name('inventory');
        Route::get('inventory/export', [InventoryReportController::class, 'export'])->name('inventory.export');
        Route::get('assets', [AssetReportController::class, 'index'])->name('assets');
        Route::get('assets/export', [AssetReportController::class, 'export'])->name('assets.export');
    });

    // Settings Domain (Restricted to Super Admin / Admin)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RolePermissionController::class);
        Route::get('system', [SystemConfigurationController::class, 'index'])->name('system.index');
        Route::post('system', [SystemConfigurationController::class, 'update'])->name('system.update');
    });
});
```

---

## 7. Docker Directory Structure (`docker/` & `compose.yaml`)

```
docker/
├── nginx/
│   └── default.conf               # FastCGI forwarding, security headers, gzip
├── php/
│   ├── Dockerfile                 # PHP 8.4-FPM + BCMath, Intl, Redis, PDO MySQL, GD
│   ├── local.ini                  # Memory limit (512M), upload max (50M), execution time
│   └── opcache.ini                # JIT & Opcache settings for dev/prod
└── mysql/
    └── my.cnf                     # UTF8mb4, innodb buffer pool, slow query log

compose.yaml                       # Root Docker Compose file
# Services:
#   1. app (PHP 8.4-FPM)
#   2. webserver (Nginx 1.25 Alpine)
#   3. db (MySQL 8.0)
#   4. cache (Redis 7.2 Alpine)
#   5. mailpit (Mailpit for local email testing)
#   6. phpmyadmin (Web GUI for database inspection)
```

---

## 8. Test Suite Organization (`tests/`)

Pest PHP testing framework struktur:

```
tests/
├── Architecture/                  # Architectural constraint enforcement
│   ├── ArchitectureTest.php       # Enforce Models don't call external APIs, Controllers stay thin
│   └── SecurityTest.php           # Enforce no raw SQL queries without parameterization
│
├── Feature/                       # Feature integration tests
│   ├── Approvals/
│   │   ├── MultiLevelApprovalTest.php
│   │   └── RejectionFlowTest.php
│   ├── Assets/
│   │   ├── AssetCheckInOutTest.php
│   │   ├── AssetDisposalWorkflowTest.php
│   │   └── StraightLineDepreciationTest.php
│   ├── Audits/
│   │   ├── ImmutableAuditLogTest.php
│   │   └── SensitiveAttributeMaskingTest.php
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   └── InactiveUserLockoutTest.php
│   ├── Inventory/
│   │   ├── EventBasedLedgerTest.php
│   │   ├── StockAdjustmentIntegrityTest.php
│   │   └── StockTransferTest.php
│   └── Reports/
│       ├── ExportSecurityTest.php
│       └── ReportCalculationTest.php
│
├── Unit/                          # Isolated unit tests
│   ├── Actions/
│   │   ├── CalculateDepreciationActionTest.php
│   │   └── RecordStockLedgerEntryActionTest.php
│   └── Services/
│       ├── ApprovalEngineTest.php
│       └── StockBalanceCalculatorTest.php
│
├── TestCase.php                   # Base test case setup
└── Pest.php                       # Pest configuration & helper bindings
```

---

## 9. File Naming & Coding Conventions

| Type | Directory | Convention | Example |
|---|---|---|---|
| **Controller** | `app/Http/Controllers/` | PascalCase + `Controller` suffix | `ItemController.php` |
| **Model** | `app/Models/` | PascalCase, singular | `StockLedger.php` |
| **Service** | `app/Services/` | PascalCase + `Service` suffix | `StockLedgerWriter.php` |
| **Action** | `app/Actions/` | PascalCase + Verb + `Action` | `AdjustStockAction.php` |
| **Form Request** | `app/Http/Requests/` | PascalCase + Verb + Model + `Request` | `StoreItemRequest.php` |
| **Enum** | `app/Enums/` | PascalCase, singular | `AssetStatus.php` |
| **Event** | `app/Events/` | PascalCase, past tense / state | `StockLedgerChanged.php` |
| **Listener** | `app/Listeners/` | PascalCase, active verb phrase | `UpdateStockSummaryCache.php` |
| **Migration** | `database/migrations/` | `YYYY_MM_DD_HHMMSS_create_table_name_table.php` | `2026_09_19_000005_create_stock_ledgers_table.php` |
| **Vue Page** | `resources/js/Pages/` | PascalCase `.vue` | `Index.vue`, `Show.vue` |
| **Vue Component** | `resources/js/Components/` | PascalCase `.vue` | `StatusBadge.vue` |
| **Composable** | `resources/js/Composables/` | camelCase prefixed with `use` | `usePermission.js` |
| **Pest Test** | `tests/Feature/`, `tests/Unit/` | PascalCase + `Test.php` suffix | `EventBasedLedgerTest.php` |

---

## 10. Phase 04 Completion Checklist

- [x] `architecture/SYSTEM_ARCHITECTURE.md` (System overview, stack, security, ADR references)
- [x] `architecture/MODULE_ARCHITECTURE.md` (Domain breakdown, service patterns, flow diagrams)
- [x] `architecture/DATABASE_GUIDELINE.md` (Conventions, indexing, zero soft-delete, ledger rules)
- [x] `architecture/FOLDER_STRUCTURE.md` (Full repository tree, backend, frontend, docker, tests)

---
*Next Phase: Phase 05 — Database Design (`docs/05_DATABASE.md`)*
