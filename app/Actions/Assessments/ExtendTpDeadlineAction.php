<?php

declare(strict_types=1);

namespace App\Actions\Assessments;

use App\Exceptions\InvalidTpExtensionException;
use App\Models\Assessment;
use App\Services\CalendarFeedService;
use App\Services\TpDeadlineService;
use Illuminate\Support\Facades\DB;

class ExtendTpDeadlineAction
{
    public function __construct(
        protected TpDeadlineService $deadlineService,
        protected CalendarFeedService $calendarService,
    ) {}

    public function execute(Assessment $assessment, string $letterNo): Assessment
    {
        if (! $this->deadlineService->isEligibleForExtension($assessment)) {
            if ($assessment->tp_findings_total > 0 && $assessment->tp_findings_closed <= 0) {
                throw new InvalidTpExtensionException(
                    'Laboratorium tidak memiliki progres perbaikan sama sekali (0 temuan tertutup). Perpanjangan batas waktu dilarang dan proses dihentikan.'
                );
            }

            throw new InvalidTpExtensionException(
                'Asesmen belum memenuhi syarat perpanjangan batas waktu tindakan perbaikan.'
            );
        }

        return DB::transaction(function () use ($assessment, $letterNo) {
            $extendedDate = $this->deadlineService->calculateExtendedDueDate($assessment);

            $assessment->update([
                'tp_has_extension' => true,
                'tp_extension_months' => 1,
                'tp_extension_letter_no' => $letterNo,
                'tp_extended_due_date' => $extendedDate->toDateString(),
            ]);

            $fresh = $assessment->fresh();
            $this->calendarService->syncAssessmentEvents($fresh);

            return $fresh;
        });
    }
}
