# DATABASE GUIDELINE
## Inventory & Asset Management System

> **Status**: DRAFT
> **Versi**: 1.0
> **Tanggal**: 2026-09-18

---

## 1. DATABASE ENGINE & CHARSET

```sql
Engine:    InnoDB (mendukung foreign key, transaction, row-level locking)
Charset:   utf8mb4
Collation: utf8mb4_unicode_ci
MySQL:     8.0+
```

---

## 2. NAMING CONVENTION

| Elemen | Convention | Contoh |
|---|---|---|
| Table | snake_case, plural | `locations`, `stock_ledger`, `asset_assignments` |
| Column | snake_case | `parent_id`, `is_active`, `created_at` |
| Primary Key | `id` (BIGINT UNSIGNED AUTO_INCREMENT) | `id` |
| Foreign Key | `{table_singular}_id` | `location_id`, `item_id` |
| Boolean | `is_{state}` atau `can_{action}` | `is_active`, `can_be_asset` |
| Timestamp | `{event}_at` | `created_at`, `approved_at`, `deleted_at` |
| Enum column | nama deskriptif | `status`, `condition`, `method` |
| Index | `idx_{table}_{column(s)}` | `idx_items_category_id` |
| Unique Index | `uniq_{table}_{column}` | `uniq_items_item_code` |
| FK Constraint | `fk_{table}_{column}` | `fk_items_category_id` |

---

## 3. STANDARD COLUMNS

Semua tabel menggunakan kolom standar berikut:

```php
// Minimal untuk tabel transaksional:
$table->id();               // BIGINT UNSIGNED AUTO_INCREMENT PK
$table->timestamps();       // created_at, updated_at

// Tabel yang butuh soft delete (TIDAK DIGUNAKAN — kita pakai is_active):
// $table->softDeletes();

// Tabel master data (lokasi, item, kategori, dll.):
$table->boolean('is_active')->default(true);

// Tabel transaksi (butuh tahu siapa yang membuat):
$table->foreignId('created_by')->constrained('users');
```

---

## 4. INDEXING STRATEGY

### 4.1 Prinsip Indexing

| Prinsip | Penjelasan |
|---|---|
| Index foreign key | Semua kolom FK harus diindex |
| Index filter columns | Kolom yang sering digunakan di WHERE |
| Index sort columns | Kolom yang sering digunakan di ORDER BY |
| Composite index | Untuk query multi-kolom yang sering bersama |
| Avoid over-indexing | Terlalu banyak index memperlambat INSERT/UPDATE |

### 4.2 Index Per Tabel Penting

#### `locations`
```sql
INDEX idx_locations_parent_id (parent_id)
INDEX idx_locations_is_active (is_active)
```

#### `items`
```sql
UNIQUE idx_items_item_code (item_code)
INDEX idx_items_category_id (category_id)
INDEX idx_items_is_active (is_active)
INDEX idx_items_can_be_inventory (can_be_inventory)
INDEX idx_items_can_be_asset (can_be_asset)
```

#### `stock_ledger`
```sql
INDEX idx_stock_ledger_item_location (item_id, location_id)
INDEX idx_stock_ledger_item_location_date (item_id, location_id, created_at)
INDEX idx_stock_ledger_transaction_type (transaction_type)
INDEX idx_stock_ledger_reference (reference_type, reference_id)
```

#### `assets`
```sql
UNIQUE idx_assets_asset_code (asset_code)
UNIQUE idx_assets_serial_number (serial_number)   -- nullable unique
INDEX idx_assets_item_id (item_id)
INDEX idx_assets_location_id (location_id)
INDEX idx_assets_assigned_to (assigned_to)
INDEX idx_assets_status (status)
INDEX idx_assets_condition (condition)
INDEX idx_assets_warranty_end (warranty_end)      -- untuk garansi alert
```

#### `asset_assignments`
```sql
INDEX idx_asset_assignments_asset_id (asset_id)
INDEX idx_asset_assignments_user_id (user_id)
INDEX idx_asset_assignments_active (asset_id, returned_at)  -- returned_at NULL = active
```

#### `approval_requests`
```sql
INDEX idx_approval_requests_transaction (transaction_type, transaction_id)
INDEX idx_approval_requests_status (status)
INDEX idx_approval_requests_created_by (created_by)
```

#### `approval_steps`
```sql
INDEX idx_approval_steps_request_level (approval_request_id, level)
INDEX idx_approval_steps_approver (approver_id, status)
```

