<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string $conversation_id
 * @property string $user_id
 * @property string|null $role
 * @property string|null $type
 * @property string|null $content
 * @property string|null $body
 * @property string|null $attachment
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Message extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    use HasUuids;

    protected $connection = 'mongodb';

    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    public const TYPE_TEXT = 'text';

    public const TYPE_IMAGE = 'image';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'conversation_id',
        'user_id',
        'role',
        'type',
        'content',
        'body',
        'attachment',
        'read_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $message): void {
            if (! array_key_exists('type', $message->attributes) || $message->attributes['type'] === null || $message->attributes['type'] === '') {
                $message->type = self::TYPE_TEXT;
            }

            if (
                (! array_key_exists('content', $message->attributes) || $message->attributes['content'] === null || $message->attributes['content'] === '')
                && is_string($message->attributes['body'] ?? null)
                && $message->attributes['body'] !== ''
            ) {
                $message->content = $message->attributes['body'];
            }
        });
    }

    /**
     * @return BelongsTo<Conversation, self>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setRoleAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['role'] = null;

            return;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException('Message role must be a string.');
        }

        $normalized = strtolower($value);
        if (! in_array($normalized, [self::ROLE_USER, self::ROLE_ASSISTANT], true)) {
            throw new InvalidArgumentException('Message role must be user or assistant.');
        }

        $this->attributes['role'] = $normalized;
    }

    public function setTypeAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['type'] = self::TYPE_TEXT;

            return;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException('Message type must be a string.');
        }

        $normalized = strtolower($value);
        if (! in_array($normalized, [self::TYPE_TEXT, self::TYPE_IMAGE], true)) {
            throw new InvalidArgumentException('Message type must be text or image.');
        }

        $this->attributes['type'] = $normalized;
    }
}
