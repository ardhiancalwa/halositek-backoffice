<?php

declare(strict_types=1);

namespace App\Support;

final class CatalogHelper
{
    /**
     * Normalize free-text keyword into URL-friendly slug.
     *
     * @param  string  $keyword  Raw keyword from user input.
     * @return string Normalized slug value.
     */
    public static function toSlug(string $keyword): string
    {
        $value = trim(strtolower($keyword));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    /**
     * Convert nullable string into safe trimmed value.
     *
     * @param  string|null  $value  Input value that may be null.
     * @return string|null Trimmed value or null when empty.
     */
    public static function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
