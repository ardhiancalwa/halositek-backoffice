<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="HaloSitek Core API (Annotations shim)",
 *   version="1.0.0",
 *   description="Minimal shim to satisfy swagger-php PathItem requirement. Full docs are provided via `halositek-api-docs/openapi.yaml`.")
 *
 * @OA\Server(url="http://localhost:8000/api/v1", description="Local Development Server")
 *
 * @OA\Get(
 *   path="/ping",
 *   summary="Ping endpoint used only for documentation generation",
 *
 *   @OA\Response(response=200, description="pong")
 * )
 */
class StaticDocs
{
    // This class intentionally left blank. It only holds OpenAPI docblocks for swagger-php.
}
