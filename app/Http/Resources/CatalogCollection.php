<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CatalogCollection extends ResourceCollection
{
    private string $message;

    public function __construct($resource, string $message = 'Daftar katalog berhasil diambil.')
    {
        parent::__construct($resource);
        $this->message = $message;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\JsonResponse  $response
     * @return void
     */
    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);

        // Map mapping from paginator standard output
        $data = $jsonResponse['data'] ?? [];
        $meta = $jsonResponse['meta'] ?? [];
        $links = $jsonResponse['links'] ?? [];

        $formatted = [
            'success' => true,
            'status_code' => 200,
            'message' => $this->message,
            'data' => $data,
        ];

        // Ensure meta structure matches exact specification
        if (!empty($meta)) {
            $formatted['meta'] = [
                'current_page' => $meta['current_page'] ?? null,
                'last_page' => $meta['last_page'] ?? null,
                'per_page' => $meta['per_page'] ?? null,
                'total' => $meta['total'] ?? null,
            ];
        }

        // Ensure links structure matches exact specification
        if (!empty($links)) {
            $formatted['links'] = [
                'first_page_url' => $links['first'] ?? null,
                'last_page_url' => $links['last'] ?? null,
                'next_page_url' => $links['next'] ?? null,
                'prev_page_url' => $links['prev'] ?? null,
            ];
        }

        $response->setContent(json_encode($formatted));
    }
}
