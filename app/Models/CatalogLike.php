<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class CatalogLike extends Model
{
    use HasFactory, HasUuids;

    protected $connection = 'mongodb';
        protected $fillable = [
        'id',
        'catalog_id',
        'user_id',
    ];

    public $timestamps = false;
    
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected static function boot()
    {
        parent::boot();
        // Since we disabled general timestamps, we must handle created_at manually if needed
        // but since MongoDB/Laravel supports just setting defined constants, 
        // it may still hook into creating events.
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
