<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Assessments\ExtendTpDeadlineAction;
use App\Enums\AssessmentType;
use App\Enums\TpStatus;
use App\Models\Assessment;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class TpDeadlineService
{
    /**
     * Hitung tanggal jatuh tempo normal tindakan perbaikan
     * AA: 3 Bulan Kalender, Lainnya: 2 Bulan Kalender
     */
    public function calculateDueDate(AssessmentType $type, CarbonInterface $endAt): Carbon
    {
        $months = $type->normalDeadlineMonths();

        return Carbon::parse($endAt)->addMonths($months);
    }

    /**
     * Hitung tanggal pengingat awal (Notifikasi 1)
     * AA: 2 bulan setelah realisasi, Lainnya: 1 bulan setelah realisasi
     */
    public function calculateEarlyReminderDate(AssessmentType $type, CarbonInterface $endAt): Carbon
    {
        $months = $type->earlyReminderMonths();

        return Carbon::parse($endAt)->addMonths($months);
    }

    /**
     * Syarat kelayakan perpanjangan batas waktu (+1 bulan):
     * 1. Belum pernah diperpanjang sebelumnya
     * 2. Lab wajib memiliki progres perbaikan sebagian (tp_findings_closed > 0)
     * 3. Jika temuan perbaikan 0 / kosong, perpanjangan DITOLAK (harus dihentikan dan dibekukan)
     */
    public function isEligibleForExtension(Assessment $assessment): bool
    {
        if ($assessment->tp_has_extension) {
            return false;
        }

        // Jika temuan ketidaksesuaian tidak dicatat atau total 0
        if ($assessment->tp_findings_total <= 0) {
            return false;
        }

        // Syarat mutlak: lab wajib ada progres sebagian
        // Jika 0 temuan tertutup, ditolak
        if ($assessment->tp_findings_closed <= 0) {
            return false;
        }

        // Jika semua sudah tertutup, tidak perlu perpanjangan
        if ($assessment->tp_findings_closed >= $assessment->tp_findings_total) {
            return false;
        }

        return true;
    }

    public function canExtend(Assessment $assessment): bool
    {
        return $this->isEligibleForExtension($assessment);
    }

    public function extendDeadline(Assessment $assessment, string $letterOrReason = ''): Assessment
    {
        return app(ExtendTpDeadlineAction::class)->execute($assessment, $letterOrReason);
    }

    /**
     * Hitung tanggal jatuh tempo setelah perpanjangan (+1 bulan)
     * AA total menjadi 4 bulan, Lainnya total menjadi 3 bulan
     */
    public function calculateExtendedDueDate(Assessment $assessment): Carbon
    {
        $baseDate = $assessment->tp_due_date
            ? Carbon::parse($assessment->tp_due_date)
            : $this->calculateDueDate($assessment->assessment_type, $assessment->end_at);

        return $baseDate->addMonth();
    }

    /**
     * Evaluasi status kepatuhan tindakan perbaikan
     */
    public function evaluateTpStatus(Assessment $assessment): TpStatus
    {
        if (! empty($assessment->tp_satisfied_at)) {
            return TpStatus::SATISFIED;
        }

        $effectiveDue = $assessment->effectiveTpDueDate();

        if ($effectiveDue && Carbon::now()->greaterThan($effectiveDue)) {
            return TpStatus::SUSPENDED;
        }

        return TpStatus::IN_PROGRESS;
    }
}
