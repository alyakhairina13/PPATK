# SANTRIS — Sistem Informasi Manajemen Akta Notaris & PPAT

## Project Overview
Aplikasi web internal untuk **Kantor Notaris Wiga Angraini** yang mengelola alur akta notaris & PPAT secara digital. Menggantikan proses manual dengan workflow digital, penomoran otomatis, dan arsip terpusat.

## Tech Stack
| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS 4 |
| Build | Vite 7 |
| Database | MySQL |
| Testing | Pest |
| Storage | Local / Cloud (Google Drive API / AWS S3) |

## Architecture
- **Pattern:** MVC (Model–View–Controller)
- **Roles:** Hanya `AdminStaff` dan `Notaris` (tidak ada portal Klien)
- **Kewenangan:** Notaris memiliki akses eksklusif untuk status **Final** dan **Selesai**
- **Klien:** Data pelanggan, **bukan** pengguna sistem

## Database Schema (6 tables)

### users
| Column | Type | Notes |
|--------|------|-------|
| id_user | INT PK AI | |
| username | VARCHAR(50) UNIQUE | |
| password | VARCHAR(255) | hashed |
| nama_lengkap | VARCHAR(150) | |
| role | ENUM('AdminStaff','Notaris') | |
| nip_staff | VARCHAR(30) NULL | untuk AdminStaff |
| no_sertifikat_notaris | VARCHAR(50) NULL | untuk Notaris |

### klien
| Column | Type | Notes |
|--------|------|-------|
| id_klien | INT PK AI | |
| nama_lengkap | VARCHAR(150) | |
| nik | CHAR(16) UNIQUE | mencegah duplikasi |
| tempat_tanggal_lahir | VARCHAR(100) | |
| jenis_kelamin | ENUM('Laki-laki','Perempuan') | |
| alamat | TEXT | |
| nomor_telepon | VARCHAR(20) | |
| pekerjaan | VARCHAR(100) | |
| npwp | VARCHAR(30) NULL | opsional |

### akta (central entity)
| Column | Type | Notes |
|--------|------|-------|
| id_akta | INT PK AI | |
| id_klien | INT FK → klien | |
| id_user | INT FK → users | |
| jenis_template | ENUM('AJB','Perjanjian','Kuasa','PT','Wasiat') | |
| konten_teks_utama | LONGTEXT | isi konten akta |
| status_workflow | ENUM('Draft','Diverifikasi','Final','Selesai') | |
| tanggal_dibuat | DATETIME | |
| last_updated | DATETIME | |

### version_history
| Column | Type | Notes |
|--------|------|-------|
| id_version | INT PK AI | |
| id_akta | INT FK → akta | |
| versi_ke | VARCHAR(10) | |
| backup_konten_teks | LONGTEXT | |
| timestamp_perubahan | DATETIME | |
| diubah_oleh | VARCHAR(150) | |

### lampiran_dokumen
| Column | Type | Notes |
|--------|------|-------|
| id_dokumen | INT PK AI | |
| id_akta | INT FK → akta | |
| nama_file | VARCHAR(255) | |
| format_extension | ENUM('jpg','png','pdf') | |
| ukuran_berkas | DECIMAL(5,2) | MB |
| path_penyimpanan | VARCHAR(500) | |

### repertorium
| Column | Type | Notes |
|--------|------|-------|
| id_repertorium | INT PK AI | |
| id_akta | INT FK UNIQUE | 1 akta = 1 nomor resmi |
| nomor_akta_resmi | VARCHAR(100) UNIQUE | format: Nomor/Tahun/Bulan-Repertorium |
| indeks_urutan | INT | berurutan |
| bulan_buku | CHAR(2) | |
| tahun_buku | CHAR(4) | |
| timestamp_generasi | DATETIME | |

### Relationships
- `users` 1:N `akta` (memproses)
- `klien` 1:N `akta` (memiliki)
- `akta` 1:N `lampiran_dokumen` (memuat)
- `akta` 1:N `version_history` (mencatat)
- `akta` 1:0..1 `repertorium` (menerbitkan saat Final)

