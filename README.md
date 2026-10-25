# PotionVault — Sistem Penyewaan Ramuan dan Artefak Sihir

PotionVault adalah aplikasi Laravel untuk mengelola penyewaan unit fisik artefak/ramuan magis. Aplikasi ini mendukung katalog publik, akun Customer, operasi Admin, reservasi, approval, pembayaran manual, handover, pengembalian per unit, denda, refund, dashboard, dan laporan.

## Informasi proyek

| Item | Detail |
|---|---|
| Nama proyek | PotionVault |
| Nama kelompok | `[SahabatClaude]` |
| Nama team/anggota | `[Darvesh Gladwin Musyaffa]` |
| Platform | Web |
| Status | Automated UAT selesai; human acceptance/sign-off masih menunggu |
| Repository | URL repository tidak tersedia di workspace ini; jangan menganggap URL GitHub/GitLab tanpa verifikasi |
| Demo video | Belum tersedia |

## Dokumentasi navigasi

- [PRD PotionVault](docs/PRD_PotionVault.md)
- [BRD Final](docs/PotionVault_BRD_Final.md)
- [ERD Final](docs/PotionVault_ERD_Final.md)
- [Flowchart Final](docs/PotionVault_Flowcharts_Final.md)
- [Formal UAT Final](docs/PotionVault_UAT_Final.md)
- [Full Deliverable Report](docs/full-deliverable-report.md)
- [Gap Analysis dan Traceability](docs/PotionVault_Deliverable_Gap_Analysis.md)

## Tech stack

- Laravel 13, PHP 8.3+, Composer
- MySQL 8+ untuk aplikasi dan concurrency testing
- Blade, Vite, Tailwind CSS, Alpine.js, Chart.js
- PHPUnit/Laravel Feature Test dan Playwright Chromium
- Storage publik Laravel untuk foto artefak; path foto disimpan pada kolom `units.image`

## Role dan fitur

### Customer/User

- Registrasi, login, logout, dan edit profil sendiri.
- Mengubah password dengan verifikasi password saat ini.
- Melihat katalog, mencari nama/kode unit, serta memfilter kategori dan rarity.
- Melihat detail unit dan foto artefak.
- Mengajukan satu atau dua unit dalam satu rental.
- Melihat status rental, tagihan, payment, jatuh tempo, denda, dan histori milik sendiri.
- Membatalkan pengajuan hanya ketika status masih `PENDING`.

### Admin

- Login dan edit profil/password sendiri.
- CRUD unit artefak, kategori, dan Customer.
- Upload, mengganti, dan menghapus foto artefak.
- Menetapkan beberapa kategori untuk satu unit.
- Mengaktifkan, menonaktifkan, menghapus aman, dan memulihkan Customer.
- Approve/reject rental, konfirmasi pembayaran, handover, return per unit, pelunasan denda, dan refund.
- Melihat dashboard KPI, grafik rarity, tren rental, serta laporan dengan filter dan print browser.

## Aturan bisnis dan status

- Semua fitur operasional membutuhkan login; Customer harus terdaftar.
- Setiap akun memiliki satu `profile` melalui constraint unik `profiles.user_id`.
- Nama unit boleh sama, tetapi `unit_code` wajib unik.
- Satu unit dapat memiliki banyak kategori melalui tabel pivot `category_unit`.
- Customer maksimal memiliki dua unit aktif. Rental `PENDING`, `APPROVED`, dan `BORROWED` ikut menghitung kuota.
- Rental disimpan sebagai `PENDING` dan unit terkait menjadi `RESERVED`.
- Approval Admin menetapkan batas pengambilan 24 jam (`expires_at`).
- Rental yang melewati batas pengambilan menjadi `EXPIRED` dan unit `RESERVED` dilepas.
- Jika rental expired sudah `PAID`, payment menjadi `REFUND_PENDING`; Admin dapat mencatatnya sebagai `REFUNDED`. Histori payment dipertahankan.
- Handover hanya boleh untuk rental `APPROVED`, payment `PAID`, dan unit `RESERVED`.
- Masa sewa adalah lima hari sejak handover. Pengembalian dapat dilakukan per unit.
- Denda adalah Rp2.000 untuk setiap periode keterlambatan 24 jam yang dimulai per unit.
- Return `GOOD` mengembalikan unit menjadi `AVAILABLE`; kondisi `MAINTENANCE` atau `DAMAGED` mempertahankan unit pada status tersebut.
- Customer hanya dapat melihat rental miliknya; Admin dapat melihat seluruh data.

