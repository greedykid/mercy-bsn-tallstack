<?php

declare(strict_types=1);

namespace App\Enums;

enum LpkStatus: string
{
    case ACTIVE = 'ACTIVE';
    case SURVEILLANCE_DUE = 'SURVEILLANCE_DUE';
    case SURVEILLANCE_OVERDUE = 'SURVEILLANCE_OVERDUE';
    case SUSPENDED = 'SUSPENDED';
    case REVOKED = 'REVOKED';
    case EXPIRED = 'EXPIRED';
    case INACTIVE = 'INACTIVE';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::SURVEILLANCE_DUE => 'Jatuh Tempo Pengawasan',
            self::SURVEILLANCE_OVERDUE => 'Surveilen Terlewat',
            self::SUSPENDED => 'Dibekukan',
            self::REVOKED => 'Dicabut',
            self::EXPIRED => 'Kedaluwarsa',
            self::INACTIVE => 'Tidak Aktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'emerald',
            self::SURVEILLANCE_DUE => 'amber',
            self::SURVEILLANCE_OVERDUE, self::EXPIRED => 'red',
            self::SUSPENDED => 'purple',
            self::REVOKED => 'rose',
            self::INACTIVE => 'neutral',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::SURVEILLANCE_DUE => 'warning',
            self::SURVEILLANCE_OVERDUE, self::REVOKED, self::EXPIRED => 'danger',
            self::SUSPENDED => 'info',
            self::INACTIVE => 'neutral',
        };
    }

    public function isCompliant(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function isRevoked(): bool
    {
        return $this === self::REVOKED;
    }
}
