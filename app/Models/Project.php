<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\ProjectStyle;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property array<int, string>|null $images
 * @property array<int, string>|null $layout_images
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $connection = 'mongodb';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'architect_id',
        'name',
        'style',
        'description',
        'images',
        'layout_images',
        'highlight_features',
        'estimated_cost',
        'area',
        'status',
        'likes_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'layout_images' => 'array',
            'status' => ProjectStatus::class,
            'style' => ProjectStyle::class,
            'likes_count' => 'integer',
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
     * @return HasMany<ProjectLike, self>
     */
    public function likes()
    {
        return $this->hasMany(ProjectLike::class);
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
