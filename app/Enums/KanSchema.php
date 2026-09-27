<?php

declare(strict_types=1);

namespace App\Enums;

enum KanSchema: string
{
    case LP = 'LP';

    public function label(): string
    {
        return 'Laboratorium Penguji';
    }

    public function standard(): string
    {
        return 'SNI ISO/IEC 17025:2017';
    }

    public function color(): string
    {
        return 'blue';
    }

    /**
     * Bidang lingkup pengujian teknis Laboratorium Penguji.
     */
    public static function testingScopes(): array
    {
        return [
            'KIMIA' => 'Pengujian Kimia & Pangan',
            'BIOLOGI' => 'Pengujian Biologi & Mikrobiologi',
            'FISIKA' => 'Pengujian Fisika & Mekanik',
            'ELEKTRIK' => 'Pengujian Kelistrikan & Elektronika',
            'LINGKUNGAN' => 'Pengujian Lingkungan Hidup',
        ];
    }
}
