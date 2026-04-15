<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreProjectRequest;
use App\Http\Requests\Api\V1\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Responses\ApiResponse;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *   path="/projects",
     *   tags={"Projects"},
     *   summary="List projects",
     *   description="Returns a paginated project list with optional status filtering.",
     *
     *   @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending","approved","declined"})),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Projects retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Projects retrieved successfully", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QRP", "architect_id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Modern House", "style": "Modern", "description": "Two-story tropical house with natural lighting.", "images": {"projects/images/project-1.jpg"}, "image_urls": {"http://localhost:8000/storage/projects/images/project-1.jpg"}, "estimated_cost": "Rp 2M - 3M", "layout_images": {"projects/layouts/layout-1.jpg"}, "layout_image_urls": {"http://localhost:8000/storage/projects/layouts/layout-1.jpg"}, "highlight_features": "Void area, skylight, and rooftop garden.", "area": "120 m2", "likes_count": 12, "status": "approved", "architect": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com"}, "created_at": "2026-04-13T10:00:00Z", "updated_at": "2026-04-13T10:00:00Z"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 12, "total": 1}, "links": {"first_page_url": "http://localhost:8000/api/v1/projects?page=1", "last_page_url": "http://localhost:8000/api/v1/projects?page=1", "next_page_url": null, "prev_page_url": null}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::with('architect')->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->string('status')->toString());
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 12)));
        $projects = $query->paginate($perPage);
        $projects->setCollection(ProjectResource::collection($projects->getCollection())->collection);

        return ApiResponse::paginated($projects, 'Projects retrieved successfully.');
    }

    /**
     * @OA\Post(
     *   path="/projects",
     *   tags={"Projects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Create project",
     *   description="Creates a new project by an architect with image and layout uploads.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *
     *       @OA\Schema(
     *         type="object",
     *         required={"name","style","estimated_cost"},
     *
     *         @OA\Property(property="name", type="string", example="Modern House"),
     *         @OA\Property(property="style", type="string", example="Modern"),
     *         @OA\Property(property="description", type="string", nullable=true, example="Two-story tropical house with natural lighting."),
     *         @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary")),
     *         @OA\Property(property="estimated_cost", type="string", example="Rp 2M - 3M"),
     *         @OA\Property(property="layout_images", type="array", @OA\Items(type="string", format="binary")),
     *         @OA\Property(property="highlight_features", type="string", nullable=true, example="Void area, skylight, and rooftop garden."),
     *         @OA\Property(property="area", type="string", nullable=true, example="120 m2")
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="Project created successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 201, "message": "Project created successfully", "data": {"project": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRP", "architect_id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Modern House", "style": "Modern", "description": "Two-story tropical house with natural lighting.", "images": {"projects/images/project-1.jpg"}, "image_urls": {"http://localhost:8000/storage/projects/images/project-1.jpg"}, "estimated_cost": "Rp 2M - 3M", "layout_images": {"projects/layouts/layout-1.jpg"}, "layout_image_urls": {"http://localhost:8000/storage/projects/layouts/layout-1.jpg"}, "highlight_features": "Void area, skylight, and rooftop garden.", "area": "120 m2", "likes_count": 0, "status": "pending", "architect": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com"}, "created_at": "2026-04-13T10:00:00Z", "updated_at": "2026-04-13T10:00:00Z"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        if (Gate::denies('create', Project::class)) {
            return ApiResponse::forbidden('You are not allowed to create project.');
        }

        $data = $request->validated();

        $user = Auth::user();
        $architectId = $user?->id;

        $imagePaths = [];
        foreach ($request->file('images', []) as $image) {
            $imagePaths[] = $image->store('projects/images', 'public');
        }

        $layoutPaths = [];
        foreach ($request->file('layout_images', []) as $image) {
            $layoutPaths[] = $image->store('projects/layouts', 'public');
        }

        $project = Project::create([
            'architect_id' => $architectId,
            'name' => $data['name'],
            'style' => $data['style'],
            'description' => $data['description'] ?? null,
            'images' => $imagePaths,
            'estimated_cost' => $data['estimated_cost'],
            'layout_images' => $layoutPaths,
            'highlight_features' => $data['highlight_features'] ?? null,
            'area' => $data['area'] ?? null,
            'status' => 'pending',
            'likes_count' => 0,
        ]);

        return ApiResponse::created([
            'project' => new ProjectResource($project->load('architect')),
        ], 'Project created successfully.');
    }

    /**
     * @OA\Get(
     *   path="/projects/{id}",
     *   tags={"Projects"},
     *   summary="Get project detail",
     *   description="Returns project details by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Project retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Project retrieved successfully", "data": {"project": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRP", "architect_id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Modern House", "style": "Modern", "description": "Two-story tropical house with natural lighting.", "images": {"projects/images/project-1.jpg"}, "image_urls": {"http://localhost:8000/storage/projects/images/project-1.jpg"}, "estimated_cost": "Rp 2M - 3M", "layout_images": {"projects/layouts/layout-1.jpg"}, "layout_image_urls": {"http://localhost:8000/storage/projects/layouts/layout-1.jpg"}, "highlight_features": "Void area, skylight, and rooftop garden.", "area": "120 m2", "likes_count": 12, "status": "approved", "architect": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com"}, "created_at": "2026-04-13T10:00:00Z", "updated_at": "2026-04-13T10:00:00Z"}}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $project = Project::with('architect')->findOrFail($id);

        return ApiResponse::success([
            'project' => new ProjectResource($project),
        ], 'Project retrieved successfully.');
    }

    /**
     * @OA\Put(
     *   path="/projects/{id}",
     *   tags={"Projects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Update project",
     *   description="Updates project data; admin users can only update project status.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *
     *       @OA\Schema(
     *         type="object",
     *
     *         @OA\Property(property="status", type="string", enum={"pending","approved","declined"}),
     *         @OA\Property(property="name", type="string", example="Modern House Updated"),
     *         @OA\Property(property="style", type="string", example="Modern"),
     *         @OA\Property(property="description", type="string", nullable=true, example="Updated description for the project."),
     *         @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary")),
     *         @OA\Property(property="estimated_cost", type="string", example="Rp 2M - 3M"),
     *         @OA\Property(property="layout_images", type="array", @OA\Items(type="string", format="binary")),
     *         @OA\Property(property="highlight_features", type="string", nullable=true, example="Updated highlight features."),
     *         @OA\Property(property="area", type="string", nullable=true, example="120 m2")
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Project updated successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Project updated successfully", "data": {"project": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRP", "architect_id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Modern House Updated", "style": "Modern", "description": "Updated description for the project.", "images": {"projects/images/project-1-updated.jpg"}, "image_urls": {"http://localhost:8000/storage/projects/images/project-1-updated.jpg"}, "estimated_cost": "Rp 2M - 3M", "layout_images": {"projects/layouts/layout-1-updated.jpg"}, "layout_image_urls": {"http://localhost:8000/storage/projects/layouts/layout-1-updated.jpg"}, "highlight_features": "Updated highlight features.", "area": "120 m2", "likes_count": 12, "status": "pending", "architect": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com"}, "created_at": "2026-04-13T10:00:00Z", "updated_at": "2026-04-13T11:00:00Z"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        $project = Project::findOrFail($id);

        $user = Auth::user();
        if ($user === null) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        if (Gate::forUser($user)->denies('update', $project)) {
            return ApiResponse::forbidden('You are not allowed to update this project.');
        }

        $isAdmin = $user->role === 'admin';

        $data = $request->validated();

        if ($isAdmin) {
            if (! array_key_exists('status', $data) || count($data) !== 1) {
                return ApiResponse::validationError([
                    'status' => ['Admin can only update project status.'],
                ], 'Validation error.');
            }

            $project->status = $data['status'];
            $project->save();

            return ApiResponse::success([
                'project' => new ProjectResource($project->load('architect')),
            ], 'Project status updated successfully.');
        }

        if (isset($data['status'])) {
            unset($data['status']);
        }

        if ($request->hasFile('images')) {
            foreach ((array) $project->images as $existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            $imagePaths = [];
            foreach ($request->file('images', []) as $image) {
                $imagePaths[] = $image->store('projects/images', 'public');
            }
            $data['images'] = $imagePaths;
        }

        if ($request->hasFile('layout_images')) {
            foreach ((array) $project->layout_images as $existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            $layoutPaths = [];
            foreach ($request->file('layout_images', []) as $image) {
                $layoutPaths[] = $image->store('projects/layouts', 'public');
            }
            $data['layout_images'] = $layoutPaths;
        }

        $project->fill($data);
        $project->status = 'pending';
        $project->save();

        return ApiResponse::success([
            'project' => new ProjectResource($project->load('architect')),
        ], 'Project updated successfully.');
    }

    /**
     * @OA\Delete(
     *   path="/projects/{id}",
     *   tags={"Projects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Delete project",
     *   description="Deletes a project and related stored image files by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Project deleted successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Project deleted successfully", "data": null})
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
        $project = Project::findOrFail($id);

        $user = Auth::user();
        if ($user === null) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        if (Gate::forUser($user)->denies('delete', $project)) {
            return ApiResponse::forbidden('You are not allowed to delete this project.');
        }

        foreach ((array) $project->images as $existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        foreach ((array) $project->layout_images as $existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        $project->delete();

        return ApiResponse::success(null, 'Project deleted successfully.');
    }
}
