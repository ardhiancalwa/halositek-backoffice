<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Award\StoreAwardRequest;
use App\Http\Requests\Api\Award\UpdateAwardRequest;
use App\Http\Resources\AwardResource;
use App\Http\Responses\ApiResponse;
use App\Models\Award;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class AwardController extends Controller
{
    /**
     * @OA\Get(
     *   path="/awards",
     *   tags={"Awards"},
     *   security={},
     *   summary="List awards",
     *   description="Returns a paginated award list with optional status filtering.",
     *
     *   @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending","approved","declined"})),
     *   @OA\Parameter(name="architect_id", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Awards retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Awards retrieved successfully", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QAW", "name": "Best Residential Design", "project_name": "Modern House", "status": "approved"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 12, "total": 1}, "links": {"first_page_url": "http://localhost:8000/api/v1/awards?page=1", "last_page_url": "http://localhost:8000/api/v1/awards?page=1", "next_page_url": null, "prev_page_url": null}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Award::with('architect')->latest();

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if (in_array($status, ['pending', 'approved', 'declined'], true)) {
                $query->byStatus($status);
            }
        }

        if ($request->filled('architect_id')) {
            $query->where('architect_id', $request->string('architect_id')->toString());
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%");
            });
        }

        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $approvedCount = (clone $query)->where('status', 'approved')->count();

        $perPage = min(50, max(1, (int) $request->input('per_page', 12)));
        $awards = $query->paginate($perPage);
        $awards->setCollection(AwardResource::collection($awards->getCollection())->collection);

        return ApiResponse::paginated($awards, 'Awards retrieved successfully.', meta: [
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
        ]);
    }

    /**
     * @OA\Post(
     *   path="/awards",
     *   tags={"Awards"},
     *   security={{"BearerAuth":{}}},
     *   summary="Create award",
     *   description="Creates a new award record by an architect, including verification file upload.",
     *
     *   @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data")),
     *
     *   @OA\Response(response=201, description="Award created successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 201, "message": "Award created successfully", "data": {"award": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QAW", "name": "Best Residential Design", "project_name": "Modern House", "status": "pending"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(StoreAwardRequest $request): JsonResponse
    {
        if (Gate::denies('create', Award::class)) {
            return ApiResponse::forbidden('You are not allowed to create award.');
        }

        $data = $request->validated();

        $user = Auth::user();
        $architectId = $user?->id;

        $verificationFilePath = $request->file('verification_file')
            ? $request->file('verification_file')->store('awards/verification-files', 'public')
            : null;

        $award = Award::create([
            'architect_id' => $architectId,
            'name' => $data['name'],
            'project_name' => $data['project_name'],
            'role' => $data['role'],
            'award_date' => $data['award_date'],
            'description' => $data['description'] ?? null,
            'verification_file' => $verificationFilePath,
            'status' => 'pending',
        ]);

        return ApiResponse::created([
            'award' => new AwardResource($award->load('architect')),
        ], 'Award created successfully.');
    }

    /**
     * @OA\Get(
     *   path="/awards/{id}",
     *   tags={"Awards"},
     *   security={},
     *   summary="Get award detail",
     *   description="Returns award details by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Award retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Award retrieved successfully", "data": {"award": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QAW", "name": "Best Residential Design", "project_name": "Modern House", "status": "approved"}}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $award = Award::with('architect')->findOrFail($id);

        return ApiResponse::success([
            'award' => new AwardResource($award),
        ], 'Award retrieved successfully.');
    }

    /**
     * @OA\Put(
     *   path="/awards/{id}",
     *   tags={"Awards"},
     *   security={{"BearerAuth":{}}},
     *   summary="Update award",
     *   description="Updates award data; admin users can only update award status.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data")),
     *
     *   @OA\Response(response=200, description="Award updated successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Award updated successfully", "data": {"award": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QAW", "name": "Best Residential Design", "project_name": "Modern House", "status": "pending"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function update(UpdateAwardRequest $request, string $id): JsonResponse
    {
        $award = Award::findOrFail($id);

        /** @var User|null $user */
        $user = Auth::user();
        if ($user === null) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        if (Gate::forUser($user)->denies('update', $award)) {
            return ApiResponse::forbidden('You are not allowed to update this award.');
        }

        $isAdmin = $user->isAdmin();

        $data = $request->validated();

        if ($isAdmin) {
            if (! array_key_exists('status', $data) || count($data) !== 1) {
                return ApiResponse::validationError([
                    'status' => ['Admin can only update award status.'],
                ], 'Validation error.');
            }

            $award->status = $data['status'];
            $award->save();

            return ApiResponse::success([
                'award' => new AwardResource($award->load('architect')),
            ], 'Award status updated successfully.');
        }

        if (isset($data['status'])) {
            unset($data['status']);
        }

        if ($request->hasFile('verification_file')) {
            if ($award->verification_file) {
                Storage::disk('public')->delete($award->verification_file);
            }

            $data['verification_file'] = $request->file('verification_file')->store('awards/verification-files', 'public');
        }

        $award->fill($data);
        $award->status = 'pending';
        $award->save();

        return ApiResponse::success([
            'award' => new AwardResource($award->load('architect')),
        ], 'Award updated successfully.');
    }

    /**
     * @OA\Put(
     *   path="/awards/{id}/approve",
     *   tags={"Awards"},
     *   security={{"BearerAuth":{}}},
     *   summary="Review award approval",
     *   description="Approves or declines an award by setting status to approved or declined. Admin only endpoint.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"status"},
     *
     *       @OA\Property(property="status", type="string", enum={"approved","declined"}, example="approved")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Award status updated successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Award status updated successfully.", "data": {"award": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QAW", "status": "approved"}}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $award = Award::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,declined'],
        ]);

        $award->status = $validated['status'];
        $award->save();

        return ApiResponse::success([
            'award' => new AwardResource($award->load('architect')),
        ], 'Award status updated successfully.');
    }

    /**
     * @OA\Delete(
     *   path="/awards/{id}",
     *   tags={"Awards"},
     *   security={{"BearerAuth":{}}},
     *   summary="Delete award",
     *   description="Deletes an award record and its verification file by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Award deleted successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Award deleted successfully", "data": null})
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
        $award = Award::findOrFail($id);

        /** @var User|null $user */
        $user = Auth::user();
        if ($user === null) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        if (Gate::forUser($user)->denies('delete', $award)) {
            return ApiResponse::forbidden('You are not allowed to delete this award.');
        }

        if ($award->verification_file) {
            Storage::disk('public')->delete($award->verification_file);
        }

        $award->delete();

        return ApiResponse::success(null, 'Award deleted successfully.');
    }
}
