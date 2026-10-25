# PotionVault — Product Requirements Document Final

**Versi:** 2.0 — disinkronkan dengan implementasi Laravel aktual 25 Oktober 2026
**Sumber normatif:** `Bahan Soal.docx` dan kebutuhan bisnis PotionVault yang disetujui.
**Catatan:** dokumentasi ini tidak mengubah requirement untuk menutupi gap deliverable yang belum memiliki bukti.

## 1. Ringkasan produk

PotionVault adalah aplikasi web penyewaan unit fisik ramuan dan artefak magis. Customer terdaftar dapat mencari unit, melihat detail, dan mengajukan rental. Admin mengelola unit, kategori, Customer, approval, pembayaran manual, handover, pengembalian, denda, refund, dan laporan.

## 2. Peran dan akses

| Fitur | Admin | Customer |
|---|:---:|:---:|
| Login/logout dan edit profil sendiri | Ya | Ya |
| Registrasi | Tidak | Ya |
| Ganti password dengan password lama | Ya | Ya |
| CRUD unit, kategori, Customer | Ya | Tidak |
| Upload/ganti/hapus foto unit | Ya | Tidak |
| Katalog, pencarian, filter, detail | Ya | Ya |
| Ajukan rental | Tidak | Ya |
| Batalkan rental `PENDING` milik sendiri | Tidak | Ya |
| Approve/reject rental | Ya | Tidak |
| Konfirmasi payment dan refund | Ya | Tidak |
| Handover, return, fine payment | Ya | Tidak |
| Melihat seluruh report dan print | Ya | Tidak |
| Melihat rental sendiri | Tidak | Ya |

Nama role database adalah `admin` dan `user`; label UI menggunakan Admin/Potion Keeper dan Customer.

## 3. Fitur Customer

1. Registrasi membuat akun user aktif dan satu profile.
2. Login hanya berhasil untuk kredensial valid dan akun aktif.
3. Profile dapat memperbarui nama, email, telepon, alamat, serta password.
4. Katalog mendukung pencarian nama/kode serta filter kategori dan rarity.
5. Detail unit menampilkan kode, nama, foto, deskripsi, magical effect, harga, rarity, kategori, kondisi, dan availability.
6. Rental request menerima satu atau dua unit `AVAILABLE`.
7. Dashboard menampilkan unit aktif, rental aktif, pending approval, denda, dan jatuh tempo terdekat.
8. Detail rental menampilkan waktu pengajuan/approval, batas pickup, handover, due date, status rental/payment, status unit, return, dan fine.
9. Billing dan histori hanya mengambil data Customer yang sedang login.
10. Customer hanya dapat membatalkan rental miliknya yang berstatus `PENDING`.

## 4. Fitur Admin

1. Dashboard KPI: total/available unit, active/overdue item, pending rental, Customer, revenue payment `PAID`, dan unpaid fine.
2. Grafik tren rental enam bulan dan distribusi unit berdasarkan rarity.
3. Artifact CRUD dengan validasi field, kode unik, kategori jamak, kondisi, harga, dan foto.
4. Foto divalidasi sebagai JPG/JPEG/PNG/WEBP maksimal 2 MB; file disimpan pada public disk dan path disimpan di `units.image`.
5. Update dapat mengganti atau menghapus foto lama.
6. Kategori CRUD dengan nama unik.
7. Customer CRUD, toggle aktif/nonaktif, safe delete, soft delete, dan restore.
8. Rental request approval/rejection dengan alasan.
9. Payment manual, refund, handover, return per unit, pelunasan fine, dashboard operasi, dan report print.
10. Report dapat difilter berdasarkan status, Customer, artefak/kode, payment status, fine status, rentang tanggal, dan status overdue turunan.

## 5. Aturan bisnis

