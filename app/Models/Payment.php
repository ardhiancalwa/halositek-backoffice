<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string $order_id
 * @property string $user_id
 * @property string $architect_id
 * @property string|null $consultation_id
 * @property string|null $conversation_id
 * @property string|null $transaction_id
 * @property string|null $snap_token
 * @property string|null $snap_redirect_url
 * @property int $amount
 * @property int $user_tax_amount
 * @property int $total_paid_amount
 * @property int $duration_hours
 * @property string $status
 * @property string|null $refund_status
 * @property string|null $payment_method
 * @property array<string, mixed>|null $midtrans_response
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read User|null $architect
 * @property-read Consultation|null $consultation
 * @property-read iterable<PaymentHistory> $histories
 */
class Payment extends Model
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
        'order_id',
        'user_id',
        'architect_id',
        'consultation_id',
        'conversation_id',
        'transaction_id',
        'snap_token',
        'snap_redirect_url',
        'amount',
        'user_tax_amount',
        'total_paid_amount',
        'duration_hours',
        'status',
        'refund_status',
        'payment_method',
        'midtrans_response',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'user_tax_amount' => 'integer',
            'total_paid_amount' => 'integer',
            'duration_hours' => 'integer',
            'midtrans_response' => 'array',
            'paid_at' => 'datetime',
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
     * @return HasOne<Consultation, self>
     */
    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class, 'payment_id');
    }

    /**
     * @return HasMany<PaymentHistory, self>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class, 'payment_id');
    }
}
