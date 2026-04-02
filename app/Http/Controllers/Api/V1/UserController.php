<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * Get list of users.
     *
     * @OA\Get(
     *   path="/users",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Response(response=200, description="Users retrieved successfully.")
     * )
     */
    public function index(): JsonResponse
    {
        $users = User::latest()->paginate(15);

        return ApiResponse::paginated($users, 'Users retrieved successfully.');
    }

    /**
     * Create a new user.
     *
     * @OA\Post(
     *   path="/users",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *   @OA\RequestBody(@OA\JsonContent(type="object")),
     *   @OA\Response(response=201, description="User created successfully.")
     * )
     */
    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request);
        $user = $action->execute($dto);

        return ApiResponse::created([
            'user' => $user,
        ], 'User created successfully.');
    }
}
