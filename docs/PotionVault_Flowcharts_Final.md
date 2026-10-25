# PotionVault — Flowcharts Final

Diagram berikut menggambarkan workflow aktual Laravel, termasuk pemisahan status rental, payment, fine, dan unit.

## 1. Registration dan login

```mermaid
flowchart TD
    A([Mulai]) --> B{Sudah punya akun?}
    B -- Tidak --> C[Registrasi Customer]
    C --> D[Buat users + satu profiles]
    B -- Ya --> E[Login]
    D --> E
    E --> F{Kredensial dan akun valid?}
    F -- Tidak --> G[Tampilkan error]
    F -- Ya --> H{Role}
    H -- Admin --> I[Admin dashboard]
    H -- User --> J[Customer dashboard]
```

## 2. Rental request sampai approval

```mermaid
flowchart TD
    A[Customer lihat katalog] --> B[Pilih 1-2 unit AVAILABLE]
    B --> C{Kuota Customer <= 2?}
    C -- Tidak --> D[Tolak request]
    C -- Ya --> E[Lock Customer dan unit]
    E --> F{Semua unit masih AVAILABLE?}
    F -- Tidak --> D
    F -- Ya --> G[Buat rental PENDING + snapshot harga + payment UNPAID]
    G --> H[Unit menjadi RESERVED]
    H --> I{Customer membatalkan?}
    I -- Ya --> J[CANCELLED + unit AVAILABLE]
    I -- Tidak --> K{Admin approve/reject}
    K -- Reject --> L[REJECTED + unit AVAILABLE]
    K -- Approve --> M[APPROVED + expiry 24 jam]
```

## 3. Expiry, payment, handover, dan paid-but-expired refund

```mermaid
flowchart TD
    A[APPROVED] --> B{Batas pickup 24 jam lewat?}
    B -- Tidak --> C{Admin konfirmasi payment?}
    C -- Tidak --> A
    C -- Ya --> D[Payment PAID]
    D --> E{Handover dilakukan sebelum expiry?}
    E -- Ya --> F[Unit BORROWED + rental BORROWED]
    E -- Tidak --> G[Rental EXPIRED]
    B -- Ya, scheduler atau operasi Admin --> G
    G --> H[Reserved unit menjadi AVAILABLE]
    H --> I{Payment PAID?}
    I -- Tidak --> J[Payment tetap UNPAID]
    I -- Ya --> K[Payment REFUND_PENDING]
    K --> L[Admin mencatat refund]
    L --> M[Payment REFUNDED + audit refund]
```

## 4. Handover, return, fine, dan completion

```mermaid
flowchart TD
    A[Rental APPROVED + Payment PAID] --> B[Admin handover]
    B --> C[Rental BORROWED; unit BORROWED]
    C --> D[Due date = handover + 5 hari]
    D --> E[Admin memilih item yang dikembalikan]
    E --> F[Catat returned_at dan return_condition]
    F --> G{Kondisi pengembalian}
    G -- GOOD --> H[Unit AVAILABLE]
    G -- MAINTENANCE --> I[Unit MAINTENANCE]
    G -- DAMAGED --> J[Unit DAMAGED]
    H --> K{Semua item returned?}
    I --> K
    J --> K
    K -- Tidak --> L[Rental tetap BORROWED; partial return]
    K -- Ya --> M[Rental RETURNED]
    F --> N{Melewati due_at?}
    N -- Tidak --> O[fine NO_FINE]
    N -- Ya --> P[Hitung fine Rp2.000 per periode 24 jam per unit]
    P --> Q[fine UNPAID]
    Q --> R{Admin menjalankan payFine}
    R -- Ya --> S[fine PAID + aktor/waktu]
    R -- Tidak --> T[fine tetap UNPAID]
```

### Ringkasan status

| Domain | Status dan makna |
|---|---|
| Rental | `PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`, `EXPIRED`, `BORROWED`, `RETURNED` |
| Payment | `UNPAID`, `PAID`, `REFUND_PENDING`, `REFUNDED` |
| Unit | `AVAILABLE`, `RESERVED`, `BORROWED`, `MAINTENANCE`, `DAMAGED`, `DEACTIVATED` |
| Fine | `NO_FINE`, `UNPAID`, `PAID` |

`OVERDUE` merupakan status tampilan/query berdasarkan `due_at` yang telah lewat dan item belum dikembalikan; tidak ada kolom status `OVERDUE` pada schema.
