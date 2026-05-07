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

## Implementasi Fitur Chat

Endpoint chat ada di prefix `/api/v1/chat` (butuh `auth:sanctum`):

- `GET /conversations`: ambil daftar conversation milik user login.
- `POST /conversations`: buat conversation baru (private/group).
- `GET /conversations/{conversationId}`: detail conversation.
- `GET /conversations/{conversationId}/messages`: daftar message per conversation.
- `POST /messages`: kirim message.
- `POST /conversations/{conversationId}/read`: tandai message lawan bicara sebagai sudah dibaca.
- `POST /conversations/{conversationId}/typing`: kirim typing indicator realtime.

### Alur Implementasi

- `CreateConversationAction`: validasi partisipan, buat private/group chat, dan cegah duplikasi private conversation untuk partisipan yang sama.
- `GetUserConversationsAction`: ambil conversation user berdasarkan `participant_ids`, urut `updated_at DESC`, sertakan `last_message` dan `unread_count`.
- `SendMessageAction`: simpan message, update `last_read_at` pengirim, broadcast event `MessageSent`, lalu kirim notifikasi.
- `MarkMessageAsReadAction`: update `read_at` untuk message lawan bicara dan update `last_read_at` user aktif.

### Model dan Data

- `Conversation` dan `Message` disimpan di MongoDB.
- `participant_ids` dan `last_read_at` pada `Conversation` harus bertipe array/object (bukan JSON string).
- Untuk normalisasi data lama, jalankan:

```bash
php artisan chat:normalize-conversations --dry-run
php artisan chat:normalize-conversations
```

### Realtime dan Notifikasi

- Channel broadcast privat: `chat.conversation.{conversationId}` (lihat `routes/channels.php`).
- Event realtime: `chat.message.sent` dan `chat.typing`.
- `NewMessageNotification` saat ini dikirim via channel `broadcast` agar alur API chat tetap stabil pada setup lokal tanpa dependency notifikasi database.

### Test Chat

Jalankan test fitur chat:

```bash
php artisan test tests/Feature/Api/ChatApiTest.php tests/Feature/Api/ChatConversationFlowApiTest.php
```

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
