# PotionVault — Dokumen Full Deliverable Proyek

Dokumen ini merangkum seluruh pekerjaan yang telah dilakukan pada proyek PotionVault berdasarkan PRD, README, flowchart, testcase, serta kebutuhan pengembangan yang disepakati.

Tanggal pembaruan: 23 Oktober 2026  
Platform: Web  
Bahasa antarmuka: Bahasa Indonesia  
Pengecualian bahasa: istilah rarity tetap menggunakan `Common`, `Rare`, `Epic`, dan `Legendary`.

---

## 1. Ringkasan Proyek

PotionVault adalah aplikasi web penyewaan ramuan dan artefak magis. Customer dapat melihat katalog, mengajukan rental maksimal dua unit, memantau status rental, melihat tagihan dan denda, serta mengelola profil. Admin atau Potion Keeper dapat mengelola inventaris, kategori, customer, persetujuan rental, pembayaran, serah terima, pengembalian, denda, dan laporan.

Referensi desain digunakan sebagai acuan tema visual dark fantasy PotionVault. Fitur dan alur aplikasi mengikuti PRD dan README.

---

## 2. Tech Stack

- Laravel 13.38
- PHP 8.3
- MySQL untuk database utama
- SQLite in-memory untuk automated testing terisolasi
- Blade Template
- Tailwind CSS melalui Vite
- CSS custom tema PotionVault
- Alpine.js
- Chart.js
- PHPUnit melalui Laravel Testing
- Laravel Scheduler

---

## 3. Role dan Hak Akses

### Customer/User

Customer dapat:

- Registrasi akun.
- Login dan logout.
- Melihat dan mengubah profil sendiri.
- Melihat dashboard customer.
- Melihat katalog artefak.
- Mencari artefak berdasarkan nama.
- Filter berdasarkan kategori dan rarity.
- Melihat detail artefak.
- Mengajukan rental satu atau dua unit.
- Membatalkan pengajuan yang masih `PENDING`.
- Melihat rental milik sendiri.
- Melihat status rental dan status `OVERDUE`.
- Melihat tagihan rental.
- Melihat denda.

Customer tidak dapat:

- Mengakses halaman admin.
- Mengubah data customer lain.
- Menyetujui atau menolak rental.
- Mengonfirmasi pembayaran.
- Melakukan serah terima.
- Memproses pengembalian.
- Melunasi denda secara mandiri.

### Admin/Potion Keeper

Admin dapat:

- Login dan logout.
- Mengubah profil admin sendiri.
- Melihat dashboard admin.
- Mengelola artefak.
- Mengelola kategori.
- Mengelola customer.
- Melihat seluruh pengajuan rental.
- Menyetujui atau menolak rental.
- Mengonfirmasi pembayaran rental.
- Melakukan serah terima artefak.
- Memproses pengembalian per unit.
- Mengatur kondisi unit setelah pengembalian.
- Mengonfirmasi pelunasan denda.
- Melihat dan mencetak laporan.

---

## 4. Fitur Customer yang Telah Diimplementasikan

### 4.1 Registrasi

Input yang tersedia:

- Nama lengkap.
- Email.
- Nomor telepon.
- Password.
- Konfirmasi password.

Hasil registrasi:

- Akun dibuat dengan role `user`.
- Satu profile dibuat otomatis.
- Password disimpan dalam bentuk hash.
- Customer diarahkan ke dashboard setelah registrasi.

### 4.2 Login dan Logout

- Email dan password divalidasi.
- Akun tidak aktif tidak dapat login.
- Customer diarahkan ke dashboard customer.
- Admin diarahkan ke dashboard admin.
- Logout mengakhiri session dan menghapus autentikasi.

### 4.3 Dashboard Customer

Dashboard menampilkan:

- Jumlah unit aktif.
- Jumlah pengajuan yang menunggu persetujuan.
- Total denda yang belum lunas.
- Daftar rental terbaru.
- Tombol menuju katalog dan riwayat rental.

