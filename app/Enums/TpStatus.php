<?php

declare(strict_types=1);

namespace App\Enums;

enum TpStatus: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case SATISFIED = 'SATISFIED';
    case COMPLETED = 'COMPLETED';
    case SUSPENDED = 'SUSPENDED';
    case OVERDUE = 'OVERDUE';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'Sedang Berlangsung',
            self::SATISFIED => 'Memenuhi',
            self::COMPLETED => 'Selesai',
            self::SUSPENDED => 'Dibekukan',
            self::OVERDUE => 'Melewati Batas Waktu',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SATISFIED, self::COMPLETED => 'emerald',
            self::IN_PROGRESS => 'amber',
            self::SUSPENDED => 'purple',
            self::OVERDUE => 'red',
        };
    }

    public function isSatisfied(): bool
    {
        return in_array($this, [self::SATISFIED, self::COMPLETED], true);
    }
}
