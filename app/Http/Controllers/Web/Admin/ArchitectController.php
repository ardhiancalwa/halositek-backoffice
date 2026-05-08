<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Award\AwardResource;
use App\Http\Responses\ApiResponse;
use App\Models\Award;
use App\Models\Project;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchitectController extends Controller
{
    public function index(Request $request): Factory|View
    {
        $type = $request->input('type', 'award');
        $status = $request->input('status') ?? '';
        $perPage = 10;

        // Statistics
        $awardStats = [
            'pending' => Award::query()->where('status', 'pending')->count(),
            'approved' => Award::query()->where('status', 'approved')->count(),
            'declined' => Award::query()->where('status', 'declined')->count(),
        ];

        $designStats = [
            'pending' => Project::query()->where('status', 'pending')->count(),
            'approved' => Project::query()->where('status', 'approved')->count(),
            'declined' => Project::query()->where('status', 'declined')->count(),
        ];

        if ($type === 'design') {
            $query = Project::with('architect')->latest();

            if ($status && \in_array($status, ['pending', 'approved', 'declined'], true)) {
                $query->where('status', $status);
            }

            $items = $query->paginate($perPage)->withQueryString();
        } else {
            $query = Award::with('architect')->latest();

            if ($status && \in_array($status, ['pending', 'approved', 'declined'], true)) {
                $query->where('status', $status);
            }

            $items = $query->paginate($perPage)->withQueryString();
        }

        return view('admin.pages.dashboard.architects.index', compact(
            'items',
            'awardStats',
            'designStats',
            'type',
            'status',
        ));
    }

    public function awards(Request $request): JsonResponse
    {
        $query = Award::with('architect')->latest();

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if (\in_array($status, ['pending', 'approved', 'declined'], true)) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%");
            });
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 10)));
        $awards = $query->paginate($perPage);
        $awards->setCollection(AwardResource::collection($awards->getCollection())->collection);

        return ApiResponse::paginated($awards, 'Awards retrieved successfully.');
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => [
                'awards' => [
                    'pending' => Award::query()->where('status', 'pending')->count(),
                    'approved' => Award::query()->where('status', 'approved')->count(),
                    'declined' => Award::query()->where('status', 'declined')->count(),
                ],
                'designs' => [
                    'pending' => Project::query()->where('status', 'pending')->count(),
                    'approved' => Project::query()->where('status', 'approved')->count(),
                    'declined' => Project::query()->where('status', 'declined')->count(),
                ],
            ],
        ]);
    }
}
