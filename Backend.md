# 📘 EventStock API Documentation

**Versi:** 2.0.0  
**Deskripsi:** Sistem Backend berbasis RESTful API untuk manajemen inventaris logistik acara, dengan fitur otomatisasi mutasi stok, flightcase bundling, deteksi insiden barang, riwayat transaksi stok, dan manajemen stok masuk/keluar.

> **Catatan Revisi:** Versi 2.0.0 adalah penyempurnaan besar untuk kesiapan industri. Semua logika telah diperbaiki, ditambahkan sistem riwayat transaksi stok (`stock_transactions`), perbaikan flightcase bundling menggunakan pivot table, pessimistic locking untuk cegah double inbound, perbaikan bug filter status, dan penambahan endpoint manajemen stok.

---

## 🏗️ 1. Arsitektur Sistem

Sistem dibangun menggunakan kerangka kerja **Laravel 11** dengan arsitektur **Service-Oriented**.

| Komponen    | Teknologi                     |
| ----------- | ----------------------------- |
| Framework   | Laravel 11                    |
| Database    | MySQL (Relational)            |
| Autentikasi | Laravel Sanctum (Token-based) |
| PHP Version | 8.2+                          |
| Caching     | Redis (Opsional)              |
| Queue       | Laravel Queue (Opsional)      |

### Prinsip Keamanan

- **Pessimistic Locking:** Menggunakan `lockForUpdate()` untuk mencegah race condition pada stok barang dan validasi inbound.
- **Database Transaction:** Menggunakan `DB::transaction()` untuk menjamin integritas data (prinsip ACID).
- **Gate Policy:** Menggunakan `Gate::denies('manageUsers')` untuk membatasi akses manajemen user berdasarkan role.
- **Audit Trail:** Semua perubahan stok tercatat di tabel `stock_transactions` untuk traceability penuh.

---

## 🔐 2. Authentication

### Base URL

```
http://127.0.0.1:8000/api
```

### Headers Wajib

```
Accept: application/json
Content-Type: application/json
```

---

### 🔑 Login

**Endpoint:** `POST /login`

**Request Body:**

```json
{
    "email": "admin@eventstock.test",
    "password": "password"
}
```

**Response Success (200):**

```json
{
    "message": "Login berhasil",
    "access_token": "1|abcdefg123456",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@eventstock.test",
        "role": "super_admin"
    }
}
```

**Response Error (401):**

```json
{
    "message": "Kredensial tidak valid. Periksa kembali email dan password Anda!"
}
```

---

### 🚪 Logout

**Endpoint:** `POST /logout`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "message": "Logout berhasil, token telah dihapus."
}
```

---

### 👤 Get Profile

**Endpoint:** `GET /profile`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "name": "Admin",
        "email": "admin@eventstock.test",
        "role": "super_admin",
        "created_at": "2026-06-22T10:00:00.000000Z",
        "updated_at": "2026-06-22T10:00:00.000000Z"
    }
}
```

---

## 📦 3. API Endpoints Reference

### 3.1 Autentikasi

| Method | Endpoint   | Deskripsi                                   |
| ------ | ---------- | ------------------------------------------- |
| POST   | `/login`   | Mendapatkan access token (Sanctum)          |
| POST   | `/logout`  | Membatalkan session/token                   |
| GET    | `/profile` | Mendapatkan data pengguna yang sedang login |

---

### 3.2 Master Data Barang & Kategori

| Method | Endpoint           | Deskripsi                                       |
| ------ | ------------------ | ----------------------------------------------- |
| GET    | `/items`           | Mendapatkan semua barang (dengan filter)        |
| POST   | `/items`           | Menambah barang baru                            |
| GET    | `/items/{id}`      | Mendapatkan detail barang                       |
| PUT    | `/items/{id}`      | Mengupdate data barang (non-stok)               |
| DELETE | `/items/{id}`      | Menghapus barang                                |
| GET    | `/categories`      | Mendapatkan semua kategori                      |
| POST   | `/categories`      | Menambah kategori baru (type: asset/consumable) |
| GET    | `/categories/{id}` | Mendapatkan detail kategori                     |
| PUT    | `/categories/{id}` | Mengupdate kategori                             |
| DELETE | `/categories/{id}` | Menghapus kategori                              |

