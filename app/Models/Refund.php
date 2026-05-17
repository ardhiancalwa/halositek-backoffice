<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string $payment_id
 * @property string $order_id
 * @property string $report_id
 * @property int $amount
 * @property string $reason
 * @property string $status
 * @property string|null $midtrans_refund_key
 * @property array<string, mixed>|null $midtrans_response
 * @property string|null $error_message
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment $payment
 * @property-read ConsultationReport $report
 */
class Refund extends Model
{
    use HasFactory;
    use HasUuids;

    protected $connection = 'mongodb';

    protected $fillable = [
        'payment_id',
        'order_id',
        'report_id',
        'amount',
        'reason',
        'status',
        'midtrans_refund_key',
        'midtrans_response',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'midtrans_response' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Payment, self>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * @return BelongsTo<ConsultationReport, self>
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ConsultationReport::class, 'report_id');
    }
}