- Setiap user memiliki satu profile; `profiles.user_id` unik.
- Nama unit boleh sama, tetapi `units.unit_code` unik.
- Satu unit dapat memiliki banyak kategori melalui `category_unit`.
- Maksimal dua unit aktif per Customer. `PENDING`, `APPROVED`, dan `BORROWED` dihitung sebagai aktif.
- Request mengunci Customer dan unit dalam transaksi database, membuat payment `UNPAID`, dan mengubah unit menjadi `RESERVED`.
- Admin approval mengisi `approved_at`, `approved_by`, dan `expires_at = approved_at + 24 jam`.
- Customer cancel hanya pada `PENDING`; unit dilepas dan histori rental dipertahankan.
- Reject juga melepas unit yang masih `RESERVED`.
- Handover harus dilakukan sebelum expiry, payment harus `PAID`, dan unit harus `RESERVED`.
- Handover mengisi `borrowed_at`, `due_at = borrowed_at + 5 hari`, unit `BORROWED`, rental `BORROWED`.
- Expiry scheduler dan expiry saat Admin mencoba payment/handover sama-sama mengubah rental `EXPIRED` dan melepaskan unit.
- Jika payment sudah `PAID`, expiry mengubahnya menjadi `REFUND_PENDING`; Admin dapat menyelesaikannya menjadi `REFUNDED` dengan audit.
- Jika payment `UNPAID`, status tetap `UNPAID` ketika rental expired.
- Return diproses per item. Return `GOOD` membuat unit `AVAILABLE`; kondisi lain menjadi `MAINTENANCE` atau `DAMAGED`.
- Fine dihitung Rp2.000 per periode keterlambatan 24 jam yang dimulai per unit; fine positif menjadi `UNPAID` dan dapat dikonfirmasi Admin menjadi `PAID`.
- Rental induk menjadi `RETURNED` setelah semua item returned. `OVERDUE` adalah status tampilan/query, bukan kolom database.
- Soft delete unit/user mempertahankan histori; unit/customer dengan aktivitas aktif tidak boleh dihapus aman.

## 6. Status

| Entitas | Nilai aktual |
|---|---|
| Rental | `PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`, `EXPIRED`, `BORROWED`, `RETURNED` |
| Payment | `UNPAID`, `PAID`, `REFUND_PENDING`, `REFUNDED` |
| Unit | `AVAILABLE`, `RESERVED`, `BORROWED`, `MAINTENANCE`, `DAMAGED`, `DEACTIVATED` |
| Fine | `NO_FINE`, `UNPAID`, `PAID` |

## 7. Data dan teknis

Migration aktual membuat `users`, `profiles`, `units`, `categories`, `category_unit`, `rentals`, `rental_items`, dan `payments`. Detail primary key, foreign key, unique, nullable, index, soft delete, image, approval, expiry, return, fine, dan refund ada pada [ERD Final](PotionVault_ERD_Final.md).

Nonfungsional:

- Authorization middleware dan ownership query.
- Server-side validation dan password hashing.
- Transaction dan row locking pada reservasi, approval, payment, handover, return, fine, dan refund.
- Responsive Blade UI untuk desktop/tablet/mobile.
- Migration dan seeder sebagai sumber schema/data awal.

## 8. Acceptance criteria

| ID | Acceptance criterion |
|---|---|
| AC-01 | Login, registration, logout, role guard, dan profile/password bekerja. |
| AC-02 | Duplicate unit code ditolak dan kategori jamak tersimpan. |
| AC-03 | Customer tidak dapat melebihi dua unit aktif. |
| AC-04 | Dua request paralel tidak menghasilkan double booking. |
| AC-05 | Approval, reject, cancel, expiry scheduler, dan expiry handover konsisten. |
| AC-06 | Paid-expired menghasilkan `EXPIRED`, unit `AVAILABLE`, payment `REFUND_PENDING`, lalu dapat menjadi `REFUNDED`. |
| AC-07 | Payment wajib `PAID` sebelum handover dan due date lima hari. |
| AC-08 | Partial return, kondisi unit, fine, dan fine payment tersimpan. |
| AC-09 | Customer hanya melihat data miliknya; Admin melihat data operasional. |
| AC-10 | Dashboard, report filter, print, image upload, dan responsive browser flow bekerja. |

## 9. Status verifikasi

Pemeriksaan terbaru mencatat 24 Laravel test dengan 120 assertion PASS dan 15 Playwright test PASS pada MySQL testing terisolasi. `route:list` menampilkan 54 route, Blade view cache PASS, dan Vite build PASS dengan warning nonfatal optional `fontaine` serta referensi gambar dokumentasi.

UAT formal, manual book, video demo, screenshot schema database, dan URL repository GitLab belum tersedia sebagai bukti di workspace.

## 10. Referensi

- [BRD Final](PotionVault_BRD_Final.md)
- [ERD Final](PotionVault_ERD_Final.md)
- [Flowcharts Final](PotionVault_Flowcharts_Final.md)
- Bahan Soal asli berada di lokasi eksternal `D:\SEMESTER 7\SEERTIFIKASI\LSP\Bahan Soal.docx` dan tidak disalin ke repository.
