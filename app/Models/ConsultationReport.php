<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string $consultation_id
 * @property string $requester_id
 * @property string $opposing_party_id
 * @property string $requester_role
 * @property string $reason
 * @property string $action_status
 * @property string|null $actioned_by
 * @property Carbon|null $actioned_at
 * @property-read Consultation|null $consultation
 * @property-read User|null $requester
 * @property-read User|null $opposingParty
 */
class ConsultationReport extends Model
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
        'consultation_id',
        'requester_id',
        'opposing_party_id',
        'requester_role',
        'reason',
        'action_status',
        'actioned_by',
        'actioned_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actioned_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Consultation, self>
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function opposingParty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opposing_party_id');
    }
}
