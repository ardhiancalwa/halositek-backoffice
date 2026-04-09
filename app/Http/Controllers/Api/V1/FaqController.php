<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Faq\StoreFaqRequest;
use App\Http\Requests\Api\V1\Faq\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Http\Responses\ApiResponse;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class FaqController extends Controller
{
    /**
     * @OA\Get(
     *   path="/faqs",
     *   tags={"FAQ"},
     *   security={},
     *
     *   @OA\Response(response=200, description="Berhasil memuat daftar FAQ")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(50, (int) $request->input('per_page', 15));

        $query = Faq::query()->orderBy('created_at', 'desc');

        if (! $request->user()?->isAdmin()) {
            $query->where('is_active', true);
        }

        $faqs = $query->paginate($perPage);

        $faqs->setCollection(
            FaqResource::collection($faqs->getCollection())->collection
        );

        return ApiResponse::paginated($faqs, 'Daftar FAQ berhasil diambil.');
    }

    /**
     * @OA\Get(
     *   path="/faqs/{id}",
     *   tags={"FAQ"},
     *   security={},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Berhasil memuat FAQ")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);

        if (! $faq->is_active && ! $request->user()?->isAdmin()) {
            return ApiResponse::notFound('FAQ tidak ditemukan.');
        }

        return ApiResponse::success((new FaqResource($faq))->resolve(), 'Detail FAQ berhasil diambil.');
    }

    /**
     * @OA\Post(
     *   path="/faqs",
     *   tags={"FAQ"},
     *   security={{"BearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"question","answer"},
     *
     *       @OA\Property(property="question", type="string"),
     *       @OA\Property(property="answer", type="string"),
     *       @OA\Property(property="is_active", type="boolean")
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="FAQ berhasil ditambahkan")
     * )
     */
    public function store(StoreFaqRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $faq = Faq::create($data);

        return ApiResponse::created((new FaqResource($faq))->resolve(), 'FAQ berhasil ditambahkan.');
    }

    /**
     * @OA\Put(
     *   path="/faqs/{id}",
     *   tags={"FAQ"},
     *   security={{"BearerAuth":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="question", type="string"),
     *       @OA\Property(property="answer", type="string"),
     *       @OA\Property(property="is_active", type="boolean")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="FAQ berhasil diperbarui")
     * )
     */
    public function update(UpdateFaqRequest $request, string $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);
        $faq->fill($request->validated());
        $faq->save();

        return ApiResponse::success((new FaqResource($faq))->resolve(), 'FAQ berhasil diperbarui.');
    }

    /**
     * @OA\Delete(
     *   path="/faqs/{id}",
     *   tags={"FAQ"},
     *   security={{"BearerAuth":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="FAQ berhasil dihapus")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return ApiResponse::success(message: 'FAQ berhasil dihapus.');
    }
}
