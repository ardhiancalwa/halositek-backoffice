<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string|null $name
 * @property bool $is_group
 * @property list<string> $participant_ids
 * @property array<string, string> $last_read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Message|null $lastMessage
 */
class Conversation extends Model
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
        'name',
        'is_group',
        'participant_ids',
        'last_read_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_group' => 'boolean',
        'participant_ids' => 'array',
        'last_read_at' => 'array',
    ];

    /**
     * @return HasMany<Message, self>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
}