> **PENTING:** Untuk menambah stok barang, gunakan endpoint `POST /stock/add`. Untuk adjustment stok, gunakan `POST /stock/adjust`. Jangan mengubah stok melalui PUT /items karena tidak akan tercatat di riwayat.

#### Filter Items

| Parameter     | Tipe   | Deskripsi                                 |
| ------------- | ------ | ----------------------------------------- |
| `search`      | string | Cari berdasarkan nama atau SKU            |
| `category_id` | int    | Filter berdasarkan kategori               |
| `status`      | string | Filter status (available, on_duty, dll)   |
| `for_picker`  | bool   | Khusus picker: hanya item dengan stok > 0 |
| `per_page`    | int    | Jumlah per halaman (default: 10)          |

---

### 3.3 Logistik & Transaksi (Manifests)

| Method | Endpoint              | Deskripsi                                                  |
| ------ | --------------------- | ---------------------------------------------------------- |
| POST   | `/manifests/outbound` | Mencatat barang keluar (potong stok otomatis)              |
| POST   | `/manifests/inbound`  | Mencatat barang kembali (audit kondisi & incident logging) |
| GET    | `/manifests`          | Mendapatkan semua manifest (dengan filter)                 |
| GET    | `/manifests/{id}`     | Mendapatkan detail manifest                                |

**Filter Manifest:**

| Parameter   | Tipe   | Deskripsi                        |
| ----------- | ------ | -------------------------------- |
| `type`      | string | Filter: outbound / inbound       |
| `event_id`  | int    | Filter berdasarkan event         |
| `status`    | string | Filter status manifest           |
| `date_from` | date   | Filter tanggal mulai             |
| `date_to`   | date   | Filter tanggal akhir             |
| `per_page`  | int    | Jumlah per halaman (default: 15) |

---

### 3.4 Insiden

| Method | Endpoint                  | Deskripsi                        |
| ------ | ------------------------- | -------------------------------- |
| GET    | `/incidents`              | Mendapatkan semua insiden        |
| GET    | `/incidents/{id}`         | Mendapatkan detail insiden       |
| PATCH  | `/incidents/{id}/resolve` | Menandai insiden sebagai selesai |

**Filter Insiden:**

| Parameter   | Tipe    | Deskripsi                        |
| ----------- | ------- | -------------------------------- |
| `type`      | string  | broken / lost                    |
| `resolved`  | boolean | true / false                     |
| `date_from` | date    | Filter tanggal mulai             |
| `date_to`   | date    | Filter tanggal akhir             |
| `per_page`  | int     | Jumlah per halaman (default: 15) |

---

### 3.5 Event & Flightcase Management

| Method | Endpoint            | Deskripsi                     |
| ------ | ------------------- | ----------------------------- |
| GET    | `/events`           | Mendapatkan semua acara       |
| POST   | `/events`           | Menambah acara baru           |
| GET    | `/events/{id}`      | Mendapatkan detail acara      |
| PUT    | `/events/{id}`      | Mengupdate acara              |
| DELETE | `/events/{id}`      | Menghapus acara               |
| GET    | `/flightcases`      | Mendapatkan semua flightcase  |
| POST   | `/flightcases`      | Menambah flightcase baru      |
| GET    | `/flightcases/{id}` | Mendapatkan detail flightcase |
| PUT    | `/flightcases/{id}` | Mengupdate flightcase         |
| DELETE | `/flightcases/{id}` | Menghapus flightcase          |

---

### 3.6 Manajemen Stok & Riwayat (NEW)

