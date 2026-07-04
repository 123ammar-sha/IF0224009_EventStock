# 📘 Dokumen Rancangan Proyek

# EventStock

## Sistem Manajemen Inventaris & Logistik Alat Produksi untuk Event Organizer

**Tagline:** Digitalisasi Pelacakan Aset, Bundling Cerdas, dan Manajemen Pergerakan Alat Event tanpa Kehilangan.

---

## 1. 📋 Latar Belakang

Banyak vendor **Sound System**, **Lighting**, dan **Event Organizer (EO)** masih mengandalkan ingatan atau catatan kertas saat mendistribusikan ratusan alat ke lokasi acara (venue).

Akibat pergerakan yang serba cepat dan kelelahan kru saat bongkar-muat (loading-out) di tengah malam, sering terjadi:

- Kabel, mikrofon, atau adapter berharga mahal tertinggal di venue.
- Alat bercampur dengan milik vendor lain di lapangan.
- Kesulitan melacak aset mana yang sedang berada di dalam kotak penyimpanan (flightcase) tertentu.
- Stok barang habis pakai (baterai, lakban gaffer) tidak terpantau.

**EventStock** dikembangkan untuk memastikan setiap barang yang keluar dari gudang harus kembali dengan jumlah dan kondisi yang sama, serta mempermudah kru lapangan melakukan checklist secara digital.

---

## 2. 🎯 Tujuan Sistem

Membangun sistem logistik berbasis web yang mampu:

1. Mencatat pergerakan barang keluar (persiapan acara) dan barang masuk (pengembalian ke gudang).
2. Menerapkan sistem **Bundling/Kitting** (misal: 1 Flightcase otomatis memuat daftar puluhan kabel dan alat).
3. Memantau posisi alat secara real-time (di Gudang, di Perjalanan, atau di Venue A).
4. Mendeteksi dan mencatat barang yang hilang atau rusak secara otomatis saat pengembalian.
5. Menyediakan riwayat pemakaian alat untuk keperluan maintenance.

---

## 3. 🏟️ Ruang Lingkup Sistem

Sistem difokuskan pada pengelolaan inventaris logistik di:

- **Vendor Audio, Visual & Lighting (AVL)**
- **Event Organizer / Wedding Organizer**
- **Production House (PH)**

**Catatan:** Sistem ini berfokus pada mutasi barang fisik dan pelacakan aset, tidak mengelola penjualan tiket atau budgeting keuangan acara.

---

## 4. 👥 Aktor Sistem

| Aktor                      | Deskripsi                                                                                                       |
| -------------------------- | --------------------------------------------------------------------------------------------------------------- |
| **Super Admin**            | Mengelola seluruh data master, pengaturan, dan akun pengguna.                                                   |
| **Manajer Gudang**         | Memantau ketersediaan alat, mengatur bundling flightcase, dan menyetujui jadwal barang keluar/masuk.            |
| **Kru Lapangan / Checker** | Mengeksekusi checklist saat barang masuk ke truk (Loading In) dan saat barang ditarik dari venue (Loading Out). |
| **Auditor / Owner**        | Melihat laporan mutasi barang, daftar alat rusak/hilang, dan utilisasi aset.                                    |

---

## 5. 📦 Modul Utama

### A. Autentikasi

- Login & Logout pengguna.

### B. Master Data

- **Kategori Alat** (Aset vs Barang Habis Pakai/Consumable).
- **Data Proyek / Venue Event**.
- **Manajemen Kotak Penyimpanan** (Flightcase/Rack).
- **Data Alat & Barcode/QR Code**.

### C. Transaksi Logistik (Core Module)

- **Barang Keluar (Manifest Outbound):** Menyiapkan daftar alat yang dibawa ke suatu event.
- **Barang Masuk (Manifest Inbound):** Checklist pengembalian alat dari event ke gudang.
- **Laporan Insiden:** Pencatatan otomatis jika jumlah barang masuk kurang dari barang keluar (memicu status Hilang/Rusak).

### D. Monitoring & Laporan

- **Stok Gudang Real-time.**
- **Alat On-Duty** (Alat yang sedang berada di venue).
- **Kartu Riwayat Alat.**

---

## 6. 🔄 Alur Bisnis Sistem

### A. Persiapan Event (Barang Keluar)