## Workflow States
```
Draft → Diverifikasi → Final → Selesai
```
- **Draft:** Dibuat/diedit oleh AdminStaff/Notaris
- **Diverifikasi:** Dikirim ke Notaris untuk review
- **Final:** Disetujui Notaris → auto-generate nomor repertorium
- **Selesai:** Ditandatangani fisik → dokumen dikunci (Final Archive)

## Design System Tokens
- **Primary:** `#004e9f` (deep blue)
- **Action/Interactive:** `#0066cc`
- **Focus ring:** `#0071e3`
- **Body font:** Inter 17px
- **Spacing base:** 8px
- **Border radius pills:** 9999px
- **Border radius cards:** 1rem (16px)
- **Elevation:** No shadows, frosted glass for nav
- **Design philosophy:** "Museum-Gallery" — Corporate Minimalism + Editorial Precision

## File Structure Conventions
```
app/
├── Http/
│   └── Controllers/     # Resource controllers per domain
├── Models/              # Eloquent models (1:1 with tables)
└── Providers/
routes/
├── web.php              # Web routes
database/
├── migrations/          # Schema migrations
├── factories/           # Model factories
└── seeders/             # Database seeders
resources/
├── css/                 # Tailwind entry
├── js/                  # Alpine.js / vanilla JS
└── views/
    ├── components/      # Blade components
    ├── layouts/         # Main layout (sidebar + topbar)
    └── pages/           # Page views per feature
```

## Development Commands
```bash
# Setup
composer setup          # Install + migrate + build

# Development
composer dev             # Run server, queue, logs, vite concurrently

# Testing
composer test            # Clear config + run Pest tests

# Individual
php artisan serve        # Start dev server
npm run dev              # Start Vite dev server
npm run build            # Build for production
php artisan migrate      # Run migrations
php artisan test         # Run tests
```

## Coding Standards
- **PHP:** PSR-12, use Laravel conventions (singular model names, plural table names)
- **Blade:** Component-based architecture, use `x-` prefix for custom components
- **CSS:** Tailwind utility-first, follow design system tokens (no custom CSS unless necessary)
- **JS:** Minimal vanilla JS or Alpine.js for interactivity
- **Naming:** `snake_case` for DB columns, `camelCase` for JS, `PascalCase` for PHP classes
- **No comments** in code unless absolutely necessary
- **Validation:** Server-side validation on all forms, unique NIK constraint on klien
- **File uploads:** Validate format (JPG/PNG/PDF) and size (≤10MB) before storage

## Pages (13 total)
1. Login
2. Dashboard Performa
3. Data Klien — List
4. Data Klien — Form (Tambah/Edit)
5. Data Klien — Detail + Riwayat Akta
6. Data Klien — Import Excel/CSV
7. Manajemen Akta — List
8. Editor Draft Akta
9. Detail Akta & Workflow
10. Repertorium Digital
11. Konfigurasi Format Penomoran
12. Laporan Berkala

## Frontend Patterns
- **Confirmation Modal:** Setiap action button (submit, delete, status change, dll) WAJIB menampilkan modal konfirmasi terlebih dahulu sebelum menjalankan proses. Gunakan pola: klik button → muncul modal → user konfirmasi → proses dijalankan.

## Key Behaviors
- Status badge: `○ Draft` · `● Diverifikasi` · `◈ Final` · `✅ Selesai`
- Notaris-only buttons visible but disabled for AdminStaff
- Version history saved on every draft save
- Nomor akta auto-generated only at Final status
- Selesai status locks document (read-only)
- In-app notifications for workflow transitions
- NIK uniqueness enforced at DB + application level

## Reference Docs
- `dev_docs/SRS_SANTRIS.md` — Software Requirements Specification
- `dev_docs/ERD_SANTRIS.md` — Entity-Relationship Diagram
- `dev_docs/FRONTEND_BRIEF_SANTRIS.md` — Frontend wireframes & page briefs
- `dev_docs/sanitris_legal_design_system/DESIGN.md` — Design system tokens & guidelines
- `dev_docs/AD-*.png` — Activity diagrams (workflow, draft, repertorium, laporan, klien)
- `dev_docs/*_high_fidelity_v2/screen.png` — High-fidelity screen mockups
