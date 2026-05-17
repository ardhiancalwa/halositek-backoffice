<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string $user_id
 * @property string $architect_id
 * @property Carbon|null $consultation_date
 * @property int $duration_hours
 * @property int $session_fee
 * @property int|null $payout_tax_amount
 * @property int|null $payout_amount
 * @property string|null $payment_id
 * @property string|null $conversation_id
 * @property string|null $transcript
 * @property string $status
 * @property string $verification_status
 * @property string $payout_status
 * @property Carbon|null $payout_released_at
 * @property-read User|null $user
 * @property-read User|null $architect
 * @property-read Payment|null $payment
 * @property-read Conversation|null $conversation
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
        'payout_tax_amount',
        'payout_amount',
        'payment_id',
        'conversation_id',
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
            'payout_tax_amount' => 'integer',
            'payout_amount' => 'integer',
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
     * @return BelongsTo<Payment, self>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * @return BelongsTo<Conversation, self>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * @return HasMany<ConsultationReport, self>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ConsultationReport::class, 'consultation_id');
    }

    public function expiresAt(): ?Carbon
    {
        if (! $this->consultation_date instanceof Carbon) {
            return null;
        }

        return $this->consultation_date->copy()->addHours(max(0, (int) $this->duration_hours));
    }

    public function remainingSeconds(?Carbon $now = null): int
    {
        $expiresAt = $this->expiresAt();
        if (! $expiresAt instanceof Carbon) {
            return 0;
        }

        $referenceTime = $now ?? now();

        return max(0, $referenceTime->diffInSeconds($expiresAt, false));
    }

    /**
     * @return array{days: int, hours: int, minutes: int, seconds: int}
     */
    public function remainingDuration(?Carbon $now = null): array
    {
        $remaining = $this->remainingSeconds($now);
        $days = intdiv($remaining, 86400);
        $hours = intdiv($remaining % 86400, 3600);
        $minutes = intdiv($remaining % 3600, 60);
        $seconds = $remaining % 60;

        return [
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
        ];
    }

    public function isSessionActive(?Carbon $now = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $active = $this->remainingSeconds($now) > 0;

        if (! $active) {
            $this->status = 'completed';
            $this->save();
        }

        return $active;
    }
}
