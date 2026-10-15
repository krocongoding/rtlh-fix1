# Instalasi Windows (Laravel 13)

## Prasyarat
- PHP 8.3+
- Composer 2+
- Node.js 22+
- PostgreSQL 17 + PostGIS 3.5+ (atau Docker)

## 1. Buat project Laravel 13
```powershell
composer create-project laravel/laravel:^13.0 cirebon-rtlh
cd cirebon-rtlh
```

## 2. Salin source aplikasi ini ke project Laravel
Salin folder `app`, `bootstrap`, `database`, `resources`, `routes`, `public`, `.env.example`, dan `compose.yaml` dari paket ini, menimpa file yang sama.

## 3. Database
Cara Docker paling mudah:
```powershell
docker compose up -d
```
Lalu salin `.env.example` menjadi `.env` dan jalankan:
```powershell
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

## Demo accounts
- Admin: `admin@cirebon-rtlh.test` / `password`
- Surveyor: `surveyor@cirebon-rtlh.test` / `password`

Ganti password demo sebelum deployment.
