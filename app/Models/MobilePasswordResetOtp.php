<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $email
 * @property string $otp
 * @property Carbon|null $expires_at
 * @property Carbon|null $verified_at
 * @property Carbon|null $used_at
 */
class MobilePasswordResetOtp extends Model
{
    use HasUuids;

    protected $connection = 'mongodb';

    /** @var string */
    protected $collection = 'mobile_password_reset_otps';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'verified_at',
        'used_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at === null
            || Carbon::parse($this->expires_at)->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
