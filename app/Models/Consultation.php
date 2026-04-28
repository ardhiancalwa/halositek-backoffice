<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property-read User|null $user
 * @property-read User|null $architect
 */
class Consultation extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    use HasUuids;

    protected $connection = 'mongodb';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'architect_id',
        'consultation_date',
        'duration_hours',
        'session_fee',
        'transcript',
        'status',
        'verification_status',
        'payout_status',
        'payout_released_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consultation_date' => 'datetime',
            'duration_hours' => 'integer',
            'session_fee' => 'integer',
            'payout_released_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'architect_id');
    }

    /**
     * @return HasMany<ConsultationReport, self>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ConsultationReport::class, 'consultation_id');
    }
}
