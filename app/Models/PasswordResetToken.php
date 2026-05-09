<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;
    use HasUuids;

    protected $connection = 'mongodb';

    protected $collection = 'password_reset_tokens';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'token',
    ];

    /**
     * Check if the token has expired (default 60 minutes).
     */
    public function isExpired(int $minutes = 60): bool
    {
        return $this->created_at === null
            || Carbon::parse($this->created_at)->addMinutes($minutes)->isPast();
    }
}
