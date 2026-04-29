<?php

namespace App\Actions\Faq;

use App\DTOs\Faq\CreateFaqDTO;
use App\Models\Faq;

final class CreateFaqAction
{
    public function execute(CreateFaqDTO $dto): Faq
    {
        return Faq::create([
            'question' => $dto->question,
            'answer' => $dto->answer,
            'is_active' => $dto->isActive,
        ]);
    }
}
