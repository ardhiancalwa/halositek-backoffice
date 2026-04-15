<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;

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

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function architectProfile(): HasOne
    {
        return $this->hasOne(ArchitectProfile::class);
    }

    public function wishlistArchitects(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'architect_wishlists', 'user_id', 'architect_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'architect_id');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'architect_id');
    }
}