### 4.4 Katalog Artefak

Katalog mendukung:

- Pencarian berdasarkan nama artefak.
- Filter kategori.
- Filter rarity.
- Pagination.
- Tampilan kode unit.
- Harga sewa per lima hari.
- Kondisi unit.
- Status ketersediaan.
- Tombol detail dan pengajuan rental.

Rarity yang digunakan:

- `Common`
- `Rare`
- `Epic`
- `Legendary`

### 4.5 Detail Artefak

Detail artefak menampilkan:

- Nama artefak.
- Kode unit unik.
- Deskripsi.
- Efek magis.
- Kategori.
- Rarity.
- Kondisi.
- Harga sewa.
- Status ketersediaan.
- Tombol tambah ke pengajuan.

Unit yang tidak tersedia tidak dapat diajukan.

### 4.6 Pengajuan Rental

Aturan pengajuan:

- Customer dapat memilih satu atau dua unit.
- Unit yang dipilih harus tersedia.
- Unit yang sama tidak dapat dipilih dua kali.
- Maksimal dua unit aktif per customer.
- Harga unit disimpan sebagai price snapshot.
- Status awal rental adalah `PENDING`.
- Unit langsung berubah menjadi `RESERVED`.
- Pembayaran dibuat dengan status `UNPAID`.

### 4.7 Pembatalan Rental oleh Customer

Customer dapat membatalkan rental hanya jika statusnya masih `PENDING`.

Ketika dibatalkan:

- Status rental berubah menjadi `CANCELLED`.
- Unit reservasi dikembalikan menjadi `AVAILABLE`.
- Pembatalan dapat dilakukan dari halaman daftar rental.
- Pembatalan dapat dilakukan dari halaman detail rental.
- Konfirmasi pembatalan tampil langsung di halaman, bukan popup browser.

Rental yang sudah `APPROVED`, `BORROWED`, `RETURNED`, atau `REJECTED` tidak dapat dibatalkan oleh customer.

### 4.8 My Rentals

Customer dapat melihat:

- Nomor transaksi.
- Jumlah unit.
- Total harga rental.
- Status rental.
- Status pembayaran.
- Detail unit.
- Status unit.
- Jatuh tempo.
- Status `OVERDUE` jika melewati jatuh tempo.

### 4.9 Billing dan Denda

Halaman tagihan menampilkan:

- Total tagihan rental yang belum lunas.
- Total denda yang belum lunas.
- Status pembayaran rental.
- Total denda setiap rental.
- Riwayat tagihan customer.

Pembayaran rental dan denda dipisahkan sesuai PRD.

### 4.10 Profil Customer

Customer dapat mengubah:

- Nama.
- Nomor telepon.
- Alamat.

Email ditampilkan sebagai informasi dan tidak diubah melalui form profil.

---

## 5. Fitur Admin yang Telah Diimplementasikan

### 5.1 Dashboard Admin

Dashboard admin menggunakan data database nyata dan menampilkan:

- Total artefak.
- Unit tersedia.
- Rental aktif.
- Unit terlambat.
- Pengajuan pending.
- Jumlah customer.
- Denda belum lunas.
- Pendapatan dari pembayaran rental yang sudah lunas.
- Grafik tren rental enam bulan terakhir.
- Pengajuan rental terbaru.

### 5.2 Manajemen Artefak

Admin dapat:

- Menambah artefak.
- Mengedit artefak.
- Menonaktifkan artefak.
- Mengatur kode unit unik.
- Mengatur nama artefak.
- Mengatur deskripsi.
- Mengatur efek magis.
- Mengatur kategori.
- Mengatur rarity.
- Mengatur harga rental.
- Mengatur kondisi.
- Mengunggah foto artefak.

Validasi yang tersedia:

- Kode unit wajib unik.
- Harga tidak boleh negatif.
- Rarity harus menggunakan nilai yang valid.
- Kondisi harus menggunakan nilai yang valid.
- Minimal satu kategori harus dipilih.
- File gambar divalidasi tipe dan ukuran.

