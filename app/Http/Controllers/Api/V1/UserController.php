<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
     *   summary="List users",
     *   description="Returns a paginated user list for administrative usage (admin only).",
     *
     *   @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"active","suspend"})),
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Users retrieved successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Users retrieved successfully.", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QRS", "name": "Admin User", "email": "admin@halositek.com", "role": "admin", "account_status": "active"}, {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRT", "name": "Regular User", "email": "user@halositek.com", "role": "user", "account_status": "active"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 2}, "links": {"first_page_url": "http://localhost:8000/api/v1/users?page=1", "last_page_url": "http://localhost:8000/api/v1/users?page=1", "next_page_url": null, "prev_page_url": null}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if (Gate::denies('viewAny', User::class)) {
            return ApiResponse::forbidden('You are not allowed to view users.');
        }

        $query = User::query()->latest();

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if (in_array($status, ['active', 'suspend'], true)) {
                $query->where('account_status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 15)));
        $users = $query->paginate($perPage);
        $users->setCollection(UserResource::collection($users->getCollection())->collection);

        return ApiResponse::paginated($users, 'Users retrieved successfully.');
    }

    /**
     * Create a new user.
     *
     * @OA\Post(
     *   path="/users",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *   summary="Create user",
     *   description="Creates a new user account from the administrative panel.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"name","email","password"},
     *
     *       @OA\Property(property="name", type="string", example="New User"),
     *       @OA\Property(property="email", type="string", format="email", example="new_user@halositek.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password123"),
     *       @OA\Property(property="role", type="string", enum={"user","architect","admin"}, example="user"),
     *       @OA\Property(property="account_status", type="string", enum={"active","suspend"}, example="active")
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="User created successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 201, "message": "User created successfully.", "data": {"user": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRU", "name": "New User", "email": "new_user@halositek.com", "role": "user", "account_status": "active"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        if (Gate::denies('create', User::class)) {
            return ApiResponse::forbidden('You are not allowed to create user.');
        }

        $dto = CreateUserDTO::fromRequest($request);
        $user = $action->execute($dto);

        return ApiResponse::created([
            'user' => new UserResource($user),
        ], 'User created successfully.');
    }

    /**
     * Get user detail by id.
     *
     * @OA\Get(
     *   path="/users/{id}",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get user detail",
     *   description="Returns details for a specific user by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="User retrieved successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "User retrieved successfully.", "data": {"user": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRT", "name": "Regular User", "email": "user@halositek.com", "role": "user", "account_status": "active"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (Gate::denies('view', $user)) {
            return ApiResponse::forbidden('You are not allowed to view this user.');
        }

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'User retrieved successfully.');
    }

    /**
     * Update user account status by id.
     *
     * @OA\Put(
     *   path="/users/{id}",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *   summary="Update user status",
     *   description="Updates a target user's account status (for example: active or suspend).",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"account_status"},
     *
     *       @OA\Property(property="account_status", type="string", enum={"active","suspend"})
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="User status updated successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "User status updated successfully.", "data": {"user": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRT", "name": "Regular User", "email": "user@halositek.com", "role": "user", "account_status": "suspend"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (Gate::denies('update', $user)) {
            return ApiResponse::forbidden('You are not allowed to update this user.');
        }

        $user->account_status = $request->validated('account_status');
        $user->save();

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'User status updated successfully.');
    }

    /**
     * Update profile of current authenticated user.
     *
     * @OA\Put(
     *   path="/me",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *   summary="Update current profile",
     *   description="Updates the profile of the currently authenticated user.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="name", type="string", example="Budi Santoso"),
     *       @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password123")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Profile updated successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Profile updated successfully.", "data": {"user": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRT", "name": "Updated User Name", "email": "user@halositek.com", "role": "user", "account_status": "active"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        if (Gate::denies('updateProfile', $user)) {
            return ApiResponse::forbidden('You are not allowed to update this profile.');
        }

        $data = $request->validated();
        $user->fill($data);
        $user->save();

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Profile updated successfully.');
    }

    /**
     * Delete user by id.
     *
     * @OA\Delete(
     *   path="/users/{id}",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *   summary="Delete user",
     *   description="Deletes a user account by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="User deleted successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "User deleted successfully.", "data": null})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (Gate::denies('delete', $user)) {
            return ApiResponse::forbidden('You are not allowed to delete this user.');
        }

        $user->delete();

        return ApiResponse::success(null, 'User deleted successfully.');
    }
}
