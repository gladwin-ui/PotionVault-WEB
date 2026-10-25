# PotionVault — Business Requirements Document Final

## 1. Latar belakang

PotionVault adalah sistem web untuk penyewaan unit fisik ramuan dan artefak magis kepada Customer umum. Sistem menggantikan pencatatan manual dengan katalog, reservasi, approval, pembayaran manual, handover, pengembalian, denda, refund, dan laporan terpusat.

Dokumen ini menggunakan requirement pada `Bahan Soal.docx` dan mencatat implementasi Laravel aktual. Fitur yang belum memiliki bukti serah-terima tidak dinyatakan selesai hanya karena tersedia di source.

## 2. Tujuan bisnis

1. Menyediakan katalog unit yang dapat dicari dan dikelola Admin.
2. Menjaga setiap unit fisik dapat dibedakan melalui kode unik.
3. Mencegah benturan reservasi dan membatasi Customer maksimal dua unit aktif.
4. Memelihara histori rental, pembayaran, pengembalian, denda, dan refund.
5. Menyediakan laporan operasional yang dapat dicetak.

## 3. Stakeholder dan peran

| Stakeholder | Tanggung jawab |
|---|---|
| Admin/Potion Keeper | Mengelola unit, kategori, Customer, approval, payment, handover, return, denda, refund, dan laporan. |
| Customer/User | Terdaftar, login, mengelola profil, mencari unit, mengajukan rental, membayar melalui proses manual, dan memantau rental sendiri. |
| Pemilik/evaluator | Menilai kesesuaian fitur dan deliverable terhadap Bahan Soal serta PRD. |

## 4. Ruang lingkup

### Termasuk

- Authentication, registration, profile tunggal, dan perubahan password.
- CRUD unit, kategori, dan Customer dengan kebijakan penghapusan yang aman: unit menggunakan soft delete tanpa fitur restore Admin, Customer menggunakan soft delete dan dapat di-restore, sedangkan kategori dapat dihapus hanya jika belum digunakan.
- Kategori many-to-many, kode unit unik, foto unit, kondisi, rarity, harga, dan status.
- Rental maksimal dua unit, approval/rejection, cancellation `PENDING`, expiry 24 jam, payment manual, refund, handover lima hari, partial return, fine, dashboard, billing, dan report.
- Migration, seeder, Laravel tests, dan Playwright tests.

### Tidak termasuk

Payment gateway, aplikasi mobile native, pengiriman barang, dan efek sihir nyata.

## 5. Kebutuhan fungsional

| ID | Kebutuhan | Aktor |
|---|---|---|
| BR-01 | User harus login untuk fitur operasional dan Customer harus terdaftar. | Admin/Customer |
| BR-02 | Satu user memiliki satu profile dan dapat memperbarui data profil/password sendiri. | Admin/Customer |
| BR-03 | Admin dapat menambah dan memperbarui unit, kategori, dan Customer. Unit dapat dinonaktifkan melalui soft delete tanpa restore Admin; Customer dapat dihapus secara soft delete dan di-restore; kategori dapat dihapus jika tidak sedang digunakan. Histori transaksi dipertahankan. | Admin |
| BR-04 | Unit memiliki nama, kode unik, kategori jamak, rarity, harga, kondisi, status, deskripsi, dan foto. | Admin |
| BR-05 | Customer dapat mencari unit berdasarkan nama/kode dan melihat katalog/detail. | Customer |
| BR-06 | Customer dapat mengajukan maksimal dua unit yang tersedia. | Customer |
| BR-07 | Admin dapat approve/reject rental dan mencatat alasan penolakan. | Admin |
| BR-08 | Customer dapat membatalkan rental hanya saat `PENDING`. | Customer |
| BR-09 | Admin dapat mengonfirmasi payment manual sebelum handover. | Admin |
| BR-10 | Sistem memproses expiry scheduler maupun expiry saat operasi terlambat. | Sistem/Admin |
| BR-11 | Sistem memproses handover dan menetapkan masa lima hari. | Admin |
| BR-12 | Admin dapat mengembalikan unit satu per satu dengan kondisi dan catatan. | Admin |
| BR-13 | Sistem menghitung fine per unit dan Admin dapat mengonfirmasi pelunasannya. | Admin/Sistem |
| BR-14 | Admin dapat mencatat refund paid-expired secara auditable. | Admin |
| BR-15 | Admin dapat melihat dashboard, filter report, dan mencetak laporan. | Admin |
| BR-16 | Customer hanya dapat melihat rental miliknya. | Customer |

## 6. Aturan bisnis

- `unit_code` unik; nama unit boleh sama.
- Satu unit boleh memiliki banyak kategori.
- Rental aktif Customer dihitung dari `PENDING`, `APPROVED`, dan `BORROWED`; total maksimum dua item.
- Unit yang dipilih berubah menjadi `RESERVED` dalam transaksi database.
- Approval memberi waktu pickup 24 jam.
- `APPROVED + PAID` yang melewati expiry menjadi `EXPIRED`, unit kembali `AVAILABLE`, dan payment menjadi `REFUND_PENDING`.
- Admin mengubah `REFUND_PENDING` menjadi `REFUNDED` dengan waktu, aktor, dan catatan; histori tidak dihapus.
- Handover mensyaratkan status rental `APPROVED`, payment `PAID`, dan unit `RESERVED`.
- Due date adalah lima hari dari waktu handover.
- Fine adalah Rp2.000 per periode keterlambatan 24 jam yang dimulai per unit.
- Return `GOOD` membuat unit `AVAILABLE`; `MAINTENANCE`/`DAMAGED` mempertahankan status kondisi tersebut.
- Rental menjadi `RETURNED` jika seluruh item telah dikembalikan.
- Soft delete Customer dan unit menjaga histori. Customer dapat di-restore oleh Admin; unit belum memiliki fitur restore Admin. Kategori tidak menggunakan restore universal dan hanya dapat dihapus jika belum dipakai unit. Unit atau Customer dengan aktivitas rental aktif tidak dapat dihapus aman.

## 7. Acceptance criteria

- Login, role guard, ownership rental, dan profile tunggal bekerja.
- Duplicate unit code ditolak.
- Kategori jamak tersimpan.
- Batas maksimal dua unit ditegakkan.
- Approval, cancellation, expiry, payment, refund, handover, partial return, fine, dan report mengikuti status pada ERD/flowchart.
- Dua customer tidak dapat memesan unit yang sama secara valid bersamaan.
- Automated test dan browser test yang tercatat pada README tetap PASS.

## 8. Bukti penyerahan yang masih harus dilengkapi

Nama kelompok/anggota, screenshot schema database, video demo, UAT formal, manual book, dan URL GitLab belum tersedia di workspace sehingga statusnya tetap `BELUM ADA BUKTI`.
