# Cirebon RTLH MVP

This is the application source layer for a Laravel 13 project. `vendor/` is intentionally not included.

## Requirements
- PHP 8.3+
- Composer
- PostgreSQL + PostGIS
- Docker optional (included compose.yaml)

## Install
1. Start from a clean Laravel 13 skeleton.
2. Copy/merge this package into it.
3. `composer install`
4. `cp .env.example .env`
5. `php artisan key:generate`
6. `docker compose up -d`
7. `php artisan migrate --seed`
8. `php artisan serve`

## Demo accounts
admin@cirebon-rtlh.test / password
surveyor@cirebon-rtlh.test / password

## Included in MVP
- Login/logout + 3 role redirects
- Admin dashboard + CRUD RTLH + review approve/revision
- Surveyor workspace + own-data CRUD
- Public map shell + public JSON API
- Public statistics
- Public CSV download
- PostGIS boundary and house point columns
- Assessment/program/statistics/import/download/audit schema
- Master condition values

## Intentionally pending
- official Cirebon GIS import
- full condition assessment UI
- photo upload UI
- Excel/GeoJSON/GDB import implementation
- advanced filtered exports (Excel/GeoJSON)
- program management UI
- production security/hardening/tests
- final scoring methodology based on official reference