| Method | Endpoint                  | Deskripsi                                      |
| ------ | ------------------------- | ---------------------------------------------- |
| POST   | `/stock/add`              | Tambah stok barang (pembelian/restock)         |
| POST   | `/stock/adjust`           | Adjustment stok (set manual ke nilai tertentu) |
| GET    | `/stock/history`          | Riwayat transaksi stok (global, dengan filter) |
| GET    | `/stock/history/{itemId}` | Riwayat transaksi stok untuk item tertentu     |

#### Filter Riwayat Stok

| Parameter   | Tipe   | Deskripsi                          |
| ----------- | ------ | ---------------------------------- |
| `item_id`   | int    | Filter berdasarkan item            |
| `type`      | string | in / out / adjustment / correction |
| `date_from` | date   | Filter tanggal mulai               |
| `date_to`   | date   | Filter tanggal akhir               |
| `per_page`  | int    | Jumlah per halaman (default: 20)   |

---

### 3.7 User Management (Super Admin Only)

| Method | Endpoint      | Deskripsi                                      |
| ------ | ------------- | ---------------------------------------------- |
| GET    | `/users`      | Mendapatkan semua pengguna (Super Admin Only)  |
| POST   | `/users`      | Menambah pengguna baru (Super Admin Only)      |
| GET    | `/users/{id}` | Mendapatkan detail pengguna (Super Admin Only) |
| PUT    | `/users/{id}` | Mengupdate pengguna (Super Admin Only)         |
| DELETE | `/users/{id}` | Menghapus pengguna (Super Admin Only)          |

---

## 🔥 4. Fitur Utama (Backend Highlights)

### A. Manajemen Stok & Riwayat Transaksi (NEW)

Sistem mencatat **setiap perubahan stok** ke tabel `stock_transactions` untuk audit trail penuh.

**Jenis Transaksi:**

| Type         | Deskripsi                                  | Dampak ke Stok              |
| ------------ | ------------------------------------------ | --------------------------- |
| `in`         | Barang masuk (pembelian, restock, inbound) | available_qty + total_qty + |
| `out`        | Barang keluar (outbound, hilang, rusak)    | available_qty -             |
| `adjustment` | Penyesuaian stok manual                    | available_qty ± total_qty ± |
| `correction` | Koreksi kesalahan data                     | available_qty ±             |

**Contoh Tambah Stok (Pembelian Baru):**

```json
// POST /stock/add
{
    "item_id": 1,
    "quantity": 50,
    "description": "Pembelian 50 unit Sound System baru"
}
```

**Response:**

```json
{
    "message": "Stok Sound System berhasil ditambahkan. Stok tersedia: 135",
    "data": {
        "id": 1,
        "name": "Sound System",
        "available_qty": 135,
        "total_qty": 150,
        "status": "available"
    }
}
```

**Contoh Adjustment Stok:**

```json
// POST /stock/adjust
{
    "item_id": 1,
    "new_available_qty": 100,
    "reason": "Stock opname: penyesuaian fisik"
}
```

**Contoh Riwayat Stok:**

```json
// GET /stock/history/1
{
    "message": "Riwayat stok untuk Sound System berhasil diambil",
    "data": {
        "item": { "id": 1, "name": "Sound System" },
        "transactions": [
            {
                "id": 1,
                "type": "in",
                "qty_change": 50,
                "qty_before": 85,
                "qty_after": 135,
                "reference_type": "purchase",
                "description": "Pembelian 50 unit Sound System baru",
                "user": { "id": 1, "name": "Admin" },
                "created_at": "2026-06-27T10:00:00.000000Z"
            }
        ]
    }
}
```

---

### B. Tipe Barang: Asset vs Consumable

| Tipe         | Perilaku Outbound                                     | Perilaku Inbound            |
| ------------ | ----------------------------------------------------- | --------------------------- |
| `asset`      | `available_qty` berkurang, `total_qty` tetap          | Bisa dikembalikan ke gudang |
| `consumable` | `available_qty` & `total_qty` berkurang (habis pakai) | **Tidak bisa dikembalikan** |