Status utama:

| Domain | Status |
|---|---|
| Rental | `PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`, `EXPIRED`, `BORROWED`, `RETURNED` |
| Payment sewa | `UNPAID`, `PAID`, `REFUND_PENDING`, `REFUNDED` |
| Unit | `AVAILABLE`, `RESERVED`, `BORROWED`, `MAINTENANCE`, `DAMAGED`, `DEACTIVATED` |
| Fine per item | `NO_FINE`, `UNPAID`, `PAID` |

## Instalasi dan konfigurasi MySQL

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Buat database MySQL, lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=potionvault
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration dan seeder:

```powershell
php artisan migrate --seed
php artisan storage:link
```

Seeder menyediakan akun contoh:

- Admin: `admin@potionvault.test` / `password`
- Customer: `aria@potionvault.test` / `password`

Ganti kredensial contoh untuk lingkungan non-development.

## Menjalankan aplikasi

Terminal pertama:

```powershell
php artisan serve
```

Terminal kedua:

```powershell
npm.cmd run dev
```

Buka `http://127.0.0.1:8000`.

## Testing dan quality checks

Gunakan database testing terisolasi; jangan menjalankan `migrate:fresh` pada database development.

```powershell
php artisan test
npx playwright test
php artisan route:list
php artisan view:cache
npm.cmd run build
```

Hasil verifikasi terakhir:

- Laravel: 40 test, 200 assertion, semuanya PASS.
- Playwright Chromium: 20 test PASS pada desktop, tablet, dan mobile.
- Route: 54 route.
- Blade view cache: PASS.
- Vite build: PASS; masih ada warning nonfatal terkait optional `fontaine` dan referensi gambar dokumentasi.

Formal UAT Sprint 3.1:

- 81 dari 81 skenario UAT PASS.
- FAIL: 0; BLOCKED: 0; NOT RUN: 0.
- Database concurrency MySQL terisolasi: PASS.
- Evidence screenshot tersedia di [docs/uat/evidence](docs/uat/evidence).
- Status: `UAT READY FOR HUMAN ACCEPTANCE`; sign-off manusia belum diisi.

## Struktur penting

```text
app/Http/Controllers/   Controller Customer, Admin, dan Auth
app/Models/              Model Eloquent
app/Services/            Workflow rental dan expiry
database/migrations/     Schema database
database/seeders/        Data awal
resources/views/         Blade UI
resources/css/           Styling PotionVault
routes/web.php           Route aplikasi
tests/Feature/           Laravel feature tests
tests/browser/           Playwright tests
docs/                    PRD, BRD, ERD, flowchart, dan laporan
```

## Schema database

Schema final diringkas pada [ERD Final](docs/PotionVault_ERD_Final.md). Tabel aktual: `users`, `profiles`, `units`, `categories`, `category_unit`, `rentals`, `rental_items`, dan `payments`. Kolom, key, index, soft delete, expiry, return, fine, image, dan refund mengikuti migration Laravel aktual.

## Deliverable tugas yang masih perlu dilengkapi

Source, automated test, dan dokumen formal UAT telah tersedia. Berdasarkan pemeriksaan workspace, item berikut masih perlu dilengkapi atau dikonfirmasi:

- Screenshot skema database aktual.
- Screen recording/video demo.
- Manual Book Admin dan Customer.
- URL repository GitLab yang diwajibkan oleh Bahan Soal.
- Nama kelompok dan daftar anggota.

Dokumen UAT dan screenshot evidence tersedia pada [Formal UAT Final](docs/PotionVault_UAT_Final.md).

Dokumen tersebut tidak dibuat-buat atau diberi status selesai tanpa bukti.
