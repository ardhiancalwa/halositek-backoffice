<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="HaloSitek Core API",
 *   version="1.0.0",
 *   description="OpenAPI metadata for HaloSitek Backoffice API.")
 *
 * @OA\Server(url="http://localhost:8000/api/v1", description="Local Development Server")
 *
 * @OA\SecurityScheme(
 *   securityScheme="BearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 *   description="Use Sanctum access token in Authorization header as: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *   schema="ApiResponse",
 *   type="object",
 *
 *   @OA\Property(property="success", type="boolean", example=true),
 *   @OA\Property(property="status_code", type="integer", example=200),
 *   @OA\Property(property="message", type="string", example="Operation successful"),
 *   @OA\Property(property="data", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="PaginationMeta",
 *   type="object",
 *
 *   @OA\Property(property="current_page", type="integer", example=1),
 *   @OA\Property(property="last_page", type="integer", example=10),
 *   @OA\Property(property="per_page", type="integer", example=15),
 *   @OA\Property(property="total", type="integer", example=150)
 * )
 *
 * @OA\Schema(
 *   schema="PaginationLinks",
 *   type="object",
 *
 *   @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/v1/users?page=1"),
 *   @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/v1/users?page=10"),
 *   @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost:8000/api/v1/users?page=2"),
 *   @OA\Property(property="prev_page_url", type="string", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedResponse",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *     @OA\Schema(
 *       type="object",
 *
 *       @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 *       @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 *     )
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="AuthTokenPair",
 *   type="object",
 *
 *   @OA\Property(property="access_token", type="string", example="1|plain-access-token"),
 *   @OA\Property(property="refresh_token", type="string", example="2|plain-refresh-token"),
 *   @OA\Property(property="token_type", type="string", example="Bearer"),
 *   @OA\Property(property="expires_in", type="integer", example=3600)
 * )
 *
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *
 *   @OA\Property(property="id", type="string", example="01HZX9M1F45M2Z6K7T9K7Y8QRS"),
 *   @OA\Property(property="name", type="string", example="Budi Santoso"),
 *   @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
 *   @OA\Property(property="role", type="string", enum={"user","architect","admin"}, example="user"),
 *   @OA\Property(property="account_status", type="string", enum={"active","suspend"}, example="active"),
 *   @OA\Property(property="member_since", type="string", format="date-time"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="ArchitectProfile",
 *   type="object",
 *
 *   @OA\Property(property="id", type="string", example="01HZX9M1F45M2Z6K7T9K7Y8QRA"),
 *   @OA\Property(property="name", type="string", example="Architect User"),
 *   @OA\Property(property="email", type="string", format="email", example="architect@halositek.com"),
 *   @OA\Property(property="profile_picture", type="string", nullable=true, example=null),
 *   @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example=null),
 *   @OA\Property(property="role", type="string", enum={"architect"}, example="architect"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-13T10:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2026-04-13T10:00:00Z"),
 *   @OA\Property(property="headline", type="string", nullable=true, example="Residential Specialist"),
 *   @OA\Property(property="bio", type="string", nullable=true, example="Experienced architect specializing in tropical modern homes."),
 *   @OA\Property(property="location", type="string", nullable=true, example="Jakarta"),
 *   @OA\Property(property="total_projects", type="integer", example=8),
 *   @OA\Property(property="total_awards", type="integer", example=4),
 *   @OA\Property(property="status", type="string", enum={"pending","approved","rejected"}, example="approved"),
 *   @OA\Property(property="specialization", type="string", nullable=true, example="Residential Design"),
 *   @OA\Property(property="rating", type="number", format="float", example=4.8)
 * )
 *
 * @OA\Schema(
 *   schema="Project",
 *   type="object",
 *
 *   @OA\Property(property="id", type="string"),
 *   @OA\Property(property="architect_id", type="string"),
 *   @OA\Property(property="name", type="string", example="Rumah Tropis 2 Lantai"),
 *   @OA\Property(property="style", type="string", example="Modern"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="images", type="array", @OA\Items(type="string")),
 *   @OA\Property(property="image_urls", type="array", @OA\Items(type="string", format="uri")),
 *   @OA\Property(property="estimated_cost", type="string", example="Rp 2M - 3M"),
 *   @OA\Property(property="layout_images", type="array", @OA\Items(type="string")),
 *   @OA\Property(property="layout_image_urls", type="array", @OA\Items(type="string", format="uri")),
 *   @OA\Property(property="highlight_features", type="string", nullable=true),
 *   @OA\Property(property="area", type="string", nullable=true, example="120 m2"),
 *   @OA\Property(property="likes_count", type="integer", example=12),
 *   @OA\Property(property="status", type="string", enum={"pending","approved","declined"}, example="pending"),
 *   @OA\Property(property="architect", type="object", nullable=true,
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email")
 *   ),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="Award",
 *   type="object",
 *
 *   @OA\Property(property="id", type="string"),
 *   @OA\Property(property="architect_id", type="string"),
 *   @OA\Property(property="name", type="string", example="Best Residential Design"),
 *   @OA\Property(property="project_name", type="string", example="Villa Aster"),
 *   @OA\Property(property="role", type="string", example="Lead Architect"),
 *   @OA\Property(property="award_date", type="string", format="date", example="2026-04-01"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="verification_file", type="string", nullable=true),
 *   @OA\Property(property="verification_file_url", type="string", format="uri", nullable=true),
 *   @OA\Property(property="status", type="string", enum={"pending","approved","declined"}, example="pending"),
 *   @OA\Property(property="architect", type="object", nullable=true,
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email")
 *   ),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="Faq",
 *   type="object",
 *
 *   @OA\Property(property="id", type="string"),
 *   @OA\Property(property="question", type="string", example="Bagaimana cara mendaftar arsitek?"),
 *   @OA\Property(property="answer", type="string", example="Daftar akun lalu lengkapi profil arsitek."),
 *   @OA\Property(property="is_active", type="boolean", example=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="ChatConversation",
 *   type="object",
 *
 *   @OA\Property(property="id", type="string", example="01J2CHATCONVERSATION001"),
 *   @OA\Property(property="name", type="string", nullable=true, example=null),
 *   @OA\Property(property="is_group", type="boolean", example=false),
 *   @OA\Property(property="participant_ids", type="array", @OA\Items(type="string"), example={"01J2USERA", "01J2USERB"}),
 *   @OA\Property(property="last_read_at", type="string", format="date-time", nullable=true, example="2026-04-19T10:00:00+00:00"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="ChatMessage",
 *   type="object",
 *
 *   @OA\Property(property="id", type="string", example="01J2MESSAGE001"),
 *   @OA\Property(property="conversation_id", type="string", example="01J2CHATCONVERSATION001"),
 *   @OA\Property(property="user_id", type="string", example="01J2USERA"),
 *   @OA\Property(property="body", type="string", example="Halo, kabar kamu gimana?"),
 *   @OA\Property(property="attachment", type="string", nullable=true, example=null),
 *   @OA\Property(property="read_at", type="string", format="date-time", nullable=true, example=null),
 *   @OA\Property(property="is_mine", type="boolean", example=true),
 *   @OA\Property(property="sender", type="object", nullable=true,
 *     @OA\Property(property="id", type="string", example="01J2USERA"),
 *     @OA\Property(property="name", type="string", example="Budi"),
 *     @OA\Property(property="email", type="string", format="email", example="budi@example.com")
 *   ),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="ChatListItem",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ChatConversation"),
 *     @OA\Schema(
 *       type="object",
 *
 *       @OA\Property(property="unread_count", type="integer", example=0),
 *       @OA\Property(property="last_message", ref="#/components/schemas/ChatMessage", nullable=true)
 *     )
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="ApiError",
 *   type="object",
 *
 *   @OA\Property(property="success", type="boolean", example=false),
 *   @OA\Property(property="status_code", type="integer", example=401),
 *   @OA\Property(property="message", type="string", example="Unauthorized"),
 *   @OA\Property(property="errors", type="object", nullable=true)
 * )
 *
 * @OA\Response(
 *   response="UnauthorizedError",
 *   description="Unauthorized - missing or invalid access token.",
 *
 *   @OA\JsonContent(
 *     allOf={@OA\Schema(ref="#/components/schemas/ApiError")},
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="status_code", type="integer", example=401),
 *     @OA\Property(property="message", type="string", example="Unauthorized. Access token is missing or invalid."),
 *     @OA\Property(property="errors", type="object", nullable=true, example=null)
 *   )
 * )
 *
 * @OA\Response(
 *   response="ForbiddenError",
 *   description="Forbidden - user does not have permission for this action.",
 *
 *   @OA\JsonContent(
 *     allOf={@OA\Schema(ref="#/components/schemas/ApiError")},
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="status_code", type="integer", example=403),
 *     @OA\Property(property="message", type="string", example="Forbidden. You are not allowed to perform this action."),
 *     @OA\Property(property="errors", type="object", nullable=true, example=null)
 *   )
 * )
 *
 * @OA\Response(
 *   response="NotFoundError",
 *   description="Not Found - requested resource was not found.",
 *
 *   @OA\JsonContent(
 *     allOf={@OA\Schema(ref="#/components/schemas/ApiError")},
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="status_code", type="integer", example=404),
 *     @OA\Property(property="message", type="string", example="Resource not found."),
 *     @OA\Property(property="errors", type="object", nullable=true, example=null)
 *   )
 * )
 *
 * @OA\Response(
 *   response="ConflictError",
 *   description="Conflict - resource already exists or state conflict occurred.",
 *
 *   @OA\JsonContent(
 *     allOf={@OA\Schema(ref="#/components/schemas/ApiError")},
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="status_code", type="integer", example=409),
 *     @OA\Property(property="message", type="string", example="Conflict. Resource state already exists."),
 *     @OA\Property(property="errors", type="object", nullable=true, example=null)
 *   )
 * )
 *
 * @OA\Response(
 *   response="ValidationError",
 *   description="Validation Error - request input is invalid.",
 *
 *   @OA\JsonContent(
 *     allOf={@OA\Schema(ref="#/components/schemas/ApiError")},
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="status_code", type="integer", example=422),
 *     @OA\Property(property="message", type="string", example="Validation error."),
 *     @OA\Property(
 *       property="errors",
 *       type="object",
 *       example={"email": {"The email field is required."}, "password": {"The password must be at least 8 characters."}}
 *     )
 *   )
 * )
 *
 * @OA\Response(
 *   response="ServerError",
 *   description="Internal Server Error - an unexpected server error occurred.",
 *
 *   @OA\JsonContent(
 *     allOf={@OA\Schema(ref="#/components/schemas/ApiError")},
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="status_code", type="integer", example=500),
 *     @OA\Property(property="message", type="string", example="Internal server error."),
 *     @OA\Property(property="errors", type="object", nullable=true, example=null)
 *   )
 * )
 */
class StaticDocs
{
    // This class intentionally left blank. It only holds OpenAPI docblocks for swagger-php.
}
