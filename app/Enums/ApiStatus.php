<?php

namespace App\Enums;

enum ApiStatus: int
{
    case SUCCESS = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NO_CONTENT = 204;
    case CSRF_TOKEN_MISMATCH = 419;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNPROCESSABLE_ENTITY = 422;
    case SERVER_ERROR = 500;
    case SERVICE_UNAVAILABLE = 503;

    /**
     * Get the descriptive message for the status code.
     * Optionally override with a custom message.
     */
    public function message(?string $custom = null): string
    {
        if ($custom !== null && $custom !== '') {
            return $custom;
        }

        return match ($this) {
            self::SUCCESS => 'Operation successful.',
            self::CREATED => 'Resource created successfully.',
            self::ACCEPTED => 'Request accepted for processing.',
            self::NO_CONTENT => 'No content.',
            self::CSRF_TOKEN_MISMATCH => 'CSRF token mismatch.',
            self::BAD_REQUEST => 'Bad request.',
            self::UNAUTHORIZED => 'Unauthenticated.',
            self::FORBIDDEN => 'Forbidden access.',
            self::NOT_FOUND => 'Resource not found.',
            self::METHOD_NOT_ALLOWED => 'Method not allowed.',
            self::CONFLICT => 'Resource conflict.',
            self::UNPROCESSABLE_ENTITY => 'Validation failed.',
            self::SERVER_ERROR => 'Internal server error.',
            self::SERVICE_UNAVAILABLE => 'Service unavailable.',
        };
    }
}
