<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $status
 * @property string|null $headline
 * @property string|null $bio
 * @property string|null $location
 * @property string|null $specialization
 * @property float|null $rating
 */
class ArchitectProfile extends Model
{
    use HasFactory;
    use HasUuids;

    protected $connection = 'mongodb';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'status',
        'headline',
        'bio',
        'location',
        'specialization',
        'rating',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
            'rating' => 'float',
        ];
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Project, self>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'architect_id', 'user_id');
    }

    /**
     * @return HasMany<Award, self>
     */
    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'architect_id', 'user_id');
    }
}
