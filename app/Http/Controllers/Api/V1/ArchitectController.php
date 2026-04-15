<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApiStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Architect\VerifyArchitectRequest;
use App\Http\Resources\ArchitectProfileResource;
use App\Http\Responses\ApiResponse;
use App\Models\ArchitectProfile;
use App\Models\ArchitectWishlist;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class ArchitectController extends Controller
{
    /**
     * @OA\Get(
     *   path="/architects",
     *   tags={"Architects"},
     *   security={},
     *   summary="List architects",
     *   description="Returns a list of verified architects for public browsing.",
     *
     *   @OA\Response(response=200, description="Architect list retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Architect list retrieved successfully", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com", "headline": "Residential Specialist", "status": "approved"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 12, "total": 1}, "links": {"first_page_url": "http://localhost:8000/api/v1/architects?page=1", "last_page_url": "http://localhost:8000/api/v1/architects?page=1", "next_page_url": null, "prev_page_url": null}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(50, (int) $request->input('per_page', 12));

        $architects = User::query()
            ->where('role', UserRole::Architect->value)
            ->whereHas('architectProfile', function ($query): void {
                $query->where('status', 'approved');
            })
            ->with('architectProfile')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $architects->setCollection(
            ArchitectProfileResource::collection($architects->getCollection())->collection
        );

        return ApiResponse::paginated($architects, 'Daftar arsitek berhasil diambil.');
    }

    /**
     * @OA\Get(
     *   path="/architects/wishlist",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="List wishlist architects",
     *   description="Returns the authenticated user's saved architect wishlist.",
     *
     *   @OA\Response(response=200, description="Wishlist architects retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Wishlist architects retrieved successfully", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com", "headline": "Residential Specialist", "status": "approved"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 12, "total": 1}, "links": {"first_page_url": "http://localhost:8000/api/v1/architects/wishlist?page=1", "last_page_url": "http://localhost:8000/api/v1/architects/wishlist?page=1", "next_page_url": null, "prev_page_url": null}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function wishlist(Request $request): JsonResponse
    {
        $perPage = min(50, (int) $request->input('per_page', 12));

        $wishlist = ArchitectWishlist::query()
            ->where('user_id', (string) $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $architectIds = collect($wishlist->items())
            ->pluck('architect_id')
            ->values();

        $architectsById = User::query()
            ->where('role', UserRole::Architect->value)
            ->whereIn('id', $architectIds)
            ->with('architectProfile')
            ->get()
            ->keyBy('id');

        $orderedArchitects = $architectIds
            ->map(fn (string $id) => $architectsById->get($id))
            ->filter()
            ->values();

        $wishlist->setCollection(
            ArchitectProfileResource::collection($orderedArchitects)->collection
        );

        return ApiResponse::paginated($wishlist, 'Daftar wishlist arsitek berhasil diambil.');
    }

    /**
     * @OA\Put(
     *   path="/architects/{id}/verify",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Verify architect",
     *   description="Validates and updates an architect status to approved or rejected by admin.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"status"},
     *
     *       @OA\Property(property="status", type="string", enum={"approved","rejected"})
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Architect status updated successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Architect status updated successfully", "data": {"architect_id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "status": "approved"}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function verify(VerifyArchitectRequest $request, string $id): JsonResponse
    {
        $architect = User::query()
            ->where('id', $id)
            ->where('role', UserRole::Architect->value)
            ->first();

        if (! $architect) {
            return ApiResponse::notFound('Arsitek tidak ditemukan.');
        }

        $profile = ArchitectProfile::query()->firstOrCreate(
            ['user_id' => $architect->id],
            ['status' => 'pending']
        );

        $profile->status = $request->validated('status');
        $profile->save();

        return ApiResponse::success([
            'architect_id' => $architect->id,
            'status' => $profile->status,
        ], 'Status arsitek berhasil diperbarui.');
    }

    /**
     * @OA\Post(
     *   path="/architects/{id}/save",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Save architect to wishlist",
     *   description="Saves a specific architect to the authenticated user's wishlist.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Architect saved to wishlist successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Architect saved to wishlist successfully", "data": null})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=409, ref="#/components/responses/ConflictError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function save(string $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        $architect = User::query()
            ->where('id', $id)
            ->where('role', UserRole::Architect->value)
            ->first();

        if (! $architect) {
            return ApiResponse::notFound('Arsitek tidak ditemukan.');
        }

        $exists = ArchitectWishlist::query()
            ->where('user_id', (string) $user->id)
            ->where('architect_id', $architect->id)
            ->exists();

        if ($exists) {
            return ApiResponse::error('Arsitek sudah tersimpan di wishlist.', ApiStatus::CONFLICT);
        }

        ArchitectWishlist::create([
            'user_id' => (string) $user->id,
            'architect_id' => $architect->id,
        ]);

        return ApiResponse::success(message: 'Arsitek berhasil disimpan ke wishlist.');
    }

    /**
     * @OA\Delete(
     *   path="/architects/{id}/save",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Remove architect from wishlist",
     *   description="Removes a specific architect from the authenticated user's wishlist.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Architect removed from wishlist successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Architect removed from wishlist successfully", "data": null})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function unsave(string $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        $wishlist = ArchitectWishlist::query()
            ->where('user_id', (string) $user->id)
            ->where('architect_id', $id)
            ->first();

        if (! $wishlist) {
            return ApiResponse::notFound('Arsitek tidak ditemukan di wishlist.');
        }

        $wishlist->delete();

        return ApiResponse::success(message: 'Arsitek berhasil dihapus dari wishlist.');
    }
}