---

### C. Automatic Discrepancy & Incident Tracking

Sistem secara otomatis mendeteksi jika barang yang dikembalikan rusak (`broken`) atau hilang (`lost`).

**Flow:**

1. **Jika good:** Stok kembali ke gudang, riwayat `in` tercatat
2. **Jika broken:** Status `maintenance`, insiden tercatat, riwayat `out` tercatat
3. **Jika lost:** Status `lost`, `total_qty` dikurangi, insiden tercatat, riwayat `out` tercatat

**Contoh Request Inbound:**

```json
{
    "outbound_manifest_id": 1,
    "destination": "Gudang Pusat",
    "notes": "Pengembalian setelah acara Jakarta Concert",
    "items": [
        {
            "item_id": 1,
            "qty_actual": 10,
            "condition": "good"
        },
        {
            "item_id": 2,
            "qty_actual": 2,
            "condition": "broken",
            "notes": "Layar retak"
        }
    ]
}
```

---

### D. Intelligent Kitting (Flightcase Bundling) — FIXED

Fitur **One-Scan Loading** kini menggunakan **pivot table `flightcase_item`** untuk quantity yang akurat.

**Perbaikan dari v1.0.0:**

- ✅ Sekarang membaca quantity dari pivot table (`bundledItems` many-to-many)
- ✅ Tidak lagi menggunakan relasi lama `Item.flightcase_id` untuk quantity
- ✅ Validasi stok berdasarkan qty spesifik dari pivot
- ✅ Riwayat stok otomatis untuk setiap item yang dibawa flightcase

**Cara Membuat Flightcase:**

```json
{
    "code": "FC-001",
    "name": "Sound Package A",
    "description": "Paket sound system lengkap",
    "items": [
        { "item_id": 1, "quantity": 10 },
        { "item_id": 2, "quantity": 20 }
    ]
}
```

**Contoh Request Outbound dengan Flightcase:**

```json
{
    "event_id": 1,
    "destination": "Venue A - Istora Senayan",
    "notes": "Pengiriman untuk konser",
    "items": [{ "item_id": 3, "qty": 5 }],
    "flightcases": [{ "flightcase_id": 1 }]
}
```

---

### E. Data Safety Mechanism

- **ACID Compliance:** Semua operasi dalam transaction
- **Pessimistic Locking:** `lockForUpdate()` pada item dan manifest
- **Dual-Layer Validation:** Controller (validasi request) + Service (validasi bisnis)
- **Audit Trail:** Semua perubahan stok tercatat di `stock_transactions`
- **Cegah Double Inbound:** Outbound manifest di-lock dan dicek statusnya

---

## 🗄️ 5. Database Schema

### ERD Overview

```text
┌─────────────┐     ┌─────────────────┐     ┌─────────────┐
│    users    │─────│   manifests     │─────│   events    │
└─────────────┘     └─────────────────┘     └─────────────┘
                           │
                           ▼
                    ┌─────────────────┐     ┌─────────────┐
                    │ manifest_items  │─────│    items    │
                    └─────────────────┘     └─────────────┘
                           │                  │    │     │
                           ▼                  │    │     ▼
                    ┌─────────────────┐       │    │  ┌─────────────┐
                    │  incident_logs  │───────┘    │  │ flightcases │
                    └─────────────────┘            │  └─────────────┘
                                                   │        │
                                                   ▼        ▼
                                            ┌─────────────────────┐
                                            │  flightcase_item    │
                                            │  (pivot)            │
                                            └─────────────────────┘

┌──────────────┐     ┌─────────────┐     ┌──────────────────────┐
│  categories  │─────│    items    │─────│ stock_transactions   │
└──────────────┘     └─────────────┘     │ (audit trail)        │
                                         └──────────────────────┘
```

### Tabel-Tabel

#### stock_transactions (NEW)

