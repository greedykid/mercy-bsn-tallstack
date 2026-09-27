<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PIC = 'pic';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator Akreditasi',
            self::PIC => 'PIC Unit Teknis / Lab',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isPic(): bool
    {
        return $this === self::PIC;
    }
}
