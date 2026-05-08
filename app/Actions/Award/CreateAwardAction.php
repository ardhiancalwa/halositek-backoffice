<?php

namespace App\Actions\Award;

use App\DTOs\Award\CreateAwardDTO;
use App\Models\Award;

final class CreateAwardAction
{
    public function execute(CreateAwardDTO $dto): Award
    {
        return Award::create([
            'architect_id' => $dto->architectId,
            'name' => $dto->name,
            'project_name' => $dto->projectName,
            'role' => $dto->role,
            'award_date' => $dto->awardDate,
            'description' => $dto->description,
            'verification_file' => $dto->verificationFile,
            'status' => $dto->status->value,
        ]);
    }
}
