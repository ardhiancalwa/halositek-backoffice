<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property string|null $photo_profile
 * @property-read string $photo_profile_url
 * @property AccountStatus $account_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;

    public function getPhotoProfileUrlAttribute(): string
    {
        return $this->photo_profile ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'U') . '&background=ececec&color=333333&rounded=true&bold=true';
    }

    protected $connection = 'mongodb';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_status',
        'photo_profile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'account_status' => AccountStatus::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isArchitect(): bool
    {
        return $this->role === UserRole::Architect;
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::User;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    /**
     * @return HasOne<ArchitectProfile, self>
     */
    public function architectProfile(): HasOne
    {
        return $this->hasOne(ArchitectProfile::class);
    }

    /**
     * @return BelongsToMany<self, self>
     */
    public function wishlistArchitects(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'architect_wishlists', 'user_id', 'architect_id');
    }

    /**
     * @return HasMany<Project, self>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'architect_id');
    }

    /**
     * @return HasMany<Award, self>
     */
    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'architect_id');
    }

    /**
     * @return HasMany<Message, self>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    /**
     * @return HasMany<Consultation, self>
     */
    public function consultationsAsUser(): HasMany
    {
        return $this->hasMany(Consultation::class, 'user_id');
    }

    /**
     * @return HasMany<Consultation, self>
     */
    public function consultationsAsArchitect(): HasMany
    {
        return $this->hasMany(Consultation::class, 'architect_id');
    }

    /**
     * @return HasMany<ConsultationReport, self>
     */
    public function consultationReports(): HasMany
    {
        return $this->hasMany(ConsultationReport::class, 'requester_id');
    }

    /**
     * @return HasMany<AiChatbotLog, self>
     */
    public function aiChatbotLogs(): HasMany
    {
        return $this->hasMany(AiChatbotLog::class, 'user_id');
    }
}
