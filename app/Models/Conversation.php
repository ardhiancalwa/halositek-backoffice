<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

class Conversation extends Model
{
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
            'participant_ids' => 'array',
            'last_read_at' => 'array',
        ];
    }

    /**
     * @return HasMany<Message, self>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
}