Unit yang masih memiliki rental aktif tidak dapat dinonaktifkan.

### 5.3 Manajemen Kategori

Admin dapat:

- Menambah kategori.
- Mengubah kategori.
- Menghapus kategori yang belum digunakan.
- Melihat jumlah unit pada setiap kategori.

Kategori yang masih digunakan oleh unit tidak dapat dihapus.

### 5.4 Manajemen Customer

Admin dapat:

- Menambah customer.
- Mengubah nama customer.
- Mengubah email customer.
- Mengubah nomor telepon.
- Mengubah alamat.
- Menonaktifkan customer.
- Mengaktifkan kembali customer.

Menonaktifkan customer tidak menghapus histori rental.

### 5.5 Persetujuan Rental

Admin dapat melihat:

- Referensi rental.
- Customer.
- Artefak yang dipilih.
- Total harga.
- Status rental.
- Status pembayaran.

Untuk rental `PENDING`, Admin dapat:

- Menyetujui rental.
- Menolak rental.
- Menulis alasan penolakan.

Saat disetujui:

- Status menjadi `APPROVED`.
- Waktu persetujuan disimpan.
- Admin yang menyetujui disimpan.
- Batas pengambilan dibuat 24 jam.

Saat ditolak:

- Status menjadi `REJECTED`.
- Alasan disimpan.
- Unit reservasi dilepaskan.

### 5.6 Pembayaran Rental

Pembayaran dilakukan secara manual di luar sistem.

Admin dapat:

- Melihat rental yang belum dibayar.
- Mengonfirmasi pembayaran.
- Menyimpan waktu pembayaran.
- Menyimpan Admin yang mengonfirmasi.

Pembayaran hanya dapat dikonfirmasi pada rental yang telah disetujui dan belum kedaluwarsa.

### 5.7 Serah Terima

Admin dapat melakukan serah terima jika:

- Rental berstatus `APPROVED`.
- Batas 24 jam belum lewat.
- Pembayaran berstatus `PAID`.
- Unit masih berstatus `RESERVED`.

Setelah serah terima:

- Rental berubah menjadi `BORROWED`.
- Waktu peminjaman disimpan.
- Jatuh tempo dibuat lima hari setelah serah terima.
- Unit berubah menjadi `BORROWED`.

### 5.8 Pengembalian

Admin dapat memproses pengembalian setiap unit secara terpisah.

Kondisi yang tersedia:

- `GOOD` — unit kembali tersedia.
- `MAINTENANCE` — unit masuk perawatan.
- `DAMAGED` — unit rusak dan tidak tersedia.

Admin juga dapat menyimpan catatan pengembalian.

Pengembalian parsial didukung. Jika satu dari dua unit dikembalikan, unit lainnya tetap aktif.

### 5.9 Denda

Denda dihitung dengan aturan:

- Rp2.000 per hari keterlambatan.
- Dihitung per unit.
- Satu hari dihitung setiap periode 24 jam yang dimulai.
- Denda tidak dibuat jika pengembalian tepat waktu.
- Denda memiliki status terpisah dari pembayaran rental.

Admin dapat mengonfirmasi pelunasan denda dan sistem menyimpan:

- Status pelunasan.
- Waktu pelunasan.
- Admin yang mengonfirmasi.

### 5.10 Laporan

Admin dapat memfilter laporan berdasarkan:

- Tanggal mulai.
- Tanggal selesai.
- Customer.
- Status pembayaran.
- Status rental.

Laporan menampilkan:

- Referensi rental.
- Customer.
- Jumlah unit.
- Total rental.
- Status pembayaran.
- Total denda.
- Status rental.

Laporan dapat dicetak menggunakan fitur cetak browser.

---

## 6. Alur Rental Lengkap

