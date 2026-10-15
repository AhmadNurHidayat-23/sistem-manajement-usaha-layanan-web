# SIMAUL - Sistem Manajemen Usaha Layanan

## Arsitektur Sistem

SIMAUL menggunakan arsitektur **Role-Based Controller + Service Layer** untuk memisahkan pengelolaan permintaan pengguna, logika bisnis, dan tampilan sistem.

Controller dikelompokkan berdasarkan role atau tanggung jawab, Service berdasarkan fungsi bisnis, sedangkan Views dikelompokkan berdasarkan role dan halaman yang digunakan bersama.

### Ringkasan Struktur Folder

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/               # Endpoint REST API (Mobile Flutter)
│   │   ├── Auth/              # Autentikasi (Login, Register, Password Reset)
│   │   ├── Karyawan/          # Controller khusus role Karyawan
│   │   ├── Kasir/             # Controller khusus role Kasir
│   │   ├── Manager/           # Controller khusus role Manager
│   │   ├── Owner/             # Controller khusus role Owner
│   │   ├── Pelanggan/         # Controller khusus role Pelanggan
│   │   ├── Public/            # Controller halaman publik (Landing Page / Home)
│   │   ├── Shared/            # Controller fitur bersama lintas-role
│   │   └── Controller.php     # Base Controller
│   └── Requests/              # Form Request Validation per Fitur
│       ├── BookingAntrian/
│       ├── Cabang/
│       ├── Inventori/
│       ├── Karyawan/
│       ├── KasirPOS/
│       ├── Katalog/
│       ├── LaporanKeuangan/
│       └── MembershipPromo/
├── Models/                    # Eloquent Models
├── Policies/                  # Otorisasi & Policy (BookingPolicy, PesananPolicy, dll.)
└── Services/                  # Service Layer (Domain Bisnis)
    ├── BookingAntrian/        # Layanan booking, antrian harian, drop-off orders
    ├── Cabang/                # Manajemen cabang dan unit usaha
    ├── Dashboard/             # Agregasi data dashboard per role
    ├── DataDemo/              # Generator & seeder data demonstrasi
    ├── Inventori/             # Stok bahan, mutasi masuk/keluar
    ├── Karyawan/              # Data karyawan, shift kerja, komisi
    ├── KasirPOS/              # Transaksi POS, shift kasir, payment gateway
    ├── Katalog/               # Layanan, kategori layanan, ulasan/rating
    ├── LaporanKeuangan/       # Laporan transaksi, tren omzet, ekspor PDF/Excel
    ├── MembershipPromo/       # Loyalitas pelanggan, tier membership, voucher promo
    └── Notifications/         # Log notifikasi dan aktivitas sistem

resources/
└── views/
    ├── auth/                  # Halaman login, register, forgot password
    ├── components/            # Komponen Blade UI reusable (alerts, buttons, cards, modals, dll.)
    ├── karyawan/              # Tampilan role Karyawan (dashboard, booking, tugas, profil)
    ├── kasir/                 # Tampilan role Kasir (dashboard, pos, shift, antrian, profil)
    ├── landing/               # Tampilan landing page dan katalog publik
    ├── layouts/               # Master layout aplikasi dan sidebar navigasi
    ├── manager/               # Tampilan role Manager (dashboard, cabang, inventori, karyawan, katalog, laporan, ulasan)
    ├── owner/                 # Tampilan role Owner (dashboard, user management, cabang, laporan keuangan)
    ├── pelanggan/             # Tampilan role Pelanggan (dashboard, booking, riwayat pesanan, profil)
    └── shared/                # Tampilan bersama lintas-role (notifikasi, aktivitas, invoice)
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
