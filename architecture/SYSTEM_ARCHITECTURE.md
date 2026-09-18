# SYSTEM ARCHITECTURE
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18
> **Phase**: 04 — Architecture Design

---

## 1. ARCHITECTURE OVERVIEW

Sistem ini menggunakan arsitektur **Monolithic Web Application** dengan pendekatan **Server-Side Rendering via Inertia.js**.

```
┌─────────────────────────────────────────────────────────┐
│                      DOCKER NETWORK                      │
│                                                         │
│  ┌──────────┐    ┌──────────────┐    ┌───────────────┐ │
│  │  Browser │───▶│    Nginx     │───▶│  PHP-FPM 8.4  │ │
│  │  (Vue 3) │    │  (Port 80)   │    │  Laravel 13   │ │
│  └──────────┘    └──────────────┘    └───────┬───────┘ │
│                                              │          │
│                         ┌────────────────────┼───────┐  │
│                         │                   │       │  │
│                    ┌────▼────┐         ┌────▼────┐  │  │
│                    │ MySQL   │         │  Redis  │  │  │
│                    │  8.0    │         │         │  │  │
│                    └─────────┘         └─────────┘  │  │
│                                                      │  │
│  ┌──────────────┐    ┌──────────────┐               │  │
│  │  phpMyAdmin  │    │   Mailpit    │               │  │
│  │  (Port 8081) │    │  (Port 8025) │               │  │
│  └──────────────┘    └──────────────┘               │  │
│                                                      │  │
│  ┌──────────────────────────────────────────────────┘  │
│  │  Queue Worker (php artisan queue:work via Redis)     │
│  └──────────────────────────────────────────────────────│
└─────────────────────────────────────────────────────────┘
```

---

## 2. TECHNOLOGY STACK

### Backend
| Komponen | Teknologi | Versi |
|---|---|---|
| Framework | Laravel | 13 |
| Runtime | PHP | 8.4 |
| Web Server | Nginx | Latest Stable |
| PHP Handler | PHP-FPM | 8.4 |
| OPcache | PHP OPcache | Built-in PHP |

### Frontend
| Komponen | Teknologi |
|---|---|
| Bridge | Inertia.js |
| Framework | Vue 3 (Composition API) |
| Build Tool | Vite |
| CSS | Tailwind CSS |
| UI Components | shadcn-vue |
| DataTable | TanStack Table (@tanstack/vue-table) |
| Icons | Lucide Vue Next |

### Data Layer
| Komponen | Teknologi | Versi | Kegunaan |
|---|---|---|---|
| Database | MySQL | 8.0 | Primary data store |
| Cache | Redis | 7.x | Query cache, result cache |
| Session | Redis | 7.x | User session storage |
| Queue | Redis | 7.x | Background job queue |

### Development Tools
| Tool | Kegunaan |
|---|---|
| Docker + Compose | Container orchestration |
| phpMyAdmin | Database GUI |
| Mailpit | Email testing |
| Laravel Telescope | Debug & monitoring |
| Pest PHP | Testing framework |
| GitHub | Version control |

### Key Packages
| Package | Kegunaan |
|---|---|
| spatie/laravel-permission | RBAC (Role & Permission) |
| maatwebsite/excel | Export Excel & CSV |
| barryvdh/laravel-dompdf | Export PDF |
| laravel/telescope | Development debugging |

---

## 3. REQUEST LIFECYCLE

```
Browser (Vue 3)
    │
    │ HTTP Request (GET/POST/PUT/DELETE)
    │ Inertia header: X-Inertia: true
    ▼
Nginx
    │ Forward ke PHP-FPM (Unix socket / TCP)
    ▼
PHP-FPM (Laravel)
    │
    ├─▶ Middleware Stack
    │     ├── HandleInertiaRequests
    │     ├── Authentication (session check)
    │     ├── Role/Permission Check (Spatie)
    │     └── Rate Limiting (Redis)
    │
    ├─▶ Router → Controller
    │
    ├─▶ Form Request Validation
    │
    ├─▶ Service / Action
    │     ├── Business Logic
    │     └── Database Transaction
    │
    ├─▶ Eloquent ORM → MySQL
    │
    ├─▶ Cache Check/Set → Redis
    │
    └─▶ Inertia Response
          │ return Inertia::render('PageName', [data])
          │ atau return redirect()->back()
          ▼
      Vue 3 Component (re-renders on client)
```

