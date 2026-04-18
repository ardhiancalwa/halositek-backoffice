# 📘 HaloSitek AI Microservice — Dokumentasi API untuk Developer Back-End

> Dokumen ini ditujukan untuk developer **Laravel** yang akan mengintegrasikan AI Microservice ke dalam sistem HaloSitek.

---

## Base URL

```
http://<AI_SERVER_IP>:8000
```

Pada saat development lokal:
```
http://localhost:8000
```

---

## Daftar Endpoint

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/health` | Cek status service & koneksi Ollama |
| `POST` | `/api/v1/generate` | Generate respons AI (teks atau gambar) |
| `GET` | `/docs` | Swagger UI (hanya untuk debugging) |

---

## 1. Health Check

Gunakan endpoint ini untuk memastikan AI service hidup sebelum mengirim request.

### Request

```
GET /health
```

### Response `200 OK`

```json
{
  "status": "healthy",
  "ollama_connected": true,
  "model": "llama3",
  "sd_model": "stabilityai/sdxl-turbo"
}
```

### Contoh di Laravel

```php
$response = Http::timeout(5)->get('http://localhost:8000/health');

if ($response->ok() && $response->json('ollama_connected') === true) {
    // AI Service siap digunakan
}
```

---

## 2. Generate (Endpoint Utama)

Endpoint ini menangani **dua jenis output** secara otomatis:
- **Teks** — jawaban konsultasi arsitektur
- **Gambar** — visualisasi / denah hasil generate AI

AI service menentukan jenis output berdasarkan isi `message` dari pengguna.

### Request

```
POST /api/v1/generate
Content-Type: application/json
```

**Body:**

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| `user_id` | `string` | ✅ | ID unik pengguna dari MongoDB |
| `message` | `string` | ✅ | Pesan/pertanyaan dari pengguna |
| `history` | `array` | ❌ | Riwayat percakapan sebelumnya (default: `[]`) |

**Format `history`:**

```json
[
  { "role": "user", "content": "pesan user sebelumnya" },
  { "role": "assistant", "content": "jawaban AI sebelumnya" }
]
```

> ⚠️ `role` hanya boleh bernilai `"user"` atau `"assistant"`.

---

### Response: Teks (`type = "text"`)

Dikembalikan ketika pengguna bertanya tentang arsitektur (FAQ, konsultasi, dll).

```json
{
  "type": "text",
  "content": "Pondasi cakar ayam adalah jenis pondasi dangkal yang terdiri dari pelat beton bertulang...",
  "prompt_used": null
}
```

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `type` | `"text"` | Jenis respons |
| `content` | `string` | Jawaban teks dari AI |
| `prompt_used` | `null` | Selalu `null` untuk respons teks |

---

### Response: Gambar (`type = "image"`)

Dikembalikan ketika pengguna meminta visualisasi / gambar desain.

```json
{
  "type": "image",
  "content": "iVBORw0KGgoAAAANSUhEUgAAAgAAAAIACAIAAAB7...",
  "prompt_used": "A minimalist 2-story house floor plan, top-down architectural rendering, clean lines, 4K, photorealistic"
}
```

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `type` | `"image"` | Jenis respons |
| `content` | `string` | **Data gambar PNG dalam format Base64** |
| `prompt_used` | `string` | Prompt teknis (English) yang digunakan untuk generate gambar |

**Cara menampilkan gambar di front-end:**
```html
<img src="data:image/png;base64,{{ $response['content'] }}" alt="AI Generated Image" />
```

**Cara menyimpan gambar ke storage Laravel:**
```php
$imageData = base64_decode($response['content']);
Storage::disk('public')->put("ai_images/{$userId}/" . time() . '.png', $imageData);
```

---

### Response: Error

**`422 Unprocessable Entity`** — Field wajib tidak diisi:
```json
{
  "detail": [
    {
      "type": "missing",
      "loc": ["body", "user_id"],
      "msg": "Field required"
    }
  ]
}
```

**`503 Service Unavailable`** — Ollama tidak bisa dijangkau:
```json
{
  "detail": "Tidak dapat terhubung ke Ollama di http://localhost:11434. Pastikan Ollama sudah berjalan (`ollama serve`)."
}
```

**`500 Internal Server Error`** — Error tak terduga:
```json
{
  "detail": "Terjadi kesalahan internal: [pesan error]"
}
```

---

## 3. Contoh Lengkap Integrasi Laravel

### Service Class

```php
<?php
// app/Services/HaloSitekAIService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HaloSitekAIService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.halositek_ai.url', 'http://localhost:8000');
    }

    /**
     * Kirim pesan ke AI dan dapatkan respons (teks atau gambar).
     */
    public function generate(string $userId, string $message, array $history = []): array
    {
        $response = Http::timeout(120)
            ->post("{$this->baseUrl}/api/v1/generate", [
                'user_id' => $userId,
                'message' => $message,
                'history' => $history,
            ]);

        if ($response->failed()) {
            Log::error('AI Service error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('AI Service tidak merespons: ' . $response->body());
        }

        $data = $response->json();

        // Jika gambar, simpan ke storage dan ganti content dengan URL
        if ($data['type'] === 'image') {
            $filename = "ai_images/{$userId}/" . time() . '.png';
            Storage::disk('public')->put($filename, base64_decode($data['content']));
            $data['content'] = Storage::url($filename);
        }

        return $data;
    }

    /**
     * Cek apakah AI Service hidup.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->ok() && $response->json('ollama_connected') === true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

### Controller

```php
<?php
// app/Http/Controllers/ChatController.php

namespace App\Http\Controllers;

use App\Services\HaloSitekAIService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request, HaloSitekAIService $ai)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user   = $request->user();
        // Ambil history dari database (5 pesan terakhir)
        $history = $user->chatMessages()
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn ($msg) => [
                'role'    => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->toArray();

        $result = $ai->generate(
            userId:  (string) $user->_id,
            message: $validated['message'],
            history: $history,
        );

        // Simpan pesan user & respons AI ke database
        $user->chatMessages()->createMany([
            ['role' => 'user',      'content' => $validated['message']],
            ['role' => 'assistant', 'content' => $result['content'], 'type' => $result['type']],
        ]);

        return response()->json($result);
    }
}
```

### Config

```php
// config/services.php (tambahkan)
'halositek_ai' => [
    'url' => env('HALOSITEK_AI_URL', 'http://localhost:8000'),
],
```

```env
# .env Laravel
HALOSITEK_AI_URL=http://localhost:8000
```

---

## 4. Kata Kunci yang Memicu Generate Gambar

Jika `message` pengguna mengandung salah satu kata berikut, AI akan menghasilkan **gambar** (bukan teks):

| Bahasa Indonesia | Bahasa Inggris |
|-----------------|----------------|
| gambar, gambarkan, visualisasi | generate image, draw, visualize |
| denah, desain, render, sketsa | design, blueprint, sketch |
| layout, floor plan, rancangan | create image, show me |
| fasad, tampak depan/samping | facade |
| 3d, perspektif, interior, eksterior | — |

> 💡 Jika kata kunci tidak terdeteksi, AI akan menjawab dengan **teks** secara default.

---

## 5. Tips Integrasi

| Topik | Rekomendasi |
|-------|-------------|
| **Timeout** | Set minimal `120` detik — generate gambar bisa lama di CPU |
| **History** | Kirim max **10 pesan** terakhir agar konteks tidak terlalu besar |
| **Gambar** | Selalu simpan ke storage, jangan simpan Base64 mentah di database |
| **Error Handling** | Tangkap `503` secara khusus — artinya Ollama mati, bukan bug kode |
| **Health Check** | Panggil `/health` saat app boot untuk early warning |
| **Queue** | Pertimbangkan `dispatch` ke Laravel Queue untuk request gambar agar tidak blocking |