```text
Customer login
    ↓
Memilih satu atau dua artefak
    ↓
Mengirim pengajuan rental
    ↓
Status PENDING dan unit RESERVED
    ↓
Admin menyetujui atau menolak
    ├── Ditolak → REJECTED dan unit AVAILABLE
    └── Disetujui → APPROVED, berlaku 24 jam
                         ↓
                  Admin konfirmasi pembayaran
                         ↓
                  Admin melakukan serah terima
                         ↓
                  Rental BORROWED, durasi 5 hari
                         ↓
                  Admin memproses pengembalian
                         ↓
                  Sistem menghitung denda jika terlambat
                         ↓
                  Unit kembali tersedia/perawatan/rusak
                         ↓
                  Semua unit kembali → RETURNED
```

Alur pembatalan customer:

```text
Customer memiliki rental PENDING
    ↓
Customer memilih Batalkan
    ↓
Konfirmasi inline di halaman
    ↓
Status menjadi CANCELLED
    ↓
Unit kembali AVAILABLE
```

---

## 7. Struktur Database

Tabel utama:

- `users` — akun dan role.
- `profiles` — data profil satu-ke-satu.
- `units` — unit fisik artefak.
- `categories` — kategori artefak.
- `category_unit` — relasi many-to-many unit dan kategori.
- `rentals` — data utama pengajuan rental.
- `rental_items` — detail unit dalam rental.
- `payments` — pembayaran biaya rental.

Field penting:

- `units.unit_code` unik.
- `users.role` membedakan `admin` dan `user`.
- `users.is_active` untuk status akun.
- `rental_items.rental_price_snapshot` untuk menjaga harga historis.
- `rental_items.fine_amount` untuk nominal denda.
- `rental_items.fine_payment_status` untuk status pelunasan denda.
- `rentals.rejection_reason` untuk alasan penolakan.
- `rentals.expires_at` untuk batas pengambilan 24 jam.

---

## 8. Keamanan dan Validasi

Implementasi keamanan yang tersedia:

- Middleware autentikasi untuk halaman operasional.
- Middleware admin untuk halaman admin.
- Customer hanya dapat melihat rental miliknya sendiri.
- Customer tidak dapat mengakses dashboard admin.
- Role admin tidak dapat dibuat melalui registrasi publik.
- Validasi request dilakukan di server.
- CSRF token pada seluruh form POST/PUT/DELETE.
- Password disimpan dengan hashing.
- Transaksi rental menggunakan database transaction.
- Unit dikunci saat proses reservasi menggunakan `lockForUpdate()`.
- Validasi status dilakukan ulang saat approval, pembayaran, handover, dan pengembalian.

---

## 9. Notifikasi dan Interaksi

Notifikasi proses ditampilkan langsung di halaman menggunakan komponen `.notice`.

Popup browser seperti `alert()` dan `confirm()` tidak digunakan untuk notifikasi aplikasi.

Konfirmasi pembatalan rental menggunakan elemen inline di halaman dengan pilihan:

- `Ya, batalkan`.
- `Tidak`.

---

## 10. File Penting yang Dibuat atau Diubah

### Backend

- `app/Services/RentalWorkflowService.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/Customer/HomeController.php`
- `app/Http/Controllers/Customer/RentalController.php`
- `app/Http/Controllers/Customer/CatalogController.php`
- `app/Http/Controllers/Customer/ProfileController.php`
- `app/Http/Controllers/Customer/BillingController.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/ArtifactController.php`
- `app/Http/Controllers/Admin/CategoryController.php`
- `app/Http/Controllers/Admin/CustomerController.php`
- `app/Http/Controllers/Admin/RentalWorkflowController.php`
- `app/Http/Controllers/Admin/ResourceController.php`
- `app/Http/Controllers/Admin/ProfileController.php`

### View/Frontend

- `resources/views/components/customer-nav.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/customer/*`
- `resources/views/admin/*`
- `resources/css/asset-overrides.css`
- `resources/js/app.js`

### Database

