<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApiStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Catalog\StoreCatalogRequest;
use App\Http\Requests\Api\V1\Catalog\UpdateCatalogRequest;
use App\Http\Requests\Api\V1\Catalog\VerifyCatalogRequest;
use App\Http\Resources\CatalogCollection;
use App\Http\Resources\CatalogResource;
use App\Http\Responses\ApiResponse;
use App\Models\Catalog;
use App\Models\CatalogLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class CatalogController extends Controller
{
    /**
     * Get paginated list of catalogs.
     *
     * @OA\Get(
     *   path="/catalogs",
     *   tags={"Catalog"},
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="style", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Daftar katalog berhasil diambil")
     * )
     */
    public function index(Request $request)
    {
        $query = Catalog::with('architect')->approved();

        if ($request->has('style')) {
            $query->byStyle($request->input('style'));
        }

        if ($request->has('search')) {
            $query->search($request->input('search'));
        }

        $perPage = min(50, (int) $request->input('per_page', 12));

        $catalogs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return new CatalogCollection($catalogs, 'Daftar katalog berhasil diambil.');
    }

    /**
     * Get catalog detail by id.
     *
     * @OA\Get(
     *   path="/catalogs/{id}",
     *   tags={"Catalog"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Detail katalog berhasil diambil"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $id)
    {
        $catalog = Catalog::with('architect')->findOrFail($id);

        if ($catalog->status !== 'approved') {
            if (!auth()->check()) {
                abort(404, 'Data tidak ditemukan.');
            }
            if ($catalog->architect_id !== auth()->id() && !auth()->user()->isAdmin()) {
                abort(404, 'Data tidak ditemukan.');
            }
        }

        return (new CatalogResource($catalog))->additional([
            'success' => true,
            'status_code' => 200,
            'message' => 'Detail katalog berhasil diambil.',
        ]);
    }

    /**
     * Store a new catalog.
     *
     * @OA\Post(
     *   path="/catalogs",
     *   tags={"Catalog"},
     *   security={{"BearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object"
     *     )
     *   ),
     *   @OA\Response(response=201, description="Katalog berhasil dikirim")
     * )
     */
    public function store(StoreCatalogRequest $request)
    {
        $data = $request->validated();
        $data['architect_id'] = auth()->id();
        $data['status'] = 'pending';

        $catalog = Catalog::create($data);
        $catalog->load('architect');

        return (new CatalogResource($catalog))->additional([
            'success' => true,
            'status_code' => 201,
            'message' => 'Katalog berhasil dikirim dan menunggu persetujuan admin.',
        ])->response()->setStatusCode(201);
    }

    /**
     * Update a catalog.
     *
     * @OA\Put(
     *   path="/catalogs/{id}",
     *   tags={"Catalog"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\RequestBody(@OA\JsonContent(type="object")),
     *   @OA\Response(response=200, description="Katalog berhasil diperbarui")
     * )
     */
    public function update(UpdateCatalogRequest $request, string $id)
    {
        $catalog = Catalog::findOrFail($id);

        // Authorization handled in UpdateCatalogRequest, but just in case:
        if ($catalog->architect_id !== auth()->id()) {
            abort(403, 'Kamu tidak memiliki akses untuk melakukan tindakan ini.');
        }

        $catalog->fill($request->validated());

        if ($catalog->status === 'approved' && $catalog->isDirty()) {
            $catalog->status = 'pending';
        }

        $catalog->save();

        return (new CatalogResource($catalog->load('architect')))->additional([
            'success' => true,
            'status_code' => 200,
            'message' => 'Katalog berhasil diperbarui' . ($catalog->status === 'pending' ? ' dan menunggu persetujuan ulang admin.' : '.'),
        ]);
    }

    /**
     * Delete a catalog.
     *
     * @OA\Delete(
     *   path="/catalogs/{id}",
     *   tags={"Catalog"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Katalog berhasil dihapus")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $catalog = Catalog::findOrFail($id);

        $user = auth()->user();
        if ($catalog->architect_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Kamu tidak memiliki akses untuk melakukan tindakan ini.');
        }

        $catalog->delete();

        return ApiResponse::success(null, 'Katalog berhasil dihapus.');
    }

    /**
     * Verify (approve/reject) a catalog.
     *
     * @OA\Put(
     *   path="/catalogs/{id}/verify",
     *   tags={"Catalog"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\RequestBody(@OA\JsonContent(@OA\Property(property="status", type="string", example="approved"))),
     *   @OA\Response(response=200, description="Status katalog berhasil diperbarui")
     * )
     */
    public function verify(VerifyCatalogRequest $request, string $id): JsonResponse
    {
        $catalog = Catalog::findOrFail($id);

        $catalog->status = $request->validated('status');
        $catalog->save();

        return ApiResponse::success(null, "Status katalog berhasil diperbarui menjadi {$catalog->status}.");
    }

    /**
     * Like a catalog.
     *
     * @OA\Post(
     *   path="/catalogs/{id}/like",
     *   tags={"Catalog"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Katalog berhasil disukai")
     * )
     */
    public function like(string $id): JsonResponse
    {
        $catalog = Catalog::findOrFail($id);
        $userId = auth()->id();

        if (CatalogLike::where('catalog_id', $catalog->id)->where('user_id', $userId)->exists()) {
            return ApiResponse::error('Kamu sudah menyukai katalog ini.', ApiStatus::CONFLICT);
        }

        DB::transaction(function () use ($catalog, $userId) {
            CatalogLike::create([
                'catalog_id' => $catalog->id,
                'user_id' => $userId,
            ]);
            $catalog->increment('likes_count');
        });

        // Use fresh() because MongoDB increment doesn't always reflect in memory model immediately depending on driver versions
        $likesCount = $catalog->fresh()->likes_count;

        return ApiResponse::success(['likes_count' => $likesCount], 'Katalog berhasil disukai.');
    }

    /**
     * Unlike a catalog.
     *
     * @OA\Delete(
     *   path="/catalogs/{id}/like",
     *   tags={"Catalog"},
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Like berhasil dihapus")
     * )
     */
    public function unlike(string $id): JsonResponse
    {
        $catalog = Catalog::findOrFail($id);
        $userId = auth()->id();

        $like = CatalogLike::where('catalog_id', $catalog->id)->where('user_id', $userId)->first();

        if (!$like) {
            return ApiResponse::error('Kamu belum menyukai katalog ini.', ApiStatus::NOT_FOUND);
        }

        DB::transaction(function () use ($catalog, $like) {
            $like->delete();
            $catalog->decrement('likes_count');
        });

        $likesCount = $catalog->fresh()->likes_count;

        return ApiResponse::success(['likes_count' => $likesCount], 'Like berhasil dihapus.');
    }


}