| Kolom          | Tipe                                       | Keterangan                                                 |
| -------------- | ------------------------------------------ | ---------------------------------------------------------- |
| id             | bigint(20)                                 | Primary Key                                                |
| item_id        | bigint(20)                                 | Foreign Key → items                                        |
| user_id        | bigint(20)                                 | Foreign Key → users                                        |
| type           | enum('in','out','adjustment','correction') | Jenis transaksi                                            |
| qty_change     | int(11)                                    | Perubahan stok (+/-)                                       |
| qty_before     | int(11)                                    | Stok sebelum perubahan                                     |
| qty_after      | int(11)                                    | Stok setelah perubahan                                     |
| reference_type | varchar(255) nullable                      | Jenis referensi (manifest, purchase, incident, adjustment) |
| reference_id   | bigint(20) nullable                        | ID referensi                                               |
| description    | text nullable                              | Deskripsi transaksi                                        |
| created_at     | timestamp                                  | Waktu transaksi                                            |
| updated_at     | timestamp                                  | Waktu diupdate                                             |

Index: `(item_id, created_at)`, `(reference_type, reference_id)`

---

#### manifests (UPDATED)

| Kolom                | Tipe                                                | Keterangan                                       |
| -------------------- | --------------------------------------------------- | ------------------------------------------------ |
| id                   | bigint(20)                                          | Primary Key                                      |
| manifest_number      | varchar(50)                                         | Nomor manifest (unique)                          |
| event_id             | bigint(20)                                          | Foreign Key → events                             |
| user_id              | bigint(20)                                          | Foreign Key → users                              |
| type                 | enum('inbound','outbound')                          | Tipe manifest                                    |
| status               | enum('draft','in_progress','completed','has_issue') | Status manifest                                  |
| **destination**      | varchar(255) nullable (NEW)                         | Tujuan pengiriman                                |
| **notes**            | text nullable (NEW)                                 | Catatan manifest                                 |
| outbound_manifest_id | bigint(20) nullable                                 | Foreign Key → manifests (self-ref untuk inbound) |
| created_at           | timestamp                                           | Waktu dibuat                                     |
| updated_at           | timestamp                                           | Waktu diupdate                                   |

---

## 🧠 6. Service Layer Architecture

### Struktur Direktori Aktual

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EventController.php
│   │   │   ├── FlightcaseController.php
│   │   │   ├── IncidentController.php
│   │   │   ├── ItemController.php
│   │   │   ├── ManifestController.php
│   │   │   ├── StockController.php        (NEW)
│   │   │   └── UserController.php
│   │   └── Controller.php
├── Services/
│   ├── ManifestService.php                 (FIXED)
│   └── StockService.php                    (NEW)
├── Models/
│   ├── User.php
│   ├── Item.php
│   ├── Category.php
│   ├── Event.php
│   ├── Flightcase.php
│   ├── Manifest.php
│   ├── ManifestItem.php
│   ├── IncidentLog.php
│   └── StockTransaction.php               (NEW)
└── Policies/
    └── UserPolicy.php
