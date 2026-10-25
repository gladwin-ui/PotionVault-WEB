# PotionVault — ERD Final

ERD berikut diturunkan dari seluruh migration Laravel aktual. Nama tabel dan kolom tidak disederhanakan dari schema.

Diagram ini memodelkan business schema utama PotionVault. Tabel framework Laravel seperti `sessions`, `cache`, `jobs`, dan `password_reset_tokens` tidak ditampilkan karena berada di luar ERD bisnis aplikasi.

```mermaid
erDiagram
    users ||--|| profiles : has
    users ||--o{ rentals : places
    users ||--o{ rentals : approves
    users ||--o{ rental_items : confirms_fine
    users ||--o{ payments : confirms
    users ||--o{ payments : refunds
    rentals ||--|{ rental_items : contains
    rentals ||--o| payments : has
    units ||--o{ rental_items : appears_in
    units ||--o{ category_unit : classified_by
    categories ||--o{ category_unit : classifies

    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string role
        boolean is_active
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }
    profiles {
        bigint id PK
        bigint user_id FK
        string phone
        text address
        timestamp created_at
        timestamp updated_at
    }
    units {
        bigint id PK
        string unit_code UK
        string name
        text description
        text magical_effect
        decimal rental_price
        string rarity
        string condition
        string status
        string image
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }
    categories {
        bigint id PK
        string name UK
        text description
        timestamp created_at
        timestamp updated_at
    }
    category_unit {
        bigint unit_id PK
        bigint category_id PK
    }
    rentals {
        bigint id PK
        bigint user_id FK
        string status
        decimal rental_total
        timestamp approved_at
        bigint approved_by FK
        timestamp expires_at
        text rejection_reason
        timestamp created_at
        timestamp updated_at
    }
    rental_items {
        bigint id PK
        bigint rental_id FK
        bigint unit_id FK
        decimal rental_price_snapshot
        timestamp borrowed_at
        timestamp due_at
        timestamp returned_at
        string return_condition
        text return_notes
        decimal fine_amount
        string fine_payment_status
        timestamp fine_paid_at
        bigint fine_confirmed_by FK
        timestamp created_at
        timestamp updated_at
    }
    payments {
        bigint id PK
        bigint rental_id UK
        decimal amount
        string status
        timestamp paid_at
        bigint confirmed_by FK
        timestamp refunded_at
        bigint refunded_by FK
        text refund_notes
        timestamp created_at
        timestamp updated_at
    }
```

## Constraint dan index penting

- `users.email`, `units.unit_code`, dan `categories.name` unik.
- `profiles.user_id` unik untuk relasi satu profile per user.
- `category_unit` memakai primary key gabungan `unit_id, category_id`.
- `payments.rental_id` unik sehingga satu rental memiliki paling banyak satu payment.
- `rental_items(rental_id, unit_id)` unik.
- Index rental pada `(user_id, status)` dan `(status, expires_at)`.
- Index item pada `(unit_id, returned_at)`.
- `users` dan `units` memakai soft delete.
- Foreign key memakai cascade/null-on-delete sesuai migration aktual.

Kolom waktu dan catatan yang dapat belum terisi digambarkan dengan nama kolom aktual; migration menetapkan nullable pada `email_verified_at`, data profile, field approval/expiry, field return/fine, serta field payment/refund yang belum terjadi.
