<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::latest()->paginate(15);

        return ApiResponse::paginated($users, 'Users retrieved successfully.');
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request);
        $user = $action->execute($dto);

        return ApiResponse::created([
            'user' => $user,
        ], 'User created successfully.');
    }
}
