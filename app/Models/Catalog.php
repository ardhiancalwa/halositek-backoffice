<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

class Catalog extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $connection = 'mongodb';


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'architect_id',
        'title',
        'style',
        'description',
        'images',
        'interior_highlights',
        'layout_image',
        'rooms',
        'estimated_cost',
        'area',
        'status',
        'rating',
        'likes_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'interior_highlights' => 'array',
            'estimated_cost' => 'integer',
            'rating' => 'float',
            'likes_count' => 'integer',
        ];
    }

    /**
     * Get the architect that owns the catalog.
     */
    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'architect_id');
    }

    /**
     * Get the likes for the catalog.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(CatalogLike::class);
    }

    /**
     * Scope a query to only include approved catalogs.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to filter by architectural style.
     */
    public function scopeByStyle($query, string $style)
    {
        // Case-insensitive filtering using MongoDB regex or simple where (if exact is fine)
        // Since we are validating to exact case, a simple where is performant.
        return $query->where('style', 'like', $style);
    }

    /**
     * Scope a query to search by title or description.
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%");
        });
    }

    /**
     * Determine if the catalog is liked by the currently authenticated user.
     */
    public function getIsLikedByUserAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->likes()->where('user_id', auth()->id())->exists();
    }
}
