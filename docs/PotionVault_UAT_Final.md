# PotionVault — Sprint 3.1 Formal User Acceptance Testing

**Tanggal:** 25 Oktober 2026  
**Lingkungan:** MySQL UAT `potionvault_uat_test`, Chromium lokal, `http://127.0.0.1:8001`  
**Tester otomatis:** Codex automated runner  
**Tester manusia/tanda tangan:** ____________________  **Tanggal:** ____________________

## 1. Tujuan dan aturan

UAT ini menggunakan `docs/Bahan Soal.docx`, README, PRD, BRD, ERD, flowchart, source code, dan test aktual sebagai acuan. Database development tidak di-reset dan Sprint 3 tidak dimulai. `PASS` hanya berarti skenario benar-benar dijalankan pada run ini; `NOT RUN` belum merupakan klaim penerimaan. Sign-off manusia tetap diperlukan.

**Data/prasyarat umum:** akun seed Admin `admin@potionvault.test`, Customer `aria@potionvault.test`; actor dan tindakan dicantumkan pada setiap skenario. **Actual** yang bertanda PASS diuji pada 2026-10-25 oleh tester otomatis; baris NOT RUN belum dieksekusi sebagai skenario UAT terpisah.

## 2. Matriks UAT

Kolom hasil mencakup expected, actual, status, tester/tanggal, dan bukti bila tersedia.