---

## 4. INERTIA.JS ARCHITECTURE

Inertia.js bertindak sebagai **glue layer** antara Laravel backend dan Vue 3 frontend.

### Cara Kerja
```
Full Page Load (pertama kali):
  Browser → Nginx → Laravel → Blade (index.blade.php)
  Blade merender HTML dasar + inject Vue app
  Vue app mount → Inertia load page component

Subsequent Navigation (SPA-like):
  Vue Link click → Inertia intercept
  → XHR request ke Laravel dengan header X-Inertia: true
  → Laravel return JSON: { component, props, url }
  → Inertia swap Vue component tanpa full reload
```

### Data Flow
```
Laravel Controller:
  return Inertia::render('Inventory/StockList', [
      'stocks' => StockResource::collection($stocks),
      'locations' => LocationResource::collection($locations),
      'filters' => request()->only(['location', 'item']),
  ]);

Vue Component (resources/js/pages/Inventory/StockList.vue):
  const props = defineProps({
      stocks: Object,
      locations: Array,
      filters: Object,
  });
```

---

## 5. AUTHENTICATION ARCHITECTURE

**Approach**: Session-based authentication (tidak menggunakan API token)

```
Login Request
    │
    ▼
AuthController@login
    │
    ├── Validate credentials (email, password)
    ├── Auth::attempt() → check users table
    ├── Generate session (disimpan di Redis)
    ├── Catat audit log: login event
    └── Redirect ke Dashboard (Inertia)

Session Storage: Redis
Session Driver: redis (config/session.php)
Session Lifetime: configurable via .env

Middleware:
  auth → cek session valid
  verified → cek email verified (jika diaktifkan)
```

---

## 6. RBAC ARCHITECTURE

Menggunakan **Spatie Laravel Permission** dengan model:

```
User ──(has many)──▶ Roles ──(has many)──▶ Permissions
                              └──────────────────────────▶ Direct Permissions

Middleware (Route Level):
  route::middleware(['auth', 'role:Admin|Manager'])
  route::middleware(['auth', 'permission:inventory.stock-in.create'])

Policy (Business Logic Level):
  Cek kepemilikan data (contoh: hanya pembuat yang bisa cancel)
  Cek kondisi bisnis (contoh: aset In Maintenance tidak bisa dimutasi)
```

### Permission Naming Convention
```
{resource}.{action}

Contoh:
  location.view
  location.create
  location.edit
  location.toggle-status
  inventory.stock-in.create
  inventory.opname.confirm
  asset.assign
  asset.dispose
  approval.review.level-1
  approval.approve.level-2
  report.inventory.view
  report.export
  audit.view
  user.assign-role
```

---

## 7. APPROVAL WORKFLOW ARCHITECTURE

```
TransactionController@store
    │
    ├── Validate input
    ├── DB::transaction()
    │     ├── Buat record transaksi (status: draft)
    │     ├── ApprovalService::initiate(transaction, type)
    │     │     ├── Baca approval_flow_configs untuk type ini
    │     │     ├── Buat approval_request (status: pending, level: 1)
    │     │     └── Buat approval_steps sesuai konfigurasi level
    │     └── Commit
    │
    └── Inertia redirect ke detail transaksi

ApprovalService::approve(approval_request, approver, level)
    │
    ├── Validasi: approver punya role untuk level ini
    ├── Update approval_step (level ini) → status: approved
    ├── Cek: apakah ini level terakhir?
    │     ├── BUKAN terakhir → advance ke level berikutnya
    │     │     current_level++ → notifikasi approver berikutnya
    │     └── TERAKHIR → approval_request.status = approved
    │                     Execute transaction
    │                     Catat audit log
    └── Return result
```

---

## 8. STOCK LEDGER ARCHITECTURE

```
Stock Calculation:
  SELECT SUM(quantity)
  FROM stock_ledger
  WHERE item_id = ? AND location_id = ?

Optimization Strategy:
  - Index: (item_id, location_id, created_at)
  - Redis cache untuk hasil SUM (invalidate saat ada ledger entry baru)
  - Cache key: "stock:{item_id}:{location_id}"
  - Cache TTL: pendek (30-60 detik) untuk balance antara performa dan akurasi

Race Condition Prevention:
  DB::transaction(function() {
      // SELECT FOR UPDATE pada stock check
      $stock = StockLedger::where(...)->lockForUpdate()->sum('quantity');
      if ($stock < $requested_qty) throw new InsufficientStockException();
      // Buat ledger entry
  });
```

