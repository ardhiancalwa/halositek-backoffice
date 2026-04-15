<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Award\StoreAwardRequest;
use App\Http\Requests\Api\V1\Award\UpdateAwardRequest;
use App\Http\Resources\AwardResource;
use App\Http\Responses\ApiResponse;
use App\Models\Award;
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
            $query->byStatus($request->string('status')->toString());
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 12)));
        $awards = $query->paginate($perPage);
        $awards->setCollection(AwardResource::collection($awards->getCollection())->collection);

        return ApiResponse::paginated($awards, 'Awards retrieved successfully.');
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

        $user = Auth::user();
        if ($user === null) {
            return ApiResponse::unauthorized('Unauthorized.');
        }

        if (Gate::forUser($user)->denies('update', $award)) {
            return ApiResponse::forbidden('You are not allowed to update this award.');
        }

        $role = is_object($user->role) ? $user->role->value : $user->role;
        $isAdmin = $role === 'admin';

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
