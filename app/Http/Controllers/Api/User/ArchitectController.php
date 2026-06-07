<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\ApiStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\UpdateArchitectProfileRequest;
use App\Http\Resources\User\ArchitectProfileResource;
use App\Http\Responses\ApiResponse;
use App\Models\ArchitectProfile;
use App\Models\ArchitectWishlist;
use App\Models\Award;
use App\Models\Consultation;
use App\Models\PersonalAccessToken;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class ArchitectController extends Controller
{
    /**
     * @OA\Get(
     *   path="/architects",
     *   tags={"Architects"},
     *   security={},
     *   summary="List architects",
     *   description="Returns a list of architects for public browsing.",
     *
     *   @OA\Response(response=200, description="Architect list retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Architect list retrieved successfully", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com", "headline": "Residential Specialist"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 12, "total": 1}, "links": {"first_page_url": "http://localhost:8000/api/v1/architects?page=1", "last_page_url": "http://localhost:8000/api/v1/architects?page=1", "next_page_url": null, "prev_page_url": null}})
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
            ->with(['architectProfile', 'projects', 'awards'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $this->attachPortfolioTotals($architects->getCollection());

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
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Wishlist architects retrieved successfully", "data": {{"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com", "headline": "Residential Specialist"}}, "meta": {"current_page": 1, "last_page": 1, "per_page": 12, "total": 1}, "links": {"first_page_url": "http://localhost:8000/api/v1/architects/wishlist?page=1", "last_page_url": "http://localhost:8000/api/v1/architects/wishlist?page=1", "next_page_url": null, "prev_page_url": null}})
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
            ->with(['architectProfile', 'projects', 'awards'])
            ->get()
            ->keyBy('id');

        $orderedArchitects = $architectIds
            ->map(fn (string $id) => $architectsById->get($id))
            ->filter()
            ->values();

        $this->attachPortfolioTotals($orderedArchitects);

        $wishlist->setCollection(
            ArchitectProfileResource::collection($orderedArchitects)->collection
        );

        return ApiResponse::paginated($wishlist, 'Daftar wishlist arsitek berhasil diambil.');
    }

    /**
     * @param  Collection<int, User>  $architects
     */
    private function attachPortfolioTotals(Collection $architects): void
    {
        if ($architects->isEmpty()) {
            return;
        }

        $architectIds = $architects
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        $projectCounts = Project::query()
            ->whereIn('architect_id', $architectIds)
            ->get(['architect_id'])
            ->groupBy('architect_id')
            ->map(static fn (Collection $items): int => $items->count());

        $awardCounts = Award::query()
            ->whereIn('architect_id', $architectIds)
            ->get(['architect_id'])
            ->groupBy('architect_id')
            ->map(static fn (Collection $items): int => $items->count());

        $architects->each(function (User $architect) use ($projectCounts, $awardCounts): void {
            $architect->setAttribute('total_projects', (int) ($projectCounts->get($architect->id) ?? 0));
            $architect->setAttribute('total_awards', (int) ($awardCounts->get($architect->id) ?? 0));
        });
    }

    /**
     * @OA\Get(
     *   path="/architects/{id}",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get architect details",
     *   description="Returns detailed information about a specific architect.",
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Architect details retrieved successfully",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Data arsitek berhasil diambil.", "data": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Architect User", "email": "architect@halositek.com", "headline": "Residential Specialist", "total_projects": 5, "total_awards": 2}})
     * ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $architect = User::query()
            ->where('id', $id)
            ->where('role', UserRole::Architect->value)
            ->with(['architectProfile', 'projects', 'awards'])
            ->first();

        if (! $architect) {
            return ApiResponse::notFound('Arsitek tidak ditemukan.');
        }

        $this->attachPortfolioTotals(collect([$architect]));

        $user = $this->userFromBearerToken($request);
        if ($user instanceof User) {
            $architect->setAttribute('is_wishlisted', ArchitectWishlist::query()
                ->where('user_id', (string) $user->id)
                ->where('architect_id', (string) $architect->id)
                ->exists());
        }

        return ApiResponse::success(
            (new ArchitectProfileResource($architect))->resolve($request),
            'Data arsitek berhasil diambil.'
        );
    }

    private function userFromBearerToken(Request $request): ?User
    {
        $plainTextToken = $request->bearerToken();

        if (! $plainTextToken) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($plainTextToken);

        if (! $accessToken) {
            return null;
        }

        $expiresAt = $accessToken->getAttribute('expires_at');
        if ($expiresAt instanceof CarbonInterface && $expiresAt->isPast()) {
            return null;
        }

        $tokenable = $accessToken->tokenable;

        return $tokenable instanceof User ? $tokenable : null;
    }

    /**
     * @OA\Post(
     *   path="/architects/{userId}/save",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Save architect to wishlist",
     *   description="Saves a specific architect to the authenticated user's wishlist.",
     *
     *   @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="string")),
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
    public function save(string $userId): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        $architect = User::query()
            ->where('id', $userId)
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
     *   path="/architects/{userId}/save",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Remove architect from wishlist",
     *   description="Removes a specific architect from the authenticated user's wishlist.",
     *
     *   @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="string")),
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
    public function unsave(string $userId): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        $wishlist = ArchitectWishlist::query()
            ->where('user_id', (string) $user->id)
            ->where('architect_id', $userId)
            ->first();

        if (! $wishlist) {
            return ApiResponse::notFound('Arsitek tidak ditemukan di wishlist.');
        }

        $wishlist->delete();

        return ApiResponse::success(message: 'Arsitek berhasil dihapus dari wishlist.');
    }

    public function verify(Request $request, string $userId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,declined'],
        ]);

        $architect = User::query()
            ->where('id', $userId)
            ->where('role', UserRole::Architect->value)
            ->first();

        if (! $architect instanceof User) {
            return ApiResponse::notFound('Arsitek tidak ditemukan.');
        }

        $profile = $architect->architectProfile;

        if (! $profile instanceof ArchitectProfile) {
            $profile = ArchitectProfile::create([
                'user_id' => (string) $architect->id,
                'status' => $validated['status'],
            ]);
        } else {
            $profile->status = $validated['status'];
            $profile->save();
        }

        $architect->setRelation('architectProfile', $profile);
        $architect->setAttribute('total_projects', 0);
        $architect->setAttribute('total_awards', 0);

        return ApiResponse::success(
            (new ArchitectProfileResource($architect))->resolve($request),
            'Status arsitek berhasil diperbarui.',
        );
    }

    /**
     * @OA\Get(
     *   path="/architects/earnings",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get architect earnings per release payment",
     *   description="Returns a paginated list of released payout payments (earnings) for the authenticated architect.",
     *
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Earnings retrieved successfully",
     *
     *     @OA\JsonContent(
     *       example={"success": true, "status_code": 200, "message": "Data earnings berhasil diambil.", "data": {"total_gross_earnings": 1000000, "total_tax_paid": 100000, "total_net_earnings": 900000, "earnings": {{"consultation_id": "01J3CONS001", "gross_fee": 500000, "tax_deduction": 50000, "net_earning": 450000, "released_at": "2026-04-27T12:00:00Z"}}}}
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError")
     * )
     */
    public function earnings(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null) {
            return ApiResponse::unauthorized();
        }

        if ($user->role !== UserRole::Architect) {
            return ApiResponse::forbidden('Hanya arsitek yang dapat mengakses data earnings.');
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 15)));

        $query = Consultation::query()
            ->with('user')
            ->where('architect_id', (string) $user->id)
            ->where('status', 'completed')
            ->where('payout_status', 'released')
            ->orderBy('payout_released_at', 'desc');

        $aggregateQuery = Consultation::query()
            ->where('architect_id', (string) $user->id)
            ->where('status', 'completed')
            ->where('payout_status', 'released');

        $totalGross = (int) $aggregateQuery->sum('session_fee');
        $totalTax = (int) $aggregateQuery->sum('payout_tax_amount');
        $totalNet = (int) $aggregateQuery->sum('payout_amount');

        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (Consultation $consultation): array {
            $releasedAt = $consultation->payout_released_at;
            $releasedAtIso = $releasedAt ? $releasedAt->toIso8601String() : null;

            return [
                'consultation_id' => (string) $consultation->getKey(),
                'user' => $consultation->user ? [
                    'id' => (string) $consultation->user->getKey(),
                    'name' => (string) $consultation->user->name,
                    'email' => (string) $consultation->user->email,
                ] : null,
                'date' => $consultation->consultation_date ? $consultation->consultation_date->toIso8601String() : null,
                'gross_fee' => (int) $consultation->session_fee,
                'tax_deduction' => (int) ($consultation->payout_tax_amount ?? 0),
                'net_earning' => (int) ($consultation->payout_amount ?? 0),
                'released_at' => $releasedAtIso,
            ];
        })->all();

        return ApiResponse::success([
            'total_gross_earnings' => $totalGross,
            'total_tax_paid' => $totalTax,
            'total_net_earnings' => $totalNet,
            'earnings' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ], 'Data earnings berhasil diambil.');
    }

    /**
     * @OA\Post(
     *   path="/architects/profile",
     *   tags={"Architects"},
     *   security={{"BearerAuth":{}}},
     *   summary="Update authenticated architect profile",
     *   description="Updates profile information for the authenticated architect.",
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
     *         @OA\Property(property="name", type="string", example="Arsitek A"),
     *         @OA\Property(property="email", type="string", format="email", example="arsitek.a@example.com"),
     *         @OA\Property(property="headline", type="string", nullable=true, example="Modern Tropical Specialist"),
     *         @OA\Property(property="bio", type="string", nullable=true, example="Berpengalaman dalam proyek residensial dan komersial."),
     *         @OA\Property(property="year_of_experience", type="integer", nullable=true, minimum=0, maximum=100, example=8),
     *         @OA\Property(property="consultation_fee", type="integer", nullable=true, minimum=0, example=250000),
     *         @OA\Property(property="consultation_hours", type="integer", nullable=true, minimum=1, maximum=24, example=2),
     *         @OA\Property(property="photo_profile", type="string", format="binary")
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Architect profile updated successfully",
     *
     *     @OA\JsonContent(
     *       example={"success": true, "status_code": 200, "message": "Profil arsitek berhasil diperbarui.", "data": {"id": "01HZX9M1F45M2Z6K7T9K7Y8QRA", "name": "Arsitek A", "email": "arsitek.a@example.com", "headline": "Modern Tropical Specialist", "bio": "Berpengalaman dalam proyek residensial dan komersial.", "year_of_experience": 8, "consultation_fee": 250000, "consultation_hours": 2}}
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError")
     * )
     */
    public function updateProfile(UpdateArchitectProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        if (! $user->isArchitect()) {
            return ApiResponse::forbidden('Hanya arsitek yang dapat memperbarui profil arsitek.');
        }

        $data = $request->validated();
        if ($data === [] && ! $request->hasFile('photo_profile')) {
            return ApiResponse::validationError([
                'request' => ['Tidak ada data yang dikirim untuk diperbarui.'],
            ], 'Validation failed.');
        }

        $userFields = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ], static fn (mixed $value): bool => $value !== null);

        if ($request->hasFile('photo_profile')) {
            if ($user->photo_profile) {
                Storage::disk('public')->delete($user->photo_profile);
            }
            $userFields['photo_profile'] = $request->file('photo_profile')->store('users/profiles', 'public');
        }

        if ($userFields !== []) {
            $user->fill($userFields);
            $user->save();
        }

        $profileFields = array_filter([
            'headline' => $data['headline'] ?? null,
            'bio' => $data['bio'] ?? null,
            'year_of_experience' => $data['year_of_experience'] ?? null,
            'consultation_fee' => $data['consultation_fee'] ?? null,
            'consultation_duration' => $data['consultation_hours'] ?? null,
        ], static fn (mixed $value): bool => $value !== null);

        if ($profileFields !== []) {
            $user->architectProfile()->updateOrCreate(
                ['user_id' => (string) $user->id],
                $profileFields
            );
        }

        $user->load('architectProfile');
        $profile = $user->architectProfile;

        return ApiResponse::success([
            'name' => $user->name,
            'email' => $user->email,
            'headline' => $profile?->headline,
            'bio' => $profile?->bio,
            'year_of_experience' => (int) ($profile->year_of_experience ?? 0),
            'consultation_fee' => (int) ($profile->consultation_fee ?? 0),
            'consultation_hours' => (int) ($profile->consultation_duration ?? 1),
            'photo_profile' => $user->photo_profile,
            'photo_profile_url' => $user->photo_profile_url,
        ], 'Profil arsitek berhasil diperbarui.');
    }
}
