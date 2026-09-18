# ADR-010: Testing Framework Selection with Pest PHP

- **Status**: Accepted
- **Deciders**: QA Lead, Lead Architect
- **Date**: 2026-09-18
- **Technical Story**: Automated testing framework, architectural testing, and test conventions.

---

## Context and Problem Statement

Sistem enterprise yang mencakup mutasi stok, alur persetujuan, dan kalkulasi depresiasi membutuhkan cakupan pengujian otomatis (*automated testing*) yang tinggi guna mencegah regresi bug fatal. Diperlukan kerangka kerja pengujian yang memiliki sintaks ekspresif, cepat ditulis, serta mendukung pengujian arsitektur (*Architecture Tests*) untuk menegakkan aturan pemisahan kode.

## Decision Drivers

- Kemudahan penulisan skenario pengujian fungsional dan unit.
- Dukungan *Architecture Testing* untuk memastikan controller tetap tipis (*thin controllers*) dan model tidak mengakses HTTP langsung.
- Integrasi bawaan dengan ekosistem Laravel dan mock library.

## Considered Options

1. **Option A**: Standar PHPUnit (Class-based tests).
2. **Option B**: Pest PHP v3 (Functional / Closure-based testing engine di atas PHPUnit).
3. **Option C**: Behat / Codeception (BDD-based testing).

## Decision Outcome

**Chosen Option**: **Option B (Pest PHP)**.

Pest PHP diadopsi sebagai standar pengujian tunggal proyek.

### Kategori Test Suite:
1. `tests/Feature/`: Menguji alur lengkap request-response HTTP, Inertia props, otorisasi role, dan mutasi basis data.
2. `tests/Unit/`: Menguji kalkulasi matematika (rumus garis lurus penyusutan, kalkulasi saldo stok, formatter).
3. `tests/Architecture/`: Menegakkan aturan arsitektur secara otomatis, misalnya:
   ```php
   arch('controllers are thin')
       ->expect('App\Http\Controllers')
       ->not->toHaveSuffix('Repository')
       ->toOnlyUse([
           'App\Actions',
           'App\Services',
           'App\Http\Requests',
           'Inertia\Inertia',
           // ...
       ]);
   ```

### Consequences

- **Good**: Sintaks sangat bersih, ekspresif, dan mempercepat pembuatan test suite hingga 40%.
- **Good**: Menjaga kualitas arsitektur software secara otomatis pada pipeline CI/CD.
- **Good**: Kompatibilitas 100% dengan assertion PHPUnit yang sudah ada.
- **Bad**: Tim pengembang yang terbiasa dengan PHPUnit klasik perlu sedikit penyesuaian terhadap paradigma closure-based Pest.
