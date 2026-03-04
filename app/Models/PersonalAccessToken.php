<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\DocumentModel;
use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class PersonalAccessToken extends SanctumToken
{
    use DocumentModel;

    protected $connection = 'mongodb';

    protected $table = 'personal_access_tokens';
}