- `database/migrations/2026_10_22_000003_create_potionvault_tables.php`
- `database/migrations/2026_10_23_000004_add_workflow_fields.php`
- `database/seeders/DatabaseSeeder.php`
- `database/factories/UserFactory.php`

### Pengujian dan Dokumentasi

- `tests/Feature/PotionVaultFoundationTest.php`
- `tests/Feature/RentalWorkflowTest.php`
- `tests/Feature/UiBackendAuditTest.php`
- `docs/prd-compliance-report.md`
- `docs/ui-backend-audit.md`
- `docs/bugfix-report.md`
- `docs/testing-results.md`
- `docs/full-deliverable-report.md`

---

## 11. Hasil Verifikasi Terakhir

Pemeriksaan yang telah dijalankan:

```text
php artisan test
```

Hasil terakhir:

- 26 test berhasil.
- 72 assertion berhasil.
- Tidak ada test yang dihapus untuk mendapatkan status PASS.

```text
php artisan view:cache
```

Hasil:

- Kompilasi seluruh Blade berhasil.

```text
npm.cmd run build
```

Hasil:

- Build Vite berhasil.
- Asset CSS, JavaScript, font, dan gambar berhasil dibuat.
- Terdapat warning non-fatal dari URL gambar legacy pada CSS.

```text
php artisan route:list
```

Hasil terakhir:

- 48 route terdaftar.

Database development atau production tidak di-reset saat proses pengembangan dan verifikasi.

---

## 12. Cara Menjalankan Proyek

Pastikan Laragon/MySQL aktif, lalu buka terminal pada folder proyek.

### Menyiapkan database

Buat database MySQL dengan nama yang sesuai dengan `.env`, misalnya:

```sql
CREATE DATABASE laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Pastikan konfigurasi `.env` sesuai:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

### Instalasi dan migrasi

```powershell
composer install
npm install
php artisan migrate --seed
```

### Menjalankan aplikasi

Terminal pertama:

```powershell
php artisan serve
```

Terminal kedua untuk perubahan CSS/JavaScript:

```powershell
npm.cmd run dev
```

Buka:

```text
http://127.0.0.1:8000
```

Jika perubahan Blade/CSS belum terlihat, lakukan hard refresh:

```text
Ctrl + Shift + R
```

Jika diperlukan, bersihkan cache:

```powershell
php artisan optimize:clear
```

---

## 13. Data Login Seeder

Seeder membuat akun contoh:

### Admin

- Email: `admin@potionvault.test`
- Password default factory: `password`

### Customer

- Email: `aria@potionvault.test`
- Password default factory: `password`

Gunakan password sesuai data aktual setelah proses seeding.

---

## 14. Batasan yang Masih Ada

Beberapa hal masih menjadi catatan pengembangan lanjutan:

- Update password belum memiliki halaman khusus.
- Pengujian concurrency khusus MySQL belum dijalankan karena automated test menggunakan SQLite in-memory.
- Browser visual testing otomatis tidak dilakukan apabila browser in-app tidak tersedia.
- Payment gateway tidak dibuat karena pembayaran pada PRD dilakukan manual dan dikonfirmasi Admin.
- Fitur PDF khusus belum dibuat; laporan menggunakan fitur cetak browser.

Batasan tersebut tidak mengubah alur utama rental yang telah diimplementasikan.

---

## 15. Kesimpulan Deliverable

Proyek PotionVault telah memiliki fondasi Laravel yang terintegrasi dengan database, autentikasi dua role, katalog artefak, pengajuan rental, approval Admin, pembayaran manual, serah terima, pengembalian per unit, perhitungan denda, pembatalan rental customer, billing, dashboard, laporan, validasi, dan tampilan tema dark fantasy.

Dokumen ini menjadi ringkasan utama deliverable proyek. File laporan tambahan dan file automated testing telah dibersihkan sesuai permintaan, sedangkan dokumen PRD, README, asset referensi, dan source aplikasi tetap dipertahankan.
