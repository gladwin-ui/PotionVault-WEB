# PotionVault — Full Deliverable Report

**Tanggal verifikasi:** 25 Oktober 2026
**Basis:** source Laravel, migration, seeder, tests, Playwright, dan Bahan Soal asli.

## 1. Ringkasan deliverable

PotionVault telah diimplementasikan sebagai aplikasi Laravel/MySQL dengan dua role: Admin dan Customer. Fitur aktual mencakup authentication, profile/password, katalog, artifact CRUD, kategori jamak, foto artifact, rental request, reservasi, approval/rejection, cancellation, expiry, payment manual, refund, handover, partial return, fine, billing, dashboard, report, dan print browser.

Dokumen teknis final:

- [README](../README.md)
- [PRD](PRD_PotionVault.md)
- [BRD Final](PotionVault_BRD_Final.md)
- [ERD Final](PotionVault_ERD_Final.md)
- [Flowcharts Final](PotionVault_Flowcharts_Final.md)
- [Gap Analysis](PotionVault_Deliverable_Gap_Analysis.md)

## 2. Kesesuaian requirement Bahan Soal

| Requirement asli | Implementasi/bukti | Status |
|---|---|---|
| README dengan identitas project dan fitur | `README.md` telah disinkronkan; nama kelompok/anggota masih placeholder | PARTIAL |
| Dua role Admin dan User | `users.role`, middleware Admin, browser/feature tests | PASS |
| Login dan Customer terdaftar | Auth controller dan route guest/auth | PASS |
| Satu profile per user | `profiles.user_id` unique, create/update profile | PASS |
| Edit profile | Customer/Admin profile controller dan view | PASS |
| Unit multi-kategori | `category_unit`, `sync()` dan test | PASS |
| Nama unit boleh sama, kode unit unik | unique `units.unit_code`, test duplicate | PASS |
| Search unit | Catalog query nama/kode dan Playwright | PASS |
| Admin CRUD unit/kategori/Customer | Controller, route, view, feature/browser tests | PASS |
| Customer maksimal dua unit | Workflow locking dan validation test | PASS |
| Rental maksimal lima hari dan denda | Due date + Rp2.000 per 24 jam per unit | PASS |
| Pengembalian oleh Admin | Return per item, condition, notes | PASS |
| Admin melihat unit dipinjam | Admin operations/dashboard | PASS |
| Customer hanya melihat rental sendiri | Ownership query dan authorization test | PASS |
| Admin melihat/cetak histori | Report filter dan print handler | PASS |
| Validasi field | Server-side validation controller | PASS |
| Migration dan seeder | Migration aktual dan `DatabaseSeeder` | PASS |
| Screenshot schema, video, UAT, manual book, GitLab | Tidak tersedia di workspace | BLOCKED / BELUM ADA BUKTI |

## 3. Workflow kritis

Paid-but-expired telah diperbaiki. Pada `APPROVED + PAID` yang melewati batas pickup:

```text
EXPIRED -> reserved unit AVAILABLE -> payment REFUND_PENDING
-> Admin confirms -> payment REFUNDED
```

Perubahan expiry disimpan sebelum exception handover dikembalikan. Histori payment tidak dihapus. Rental `UNPAID` tetap `UNPAID` saat expired.

## 4. Hasil verifikasi teknis

```text
php artisan test       PASS — 24 tests, 120 assertions
npx playwright test    PASS — 15 tests
php artisan route:list PASS — 54 routes
php artisan view:cache PASS
npm.cmd run build      PASS
```

Playwright dijalankan pada Chromium lokal dengan database MySQL testing terisolasi `potionvault_browser_test`. Tidak ada database development yang di-reset.

Concurrency workflow menggunakan row locking Customer/unit dan telah dicakup oleh test/driver MySQL terisolasi untuk mencegah double booking dan pelanggaran kuota dua unit.

## 5. Artefak yang belum dapat dinyatakan selesai

- Nama kelompok dan daftar anggota belum diisi.
- Screenshot schema database aktual belum ada.
- Video demonstrasi belum ada.
- UAT formal dengan expected/actual/tester/tanggal/screenshot belum ada.
- Manual Book Admin/Customer belum ada.
- URL repository GitLab belum tersedia.
- Build memiliki warning nonfatal: optional `fontaine` dan referensi gambar dokumentasi yang tidak di-resolve saat build.

## 6. Kesimpulan

Implementasi aplikasi dan automated verification memenuhi requirement fungsional utama. Dokumentasi teknis kini mengikuti source aktual. Deliverable penyerahan tugas belum dapat dinyatakan lengkap sebelum bukti UAT, manual book, screenshot schema, video, identitas tim, dan URL repository dilengkapi.
