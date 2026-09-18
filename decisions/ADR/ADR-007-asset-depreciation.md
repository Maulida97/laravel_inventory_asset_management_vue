# ADR-007: Straight-Line Depreciation Engine for Fixed Assets

- **Status**: Accepted
- **Deciders**: Software Architect, Financial Accounting Advisor
- **Date**: 2026-09-18
- **Technical Story**: Depreciation calculation formula, schedule generation, and book value tracking.

---

## Context and Problem Statement

Aset tetap perusahaan (laptop, kendaraan operasional, perabot kantor, mesin) mengalami penurunan nilai seiring berjalannya waktu. Sistem harus mampu menghitung nilai buku terkini (*Current Book Value*) secara konsisten untuk keperluan pelaporan neraca aset dan penentuan harga dasar jika aset hendak dijual saat disposal.

## Decision Drivers

- Standar Akuntansi Keuangan (PSAK/IFRS) yang umum digunakan untuk aset operasional non-spesialis.
- Keterprediksian matematis yang transparan dan mudah diaudit.
- Otomasi pembukuan susut per bulan atau per tahun melalui task scheduler.

## Considered Options

1. **Option A**: Metode Garis Lurus (*Straight-Line Depreciation*).
2. **Option B**: Metode Saldo Menurun Ganda (*Double Declining Balance*).
3. **Option C**: Metode Jumlah Angka Tahun (*Sum-of-the-Years-Digits*).

## Decision Outcome

**Chosen Option**: **Option A (Straight-Line Depreciation / Garis Lurus)**.

Rumus baku yang diimplementasikan:
$$\text{Depreciation Per Year} = \frac{\text{Purchase Cost} - \text{Salvage Value}}{\text{Useful Life in Years}}$$
$$\text{Depreciation Per Month} = \frac{\text{Depreciation Per Year}}{12}$$

### Atribut Utama pada Tabel `assets`:
- `purchase_cost`: Biaya perolehan awal.
- `useful_life_years`: Estimasi masa pakai dalam tahun (contoh: 4 tahun untuk IT Hardware).
- `salvage_value`: Estimasi nilai residu di akhir masa pakai (contoh: Rp 0 atau Rp 500.000).
- `current_book_value`: Nilai buku saat ini (`purchase_cost - accumulated_depreciation`).
- Riwayat susut periodik dicatat pada tabel `asset_depreciations`.

### Consequences

- **Good**: Sangat mudah diverifikasi dan dipahami oleh seluruh stakeholder manajemen.
- **Good**: Jadwal penyusutan dapat diproyeksikan ke depan secara instan dalam bentuk tabel dan grafik di UI.
- **Bad**: Tidak mengakomodasi penyusutan aset yang mengalami keausan akseleratif di tahun-tahun awal (seperti kendaraan berat tertentu).