| ID | Skenario (aktor + tindakan) | Expected | Actual / status / bukti |
|---|---|---|---|
| AUTH-01 | Customer register valid | Akun/dashboard dibuat | Sesuai; **PASS**; `uat/evidence/auth/UAT-AUTH-01-registration-dashboard.png`; otomatis 2026-10-25 |
| AUTH-02 | Customer login valid | Login berhasil | Sesuai; **PASS**; otomatis 2026-10-25 |
| AUTH-03 | Admin login valid | Dashboard Admin tampil | Sesuai; **PASS**; `uat/evidence/auth/UAT-AUTH-03-admin-dashboard.png`; otomatis 2026-10-25 |
| AUTH-04 | Customer logout | Session berakhir | Sesuai; **PASS**; otomatis 2026-10-25 |
| AUTH-05 | Password login salah | Login ditolak | `UatCoverageCompletionTest::test_auth_invalid_inactive_and_guest_guards`; **PASS**; otomatis 2026-10-25 |
| AUTH-06 | User nonaktif login | Akses ditolak | `UatCoverageCompletionTest::test_auth_invalid_inactive_and_guest_guards`; **PASS**; otomatis 2026-10-25 |
| AUTH-07 | Guest buka route terlindungi | Redirect login | `UatCoverageCompletionTest::test_auth_invalid_inactive_and_guest_guards`; **PASS**; otomatis 2026-10-25 |
| PRO-01 | Customer buka profil | Profil milik sendiri tampil | `UatCoverageCompletionTest::test_customer_profile_and_duplicate_email_validation`; **PASS**; otomatis 2026-10-25 |
| PRO-02 | Customer ubah profil valid | Data tersimpan | `UatCoverageCompletionTest::test_customer_profile_and_duplicate_email_validation`; **PASS**; otomatis 2026-10-25 |
| PRO-03 | Customer ubah password valid | Password baru berlaku | `UatCoverageCompletionTest::test_customer_profile_and_duplicate_email_validation`; **PASS**; otomatis 2026-10-25 |
| PRO-04 | Password kini/konfirmasi salah | Perubahan ditolak | `UatCoverageCompletionTest::test_customer_profile_and_duplicate_email_validation`; **PASS**; otomatis 2026-10-25 |
| PRO-05 | Email duplikat | Validasi tampil | `UatCoverageCompletionTest::test_customer_duplicate_email_is_rejected_without_changing_original_email`; **PASS**; otomatis 2026-10-25 |
| CAT-01 | Customer buka katalog | Unit aktif tampil | Sesuai; **PASS**; `uat/evidence/customer/UAT-CAT-01-catalog.png`; otomatis 2026-10-25 |
| CAT-02 | Customer cari/filter rarity | Hasil sesuai filter | Sesuai; **PASS**; `uat/evidence/customer/UAT-CAT-02-search-rarity-filter.png`; otomatis 2026-10-25 |
| CAT-03 | Customer lihat unit unavailable | Status benar | `CatalogAvailabilityTest::test_customer_catalog_count_matches_all_active_admin_units_while_rental_requires_available_status`; **PASS**; otomatis 2026-10-25 |
| CAT-04 | Customer buka detail | Detail/gambar tampil | Sesuai; **PASS**; `uat/evidence/customer/UAT-CAT-04-artifact-detail.png`; otomatis 2026-10-25 |
| CAT-05 | Customer kembali ke katalog | Navigasi tanpa error | Sesuai; **PASS**; otomatis 2026-10-25 |
| CAT-06 | Customer coba unit unavailable | Tombol sewa terkunci | `UatCoverageCompletionTest::test_unavailable_catalog_detail_hides_rental_action_and_direct_request_is_rejected`; **PASS**; otomatis 2026-10-25 |
| ART-01 | Admin buka manajemen artefak | Daftar/form tampil | Sesuai; **PASS**; `uat/evidence/admin/UAT-ART-01-artifact-management.png`; otomatis 2026-10-25 |
| ART-02 | Admin buat unit valid | Unit tersimpan | `potionvault.spec.js` admin catalog test; **PASS**; otomatis 2026-10-25 |
| ART-03 | Admin edit unit | Perubahan tersimpan | `potionvault.spec.js` image/edit test; **PASS**; otomatis 2026-10-25 |
| ART-04 | Admin pilih banyak kategori | Relasi tersimpan | `potionvault.spec.js` multi-category test; **PASS**; otomatis 2026-10-25 |
| ART-05 | Admin unit-code duplikat | Ditolak validasi | `potionvault.spec.js` duplicate-code test; **PASS**; otomatis 2026-10-25 |
| ART-06 | Admin soft-delete unit idle | Tidak tampil di katalog | `Sprint1WorkflowTest::test_admin_delete_is_a_soft_delete_and_keeps_history`; **PASS**; otomatis 2026-10-25 |
| ART-07 | Admin hapus unit rental aktif | Ditolak | `UatCoverageCompletionTest::test_active_artifact_cannot_be_deleted_and_used_category_cannot_be_deleted`; **PASS**; otomatis 2026-10-25 |
| ART-08 | Admin upload gambar valid | Gambar tersimpan/tampil | `potionvault.spec.js` image upload test; **PASS**; otomatis 2026-10-25 |
| ART-09 | Admin replace/remove gambar | Referensi diperbarui | `potionvault.spec.js` image replacement/removal test; **PASS**; otomatis 2026-10-25 |
| ART-10 | Admin upload file invalid/>2MB | Ditolak validasi | `ArtifactImageTest::test_invalid_or_oversized_images_are_rejected_and_customer_cannot_upload`; **PASS**; otomatis 2026-10-25 |
| ADM-01 | Admin buka dashboard | KPI/chart tampil | Sesuai; **PASS**; `uat/evidence/admin/UAT-REP-01-02-admin-dashboard-charts.png`; otomatis 2026-10-25 |
| ADM-02 | Admin buat kategori | Tersimpan | `UatCoverageCompletionTest::test_admin_category_and_customer_crud_restore_and_authorization`; **PASS**; otomatis 2026-10-25 |
| ADM-03 | Admin edit kategori | Tersimpan | `UatCoverageCompletionTest::test_admin_category_and_customer_crud_restore_and_authorization`; **PASS**; otomatis 2026-10-25 |
| ADM-04 | Admin hapus kategori terpakai | Ditolak | `UatCoverageCompletionTest::test_active_artifact_cannot_be_deleted_and_used_category_cannot_be_deleted`; **PASS**; otomatis 2026-10-25 |
| ADM-05 | Admin lihat customer | Daftar tampil | `potionvault.spec.js` admin manage pages test; **PASS**; otomatis 2026-10-25 |
| ADM-06 | Admin buat/edit customer | Data tersimpan | `UatCoverageCompletionTest::test_admin_category_and_customer_crud_restore_and_authorization`; **PASS**; otomatis 2026-10-25 |
| ADM-07 | Admin nonaktifkan customer idle | Customer nonaktif | `UatCoverageCompletionTest::test_admin_category_and_customer_crud_restore_and_authorization`; **PASS**; otomatis 2026-10-25 |
| ADM-08 | Admin restore customer | Aktif kembali | `Sprint2ComplianceTest::test_admin_can_soft_delete_and_restore_customer_without_losing_profile`; **PASS**; otomatis 2026-10-25 |
| ADM-09 | Customer/guest akses Admin | Ditolak | `UatCoverageCompletionTest::test_admin_category_and_customer_crud_restore_and_authorization`; **PASS**; otomatis 2026-10-25 |
| REN-01 | Customer pilih unit tersedia dan submit | PENDING, unit RESERVED | Sesuai; **PASS**; `uat/evidence/rental/UAT-REN-01-pending-rental.png`; otomatis 2026-10-25 |
| REN-02 | Customer pilih >2 unit | Ditolak batas maksimum | `Sprint1WorkflowTest::test_core_customer_admin_workflow_reaches_return_and_fine`; **PASS**; otomatis 2026-10-25 |
| REN-03 | Customer sudah 2 rental aktif, submit lagi | Ditolak | `Sprint1WorkflowTest::test_customer_cannot_exceed_two_active_units`; **PASS**; otomatis 2026-10-25 |
| REN-04 | Customer pilih unit RESERVED/BORROWED | Tidak dapat dipilih | `UatCoverageCompletionTest::test_unavailable_unit_and_cancellation_restrictions`; **PASS**; otomatis 2026-10-25 |
| REN-05 | Customer cancel PENDING | CANCELLED, unit dilepas | Sesuai; **PASS**; otomatis 2026-10-25 |
| REN-06 | Customer buka rental customer lain | Ditolak | `PartialRequirementsTest::test_customer_rental_isolation_and_admin_only_return_are_enforced`; **PASS**; otomatis 2026-10-25 |
| REN-07 | Guest submit rental | Redirect login | `UatCoverageCompletionTest::test_guest_rental_submission_is_rejected_without_creating_rental`; **PASS**; otomatis 2026-10-25 |
| WFL-01 | Admin approve PENDING | APPROVED, RESERVED | Sesuai; **PASS**; `uat/evidence/rental/UAT-WFL-01-03-04-approved-paid-handover.png`; otomatis 2026-10-25 |
| WFL-02 | Admin reject PENDING | REJECTED, unit dilepas | `UatCoverageCompletionTest::test_admin_rejects_pending_rental_and_releases_units_with_reason`; **PASS**; otomatis 2026-10-25 |
| WFL-03 | Customer/Admin konfirmasi payment | PAID | Sesuai; **PASS**; bukti workflow di atas; otomatis 2026-10-25 |
| WFL-04 | Admin handover PAID | BORROWED, unit BORROWED | Sesuai; **PASS**; bukti workflow di atas; otomatis 2026-10-25 |
| WFL-05 | Admin handover sebelum payment | Ditolak, state konsisten | `UatCoverageCompletionTest::test_handover_before_payment_keeps_approved_reserved_state`; **PASS**; otomatis 2026-10-25 |
| WFL-06 | Admin handover setelah expiry | EXPIRED, unit dilepas | `Sprint1WorkflowTest::test_late_handover_persists_expiry_and_refund_pending_before_rejecting`; **PASS**; otomatis 2026-10-25 |
| WFL-07 | Scheduler expiry | EXPIRED, unit dilepas | `Sprint1WorkflowTest::test_expiry_releases_units_and_creates_auditable_refund_flow`; **PASS**; otomatis 2026-10-25 |
| WFL-08 | Admin proses refund expired PAID | REFUND_PENDING/REFUNDED | `Sprint1WorkflowTest::test_expiry_releases_units_and_creates_auditable_refund_flow`; **PASS**; otomatis 2026-10-25 |
| RET-01 | Admin return GOOD tepat waktu | AVAILABLE, returned_at | `Sprint1WorkflowTest::test_core_customer_admin_workflow_reaches_return_and_fine`; **PASS**; otomatis 2026-10-25 |
| RET-02 | Admin return MAINTENANCE | MAINTENANCE | `UatCoverageCompletionTest::test_return_conditions_partial_completion_and_fine_payment`; **PASS**; otomatis 2026-10-25 |
| RET-03 | Admin return DAMAGED | DAMAGED | `UatCoverageCompletionTest::test_return_conditions_partial_completion_and_fine_payment`; **PASS**; otomatis 2026-10-25 |
| RET-04 | Admin partial return | Rental tetap BORROWED | `Sprint1WorkflowTest::test_core_customer_admin_workflow_reaches_return_and_fine`; **PASS**; otomatis 2026-10-25 |
| RET-05 | Admin return item terakhir | RETURNED | `Sprint1WorkflowTest::test_core_customer_admin_workflow_reaches_return_and_fine`; **PASS**; otomatis 2026-10-25 |
| RET-06 | Admin return terlambat | Fine dihitung | `PartialRequirementsTest::test_handover_due_date_is_exactly_five_days_and_fine_is_calculated`; **PASS**; otomatis 2026-10-25 |
| RET-07 | Admin return kondisi invalid | Ditolak validasi | `UatCoverageCompletionTest::test_invalid_return_condition_is_rejected_without_state_change`; **PASS**; otomatis 2026-10-25 |
| RET-08 | Admin bayar fine | PAID + audit | `Sprint2ComplianceTest::test_fine_payment_is_independent_from_rental_payment`; **PASS**; otomatis 2026-10-25 |
| RET-09 | Admin bayar fine sebelum return | Ditolak | `UatCoverageCompletionTest::test_fine_cannot_be_paid_before_return`; **PASS**; otomatis 2026-10-25 |
| RET-10 | Customer return/pay fine | Ditolak | `UatCoverageCompletionTest::test_customer_cannot_return_or_pay_fine`; **PASS**; otomatis 2026-10-25 |
| REF-01 | Scheduler expiry UNPAID | EXPIRED, payment tetap sesuai | `Sprint1WorkflowTest::test_late_handover_keeps_unpaid_payment_when_expiring`; **PASS**; otomatis 2026-10-25 |
| REF-02 | Scheduler expiry PAID | REFUND_PENDING, histori ada | `Sprint1WorkflowTest::test_expiry_releases_units_and_creates_auditable_refund_flow`; **PASS**; otomatis 2026-10-25 |
| REF-03 | Admin catat refund | REFUNDED + audit | `Sprint1WorkflowTest::test_expiry_releases_units_and_creates_auditable_refund_flow`; **PASS**; otomatis 2026-10-25 |
| REF-04 | Admin refund non-expired | Ditolak | `UatCoverageCompletionTest::test_refund_non_expired_rental_is_rejected`; **PASS**; otomatis 2026-10-25 |
| REF-05 | Admin refund dua kali | Perubahan kedua ditolak | `UatCoverageCompletionTest::test_duplicate_refund_is_rejected_and_audit_remains_single`; **PASS**; otomatis 2026-10-25 |
| REF-06 | Handover terlambat payment PAID | EXPIRED atomik | `Sprint1WorkflowTest::test_late_handover_persists_expiry_and_refund_pending_before_rejecting`; **PASS**; otomatis 2026-10-25 |
| REP-01 | Admin dashboard KPI | KPI tampil | Sesuai; **PASS**; `uat/evidence/admin/UAT-REP-01-02-admin-dashboard-charts.png`; otomatis 2026-10-25 |
| REP-02 | Admin dashboard chart | Layout tidak rusak | Sesuai; **PASS**; bukti dashboard di atas; otomatis 2026-10-25 |
| REP-03 | Customer dashboard | Rental aktif/jatuh tempo terdekat | `Sprint2ComplianceTest::test_customer_dashboard_and_rental_detail_expose_due_dates_and_unit_details`; **PASS**; otomatis 2026-10-25 |
| REP-04 | Customer dashboard lintas akun | Hanya data sendiri | `Sprint2ComplianceTest::test_customer_dashboard_and_rental_detail_expose_due_dates_and_unit_details`; **PASS**; otomatis 2026-10-25 |
| REP-05 | Admin report filter | Hasil sesuai | `Sprint2ComplianceTest::test_report_filters_artifact_and_fine_status_and_exposes_financial_totals`; **PASS**; otomatis 2026-10-25 |
| REP-06 | Admin cetak histori | Histori dapat digunakan | `potionvault.spec.js` admin report print handler test; **PASS**; otomatis 2026-10-25 |
| REP-07 | Customer detail rental | Hanya rental sendiri | `PartialRequirementsTest::test_customer_rental_isolation_and_admin_only_return_are_enforced`; **PASS**; otomatis 2026-10-25 |
| VAL-01 | Form invalid/required | Error jelas, tidak tersimpan | `PartialRequirementsTest::test_invalid_server_requests_do_not_persist`; **PASS**; otomatis 2026-10-25 |
| VAL-02 | Upload invalid/lebih 2MB | Ditolak | `ArtifactImageTest::test_invalid_or_oversized_images_are_rejected_and_customer_cannot_upload`; **PASS**; otomatis 2026-10-25 |
| VAL-03 | Customer Chromium 1440x900 | Navigasi/layout usable | Sesuai responsive suite; **PASS**; otomatis 2026-10-25 |
| VAL-04 | Customer/Admin Chromium 768x1024 | Tablet usable, tabel scroll | Sesuai responsive suite; **PASS**; otomatis 2026-10-25 |
| VAL-05 | Customer/Admin Chromium 390x844 | Mobile usable, elemen tidak tertutup | Sesuai; **PASS**; `uat/evidence/responsive/UAT-VAL-05-mobile-catalog.png`; otomatis 2026-10-25 |
| VAL-06 | Customer/Admin tiga viewport | Tidak ada overflow tak semestinya | Sesuai 6 responsive tests; **PASS**; otomatis 2026-10-25 |

