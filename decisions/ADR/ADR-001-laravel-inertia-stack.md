# ADR-001: Monolithic Architecture with Laravel 13 & Inertia.js + Vue 3

- **Status**: Accepted
- **Deciders**: Software Architect, Development Team
- **Date**: 2026-09-18
- **Technical Story**: Selection of web application stack and client-server communication paradigm.

---

## Context and Problem Statement

Sistem Inventory & Asset Management ditujukan untuk lingkungan operasional korporat internal (karyawan, staf gudang, manajer, super admin). Diperlukan pengalaman pengguna yang reaktif (Single Page Application UX tanpa reload halaman penuh) untuk menangani form kompleks, filter tabel dinamis, dan dialog serah terima aset. Di saat yang sama, tim menginginkan kecepatan pengembangan monolitik tanpa overhead pemeliharaan terpisah antara REST/GraphQL API dan frontend SPA (seperti mengelola token JWT, CORS, serialisasi duplikat, dan sinkronisasi route).

## Decision Drivers

- Produktivitas pengembangan maksimal dengan single codebase.
- Reaktivitas tinggi (Vue 3 Composition API + Tailwind CSS + shadcn-vue).
- Keamanan bawaan standar enterprise (Session-based auth, CSRF protection bawaan Laravel, tidak ada exposure token di client).
- Kemudahan implementasi otorisasi (Policy dan Gate Laravel langsung mengontrol render view dan respons).

## Considered Options

1. **Option A**: Laravel 13 + Inertia.js + Vue 3 (Monolithic Modern SPA).
2. **Option B**: Decoupled Architecture (Laravel REST API + Nuxt / Vite Vue SPA terpisah).
3. **Option C**: Traditional Blade Server-Side Rendering + Alpine.js / Livewire.

## Decision Outcome

**Chosen Option**: **Option A (Laravel 13 + Inertia.js + Vue 3)**.

Inertia.js berfungsi sebagai jembatan langsung yang memungkinkan backend Laravel mengembalikan komponen Vue sebagai respons HTTP reguler, meneruskan props secara otomatis tanpa perlu membangun layer API publik khusus.

### Consequences

- **Good**: Menghilangkan kebutuhan membuat endpoint REST/API terpisah untuk UI internal (`docs/06_API.md` berstatus NOT_APPLICABLE).
- **Good**: Mengeliminasi kompleksitas refresh token JWT dan kerentanan XSS/token theft di local storage browser.
- **Good**: Routing, otorisasi, dan validasi tetap terpusat di Laravel controller & form request.
- **Bad**: Jika di masa depan diperlukan aplikasi mobile native (iOS/Android), perlu dibuatkan dedicated API controller terpisah.
