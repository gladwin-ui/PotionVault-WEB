# 🧪 PotionVault — Magical Potion & Artifact Rental System

<p align="center">
  <img src="https://img.shields.io/badge/PotionVault-Magical_Rental-6D28D9?style=for-the-badge" alt="PotionVault" />
  <img src="https://img.shields.io/badge/Laravel-Framework-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/PHP-Backend-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Blade-Templates-F55247?style=for-the-badge" alt="Blade" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-Styling-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Alpine.js-Interactivity-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js" />
  <img src="https://img.shields.io/badge/Chart.js-Dashboard-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" alt="Chart.js" />
</p>

<p align="center"><strong>Sistem Penyewaan Ramuan dan Artefak Sihir Berbasis Web</strong></p>
<p align="center">Platform rental barang magis untuk pelanggan umum: katalog artefak, persetujuan penyewaan, pembayaran, pengembalian, denda, dan laporan.</p>

<p align="center">
  <a href="#overview">Overview</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#brd--business-requirements-document">BRD</a> •
  <a href="#flowchart">Flowchart</a> •
  <a href="#erd--entity-relationship-diagram">ERD</a>
</p>

---

## 📖 Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [BRD — Business Requirements Document](#brd--business-requirements-document)
  - [Latar Belakang dan Tujuan](#latar-belakang-dan-tujuan)
  - [Aktor dan Fitur Utama](#aktor-dan-fitur-utama)
  - [Aturan Bisnis](#aturan-bisnis)
  - [Kebutuhan Nonfungsional](#kebutuhan-nonfungsional)
- [Flowchart](#flowchart)
  - [Alur Penyewaan](#alur-penyewaan)
  - [Alur Pengembalian dan Denda](#alur-pengembalian-dan-denda)
- [ERD — Entity Relationship Diagram](#erd--entity-relationship-diagram)

---

## Overview

**PotionVault** merupakan konsep platform penyewaan **botol ramuan dan artefak sihir yang dapat dipakai berulang kali** dalam dunia fantasi. Layanan ini **terbuka untuk masyarakat umum** yang telah terdaftar sebagai pelanggan, bukan khusus murid atau akademi sihir.

Pelanggan dapat mencari artefak, melihat kategori, efek fiktif, kelangkaan (*rarity*), tarif sewa dan ketersediaan, kemudian mengajukan penyewaan. Admin mengelola unit fisik, memutuskan persetujuan, mengonfirmasi pembayaran dan penyerahan, memproses pengembalian, serta mencetak laporan. Setiap barang fisik mempunyai **kode unit yang unik**, meskipun beberapa barang memiliki nama sama.

Contoh koleksi:

| Kode Unit | Nama Artefak | Rarity | Contoh Tarif per Periode (hingga 5 hari) |
|---|---|---|---:|
| HEAL-001 | Healing Potion Vessel | Common | Rp10.000 |
| INVS-001 | Invisibility Flask | Rare | Rp20.000 |
| MANA-001 | Mana Crystal Bottle | Common | Rp15.000 |
| TIME-001 | Time Freeze Vessel | Legendary | Rp50.000 |

> Tarif pada tabel merupakan **contoh rancangan awal** dan dapat diatur admin. Objek yang disewakan adalah wadah/artefak magis yang wajib dikembalikan, bukan ramuan sekali konsumsi.

## Tech Stack

Badge di bagian atas menunjukkan teknologi yang **direncanakan**:

- **Laravel + PHP** — backend, routing, validasi, autentikasi, dan logika transaksi.
- **MySQL** — penyimpanan data relasional.
- **Laravel Blade** — templating antarmuka.
- **Tailwind CSS** — desain responsif bernuansa *dark fantasy*.
- **Alpine.js** — interaksi sederhana pada antarmuka.
- **Chart.js** — grafik ringkasan dashboard.
- **Git + GitHub** — version control dan repository proyek.

> Versi dependensi akan ditentukan setelah inisialisasi proyek Laravel; badge sengaja tidak mengklaim versi tertentu.

---

## BRD — Business Requirements Document

### Latar Belakang dan Tujuan

Sebagai studi kasus fiktif, bisnis penyewaan barang sihir membutuhkan sistem terpusat untuk mencatat aset, ketersediaan, identitas pelanggan, permohonan sewa, tarif, pembayaran, pengembalian, dan denda. PotionVault bertujuan menghindari pencatatan yang terpisah, benturan reservasi barang, serta kesulitan memantau status transaksi.

Tujuan proyek adalah menyediakan layanan penyewaan melalui web yang dapat diawasi admin, menjaga setiap unit inventaris dapat ditelusuri, dan menghasilkan riwayat transaksi serta laporan yang dapat dicetak.

### Aktor dan Fitur Utama

**Admin / Potion Keeper**

- Login dan mengelola profil sendiri.
- CRUD unit artefak, kategori, serta pelanggan.
- Menentukan kode unit unik, kategori jamak, kondisi, rarity, foto, dan tarif sewa.
- Memproses pengajuan **approve/reject**.
- Mengonfirmasi pembayaran sewa sebelum barang diserahkan.
- Mengonfirmasi penyerahan dan pengembalian unit.
- Melihat/mengelola denda, termasuk status **BELUM DIBAYAR / LUNAS**.
- Memantau semua penyewaan dan mencetak riwayat/laporan.

**User / Customer**

- Login dan mengelola profil sendiri.
- Melihat katalog, mencari berdasarkan nama dan memfilter kategori.
- Mengajukan penyewaan barang yang tersedia.
- Melihat status pengajuan, rincian tarif, pinjaman aktif, tagihan, denda, dan riwayat sendiri.

### Aturan Bisnis

| ID | Aturan |
|---|---|
| BR-01 | Semua pengguna wajib login untuk mengakses fitur operasional; pelanggan harus terdaftar. |
| BR-02 | Setiap user memiliki tepat satu profil dan dapat mengubahnya sendiri. |
| BR-03 | Tiap unit fisik memiliki **kode unik**; nama unit boleh sama. |
| BR-04 | Satu unit dapat memiliki beberapa kategori (*many-to-many*). |
| BR-05 | Pelanggan maksimal memiliki **2 unit aktif**; status PENDING dan APPROVED juga memesan kuota. |
| BR-06 | Pengajuan memerlukan keputusan admin sebelum serah terima. |
| BR-07 | Persetujuan berlaku **24 jam**; tanpa serah terima sampai batas waktu, status menjadi EXPIRED dan reservasi dilepas. |
| BR-08 | Tarif sewa tetap dikenakan **per periode hingga 5 hari**, berdasarkan unit yang dipilih; nominal transaksi disimpan sebagai snapshot. |
| BR-09 | Biaya sewa harus **LUNAS sebelum penyerahan**; pembayaran dilakukan di luar aplikasi dan dikonfirmasi admin. |
| BR-10 | Masa sewa dimulai saat admin mengonfirmasi penyerahan; masa tanpa denda adalah **5 hari**. |
| BR-11 | Keterlambatan dikenai denda **Rp2.000 per hari per unit**. |
| BR-12 | Pengembalian hanya dikonfirmasi admin, termasuk kondisi unit setelah kembali. |
| BR-13 | Denda dicatat terpisah dari pembayaran biaya sewa; statusnya BELUM DIBAYAR atau LUNAS untuk denda positif. |
| BR-14 | Denda belum dibayar **tidak menghalangi** penyewaan baru bila persyaratan lain terpenuhi. |
| BR-15 | Pelanggan hanya melihat transaksinya sendiri; admin melihat semua dan bisa mencetak laporan. |
| BR-16 | Unit rusak/perawatan tidak boleh disewa; transaksi historis tetap dipertahankan. |

**Perhitungan denda:** `hari_terlambat × Rp2.000 × jumlah unit terlambat`, dihitung per unit menurut waktu pengembalian masing-masing. Untuk rancangan awal, bagian dari periode 24 jam keterlambatan dihitung sebagai satu hari.

**Status penyewaan:** `PENDING`, `APPROVED`, `REJECTED`, `EXPIRED`, `BORROWED`, `OVERDUE` (diturunkan dari jatuh tempo), dan `RETURNED`. Pengembalian parsial dilacak pada masing-masing item transaksi.

### Kebutuhan Nonfungsional

- Otorisasi berbasis role dan proteksi setiap endpoint backend.
- Hashing password, validasi server-side, dan pembatasan akses data pelanggan.
- Integritas data melalui *foreign key*, *unique constraint*, dan transaksi database untuk reservasi/pembayaran/penyerahan.
- Antarmuka responsif, konsistensi tampilan, dan pencatatan waktu serta petugas untuk tindakan penting.
- Penggunaan migration dan seeder Laravel untuk struktur dan contoh data awal.

---

## Flowchart

Diagram berikut menggunakan **Mermaid** dan dapat ditampilkan oleh renderer Markdown yang mendukung Mermaid.

### Alur Penyewaan

```mermaid
flowchart TD
    A([Mulai]) --> B[Customer login]
    B --> C[Pilih artefak]
    C --> D[Maksimal 2 unit]
    D --> E[Ajukan sewa]
    E --> F{Validasi<br/>berhasil?}

    F -- Tidak --> G[Tampilkan alasan]
    F -- Ya --> H[Simpan<br/>PENDING]

    H --> I{Keputusan<br/>Admin}
    I -- Tolak --> J[REJECTED<br/>Lepas reservasi]
    I -- Setujui --> K[APPROVED<br/>Batas ambil 24 jam]

    K --> L{Masih sebelum<br/>kedaluwarsa?}
    L -- Tidak --> M[EXPIRED<br/>Lepas reservasi]
    L -- Ya --> N[Customer bayar<br/>biaya sewa]

    N --> O{Pembayaran<br/>LUNAS?}
    O -- Belum --> P[Menunggu pembayaran]
    P --> L
    O -- Ya --> Q[Admin serahkan<br/>barang]

    Q --> R[BORROWED]
    R --> S[Customer gunakan<br/>artefak]
    S --> T[Admin proses<br/>pengembalian]

    T --> U{Terlambat?}
    U -- Tidak --> V[Tanpa denda]
    U -- Ya --> W[Hitung denda]

    W --> X[Catat status<br/>pembayaran denda]
    V --> Y[Simpan transaksi<br/>pengembalian]
    X --> Y
    Y --> Z([Selesai])

    G --> Z
    J --> Z
    M --> Z
```

> Pembayaran dan serah terima sebaiknya dilakukan dalam satu proses checkout admin. Jika pembayaran telah dicatat tetapi serah terima tidak jadi dilakukan, admin harus menangani koreksi/pengembalian dana secara eksplisit; rincian alur pengecualian akan difinalisasi pada PRD.

### Alur Pengembalian dan Denda

```mermaid

flowchart TD
    A([Mulai]) --> B[Customer kembalikan<br/>artefak]
    B --> C[Admin cari<br/>transaksi sewa]
    C --> D{Unit masih<br/>dipinjam?}

    D -- Tidak --> E[Tampilkan<br/>pesan error]
    D -- Ya --> F[Admin periksa<br/>kondisi artefak]

    F --> G[Catat tanggal<br/>pengembalian]
    G --> H{Melewati<br/>jatuh tempo?}

    H -- Tidak --> I[Denda Rp0]
    H -- Ya --> J[Hitung hari<br/>keterlambatan]

    J --> K[Denda Rp2.000<br/>per hari]
    K --> L[Status denda<br/>BELUM DIBAYAR]

    I --> M[Admin konfirmasi<br/>pengembalian]
    L --> M

    M --> N[Status unit<br/>RETURNED]
    N --> O{Kondisi<br/>artefak baik?}

    O -- Ya --> P[Unit AVAILABLE]
    O -- Tidak --> Q[Unit MAINTENANCE<br/>atau DAMAGED]

    P --> R{Semua unit<br/>dikembalikan?}
    Q --> R

    R -- Tidak --> S[Transaksi masih<br/>memiliki unit aktif]
    R -- Ya --> T[Transaksi selesai]

    S --> U([Selesai])
    T --> U
    E --> U

```

Admin dapat mengubah status denda menjadi **LUNAS** setelah pembayaran di luar sistem terkonfirmasi. Informasi pengembalian dan pembayaran dicatat untuk kebutuhan riwayat serta laporan.

---

## ERD — Entity Relationship Diagram

Rancangan awal memakai **8 tabel inti**. Atribut dan relasi final akan disesuaikan saat menulis migration Laravel.

```mermaid
erDiagram
    users ||--|| profiles : has
    users ||--o{ rentals : places
    rentals ||--|{ rental_items : contains
    rentals ||--o| payments : rental_payment
    units ||--o{ rental_items : rented_as
    units ||--o{ category_unit : classified_in
    categories ||--o{ category_unit : includes

    users {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        varchar role
    }
    profiles {
        bigint id PK
        bigint user_id FK,UK
        varchar phone
        text address
    }
    units {
        bigint id PK
        varchar unit_code UK
        varchar name
        decimal rental_price
        varchar rarity
        varchar condition
        varchar status
    }
    categories {
        bigint id PK
        varchar name
    }
    category_unit {
        bigint unit_id FK
        bigint category_id FK
    }
    rentals {
        bigint id PK
        bigint user_id FK
        varchar status
        decimal rental_total
        datetime approved_at
        datetime expires_at
    }
    rental_items {
        bigint id PK
        bigint rental_id FK
        bigint unit_id FK
        decimal rental_price_snapshot
        datetime borrowed_at
        datetime due_at
        datetime returned_at
        decimal fine_amount
        varchar fine_payment_status
    }
    payments {
        bigint id PK
        bigint rental_id FK,UK
        decimal amount
        varchar status
        datetime paid_at
        bigint confirmed_by FK
    }
```

**Penjelasan relasi:**

- `users` **1:1** `profiles`: setiap akun memiliki satu profil (`profiles.user_id` unik).
- `users` **1:N** `rentals`: satu pelanggan dapat melakukan banyak transaksi seiring waktu.
- `rentals` **1:N** `rental_items`: satu transaksi memiliki satu atau dua item.
- `units` **1:N** `rental_items`: satu unit dapat muncul dalam banyak transaksi historis, namun tidak boleh aktif dalam dua transaksi sekaligus.
- `units` **M:N** `categories`: diperantarai `category_unit`.
- `rentals` **1:0..1** `payments`: satu catatan pembayaran sewa pada rancangan awal, termasuk tagihan yang belum lunas; pembayaran denda dicatat per item secara terpisah.

**Catatan desain:** Kolom `confirmed_by` pada `payments` merujuk `users.id` milik admin. Pada migration final diperlukan kolom tambahan untuk persetujuan, pelunasan denda per item, kondisi pengembalian, timestamps, dan *soft deletes* sesuai kebutuhan. Status `OVERDUE` sebaiknya dihitung dari tenggat tiap item agar pengembalian sebagian tetap akurat.

---

> **Status proyek:** tahap dokumentasi dan perancangan. Desain, migration, seeder, implementasi, UAT, dan dokumentasi demo akan ditambahkan pada tahapan berikutnya.
