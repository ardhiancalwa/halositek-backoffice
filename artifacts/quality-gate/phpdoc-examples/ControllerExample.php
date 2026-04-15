<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $catalogService)
    {
    }

    /**
     * Create a project item from request payload.
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
            'message' => 'Project created successfully.',
            'data' => $catalog,
        ], 201);
    }
}