#### `audit_logs`
```sql
INDEX idx_audit_logs_user_id (user_id)
INDEX idx_audit_logs_auditable (auditable_type, auditable_id)
INDEX idx_audit_logs_created_at (created_at)
COMPOSITE INDEX idx_audit_logs_auditable_date (auditable_type, auditable_id, created_at)
```

#### `stock_opnames`
```sql
INDEX idx_stock_opnames_location_status (location_id, status)
```

---

## 5. FOREIGN KEY RULES

```
ON DELETE:
  RESTRICT  → Default. Mencegah delete jika ada referensi (paling aman)
  CASCADE   → HINDARI untuk data penting — bisa menghapus data tanpa sadar
  SET NULL  → Gunakan jika FK boleh null setelah parent dihapus

ON UPDATE:
  CASCADE   → OK untuk FK yang mengikuti PK

Contoh:
  locations.parent_id → locations.id (ON DELETE RESTRICT, ON UPDATE CASCADE)
  items.category_id   → categories.id (ON DELETE RESTRICT)
  assets.location_id  → locations.id (ON DELETE RESTRICT)
```

---

## 6. ENUM vs LOOKUP TABLE

### Gunakan Enum (PHP Enum + DB Enum) jika:
- Nilai bersifat tetap dan jarang berubah
- Nilai memiliki makna bisnis yang spesifik
- Tidak perlu ditampilkan atau dikelola oleh user

```php
// Contoh: status aset tidak akan berubah
$table->enum('status', ['active', 'inactive', 'in_maintenance', 'disposed', 'lost']);
$table->enum('condition', ['good', 'minor_damage', 'major_damage', 'needs_repair', 'scrapped']);
$table->enum('disposal_method', ['discarded', 'sold', 'donated', 'destroyed']);
```

### Gunakan Lookup Table jika:
- Nilai bisa bertambah/dikurangi oleh user/admin
- Diperlukan label yang bisa dikustomisasi
- Ada atribut tambahan per nilai

```php
// Contoh: kategori bisa ditambah oleh admin
// → gunakan tabel categories
```

---

## 7. SOFT DELETE POLICY

**Keputusan: TIDAK menggunakan Soft Delete (`deleted_at`)**

Alasan:
- Lokasi: cukup dengan `is_active = false`
- Item: cukup dengan `is_active = false`
- Transaksi: tidak boleh dihapus (audit trail)
- Audit log: INSERT ONLY, tidak pernah dihapus

Pengecualian: jika ada kebutuhan spesifik di masa mendatang, tambahkan per tabel.

---

## 8. TRANSACTION RULES

Gunakan `DB::transaction()` untuk operasi yang melibatkan lebih dari satu tabel:

```php
// ✅ BENAR — semua atau tidak sama sekali
DB::transaction(function() {
    $ledger = StockLedger::create([...]);
    StockRequest::where('id', $id)->update(['status' => 'executed']);
});

// ❌ SALAH — tidak di-wrap transaction
StockLedger::create([...]);
StockRequest::where('id', $id)->update(['status' => 'executed']);
// Jika yang kedua gagal, ledger sudah terbuat tapi request belum diupdate
```

### Operasi yang WAJIB menggunakan transaction:
- Semua eksekusi transaksi setelah approval
- Stock opname confirmation
- Asset assignment / return / disposal
- Setiap operasi yang mengubah lebih dari 1 tabel

---

## 9. LOCKING STRATEGY

Gunakan **Pessimistic Locking** untuk mencegah race condition pada stock:

```php
// Cek stok dengan lock sebelum transaksi keluar
DB::transaction(function() use ($item_id, $location_id, $qty) {
    $current_stock = StockLedger::where('item_id', $item_id)
        ->where('location_id', $location_id)
        ->lockForUpdate()        // SELECT ... FOR UPDATE
        ->sum('quantity');

    if ($current_stock < $qty) {
        throw new InsufficientStockException();
    }

    StockLedger::create([
        'item_id'     => $item_id,
        'location_id' => $location_id,
        'quantity'    => -$qty,  // negatif = keluar
        // ...
    ]);
});
```

---

## 10. QUERY PERFORMANCE GUIDELINES

### 10.1 Hindari SELECT *
```php
// ❌ HINDARI
$items = Item::all();

// ✅ GUNAKAN
$items = Item::select('id', 'item_code', 'name', 'is_active')->get();
```

