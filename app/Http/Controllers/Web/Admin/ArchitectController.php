<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AwardStatus;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Award\AwardResource;
use App\Http\Responses\ApiResponse;
use App\Models\Award;
use App\Models\Project;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

            if ($request->filled('search')) {
                $search = trim($request->string('search')->toString());
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('architect', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $items = $query->paginate($perPage)->withQueryString();
        } else {
            $query = Award::with('architect')->latest();

            if ($status && \in_array($status, ['pending', 'approved', 'declined'], true)) {
                $query->where('status', $status);
            }

            if ($request->filled('search')) {
                $search = trim($request->string('search')->toString());
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('architect', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
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

    public function updateDesignStatus(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,declined,PENDING,APPROVED,DECLINED'],
        ]);

        $status = strtolower($validated['status']);

        $project->status = ProjectStatus::from($status);
        $project->save();

        return redirect()
            ->route('admin.dashboard.architects.index', ['type' => 'design'])
            ->with('success', 'Design status updated successfully.');
    }

    public function updateAwardStatus(Request $request, Award $award): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,declined,PENDING,APPROVED,DECLINED'],
        ]);

        $status = strtolower($validated['status']);

        $award->status = AwardStatus::from($status);
        $award->save();

        return redirect()
            ->route('admin.dashboard.architects.index', ['type' => 'award'])
            ->with('success', 'Award status updated successfully.');
    }
}
