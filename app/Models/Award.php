<?php

namespace App\Models;

use App\Enums\AwardStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class Award extends Model
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
        'architect_id',
        'name',
        'project_name',
        'role',
        'award_date',
        'description',
        'verification_file',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'award_date' => 'date',
            'status' => AwardStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'architect_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
