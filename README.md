# SIMAUL — Sistem Manajemen Usaha Layanan

SIMAUL adalah platform manajemen operasional multi-cabang terpadu yang dirancang untuk mempermudah pengelolaan berbagai unit usaha layanan, meliputi:
- **Barbershop & Studio**
- **Laundry & Shoe Care**
- **Auto Care & Detailing**

Sistem ini membantu pemilik usaha dan staf operasional dalam mengelola alur kerja harian secara efisien, mulai dari pemesanan layanan, transaksi kasir, penugasan staf, hingga pelaporan performa bisnis secara terpusat.

---

## Fitur Utama

- **Booking dan Antrean Layanan**: Penjadwalan janji temu daring, alokasi jam/slot layanan, dan pemantauan status antrean harian cabang.
- **Pengelolaan Pesanan**: Alur penerimaan pesanan (*drop-off*), pembaruan tahapan pengerjaan layanan, hingga serah terima selesai.
- **Kasir POS dan Pembayaran**: Transaksi kasir langsung, penghitungan diskon/pajak, pencatatan shift kasir, serta dukungan pembayaran tunai dan *payment gateway*.
- **Manajemen Karyawan dan Shift**: Pengaturan jadwal kerja per cabang, pembagian penugasan teknisi/staf, dan kalkulasi komisi otomatis.
- **Inventori dan Katalog Layanan**: Manajemen daftar layanan per kategori, pemantauan stok bahan habis pakai, dan pencatatan mutasi inventori.
- **Membership dan Promo**: Program loyalitas pelanggan berbasis poin membership, benefit tier/paket, serta pengelolaan kupon dan voucher diskon.
- **Laporan Keuangan**: Rekapitulasi transaksi penjualan, pendapatan harian/bulanan cabang, rincian komisi, serta ekspor dokumen laporan ke format PDF dan Excel.
- **Notifikasi**: Pencatatan log aktivitas sistem dan pengiriman pemberitahuan pembaruan status pesanan kepada pengguna dan staf terkait.

---

## Hak Akses Pengguna

Sistem berbasis Web menyediakan lima tingkatan hak akses (role):

1. **Owner**: Pemilik usaha dengan wewenang penuh terhadap seluruh cabang, pengelolaan akun manajer, serta tinjauan laporan eksekutif dan keuangan menyeluruh.
2. **Manajer**: Pengelola operasional cabang yang bertanggung jawab atas jadwal shift staf, pemantauan antrean, inventori bahan, dan kinerja operasional cabang.
3. **Kasir**: Petugas loket/kasir yang menangani transaksi POS harian, shift kasir, pencetakan invoice/struk, dan konfirmasi penerimaan pembayaran.
4. **Karyawan**: Staf teknis layanan yang memantau penanganan antrean pelanggan, daftar tugas harian, jadwal kerja, dan rekapan komisi pribadi.
5. **Pelanggan**: Konsumen yang melakukan booking layanan, memantau riwayat pesanan, memberikan ulasan rating, serta mengelola poin membership.

---

## Teknologi

- **Backend**: PHP 8.3+ / Laravel 13
- **Frontend**: Blade Templating, Tailwind CSS v4, Vite
- **Database**: MySQL / MariaDB (mendukung SQLite untuk pengujian)
- **Ekspor Dokumen**: Barryvdh DomPDF (PDF) & PhpSpreadsheet (Excel)
- **Pengujian & Perkakas**: PHPUnit, Composer, NPM

---

## Arsitektur Sistem

SIMAUL menggunakan arsitektur **Role-Based Controller + Service Layer** untuk memisahkan pengelolaan permintaan pengguna, logika bisnis, dan tampilan aplikasi.

Controller dikelompokkan berdasarkan role, Service berdasarkan fungsi bisnis, sedangkan Views dikelompokkan berdasarkan role serta halaman yang digunakan bersama.

```text
app/Http/Controllers/ — Controller aplikasi.
app/Services/        — Logika bisnis.
app/Models/          — Model database.
resources/views/     — Tampilan berdasarkan role dan shared.
routes/              — Routing Web dan API.
```

---

## Instalasi dan Menjalankan Proyek

Panduan ini disusun untuk lingkungan pengembangan lokal (termasuk Windows dengan Laragon / XAMPP).

### Prasyarat
- PHP >= 8.3 dengan ekstensi aktif: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` / `imagick`
- Composer
- Node.js & NPM
- Database MySQL / MariaDB

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/AhmadNurHidayat-23/sistem-manajement-usaha-layanan-web.git
   cd sistem-manajement-usaha-layanan-web
   ```

2. **Instal Dependensi Backend dan Frontend**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   Salin berkas template lingkungan:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Buka berkas `.env`, lalu sesuaikan konfigurasi koneksi database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=service_management
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Pastikan database `service_management` telah dibuat terlebih dahulu melalui phpMyAdmin / Laragon).*

4. **Migrasi Database (Instalasi Baru)**
   Jalankan perintah migrasi berikut untuk inisialisasi tabel:
   ```bash
   php artisan migrate
   ```
   > **Perhatian:** Hanya jalankan migrasi ini untuk database baru. Jangan gunakan perintah `migrate:fresh` atau `migrate:refresh` pada database yang sudah berjalan agar data transaksi tidak terhapus.

5. **Jalankan Aplikasi**
   Buka terminal untuk menjalankan server aplikasi dan kompilasi aset:
   ```bash
   # Terminal 1: Web Server
   php artisan serve

   # Terminal 2: Asset Bundler
   npm run dev
   ```
   Aplikasi dapat diakses melalui peramban pada `http://localhost:8000` (atau `http://service-management.test` jika menggunakan virtual host Laragon).

---

## Konfigurasi Layanan Tambahan (Opsional)

SIMAUL Web mendukung integrasi layanan eksternal yang dapat diaktifkan melalui berkas `.env` sesuai kebutuhan:

- **Payment Gateway (Midtrans)**:
  Secara bawaan menggunakan driver `sandbox`. Jika ingin mengaktifkan Midtrans, atur `PAYMENT_GATEWAY_DRIVER=midtrans` serta lengkapi variabel `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` yang diperoleh dari dashboard Midtrans Anda.
- **WhatsApp Gateway**:
  Secara bawaan menggunakan driver `log` (pesan dicatat pada log aplikasi untuk keamanan dan pengujian). Jika menggunakan gateway WhatsApp eksternal, atur `WHATSAPP_DRIVER` (`fonnte`, `wablas`, `baileys`, atau `custom`) beserta token autentikasi penyedia terkait.

---

## Integrasi SIMAUL Mobile

SIMAUL Web menyediakan REST API yang digunakan oleh aplikasi mobile berbasis Flutter untuk kebutuhan interaksi role **Pelanggan** dan **Karyawan**.

Repository aplikasi mobile dapat diakses melalui tautan berikut:
- [SIMAUL Mobile Repository](https://github.com/AhmadNurHidayat-23/sistem-manajement-usaha-layanan-mobile)
