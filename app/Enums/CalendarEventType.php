<?php

declare(strict_types=1);

namespace App\Enums;

enum CalendarEventType: string
{
    case INITIAL = 'INITIAL';
    case SURVEILLANCE = 'SURVEILLANCE';
    case SURVEILLANCE_PRL = 'SURVEILLANCE_PRL';
    case SURVEILLANCE_2 = 'SURVEILLANCE_2';
    case SURVEILLANCE_2_PRL = 'SURVEILLANCE_2_PRL';
    case UNSCHEDULED_SURVEILLANCE = 'UNSCHEDULED_SURVEILLANCE';
    case SCOPE_EXTENSION = 'SCOPE_EXTENSION';
    case REASSESSMENT = 'REASSESSMENT';
    case ASSESSMENT = 'ASSESSMENT';
    case SURVEILLANCE_DUE = 'SURVEILLANCE_DUE';
    case TP_DEADLINE = 'TP_DEADLINE';
    case EHA_MEETING = 'EHA_MEETING';
    case SK_EXPIRY = 'SK_EXPIRY';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL => 'Akreditasi Awal',
            self::SURVEILLANCE => 'Survailen 1 (S1)',
            self::SURVEILLANCE_PRL => 'Survailen 1 + PRL',
            self::SURVEILLANCE_2 => 'Survailen 2 (S2)',
            self::SURVEILLANCE_2_PRL => 'Survailen 2 + PRL',
            self::UNSCHEDULED_SURVEILLANCE => 'Survailen Tidak Terjadwal (STT)',
            self::SCOPE_EXTENSION => 'Perluasan Ruang Lingkup (PRL)',
            self::REASSESSMENT => 'Re-Akreditasi (RA)',
            self::ASSESSMENT => 'Asesmen Lapangan',
            self::SURVEILLANCE_DUE => 'Tenggat Toleransi Pengawasan',
            self::TP_DEADLINE => 'Batas Waktu TP / VTP',
            self::EHA_MEETING => 'Sidang Evaluasi Hasil Asesmen (EHA)',
            self::SK_EXPIRY => 'Kedaluwarsa Sertifikat KAN',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::INITIAL,
            self::SURVEILLANCE,
            self::SURVEILLANCE_PRL,
            self::SURVEILLANCE_2,
            self::SURVEILLANCE_2_PRL,
            self::UNSCHEDULED_SURVEILLANCE,
            self::SCOPE_EXTENSION,
            self::REASSESSMENT,
            self::ASSESSMENT => 'ASSESSMENT',
            self::SURVEILLANCE_DUE => 'SURVEILLANCE_DUE',
            self::TP_DEADLINE => 'TP_DEADLINE',
            self::EHA_MEETING => 'EHA_MEETING',
            self::SK_EXPIRY => 'SK_EXPIRY',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INITIAL,
            self::SURVEILLANCE,
            self::SURVEILLANCE_PRL,
            self::SURVEILLANCE_2,
            self::SURVEILLANCE_2_PRL,
            self::UNSCHEDULED_SURVEILLANCE,
            self::SCOPE_EXTENSION,
            self::REASSESSMENT,
            self::ASSESSMENT => 'emerald',
            self::SURVEILLANCE_DUE => 'amber',
            self::TP_DEADLINE => 'cyan',
            self::EHA_MEETING => 'purple',
            self::SK_EXPIRY => 'rose',
        };
    }
}