### 10.2 Eager Loading (Hindari N+1)
```php
// ❌ N+1 — query terpisah per item
$assets = Asset::all();
foreach ($assets as $asset) {
    echo $asset->location->name;  // N query tambahan!
}

// ✅ Eager Loading
$assets = Asset::with(['location', 'item', 'assignedTo'])->get();
```

### 10.3 Pagination
```php
// ✅ Selalu paginate untuk daftar data
$items = Item::with('category')->paginate(25);

// ❌ HINDARI untuk daftar besar
$items = Item::with('category')->get();
```

### 10.4 WhereHas vs Join
```php
// WhereHas — lebih readable, untuk filter dengan kondisi relasi
$assets = Asset::whereHas('item', fn($q) => $q->where('category_id', $categoryId))->get();

// Join — lebih efisien untuk query dengan banyak kolom dari relasi
$assets = Asset::join('items', 'assets.item_id', '=', 'items.id')
    ->select('assets.*', 'items.name as item_name')
    ->where('items.category_id', $categoryId)
    ->get();
```

### 10.5 Chunking untuk Data Besar
```php
// ✅ Gunakan chunk untuk proses data besar (export, batch update)
Item::chunk(100, function($items) {
    foreach ($items as $item) {
        // proses per batch
    }
});
```

---

## 11. MIGRATION GUIDELINES

```php
// ✅ Setiap migration harus punya method down() yang akurat
public function down(): void
{
    Schema::dropIfExists('locations');
}

// ✅ Urutan migration harus memperhatikan dependency
// Buat tabel parent sebelum tabel child yang punya FK

// ✅ Gunakan comment pada kolom penting
$table->string('item_code')->unique()->comment('Auto-generated item code');

// ✅ Tes rollback setelah buat migration
// php artisan migrate:rollback
// php artisan migrate

// ✅ Setelah production (jika ada), JANGAN edit migration lama
// Buat migration baru untuk perubahan schema
```

---

## 12. SEEDER & FACTORY GUIDELINES

```php
// Seeder untuk data master yang WAJIB ada:
DatabaseSeeder → RolePermissionSeeder → SuperAdminSeeder

// Factory untuk testing:
ItemFactory, LocationFactory, UserFactory, AssetFactory

// JANGAN gunakan production seeder untuk testing
// Gunakan Factory + RefreshDatabase di test
```

---

## 13. DATABASE SCHEMA OVERVIEW

### Master Data Tables
```
departments         → Master departemen karyawan
categories          → Master kategori barang
units               → Master satuan barang
locations           → Lokasi operasional (hierarki 2 level)
items               → Master barang (inventory & aset)
item_specifications → Spesifikasi key-value per item
```

### User & RBAC Tables
```
users               → User system (dengan profil karyawan)
roles               → Daftar role (Spatie)
permissions         → Daftar permission (Spatie)
model_has_roles     → User ↔ Role (Spatie)
model_has_permissions → User ↔ Permission direct (Spatie)
role_has_permissions → Role ↔ Permission (Spatie)
```

### Inventory Tables
```
purchase_orders      → Dokumen PO
purchase_order_items → Item dalam PO
goods_receipts       → Dokumen penerimaan barang dari PO
goods_receipt_items  → Item yang diterima
direct_receipts      → Penerimaan langsung tanpa PO
direct_receipt_items → Item dalam direct receipt
stock_requests       → Permintaan barang oleh Requester
stock_request_items  → Item yang diminta
stock_transfers      → Transfer barang antar lokasi
stock_transfer_items → Item dalam transfer
stock_disposals      → Disposal barang
stock_disposal_items → Item yang di-dispose
stock_adjustments    → Koreksi stok
stock_adjustment_items → Item yang dikoreksi
stock_opnames        → Sesi stock opname
stock_opname_items   → Item dalam opname
stock_ledger         → Ledger semua perubahan stok (event-based)
```

### Asset Tables
```
assets              → Data aset individual
asset_assignments   → Histori pemegang aset
asset_mutations     → Histori perpindahan lokasi aset
asset_returns       → Dokumen pengembalian aset
asset_maintenances  → Riwayat maintenance aset
asset_disposals     → Dokumen disposal aset
```

### Approval Tables
```
approval_flow_configs → Konfigurasi level approval per jenis transaksi
approval_requests     → Permintaan approval per transaksi
approval_steps        → Langkah approval per level
```

### System Tables
```
audit_logs          → Log semua aktivitas sistem
```
