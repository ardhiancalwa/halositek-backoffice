<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\StoreAdminRequest;
use App\Http\Requests\Api\User\UpdateAdminRequest;
use App\Http\Resources\User\AdminResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class AdminController extends Controller
{
    /**
     * Get list of admins.
     *
     * @OA\Get(
     *   path="/admins",
     *   tags={"Admin Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="List all admins",
     *   description="Returns a paginated list of admin and super admin users (super admin only).",
     *
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Admins retrieved successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Admins retrieved successfully.", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QRS", "name": "Admin User", "email": "admin@halositek.com", "role": "admin", "account_status": "active"}, {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRT", "name": "Super Admin", "email": "superadmin@halositek.com", "role": "super_admin", "account_status": "active"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 2}, "links": {"first_page_url": "http://localhost:8000/api/v1/admins?page=1", "last_page_url": "http://localhost:8000/api/v1/admins?page=1", "next_page_url": null, "prev_page_url": null}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return ApiResponse::forbidden('Only super admin can access admin management.');
        }

        $query = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 15)));
        $admins = $query->paginate($perPage);
        $admins->setCollection(AdminResource::collection($admins->getCollection())->collection);

        return ApiResponse::paginated($admins, 'Admins retrieved successfully.');
    }

    /**
     * Create a new admin.
     *
     * @OA\Post(
     *   path="/admins",
     *   tags={"Admin Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Create admin",
     *   description="Creates a new admin or super admin user (super admin only).",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *
     *       @OA\Schema(
     *         type="object",
     *         required={"name","email","password","role"},
     *
     *         @OA\Property(property="name", type="string", example="New Admin"),
     *         @OA\Property(property="email", type="string", format="email", example="admin@halositek.com"),
     *         @OA\Property(property="password", type="string", format="password", example="password123"),
     *         @OA\Property(property="role", type="string", enum={"admin","super_admin"}, example="admin"),
     *         @OA\Property(property="photo_profile", type="string", format="binary")
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="Admin created successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 201, "message": "Admin created successfully.", "data": {"admin": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRU", "name": "New Admin", "email": "admin@halositek.com", "role": "admin", "account_status": "active"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(StoreAdminRequest $request, CreateUserAction $action): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return ApiResponse::forbidden('Only super admin can create admin users.');
        }

        $photoProfilePath = null;
        if ($request->hasFile('photo_profile')) {
            $photoProfilePath = $request->file('photo_profile')->store('users/profiles', 'public');
        }

        $dto = CreateUserDTO::fromRequest($request, $photoProfilePath);
        $admin = $action->execute($dto);

        return ApiResponse::created([
            'admin' => new AdminResource($admin),
        ], 'Admin created successfully.');
    }

    /**
     * Get admin detail by id.
     *
     * @OA\Get(
     *   path="/admins/{id}",
     *   tags={"Admin Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get admin detail",
     *   description="Returns details for a specific admin user (super admin only).",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Admin retrieved successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Admin retrieved successfully.", "data": {"admin": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRT", "name": "Admin User", "email": "admin@halositek.com", "role": "admin", "account_status": "active"}}})
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
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return ApiResponse::forbidden('Only super admin can access admin management.');
        }

        $admin = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->findOrFail($id);

        return ApiResponse::success([
            'admin' => new AdminResource($admin),
        ], 'Admin retrieved successfully.');
    }

    /**
     * Update admin user.
     *
     * @OA\Put(
     *   path="/admins/{id}",
     *   tags={"Admin Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Update admin",
     *   description="Updates an admin user's role or account status (super admin only).",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="role", type="string", enum={"admin","super_admin"}, example="admin"),
     *       @OA\Property(property="account_status", type="string", enum={"active","suspend"}, example="active")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Admin updated successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Admin updated successfully.", "data": {"admin": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRT", "name": "Admin User", "email": "admin@halositek.com", "role": "admin", "account_status": "suspend"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function update(UpdateAdminRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return ApiResponse::forbidden('Only super admin can update admin users.');
        }

        $admin = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->findOrFail($id);

        if ($user->id === $admin->id && $request->filled('role')) {
            return ApiResponse::forbidden('You cannot change your own role.');
        }

        $validated = $request->validated();

        if (isset($validated['role'])) {
            $admin->role = $validated['role'];
        }

        if (isset($validated['account_status'])) {
            $admin->account_status = $validated['account_status'];
        }

        $admin->save();

        return ApiResponse::success([
            'admin' => new AdminResource($admin),
        ], 'Admin updated successfully.');
    }

    /**
     * Delete admin user.
     *
     * @OA\Delete(
     *   path="/admins/{id}",
     *   tags={"Admin Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Delete admin",
     *   description="Deletes an admin user (super admin only).",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Admin deleted successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Admin deleted successfully.", "data": null})
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
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return ApiResponse::forbidden('Only super admin can delete admin users.');
        }

        $admin = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->findOrFail($id);

        if ($user->id === $admin->id) {
            return ApiResponse::forbidden('You cannot delete your own admin account.');
        }

        $admin->delete();

        return ApiResponse::success(null, 'Admin deleted successfully.');
    }
}
