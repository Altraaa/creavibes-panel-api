# Creavibes Panel API

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
    <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Tentang Project

Creavibes Panel API adalah backend panel untuk aplikasi Creavibes, dibangun dengan Laravel Framework. Aplikasi ini menyediakan API untuk mengelola berbagai fitur panel dengan arsitektur modern dan terstruktur.

## Fitur Utama

-   **Authentication System**: Sistem autentikasi yang aman dengan Laravel Sanctum
-   **RESTful API**: API endpoints yang terstruktur dan mudah digunakan
-   **Database Management**: Manajemen database dengan migration dan seeder
-   **User Management**: Sistem manajemen pengguna dengan role-based access
-   **File Upload**: Sistem upload file yang aman dan terorganisir
-   **Logging System**: Logging aplikasi yang terstruktur
-   **Testing**: Dukungan testing dengan Pest PHP

## Teknologi yang Digunakan

-   **PHP 8.2+**: Versi PHP yang modern dan performa tinggi
-   **Laravel 12**: Framework PHP yang powerful dan elegan
-   **Tailwind CSS**: Utility-first CSS framework untuk styling
-   **Vite**: Build tool yang modern dan cepat untuk asset management
-   **MySQL**: Database relational yang reliable
-   **Redis**: Cache dan session storage untuk performa optimal

## Struktur Project

```
creavibes-panel-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controller classes
│   │   └── Middleware/      # Custom middleware
│   ├── Models/             # Eloquent models
│   └── Providers/          # Service providers
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── public/                # Public assets
├── resources/
│   ├── js/               # JavaScript files
│   └── views/            # Blade templates
├── routes/               # Route definitions
├── storage/              # Storage directory
└── tests/                # Test files
```

## Prasyarat

Pastikan Anda telah menginstal perangkat lunak berikut:

-   PHP 8.2 atau lebih tinggi
-   Composer (untuk manajemen dependency PHP)
-   Node.js 18+ dan npm (untuk asset management)
-   MySQL 8.0 atau lebih tinggi
-   Redis (opsional, untuk caching)

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/Altraaa/creavibes-panel-api.git
cd creavibes-panel-api
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Konfigurasi Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=creavibes_panel
DB_USERNAME=root
DB_PASSWORD=

# Redis configuration (optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 5. Jalankan Migration

```bash
php artisan migrate --force
```

### 6. Build Assets

```bash
npm run build
```

## Penggunaan

### Menjalankan Server Development

```bash
# Jalankan server PHP dan Vite secara bersamaan
composer run dev
```

Atau secara terpisah:

```bash
# Server PHP
php artisan serve

# Vite development server
npm run dev
```

### Menjalankan Testing

```bash
# Jalankan semua test
composer test

# Jalankan test tertentu
php artisan test --filter=FeatureTest
```

### Menjalankan Queue

```bash
# Menjalankan queue listener
php artisan queue:listen --tries=1
```

## API Documentation

### Endpoint Utama

-   `POST /api/login` - Login pengguna
-   `POST /api/register` - Registrasi pengguna baru
-   `GET /api/profile` - Mendapatkan profil pengguna
-   `PUT /api/profile` - Update profil pengguna

### Contoh Penggunaan API

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Mendapatkan profil
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer {your-token}"
```

## Struktur API

-   **Authentication**: `/api/auth/*` - Endpoints untuk login, register, dan refresh token
-   **Users**: `/api/users/*` - Manajemen pengguna
-   **Products**: `/api/products/*` - Manajemen produk
-   **Orders**: `/api/orders/*` - Manajemen pesanan
-   **Files**: `/api/files/*` - Manajemen file upload

## Kontribusi

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/amazing-feature`)
3. Commit perubahan Anda (`git commit -m 'Add some amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Lisensi

Project ini dilisensikan di bawah lisensi [MIT](https://opensource.org/licenses/MIT). Lihat file `LICENSE` untuk informasi lebih detail.

## Dukungan

Jika Anda menemukan bug atau memiliki pertanyaan, silakan buat issue di GitHub repository.

## Kontak

-   Email: support@creavibes.com
-   Website: https://creavibes.com

---

Built with ❤️ using Laravel