**Rekap matriks:** 81 skenario, 81 PASS, 0 NOT RUN, 0 FAIL, 0 BLOCKED.

## 3. Hasil automated testing dan concurrency

- `npm.cmd run build`: **sukses**; warning non-blocking mengenai optional `fontaine` dan dua referensi gambar dokumentasi.
- `php artisan test`: **40 passed**, 200 assertions.
- `npx playwright test`: **20 passed** — 9 business/browser, 6 responsive, 5 UAT evidence.
- `php artisan route:list`: **54 routes**.
- `php artisan view:cache`: **sukses**.
- `php artisan view:cache`: **sukses** setelah environment storage runtime dibersihkan.
- MySQL concurrency pada `potionvault_uat_test`: same-unit menghasilkan 1 sukses + 1 ditolak (`unit sudah tidak tersedia`); one-quota-left menghasilkan 1 sukses + 1 ditolak (`Maksimal dua unit aktif`). Tidak ada double booking atau pelanggaran batas dua unit.
- Isolasi: sebelum run tidak ada listener port 8000–8002; server UAT dijalankan sementara dengan `DB_DATABASE=potionvault_uat_test` dan `LARAVEL_STORAGE_PATH=uat-runtime-storage`, lalu dihentikan. Port 8001 kembali kosong setelah run.

