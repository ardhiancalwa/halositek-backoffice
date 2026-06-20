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
 * @property int|null $year_of_experience
 * @property int|null $consultation_fee
 * @property int|null $consultation_duration
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
        'year_of_experience',
        'consultation_fee',
        'consultation_duration',
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
            'year_of_experience' => 'integer',
            'consultation_fee' => 'integer',
            'consultation_duration' => 'integer',
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
