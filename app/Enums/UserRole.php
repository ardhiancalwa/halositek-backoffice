<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Architect = 'architect';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Architect => 'Architect',
            self::Admin => 'Admin',
        };
    }
}