---

## 9. QUEUE ARCHITECTURE

Digunakan untuk proses yang berpotensi lambat/berat:

```
Kandidat Background Jobs:
  - Export laporan besar (PDF/Excel) → ExportReportJob
  - Notifikasi email approval → SendApprovalNotificationJob
  - Notifikasi stok minimum → StockMinimumAlertJob
  - Notifikasi garansi berakhir → WarrantyExpiryAlertJob

Queue Driver: Redis
Queue Worker: php artisan queue:work --queue=default,exports,notifications

Docker: Queue worker sebagai process tambahan di container
        (via Supervisor di dalam container PHP-FPM)
```

---

## 10. AUDIT LOG ARCHITECTURE

```
Implementasi: Laravel Observer + Custom Event

Model Observer:
  ItemObserver → created, updated
  LocationObserver → created, updated
  AssetObserver → created, updated, status_changed

Event Listener:
  StockTransactionCreated → catat ledger entry
  ApprovalActioned → catat approval action
  UserLoggedIn → catat login
  ReportExported → catat export

AuditLog Entry:
  [
    user_id: 1,
    event: 'created',
    auditable_type: 'App\Models\Asset',
    auditable_id: 42,
    old_values: null,
    new_values: { name: 'Laptop Dell', status: 'active', ... },
    ip_address: '192.168.1.1',
    user_agent: 'Mozilla/5.0...',
    created_at: '2025-01-15 10:30:00'
  ]
```

---

## 11. CACHING STRATEGY

```
Cache Driver: Redis
Cache Prefix: "inv_asset:" (per environment)

Data yang Di-cache:
  1. Stock terkini per item per lokasi
     Key: "stock:{item_id}:{location_id}"
     TTL: 60 detik
     Invalidate: saat ada stock_ledger entry baru

  2. Permission list per user
     Key: "permissions:user:{user_id}"
     TTL: 10 menit
     Invalidate: saat role/permission user diubah

  3. Konfigurasi approval flow
     Key: "approval_config:{transaction_type}"
     TTL: 1 jam
     Invalidate: saat konfigurasi diubah Super Admin

  4. Laporan yang di-generate
     Key: "report:{type}:{hash(filters)}"
     TTL: 5 menit
     Invalidate: tidak (expire saja)
```

---

## 12. SECURITY ARCHITECTURE

```
Authentication:
  ✅ Session-based (tidak ada JWT/token yang rawan bocor di URL)
  ✅ CSRF protection (Laravel default)
  ✅ Rate limiting login (via Redis)

Authorization:
  ✅ Middleware: cek role/permission sebelum controller
  ✅ Policy: cek business rule di dalam controller
  ✅ Form Request: validasi input sebelum logic

Data Protection:
  ✅ Mass Assignment: $fillable explicit di setiap Model
  ✅ SQL Injection: Eloquent ORM + parameter binding
  ✅ XSS: Blade {{ }} auto-escape, Inertia props sanitized
  ✅ Sensitive data: password di-hash (bcrypt), tidak pernah di-log

Audit:
  ✅ Semua aksi penting tercatat di audit_logs
  ✅ Failed login dicatat
  ✅ IP address dan user agent direkam
```

---

## 13. DOCKER ARCHITECTURE

```
Services:
  app (PHP-FPM 8.4)
    Image: custom (FROM php:8.4-fpm)
    Volumes: ./:/var/www/html
    Networks: app-network

  nginx
    Image: nginx:alpine
    Ports: 80:80
    Depends: app
    Networks: app-network

  mysql
    Image: mysql:8.0
    Ports: 3306:3306 (optional, untuk koneksi langsung)
    Volumes: mysql-data:/var/lib/mysql
    Networks: app-network

  redis
    Image: redis:7-alpine
    Ports: 6379:6379 (optional)
    Networks: app-network

  mailpit
    Image: axllent/mailpit
    Ports: 8025:8025 (UI), 1025:1025 (SMTP)
    Networks: app-network

  phpmyadmin
    Image: phpmyadmin/phpmyadmin
    Ports: 8081:80
    Depends: mysql
    Networks: app-network

Volumes:
  mysql-data (persistent MySQL data)
  redis-data (optional persistent Redis)

Networks:
  app-network (bridge, internal Docker network)
```
