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
     *   summary="List FAQs",
     *   description="Returns FAQ items; public users can only see active entries.",
     *
     *   @OA\Response(response=200, description="FAQ list retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "FAQ list retrieved successfully", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QFQ", "question": "How do I become an architect on this platform?", "answer": "Register and complete your architect profile.", "is_active": true}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1}, "links": {"first_page_url": "http://localhost:8000/api/v1/faqs?page=1", "last_page_url": "http://localhost:8000/api/v1/faqs?page=1", "next_page_url": null, "prev_page_url": null}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
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
     *   summary="Get FAQ detail",
     *   description="Returns details for a single FAQ by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="FAQ retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "FAQ retrieved successfully", "data": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QFQ", "question": "How do I become an architect on this platform?", "answer": "Register and complete your architect profile.", "is_active": true}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
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
     *   summary="Create FAQ",
     *   description="Creates a new FAQ entry through admin access.",
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
     *   @OA\Response(response=201, description="FAQ created successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 201, "message": "FAQ created successfully", "data": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QFQ", "question": "How do I become an architect on this platform?", "answer": "Register and complete your architect profile.", "is_active": true}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
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
     *   summary="Update FAQ",
     *   description="Updates an existing FAQ entry by id.",
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
     *   @OA\Response(response=200, description="FAQ updated successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "FAQ updated successfully", "data": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QFQ", "question": "How do I become an architect on this platform?", "answer": "Your architect profile has been updated.", "is_active": true}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
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
     *   summary="Delete FAQ",
     *   description="Deletes an FAQ entry by id.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="FAQ deleted successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "FAQ deleted successfully", "data": null})
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
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return ApiResponse::success(message: 'FAQ berhasil dihapus.');
    }
}
