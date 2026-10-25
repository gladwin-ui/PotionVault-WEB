# PotionVault — Deliverable Gap Analysis dan Traceability

**Tanggal sinkronisasi:** 25 Oktober 2026  
**Sumber requirement:** `Bahan Soal.docx` di `D:\SEMESTER 7\SEERTIFIKASI\LSP\Bahan Soal.docx`  
**Sumber implementasi:** Laravel source, migrations, seeder, feature tests, dan Playwright tests.

## Status kategori

- **PASS:** requirement dan implementasi memiliki bukti source/test yang sesuai.
- **PARTIAL:** implementasi tersedia, tetapi sebagian bukti/deliverable belum lengkap.
- **BLOCKED:** tidak dapat dinyatakan selesai karena bukti eksternal belum tersedia.

## Traceability requirement

| ID | Requirement Bahan Soal | Implementasi/bukti | Status |
|---|---|---|---|
| R01 | README berisi identitas project dan daftar fitur | README final; identitas tim masih placeholder | PARTIAL |
| R02 | Dua jenis anggota: Admin dan User | `users.role`, Admin middleware, UI role | PASS |
| R03 | User login dan harus terdaftar untuk rental | Auth route, active-account check, workflow request | PASS |
| R04 | Satu user memiliki satu profile | `profiles.user_id` unique dan profile controller | PASS |
| R05 | User dapat mengubah profile | Customer/Admin profile update dan password update | PASS |
| R06 | Unit dapat memiliki multiple kategori | `category_unit`, category sync, browser test | PASS |
| R07 | Nama unit boleh sama, kode unit unik | unique `units.unit_code`, regression test | PASS |
| R08 | Search unit berdasarkan nama | Catalog query nama/kode dan Playwright test | PASS |
| R09 | Admin CRUD unit, kategori, Customer | Controller/route/view serta test | PASS |
| R10 | Customer maksimal dua unit | Customer lock + quota validation + test | PASS |
| R11 | Rental maksimal lima hari dan denda | `due_at`, fine per periode 24 jam per unit | PASS |
| R12 | Hanya Admin memproses return | Admin-only return route/controller | PASS |
| R13 | Admin melihat unit dipinjam | Admin operations/dashboard | PASS |
| R14 | Customer hanya melihat rental sendiri | Ownership query + authorization regression test | PASS |
| R15 | Admin melihat dan mencetak histori | Report filters dan print CSS/handler | PASS |
| R16 | Required validation | Request validation pada fitur operasional | PASS |
| R17 | Migration dan seeder | 8 tabel migration + DatabaseSeeder | PASS |
| R18 | Screenshot schema database | Belum tersedia di workspace | BLOCKED |
| R19 | Video demo website | Belum tersedia di workspace | BLOCKED |
| R20 | UAT dan manual book | Belum tersedia di workspace | BLOCKED |
| R21 | Push GitLab | URL/repository GitLab tidak tersedia untuk diverifikasi | BLOCKED |

## Traceability status implementasi

| Area | Bukti aktual | Status |
|---|---|---|
| Paid-but-expired | `RentalWorkflowService`, regression expiry scheduler/handover/refund | PASS |
| Dashboard Customer | Active rental, nearest due date, fines | PASS |
| Detail rental Customer | Timestamp, status, due, return, fine per item | PASS |
| Image artifact | Upload/replace/remove, path `units.image`, browser test | PASS |
| Admin rarity chart | `DashboardController` mengirim `rarityLabels`/`rarityValues` | PASS |
| Report filter | Status, Customer, artifact, payment, fine, date, overdue | PASS |
| MySQL concurrency | Driver/worker dan isolated testing DB | PASS |
| Responsive browser | 15 Playwright test desktop/tablet/mobile | PASS |

## Hasil verifikasi terakhir

| Command | Hasil |
|---|---|
| `php artisan test` | PASS — 24 tests, 120 assertions |
| `npx playwright test` | PASS — 15 tests |
| `php artisan route:list` | PASS — 54 routes |
| `php artisan view:cache` | PASS |
| `npm.cmd run build` | PASS dengan warning nonfatal optional `fontaine` dan referensi gambar dokumentasi |

Playwright dan concurrency menggunakan database MySQL testing terisolasi, bukan database development.

## Discrepancy yang masih terbuka

1. Nama kelompok dan daftar anggota belum diisi.
2. Screenshot schema, video demo, UAT formal, manual book, dan URL GitLab belum tersedia.
3. Format nomor telepon belum ditentukan secara spesifik oleh Bahan Soal/PRD; validasi tidak dibuat terlalu restriktif.
4. Warning build dokumentasi gambar dan optional `fontaine` masih ada, tetapi tidak menggagalkan build.

## Kesimpulan

Dokumentasi teknis sudah disinkronkan dengan source aktual dan requirement Bahan Soal. Implementasi utama dapat diaudit ulang. Status keseluruhan dokumentasi adalah **PARTIAL** sampai deliverable eksternal pada R18–R21 tersedia dan identitas tim dilengkapi.
