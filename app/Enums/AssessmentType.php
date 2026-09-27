<?php

declare(strict_types=1);

namespace App\Enums;

enum AssessmentType: string
{
    case INITIAL = 'INITIAL';
    case SURVEILLANCE = 'SURVEILLANCE';
    case SURVEILLANCE_PRL = 'SURVEILLANCE_PRL';
    case SURVEILLANCE_2 = 'SURVEILLANCE_2';
    case SURVEILLANCE_2_PRL = 'SURVEILLANCE_2_PRL';
    case UNSCHEDULED_SURVEILLANCE = 'UNSCHEDULED_SURVEILLANCE';
    case SCOPE_EXTENSION = 'SCOPE_EXTENSION';
    case REASSESSMENT = 'REASSESSMENT';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL => 'Akreditasi Awal (AA)',
            self::SURVEILLANCE => 'Survailen 1 (S1)',
            self::SURVEILLANCE_PRL => 'Survailen 1 + PRL (S1_PRL)',
            self::SURVEILLANCE_2 => 'Survailen 2 (S2)',
            self::SURVEILLANCE_2_PRL => 'Survailen 2 + PRL (S2_PRL)',
            self::UNSCHEDULED_SURVEILLANCE => 'Survailen Tidak Terjadwal (STT)',
            self::SCOPE_EXTENSION => 'Perluasan Ruang Lingkup (PRL)',
            self::REASSESSMENT => 'Re-Akreditasi (RA)',
        };
    }

    public function shortCode(): string
    {
        return match ($this) {
            self::INITIAL => 'AA',
            self::SURVEILLANCE => 'S1',
            self::SURVEILLANCE_PRL => 'S1_PRL',
            self::SURVEILLANCE_2 => 'S2',
            self::SURVEILLANCE_2_PRL => 'S2_PRL',
            self::UNSCHEDULED_SURVEILLANCE => 'STT',
            self::SCOPE_EXTENSION => 'PRL',
            self::REASSESSMENT => 'RA',
        };
    }

    public function normalDeadlineMonths(): int
    {
        return match ($this) {
            self::INITIAL => 3,
            default => 2,
        };
    }

    public function earlyReminderMonths(): int
    {
        return match ($this) {
            self::INITIAL => 2,
            default => 1,
        };
    }

    public static function parse(string|self|null $input): self
    {
        if ($input instanceof self) {
            return $input;
        }

        if (empty($input)) {
            return self::SURVEILLANCE;
        }

        $raw = strtoupper(trim((string) $input));
        $normalized = str_replace(['+', '-'], '_', $raw);
        $normalized = (string) preg_replace('/[\s_]+/', '_', $normalized);

        $direct = self::tryFrom($raw);
        if ($direct) {
            return $direct;
        }

        return match (true) {
            $normalized === 'AA' || str_contains($normalized, 'AKREDITASI_AWAL') || str_contains($normalized, 'INITIAL') => self::INITIAL,

            $normalized === 'S1_PRL' || str_contains($normalized, 'S1_PRL') || (str_contains($normalized, 'SURVAILEN_1') && str_contains($normalized, 'PRL')) || (str_contains($normalized, 'SURVEILEN_1') && str_contains($normalized, 'PRL')) || (str_contains($normalized, 'SURVEILLANCE_1') && str_contains($normalized, 'PRL')) || str_contains($normalized, 'SURVEILLANCE_PRL') => self::SURVEILLANCE_PRL,

            $normalized === 'S2_PRL' || str_contains($normalized, 'S2_PRL') || (str_contains($normalized, 'SURVAILEN_2') && str_contains($normalized, 'PRL')) || (str_contains($normalized, 'SURVEILEN_2') && str_contains($normalized, 'PRL')) || (str_contains($normalized, 'SURVEILLANCE_2') && str_contains($normalized, 'PRL')) || str_contains($normalized, 'SURVEILLANCE_2_PRL') => self::SURVEILLANCE_2_PRL,

            $normalized === 'S1' || str_contains($normalized, 'SURVAILEN_1') || str_contains($normalized, 'SURVEILEN_1') || str_contains($normalized, 'SURVEILLANCE_1') || $normalized === 'SURVEILLANCE' => self::SURVEILLANCE,

            $normalized === 'S2' || str_contains($normalized, 'SURVAILEN_2') || str_contains($normalized, 'SURVEILEN_2') || str_contains($normalized, 'SURVEILLANCE_2') => self::SURVEILLANCE_2,

            $normalized === 'STT' || str_contains($normalized, 'TIDAK_TERJADWAL') || str_contains($normalized, 'UNSCHEDULED') => self::UNSCHEDULED_SURVEILLANCE,

            $normalized === 'PRL' || str_contains($normalized, 'PERLUASAN') || str_contains($normalized, 'SCOPE') => self::SCOPE_EXTENSION,

            $normalized === 'RA' || str_contains($normalized, 'RE_AKREDITASI') || str_contains($normalized, 'REAKREDITASI') || str_contains($normalized, 'REASSESSMENT') || str_contains($normalized, 'RE_ASSESSMENT') => self::REASSESSMENT,

            default => self::SURVEILLANCE,
        };
    }
}
