<?php

namespace App\Actions\Award;

use App\DTOs\Award\UpdateAwardDTO;
use App\Enums\AwardStatus;
use App\Models\Award;

final class UpdateAwardAction
{
    public function execute(Award $award, UpdateAwardDTO $dto, bool $resetStatus = false): Award
    {
        $attributes = $this->normalizeAttributes($dto->attributes);

        $award->fill($attributes);

        if ($resetStatus) {
            $award->status = AwardStatus::Pending->value;
        }

        $award->save();

        return $award;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes): array
    {
        if (isset($attributes['status']) && $attributes['status'] instanceof \BackedEnum) {
            $attributes['status'] = $attributes['status']->value;
        }

        return $attributes;
    }
}
