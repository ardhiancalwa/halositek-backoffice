<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::latest()->paginate(15);

        return response()->json($users);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $dto = CreateUserDTO::fromRequest($request);
        $user = $action->execute($dto);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }
}
