# HaloSitek Backoffice

Backoffice untuk HaloSitek berbasis Laravel 12.

Project ini dipakai untuk:
- REST API (auth, user, catalog, architect, FAQ)
- Admin panel internal via Filament
- Workflow quality check sebelum commit/push

## Stack

- Laravel 12
- MongoDB (`mongodb/laravel-mongodb`)
- Filament v5
- Sanctum
- Pest

## Kebutuhan

- PHP 8.2+
- Composer 2.x
- MongoDB aktif di local
- Node.js + npm

## Quick Start

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# set env database
# DB_CONNECTION=mongodb
# DB_DATABASE=halositek_backoffice

php artisan migrate
php artisan db:seed

php artisan filament:assets
php artisan make:filament-user

php artisan serve
```

Admin panel: `http://localhost:8000/admin`

## API Ringkas

Prefix semua endpoint: `/api/v1`

- Public auth: `/auth/register`, `/auth/login`, `/auth/refresh-token`
- Authenticated: `/me`, `/logout`, like/unlike catalog, save/unsave architect
- Admin: manage users, verify architect/catalog, manage FAQ
- Public data: list/detail catalog, architect, FAQ

Referensi route: `routes/api.php`

## Quality Check

Perintah utama:

```bash
composer quality
```

Perintah terpisah:

```bash
composer pint:check
composer phpcs
composer phpstan
composer test
```

## Git Hooks

- Pre-commit: lint staged PHP files (Pint + PHPCS)
- Pre-push: jalankan `composer phpstan`

Aktifkan hook jika belum:

```bash
npm run prepare
```

## Catatan Developer

- Jika ada issue lama di static analysis, baseline ada di `phpstan-baseline.neon`.
- Tambahkan kode baru tetap harus clean terhadap Pint, PHPCS, PHPStan, dan test.
