<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): Factory|View
    {
        return view('admin.pages.dashboard.users.index');
    }

    public function data(Request $request): JsonResponse
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
}
