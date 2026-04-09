<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalogService)
    {
    }

    /**
     * Create a catalog item from request payload.
     *
     * @param  Request  $request  Incoming request with validated payload.
     * @return JsonResponse API response with created entity.
     *
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $catalog = $this->catalogService->create($request->all());

        return response()->json([
            'message' => 'Catalog created successfully.',
            'data' => $catalog,
        ], 201);
    }
}