```text
Manajer Gudang Buat Manifest Event
            ↓
Kru Melakukan Checklist Scan Alat
            ↓
Alat Dimasukkan ke Truk (Loading In)
            ↓
Sistem Memindahkan Status Stok: "Gudang" ➔ "Di Venue A"
```

### B. Pengembalian Pasca Event (Barang Masuk)

```text
Kru Menarik Alat dari Venue (Loading Out)
            ↓
Kru Melakukan Checklist Pengembalian
            ↓
Sistem Mencocokkan: Data Keluar vs Data Masuk
            ↓
Sistem Mengembalikan Stok ke "Gudang"
```

### C. Penanganan Selisih Barang

```text
Jika jumlah kembali < jumlah berangkat
            ↓
Sistem Meminta Input Keterangan
            ↓
Status Barang Sisa Berubah: "Hilang di Venue" atau "Rusak"
            ↓
Notifikasi ke Manajer Gudang
```

---

## 7. 🧩 Pemetaan Fitur ke Konteks Event Logistik

| Terminologi Standar          | Konteks EventStock                         |
| ---------------------------- | ------------------------------------------ |
| Pelanggan / Tujuan           | Nama Event / Venue Acara                   |
| Rak Penyimpanan              | Flightcase / Kotak Hardcase                |
| Barang Masuk (Goods Receipt) | Inbound / Loading-Out (Kembali ke Gudang)  |
| Barang Keluar (Goods Issue)  | Outbound / Loading-In (Berangkat ke Event) |
| Peringatan Stok Minimum      | Peringatan Bentrok Jadwal Alat             |

---

## 8. 📑 Use Case Matrix

| Use Case                         | Super Admin | Manajer Gudang | Kru Lapangan | Auditor |
| -------------------------------- | ----------- | -------------- | ------------ | ------- |
| Login / Logout                   | ✅          | ✅             | ✅           | ✅      |
| Kelola Data Pengguna             | ✅          | ❌             | ❌           | ❌      |
| Kelola Master Alat & Event       | ✅          | ✅             | ❌           | ❌      |
| Seting Bundling / Flightcase     | ✅          | ✅             | ❌           | ❌      |
| Transaksi Keluar (Outbound)      | ✅          | ✅             | ✅           | ❌      |
| Transaksi Masuk (Inbound)        | ✅          | ✅             | ✅           | ❌      |
| Lapor Alat Hilang/Rusak          | ✅          | ✅             | ✅           | ❌      |
| Pantau Alat On-Duty              | ✅          | ✅             | ✅           | ✅      |
| Lihat Laporan Utilisasi & Mutasi | ✅          | ❌             | ❌           | ✅      |

---

## 9. 🛠️ Teknologi yang Digunakan

| Komponen         | Teknologi          |
| ---------------- | ------------------ |
| Backend          | Laravel 11         |
| API              | RESTful API        |
| Authentication   | Laravel Sanctum    |
| Frontend         | Blade + Alpine.js  |
| DOM Manipulation | Vanilla JavaScript |
| Database         | MySQL              |
| Styling          | Tailwind CSS       |

---

## 10. 🏗️ Arsitektur Sistem

```text
Frontend (Blade + Alpine.js)
            ↓
      Fetch API
            ↓
     Laravel REST API
            ↓
        MySQL
```

### Detail Arsitektur

