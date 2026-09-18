# DESIGN SYSTEM SPECIFICATION
**Inventory & Asset Management System**  
*Document Version: 1.0.0 | Phase: 08 - UI/UX Guidelines | Date: 2026-09-18*

---

## 1. Design Philosophy & Aesthetic Principles

Sistem Inventory & Asset Management dirancang untuk menghadirkan pengalaman **Enterprise Software Modern Kelas Atas (State-of-the-Art Enterprise)**:
1. **High Information Density with Visual Breathing Room**: Data tabular, metrik stok, dan status aset disajikan padat namun teratur tanpa kesan semrawut.
2. **Harmonious Modern Palette**: Menghindari warna dasar mentah; mengadopsi HSL tailored color palette dengan nuansa Slate/Zinc dan aksen Indigo/Sky.
3. **Micro-Interactions & Receptiveness**: Transisi halus (150ms-200ms ease), hover states jelas, skeleton loaders saat memuat data, dan tactile feedback pada tombol.
4. **Accessible & Responsive**: Memenuhi standar kontras WCAG 2.1 AA, dukungan mode Terang (Light Mode) dan Gelap (Dark Mode) berbasis CSS Variables.

---

## 2. Color System & Design Tokens

Sistem warna diintegrasikan langsung dengan **Tailwind CSS** dan konfigurasi token **shadcn-vue**.

### 2.1 Core Neutral Palette (Zinc / Slate Base)

| Token | Light Mode (HEX / HSL) | Dark Mode (HEX / HSL) | Penggunaan |
|---|---|---|---|
| `--background` | `#FFFFFF` (`0 0% 100%`) | `#09090B` (`240 10% 3.9%`) | Background aplikasi utama |
| `--foreground` | `#09090B` (`240 10% 3.9%`) | `#FAFAFA` (`0 0% 98%`) | Teks utama / heading |
| `--card` | `#FFFFFF` (`0 0% 100%`) | `#121215` (`240 10% 5.5%`) | Background kontainer kartu |
| `--card-foreground` | `#09090B` (`240 10% 3.9%`) | `#FAFAFA` (`0 0% 98%`) | Teks di dalam kartu |
| `--muted` | `#F4F4F5` (`240 4.8% 95.9%`)| `#27272A` (`240 3.7% 15.9%`)| Background elemen redup/tab nonaktif |
| `--muted-foreground`| `#71717A` (`240 3.8% 46.1%`)| `#A1A1AA` (`240 5% 64.9%`) | Teks sekunder, label, subheader |
| `--border` | `#E4E4E7` (`240 5.9% 90%`) | `#27272A` (`240 3.7% 15.9%`)| Garis batas border komponen |
| `--input` | `#E4E4E7` (`240 5.9% 90%`) | `#27272A` (`240 3.7% 15.9%`)| Garis batas form input |

---

### 2.2 Semantic Brand & Functional Palette

| Semantic Token | Light Mode | Dark Mode | Arti / Penerapan |
|---|---|---|---|
| `--primary` | `#4F46E5` (`243 75% 59%` Indigo-600) | `#6366F1` (`239 84% 67%` Indigo-500) | Tombol utama, active state, link navigasi |
| `--primary-foreground` | `#FFFFFF` (`0 0% 100%`) | `#09090B` (`240 10% 3.9%`) | Teks di atas warna primary |
| `--secondary` | `#F1F5F9` (`210 40% 96.1%`) | `#1E293B` (`217 33% 17.5%`) | Tombol sekunder, filter pill nonaktif |
| `--secondary-foreground` | `#0F172A` (`222 47% 11.2%`) | `#F8FAFC` (`210 40% 98%`) | Teks di atas secondary |
| `--destructive` | `#E11D48` (`347 77% 50%` Rose-600) | `#F43F5E` (`350 89% 60%` Rose-500) | Aksi berbahaya, reject, delete, disposal |
| `--destructive-foreground` | `#FFFFFF` (`0 0% 100%`) | `#FFFFFF` (`0 0% 100%`) | Teks di atas warna destruktif |

---

### 2.3 Status & Alert Indicators

Digunakan secara konsisten pada **StatusBadge**, grafik, dan notifikasi:

