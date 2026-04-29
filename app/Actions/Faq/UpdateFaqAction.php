<?php

namespace App\Actions\Faq;

use App\DTOs\Faq\UpdateFaqDTO;
use App\Models\Faq;

final class UpdateFaqAction
{
    public function execute(Faq $faq, UpdateFaqDTO $dto): Faq
    {
        $faq->fill($dto->attributes);
        $faq->save();

        return $faq;
    }
}