## 4. Responsive dan defect log

Chromium menguji desktop `1440x900`, tablet `768x1024`, dan mobile `390x844`; 6 test responsive lulus. Tidak ditemukan overflow horizontal tak semestinya atau elemen penting tertutup pada run sukses.

Percobaan awal sempat memakai proses Laravel lama di port 8001 yang terhubung ke database development sehingga dua test tidak menemukan checkbox unit tersedia. Listener stale dihentikan, UAT database di-reset terisolasi, dan full suite diulang dengan hasil 20/20 PASS. Ini masalah setup test, bukan defect aplikasi. Tidak ada defect aplikasi baru yang ditemukan. Coverage tambahan menambahkan test Feature, bukan mengubah business logic Laravel.

Satu run regression paralel juga sempat gagal pada satu test karena `php artisan test` dan `npm run build` dijalankan bersamaan sehingga manifest Vite sedang tergantikan. Run berurutan setelah build selesai menghasilkan **40/40 PASS**.

Pada rangkaian final, `view:cache` pertama kali menemukan path view runtime yang sudah dibersihkan; perintah diulang dengan environment normal dan berhasil. Hasil ini dicatat sebagai setup cleanup, bukan defect aplikasi.

## 5. Bukti, file, dan sign-off

Bukti screenshot berada di [`docs/uat/evidence/`](uat/evidence/). File baru untuk pekerjaan ini:

- `docs/PotionVault_UAT_Final.md`
- `tests/browser/uat-evidence.spec.js`
- `tests/Feature/UatCoverageCompletionTest.php`
- `docs/uat/evidence/**`

Tidak ada screenshot tambahan yang dibuat untuk 12 skenario terakhir karena seluruhnya berupa validasi server-side, authorization, dan state database; screenshot browser yang relevan dari run sebelumnya tetap dipertahankan. Human acceptance masih pending. Seluruh 81 skenario otomatis sudah dieksekusi, tetapi tester manusia tetap harus meninjau bukti, melakukan acceptance review, lalu mengisi nama, tanggal, dan tanda tangan.

**Status akhir: UAT PARTIALLY READY** — automated regression, concurrency, dan responsive evidence lulus; final acceptance belum dapat dinyatakan sebelum UAT manual dan sign-off manusia selesai.

**Nama/tanda tangan:** ____________________________________  
**Tanggal:** ____________________  
**Catatan:** __________________________________________________
