<?php

namespace App\Http\Controllers\Web\Admin;

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemAdminController extends Controller
{
    public function index(): Factory|View
    {
        $registeredAdminCount = User::where('role', UserRole::Admin->value)->count();
        $activeAdminCount = User::where('role', UserRole::Admin->value)->where('account_status', 'active')->count();

        return view('admin.pages.dashboard.admins.index', [
            'registeredAdminCount' => $registeredAdminCount,
            'activeAdminCount' => $activeAdminCount,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        if (Gate::denies('viewAny', User::class)) {
            return ApiResponse::forbidden('You are not allowed to view admins.');
        }

        $query = User::query()->where('role', UserRole::Admin->value)->latest();

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

        return ApiResponse::paginated($users, 'Admins retrieved successfully.');
    }

    public function store(Request $request, CreateUserAction $action): JsonResponse
    {
        if (Gate::denies('create', User::class)) {
            return ApiResponse::forbidden('You are not allowed to create admins.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $validated['role'] = UserRole::Admin->value;
        $validated['account_status'] = 'active';

        $dto = CreateUserDTO::fromArray($validated);
        $user = $action->execute($dto);

        return ApiResponse::created([
            'user' => new UserResource($user),
        ], 'Admin created successfully.');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if (Gate::denies('update', $user)) {
            return ApiResponse::forbidden('You are not allowed to update this admin.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'account_status' => ['sometimes', 'string', 'in:active,suspend'],
        ]);

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        if (array_key_exists('account_status', $validated)) {
            $user->account_status = $validated['account_status'];
        }

        $user->save();

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Admin updated successfully.');
    }
}
