# ADR-009: Docker Compose with 6 Dedicated Container Services

- **Status**: Accepted
- **Deciders**: DevOps Engineer, Lead Architect
- **Date**: 2026-09-18
- **Technical Story**: Local development environment and containerization baseline.

---

## Context and Problem Statement

Pengembangan aplikasi dengan tumpukan teknologi modern (PHP 8.4, Laravel 13, Node.js/Vite, MySQL 8.0, Redis, email tester) sering mengalami masalah inkonsistensi lingkungan (*"works on my machine"*), konflik versi ekstensi PHP, atau kesulitan setup database lokal bagi pengembang baru. Diperlukan standarisasi lingkungan berbasis kontainer yang terisolasi dan mudah dijalankan dalam 1 perintah.

## Decision Drivers

- Reproducibility 100% identik di seluruh mesin pengembang (Windows, macOS, Linux).
- Isolasi layanan dan port tanpa mengotori sistem operasi host.
- Kemudahan pengujian email dan inspeksi database tanpa software desktop eksternal.

## Considered Options

1. **Option A**: Native setup (XAMPP / Laragon / Homebrew langsung di host OS).
2. **Option B**: Laravel Sail (Docker Compose bawaan Laravel dengan 1 container utama).
3. **Option C**: Custom Multi-Container Docker Compose dengan 6 service terpisah (PHP-FPM, Nginx, MySQL, Redis, Mailpit, phpMyAdmin).

## Decision Outcome

**Chosen Option**: **Option C (Custom Multi-Container Docker Compose 6 Services)**.

Arsitektur kontainer yang dikonfigurasi:
1. `app`: PHP 8.4-FPM kustom dengan ekstensi lengkap (`pdo_mysql`, `bcmath`, `intl`, `redis`, `gd`, `zip`, `opcache`).
2. `webserver`: Nginx 1.25 Alpine sebagai reverse proxy & web server statis.
3. `db`: MySQL 8.0 Community Server dengan volume persisten.
4. `cache`: Redis 7.2 Alpine untuk session, cache, dan queue.
5. `mailpit`: Mock SMTP server dan UI web port 8025 untuk menangkap email notifikasi dev.
6. `phpmyadmin`: GUI database port 8081 untuk mempermudah verifikasi skema secara visual.

### Consequences

- **Good**: Arsitektur server menyerupai kondisi *production environment* sesungguhnya.
- **Good**: Mailpit mencegah email tes terkirim ke alamat email sungguhan secara tidak sengaja.
- **Good**: Setup awal sangat cepat: cukup `docker compose up -d`.
- **Bad**: Membutuhkan resource RAM lebih tinggi (~1.5GB - 2GB untuk menjalankan 6 kontainer aktif secara bersamaan).