| Status | Badge Background | Badge Text | Dot / Border | Skenario Penggunaan |
|---|---|---|---|---|
| **Success** | `bg-emerald-50` / `dark:bg-emerald-950/40` | `text-emerald-700` / `dark:text-emerald-300` | `bg-emerald-500` | Approved, Completed, Available, In Stock |
| **Warning** | `bg-amber-50` / `dark:bg-amber-950/40` | `text-amber-700` / `dark:text-amber-300` | `bg-amber-500` | Pending L1/L2, Low Stock Warning, In Maintenance |
| **Danger** | `bg-rose-50` / `dark:bg-rose-950/40` | `text-rose-700` / `dark:text-rose-300` | `bg-rose-500` | Rejected, Out of Stock, Disposed, Lost |
| **Info / Active** | `bg-sky-50` / `dark:bg-sky-950/40` | `text-sky-700` / `dark:text-sky-300` | `bg-sky-500` | In Transit, In Use, Draft |
| **Neutral** | `bg-zinc-100` / `dark:bg-zinc-800` | `text-zinc-600` / `dark:text-zinc-300` | `bg-zinc-400` | Inactive, Cancelled |

---

## 3. Typography Hierarchy

Sistem tipografi menggunakan **Plus Jakarta Sans** atau **Inter** dari Google Fonts, dikombinasikan dengan font monospace untuk kode serial & barcode.

- **Primary Font**: `'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`
- **Monospace Font**: `'JetBrains Mono', 'Fira Code', ui-monospace, monospace` (khusus Asset Code, Item Code, Serial Number, Nilai Uang)

### Type Scale:

| Tingkatan | Class Tailwind | Ukuran / Line-Height | Weight | Contoh Penggunaan |
|---|---|---|---|---|
| **Display H1** | `text-2xl font-bold tracking-tight` | 24px / 32px | Bold (700) | Judul Halaman Utama / Dashboard |
| **Header H2** | `text-xl font-semibold tracking-tight` | 20px / 28px | Semibold (600) | Judul Bagian Modul / Modal Title |
| **Header H3** | `text-base font-semibold` | 16px / 24px | Semibold (600) | Card Title, Section Header |
| **Body Default**| `text-sm font-normal` | 14px / 20px | Regular (400) | Teks tabel, label form, deskripsi |
| **Body Medium** | `text-sm font-medium` | 14px / 20px | Medium (500) | Header kolom tabel, item sidebar, tab |
| **Caption/Meta** | `text-xs text-muted-foreground` | 12px / 16px | Regular (400) | Timestamp, petunjuk input, helper text |
| **Mono Code** | `font-mono text-xs font-medium` | 12px / 16px | Medium (500) | `AST-2026-001`, `IDR 12.500.000` |

---

## 4. Spacing, Borders & Radius Tokens

- **Base Spacing Grid**: Kelipatan 4px (`0.25rem` di Tailwind).
- **Corner Radius**:
  - `rounded-lg`: 8px (Default untuk Card, Modal Dialog, Popover)
  - `rounded-md`: 6px (Default untuk Form Input, Tombol, Select Dropdown)
  - `rounded-full`: 9999px (Pill badges, Avatar)
- **Elevation / Shadows**:
  - `shadow-xs`: Border subtle dengan depth minimal untuk Form Inputs.
  - `shadow-sm`: Kartu statistik dashboard, table wrappers (`0 1px 2px 0 rgb(0 0 0 / 0.05)`).
  - `shadow-md`: Dropdown menus, tooltips, popovers.
  - `shadow-lg`: Modal dialogs, drawer sliders.

---

## 5. UI Component Primitives (shadcn-vue Specification)

### 5.1 Buttons (`Button.vue`)
- **Primary**: `bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm transition-all duration-150 active:scale-[0.98]`
- **Secondary**: `bg-secondary text-secondary-foreground hover:bg-secondary/80`
- **Outline**: `border border-input bg-background hover:bg-muted hover:text-foreground`
- **Ghost**: `hover:bg-muted hover:text-foreground`
- **Destructive**: `bg-destructive text-destructive-foreground hover:bg-destructive/90 shadow-sm`
- **Sizes**:
  - `sm`: Height 32px (`h-8 px-3 text-xs`) — untuk aksi tabel
  - `default`: Height 36px (`h-9 px-4 py-2 text-sm`) — standar form
  - `lg`: Height 40px (`h-10 px-6 text-sm`) — submit utama

### 5.2 Status Badges (`StatusBadge.vue`)
Menampilkan indikator status dengan layout konsisten:
- Layout: flex items-center gap-1.5, padding `px-2 py-0.5`, radius `rounded-full`, text `text-xs font-medium`.
- Memiliki dot indikator berkedip (pulsing dot) untuk status penting (seperti `Pending L1`, `Urgent`).

### 5.3 Form Inputs (`Input.vue`, `Select.vue`)
- Height 36px (`h-9`), padding `px-3 py-1`, text `text-sm`.
- Focus ring: `focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1`.
- Error state: `border-destructive focus-visible:ring-destructive text-destructive`.

---
*Next Document: `uiux/UI_GUIDELINES.md` (Layout structures, Data Tables, Forms & Interactions)*
