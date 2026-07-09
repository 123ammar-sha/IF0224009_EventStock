# EventStock

EventStock adalah sistem manajemen inventaris yang dibangun menggunakan **Laravel 13** dan **Fetch API**. Proyek ini dikembangkan sebagai **Tugas Akhir Mata Kuliah Pemrograman Web**.

## Tujuan Proyek

Aplikasi ini dibuat untuk mendemonstrasikan penguasaan teknis dalam pengembangan web modern, meliputi:

- **Backend**: Implementasi RESTful API menggunakan Laravel.
- **Frontend**: Interaksi data secara asinkron menggunakan Fetch API (Vanilla JavaScript) dan manipulasi DOM.
- **Keamanan**: Sistem autentikasi dan otorisasi dengan Middleware dan Gate.

## Panduan Instalasi (Menjalankan secara lokal)

Jika ingin menjalankan aplikasi ini di komputer Anda, ikuti langkah-langkah berikut:

1. **Clone repository ini**

    ```bash
    git clone <url-repo-anda>
    cd eventstock
    ```

2. **Install dependensi PHP menggunakan Composer**

    ```bash
    composer install
    ```

3. **Salin file konfigurasi environment**

    ```bash
    cp .env.example .env
    ```

    _Note: Di sistem Windows (Command Prompt), gunakan perintah `copy .env.example .env`._

4. **Konfigurasi Database**
   Buka file `.env` dan sesuaikan konfigurasi koneksi database (seperti `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`).

5. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

6. **Jalankan Migrasi & Seeder Database**
   Perintah ini akan membuat tabel yang dibutuhkan sekaligus mengisi data awal (dummy data) untuk digunakan.

    ```bash
    php artisan migrate --seed
    ```

7. **Jalankan Development Server**
    ```bash
    php artisan serve
    ```
    Aplikasi bisa diakses melalui web browser di: `http://localhost:8000`

---

_Dibuat untuk memenuhi kualifikasi Tugas Akhir Pemrograman Web._
