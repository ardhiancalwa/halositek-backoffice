<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

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
        'headline',
        'bio',
        'location',
        'catalogs_file_url',
        'awards_file_url',
        'status',
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
