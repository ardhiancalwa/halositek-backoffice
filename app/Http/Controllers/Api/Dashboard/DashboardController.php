<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Enums\ProjectStyle;
use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Responses\ApiResponse;
use App\Models\ArchitectWishlist;
use App\Models\Consultation;
use App\Models\Project;
use App\Models\SavedProject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *   path="/dashboard/summary",
     *   tags={"Dashboard"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get user dashboard summary",
     *   description="Returns the total saved designs, saved architects, and total consultations for the authenticated user.",
     *
     *   @OA\Response(response=200, description="Dashboard summary retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Dashboard summary retrieved successfully.", "data": {"total_saved_designs": 5, "total_saved_architects": 3, "total_consultations": 2}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function summary(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user === null) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        $userId = (string) $user->id;

        $totalSavedDesigns = SavedProject::query()
            ->where('user_id', $userId)
            ->count();

        $totalSavedArchitects = ArchitectWishlist::query()
            ->where('user_id', $userId)
            ->count();

        $totalConsultations = Consultation::query()
            ->where('user_id', $userId)
            ->count();

        return ApiResponse::success([
            'total_saved_designs' => $totalSavedDesigns,
            'total_saved_architects' => $totalSavedArchitects,
            'total_consultations' => $totalConsultations,
        ], 'Dashboard summary retrieved successfully.');
    }

    /**
     * @OA\Get(
     *   path="/dashboard/design/featured",
     *   tags={"Dashboard"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get featured design",
     *   description="Returns the single most popular design from the last 7 days based on likes and saves count, optionally filtered by style.",
     *
     *   @OA\Parameter(name="style", in="query", required=true,
     *
     *     @OA\Schema(type="string", enum={"all","modern","traditional","minimalist","futuristik","industrial"})
     *   ),
     *
     *   @OA\Response(response=200, description="Featured design retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Featured design retrieved successfully.", "data": {"design": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRP", "architect_id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Modern House", "style": "modern", "likes_count": 12, "saves_count": 8}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function featuredDesign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'style' => ['required', 'string', 'in:all,modern,traditional,minimalist,futuristik,industrial'],
        ]);

        $style = $validated['style'];
        $oneWeekAgo = Carbon::now()->subWeek();

        $query = Project::with('architect')
            ->where('status', 'approved')
            ->where('created_at', '>=', $oneWeekAgo);

        if ($style !== 'all') {
            $query->where('style', $style);
        }

        // Get all matching projects to compute saves_count and rank by popularity.
        $projects = $query->get();

        if ($projects->isEmpty()) {
            return ApiResponse::notFound('No featured design found for the selected style.');
        }

        // Collect project IDs to batch-count saves.
        $projectIds = $projects->pluck('id')->toArray();
        $savesCounts = SavedProject::query()
            ->whereIn('project_id', $projectIds)
            ->get()
            ->groupBy('project_id')
            ->map(fn ($group) => $group->count());

        // Find the project with the highest popularity score (likes + saves).
        $featured = $projects->sortByDesc(function (Project $project) use ($savesCounts) {
            $likesCount = (int) ($project->likes_count ?? 0);
            $savesCount = (int) ($savesCounts[$project->id] ?? 0);

            return $likesCount + $savesCount;
        })->first();

        $savesCount = (int) ($savesCounts[$featured->id] ?? 0);
        $featured->setAttribute('saves_count', $savesCount);

        return ApiResponse::success([
            'design' => array_merge(
                (new ProjectResource($featured))->toArray($request),
                ['saves_count' => $savesCount]
            ),
        ], 'Featured design retrieved successfully.');
    }

    /**
     * @OA\Get(
     *   path="/dashboard/design/recommend",
     *   tags={"Dashboard"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get recommended designs for user",
     *   description="Returns up to 2 recommended designs based on the styles of designs the user has saved, ranked by the highest likes_count. Only approved designs that the user has not yet saved are returned.",
     *
     *   @OA\Response(response=200, description="Recommended designs retrieved successfully",
     *
     *     @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Recommended designs retrieved successfully.", "data": {"designs": {{"id": "uuid-1", "name": "Modern Villa", "style": "modern", "likes_count": 25}, {"id": "uuid-2", "name": "Minimalist House", "style": "minimalist", "likes_count": 18}}}})
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function recommendDesign(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user === null) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        $userId = (string) $user->id;

        // 1. Get all project IDs saved by the user.
        $savedProjects = SavedProject::query()
            ->where('user_id', $userId)
            ->get();

        $savedProjectIds = $savedProjects->pluck('project_id')->toArray();

        if (empty($savedProjectIds)) {
            // Fallback: no saved designs, recommend the most liked approved designs.
            $recommendations = Project::with('architect')
                ->where('status', 'approved')
                ->orderByDesc('likes_count')
                ->limit(2)
                ->get();

            if ($recommendations->isEmpty()) {
                return ApiResponse::notFound('No recommended designs found.');
            }
        } else {
            // 2. Get the styles of the saved designs and find the most relevant ones.
            $savedDesigns = Project::query()
                ->whereIn('id', $savedProjectIds)
                ->get();

            // Count occurrences of each style to determine user preference.
            $styleCounts = $savedDesigns
                ->groupBy(function (Project $project): string {
                    $style = $project->style;

                    return $style instanceof ProjectStyle ? $style->value : (string) $style;
                })
                ->map(fn ($group) => $group->count())
                ->sortDesc();

            if ($styleCounts->isEmpty()) {
                // Fallback: saved projects have no styles, recommend the most liked.
                $recommendations = Project::with('architect')
                    ->where('status', 'approved')
                    ->orderByDesc('likes_count')
                    ->limit(2)
                    ->get();
            } else {
                // Get the style values (strings) ordered by relevance.
                $preferredStyles = $styleCounts->keys()->toArray();

                // 3. Find approved designs matching preferred styles, excluding already-saved ones,
                //    ordered by likes_count descending, limited to 2.
                $recommendations = Project::with('architect')
                    ->where('status', 'approved')
                    ->whereIn('style', $preferredStyles)
                    ->whereNotIn('id', $savedProjectIds)
                    ->orderByDesc('likes_count')
                    ->limit(2)
                    ->get();
            }

            if ($recommendations->isEmpty()) {
                return ApiResponse::notFound('No recommended designs found based on your saved designs.');
            }
        }

        // 4. Batch-count saves for the recommended projects.
        $recommendedIds = $recommendations->pluck('id')->toArray();
        $savesCounts = SavedProject::query()
            ->whereIn('project_id', $recommendedIds)
            ->get()
            ->groupBy('project_id')
            ->map(fn ($group) => $group->count());

        $designs = $recommendations->map(function (Project $project) use ($request, $savesCounts) {
            $savesCount = (int) ($savesCounts[$project->id] ?? 0);
            $project->setAttribute('saves_count', $savesCount);

            return array_merge(
                (new ProjectResource($project))->toArray($request),
                ['saves_count' => $savesCount]
            );
        })->values()->toArray();

        return ApiResponse::success([
            'designs' => $designs,
        ], 'Recommended designs retrieved successfully.');
    }
}