```text
┌─────────────────────────────────────────────────────────────┐
│                     Client (Browser)                       │
│              Blade + Alpine.js + Tailwind                  │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                    HTTP Request / API                      │
│            Fetch API / Axios (JSON)                       │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                   Laravel Routes                           │
│              api.php / web.php                            │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                    Middleware                              │
│         Auth Sanctum + Role Middleware                    │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                    Controllers                             │
│   AuthController, ManifestController, ItemController      │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                    Services Layer                          │
│   StockService, ManifestService, FlightcaseService        │
│   - Business Logic                                        │
│   - Database Transaction (ACID)                           │
│   - Pessimistic Locking (lockForUpdate)                  │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                       Models                               │
│   User, Item, Event, Flightcase, Manifest, Incident      │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                       Database                             │
│              MySQL (Relational)                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 11. 📱 Halaman Utama Sistem

1. **Login** - Halaman autentikasi pengguna
2. **Dashboard** - Statistik event aktif, alat hilang, persentase stok
3. **Master Alat & Kategori** - CRUD data alat dan kategori
4. **Master Event & Klien** - CRUD data event dan klien
5. **Manajemen Flightcase/Bundling** - Mengatur komposisi flightcase
6. **Manifest Keluar** - Pemilihan alat untuk event
7. **Manifest Masuk** - Sistem checklist pengembalian
8. **Posisi Aset Real-time** - Tracking lokasi alat
9. **Log & Riwayat Mutasi Alat** - Riwayat pergerakan alat

---

## 12. 🚀 Nilai Tambah Portofolio

Proyek ini menunjukkan kemampuan teknis yang sangat advanced dalam:

### 1. Algoritma Pencocokan Transaksi

Logika untuk membandingkan array barang keluar dan barang masuk secara real-time untuk mendeteksi kehilangan/selisih (**discrepancy**).

### 2. Sistem Bundling Bersarang (Nested Inventory)

Menyelesaikan kompleksitas database di mana satu aset (Flightcase) bisa menampung banyak sub-aset (Kabel, Mic).

### 3. Pencatatan Multi-Status

Barang tidak hanya sekadar "Ada/Habis", melainkan memiliki status dinamis:

- 🟢 Di Gudang
- 🟡 On-Duty
- 🔴 Rusak
- ⚫ Hilang

### 4. Antarmuka Web Interaktif

Penerapan antarmuka web interaktif yang cocok untuk kecepatan kerja kru lapangan.

---

## 13. 📌 Kesimpulan

**EventStock** adalah terobosan sistem inventaris logistik yang menyelesaikan masalah krusial di industri hiburan dan event organizer. Dengan menitikberatkan pada keakuratan mutasi barang keluar dan masuk (outbound/inbound) berbasis kegiatan (event-based), sistem ini menyelamatkan vendor dari kerugian finansial akibat kelalaian pelacakan aset di lapangan.

---

## 🎯 Ringkasan Singkat untuk Presentasi

> **"EventStock adalah sistem manajemen logistik cerdas untuk vendor Event Organizer, dirancang khusus untuk memvalidasi pergerakan barang keluar-masuk (loading) antara gudang dan venue acara. Menggunakan Laravel dan REST API, sistem ini dilengkapi fitur bundling flightcase dan deteksi alat hilang secara otomatis untuk meminimalisir kerugian aset perusahaan."**

---

## 📊 Lampiran: Database Schema

### ERD Diagram

```text
┌─────────────┐     ┌─────────────────┐     ┌─────────────┐
│    users    │─────│  manifests      │─────│  events     │
└─────────────┘     └─────────────────┘     └─────────────┘
                            │                        │
                            ▼                        ▼
                    ┌─────────────────┐     ┌─────────────┐
                    │ manifest_items  │─────│  items      │
                    └─────────────────┘     └─────────────┘
                            │                        │
                            ▼                        ▼
                    ┌─────────────────┐     ┌─────────────┐
                    │ incident_logs   │─────│ flightcases │
                    └─────────────────┘     └─────────────┘
```

### Tabel Utama

| Tabel                | Kolom Utama                                    | Keterangan             |
| -------------------- | ---------------------------------------------- | ---------------------- |
| **users**            | id, name, email, password, role                | Pengguna sistem        |
| **items**            | id, name, sku, category, available_qty, status | Data alat/barang       |
| **flightcases**      | id, name, code                                 | Kotak penyimpanan      |
| **flightcase_items** | flightcase_id, item_id, quantity               | Isi flightcase         |
| **events**           | id, name, location, start_date, end_date       | Data acara             |
| **manifests**        | id, manifest_number, event_id, user_id, type   | Transaksi keluar/masuk |
| **manifest_items**   | manifest_id, item_id, quantity, condition      | Detail transaksi       |
| **incident_logs**    | id, item_id, manifest_id, type, status         | Log insiden            |

---

## 📄 Changelog

| Versi | Tanggal    | Perubahan                                      |
| ----- | ---------- | ---------------------------------------------- |
| 1.0.0 | 2026-06-22 | Initial release - Dokumentasi rancangan proyek |

---

**© 2026 EventStock. All Rights Reserved.**

---
