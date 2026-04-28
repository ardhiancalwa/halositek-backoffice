<?php

namespace App\Http\Responses;

use App\Enums\ApiStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

final class ApiResponse
{
    /**
     * Return a success response.
     */
    public static function success(mixed $data = null, ?string $message = null, ApiStatus $status = ApiStatus::SUCCESS): JsonResponse
    {
        $response = [
            'success' => true,
            'status_code' => $status->value,
            'message' => $status->message($message),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status->value);
    }

    /**
     * Return a paginated success response.
     *
     * @param  array<string, int|string|float|bool|null>  $meta
     */
    public static function paginated(LengthAwarePaginator $paginator, ?string $message = null, ApiStatus $status = ApiStatus::SUCCESS, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'status_code' => $status->value,
            'message' => $status->message($message),
            'data' => $paginator->items(),
            'meta' => array_merge([
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ], $meta),
            'links' => [
                'first_page_url' => $paginator->url(1),
                'last_page_url' => $paginator->url($paginator->lastPage()),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ];

        return response()->json($response, $status->value);
    }

    /**
     * Return a paginated success response with pre-transformed items.
     *
     * @param  list<mixed>  $items
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @param  array<string, int|string|float|bool|null>  $meta
     */
    public static function paginatedItems(array $items, LengthAwarePaginator $paginator, ?string $message = null, ApiStatus $status = ApiStatus::SUCCESS, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'status_code' => $status->value,
            'message' => $status->message($message),
            'data' => $items,
            'meta' => array_merge([
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ], $meta),
            'links' => [
                'first_page_url' => $paginator->url(1),
                'last_page_url' => $paginator->url($paginator->lastPage()),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ];

        return response()->json($response, $status->value);
    }

    /**
     * Return a created response (201).
     */
    public static function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::success($data, $message, ApiStatus::CREATED);
    }

    /**
     * Return an error response.
     */
    public static function error(?string $message = null, ApiStatus $status = ApiStatus::BAD_REQUEST, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'status_code' => $status->value,
            'message' => $status->message($message),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status->value);
    }

    /**
     * Return an unauthorized response (401).
     */
    public static function unauthorized(?string $message = null): JsonResponse
    {
        return self::error($message, ApiStatus::UNAUTHORIZED);
    }

    /**
     * Return a forbidden response (403).
     */
    public static function forbidden(?string $message = null): JsonResponse
    {
        return self::error($message, ApiStatus::FORBIDDEN);
    }

    /**
     * Return a validation error response (422).
     */
    public static function validationError(array $errors, ?string $message = null): JsonResponse
    {
        return self::error($message, ApiStatus::UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return a not found response (404).
     */
    public static function notFound(?string $message = null): JsonResponse
    {
        return self::error($message, ApiStatus::NOT_FOUND);
    }
}
