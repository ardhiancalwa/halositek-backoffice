<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class ArchitectWishlist extends Model
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
        'architect_id',
    ];

    public $timestamps = false;

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model): void {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'architect_id');
    }
}