```

---

## 📊 7. Dashboard & Analytics

### Endpoint

```
GET /dashboard
```

### Response

```json
{
    "message": "Data Dashboard berhasil diambil",
    "data": {
        "inventory_summary": {
            "available": 45,
            "on_duty": 12,
            "maintenance": 3,
            "lost": 1
        },
        "incident_summary": {
            "active_events_count": 2,
            "active_incidents_count": 3,
            "unresolved_incidents": [...]
        },
        "events": {
            "upcoming": 3,
            "ongoing": 2,
            "completed": 5
        },
        "recent_manifests": [...],
        "agenda_events": [...]
    }
}
```

---

## 🚨 8. Error Response Format

### Business Logic Error (422)

```json
{
    "message": "Gagal memproses manifest.",
    "error": "Stok Sound System tidak mencukupi. Tersedia: 5"
}
```

### Validation Error (422)

```json
{
    "message": "The given data was invalid",
    "errors": {
        "item_id": ["The selected item_id is invalid."]
    }
}
```

### Not Found (404)

```json
{
    "message": "Barang tidak ditemukan"
}
```

---

## 🎯 9. Role & Access Control

| Role                | Deskripsi           | Akses                                          |
| ------------------- | ------------------- | ---------------------------------------------- |
| `super_admin`       | Administrator penuh | Semua fitur (CRUD semua data, user management) |
| `warehouse_manager` | Manajer gudang      | Monitoring stok, approve manifest, laporan     |
| `field_crew`        | Tim lapangan        | Transaksi outbound/inbound, view stok          |

> **Catatan:** RBAC untuk user management menggunakan Gate Policy. Endpoint lainnya (items, events, flightcases, manifests, stock) hanya memerlukan autentikasi.

---

## 🔬 10. Perbaikan & Perubahan dari v1.0.0

### Bugs Fixed

| #   | Issue                                                                   | Status     |
| --- | ----------------------------------------------------------------------- | ---------- |
| 1   | EventController filter status menggunakan `where()` bukan `whereIn()`   | ✅ FIXED   |
| 2   | Flightcase bundling menggunakan relasi lama, mengabaikan pivot quantity | ✅ FIXED   |
| 3   | Route `/dashboard/stats` tanpa handler                                  | ✅ REMOVED |
| 4   | Race condition double inbound (tanpa lock)                              | ✅ FIXED   |
| 5   | Consumable `total_qty` bisa negatif                                     | ✅ FIXED   |
| 6   | Field `destination` dan `notes` tidak ada di migration                  | ✅ FIXED   |

### New Features

| #   | Fitur                                         | Status |
| --- | --------------------------------------------- | ------ |
| 1   | Riwayat transaksi stok (`stock_transactions`) | ✅ NEW |
| 2   | Endpoint tambah stok (pembelian/restock)      | ✅ NEW |
| 3   | Endpoint adjustment stok manual               | ✅ NEW |
| 4   | Riwayat stok per item                         | ✅ NEW |
| 5   | Audit trail untuk semua mutasi stok           | ✅ NEW |
| 6   | Destination & notes di manifest               | ✅ NEW |

---

## 🚀 11. Deployment Guide

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 5.7+
- Node.js & NPM (opsional)

### Installation Steps

```bash
# 1. Clone repository
git clone https://github.com/yourusername/eventstock-api.git
cd eventstock-api

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventstock_db
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migration & seeder
php artisan migrate --seed

# 6. Install Sanctum
php artisan sanctum:install

# 7. Start server
php artisan serve
```

---

## 📝 12. Changelog

### Version 2.0.0 (2026-06-27)

- **Perbaikan Bug:**
    - EventController: filter status menggunakan `whereIn()` bukan `where()`
    - Flightcase bundling: sekarang menggunakan pivot table `flightcase_item` untuk quantity
    - Double inbound: ditambahkan `lockForUpdate()` pada outbound manifest
    - Consumable: validasi cegah `total_qty` negatif
    - Route `/dashboard/stats` dihapus (tidak memiliki handler)
    - Field `destination` dan `notes` ditambahkan ke migration manifests

- **Fitur Baru:**
    - Tabel `stock_transactions` untuk audit trail stok
    - `StockService` untuk manajemen stok terpusat
    - `StockController` dengan endpoint: add, adjust, history
    - Riwayat transaksi otomatis untuk setiap mutasi stok (outbound, inbound, incident)
    - Destination & notes pada manifest outbound dan inbound

- **Service Layer:**
    - `StockService` (baru) — manajemen stok & riwayat
    - `ManifestService` (diperbaiki) — dependency injection StockService, flightcase via pivot

### Version 1.1.0 (2026-06-27)

- Dokumentasi diselaraskan dengan implementasi aktual

### Version 1.0.0 (2026-06-22)

- Initial release

---

**© 2026 EventStock. All Rights Reserved.**
