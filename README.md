# HaloSitek Backoffice

Production-ready Laravel 12 backoffice — **admin panel** (Filament v5) + **REST API** (Sanctum), backed by **MongoDB**.

## Tech Stack

| Layer        | Technology                               |
| ------------ | ---------------------------------------- |
| Framework    | Laravel 12                               |
| Database     | MongoDB (`mongodb/laravel-mongodb ^5.6`) |
| Admin Panel  | Filament PHP v5 (Livewire v4)            |
| API Auth     | Laravel Sanctum (token-based)            |
| Architecture | Action classes + readonly DTO classes    |
| Testing      | Pest PHP                                 |

## Requirements

- PHP >= 8.2
- Composer 2.x
- MongoDB >= 6.0 (running on `localhost:27017`)
- Node.js (for Filament assets)

## Setup

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env
php artisan key:generate

# Configure MongoDB in .env
# DB_CONNECTION=mongodb
# DB_DATABASE=halositek_backoffice

# Install Filament assets
php artisan filament:assets

# Create admin user
php artisan make:filament-user

# Start server
php artisan serve
```

## Project Structure

```
app/
├── Actions/           # Business logic (single-responsibility, final classes)
│   └── User/
│       └── CreateUserAction.php
├── DTOs/              # Data Transfer Objects (readonly classes)
│   └── User/
│       └── CreateUserDTO.php
├── Filament/
│   └── Resources/     # Admin panel resources
│       └── UserResource.php
├── Http/
│   ├── Controllers/Api/V1/   # Versioned API controllers
│   └── Requests/Api/V1/      # Form request validation
├── Models/            # MongoDB Eloquent models
└── Providers/         # Service providers
```

## Architecture Patterns

### Action Classes (`app/Actions/`)

- Single responsibility — one action per class
- Must be `final` (enforced by arch tests)
- Accept a DTO, return a Model
- Reusable across API controllers and Filament

### DTOs (`app/DTOs/`)

- Must be `readonly` classes (enforced by arch tests)
- Factory methods: `fromRequest()`, `fromArray()`
- Immutable data containers

## API Endpoints

All API routes are prefixed with `/api/v1`.

| Method | Endpoint         | Auth    | Description    |
| ------ | ---------------- | ------- | -------------- |
| POST   | `/api/v1/login`  | Public  | Get auth token |
| POST   | `/api/v1/logout` | Sanctum | Revoke token   |
| GET    | `/api/v1/me`     | Sanctum | Current user   |
| GET    | `/api/v1/users`  | Sanctum | List users     |
| POST   | `/api/v1/users`  | Sanctum | Create user    |

## Admin Panel

Access at `http://localhost:8000/admin` after creating an admin user.

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=CreateUserActionTest
```

> **Note:** Feature tests require MongoDB to be running.

## Package Upgrade Notes

| Package                   | Constraint | Watch For                                        |
| ------------------------- | ---------- | ------------------------------------------------ |
| `mongodb/laravel-mongodb` | `^5.6`     | Breaking changes on Laravel 13 upgrade           |
| `filament/filament`       | `^5.0`     | Livewire v4 dependency; check Filament changelog |
| `pestphp/pest`            | `^3.8`     | v4 requires PHP 8.3+                             |
