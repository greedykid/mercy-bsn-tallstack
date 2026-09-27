<?php

declare(strict_types=1);

namespace App\Actions\Assessments;

use App\Models\Assessment;
use App\Services\CalendarFeedService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SatisfyTpAction
{
    public function __construct(
        protected CalendarFeedService $calendarService,
    ) {}

    public function execute(Assessment $assessment, ?string $satisfiedAt = null, ?string $note = null): Assessment
    {
        return DB::transaction(function () use ($assessment, $satisfiedAt, $note) {
            $date = now();
            $effectiveNote = $note;

            if ($satisfiedAt) {
                try {
                    $date = Carbon::parse($satisfiedAt);
                } catch (\Throwable) {
                    $effectiveNote = $satisfiedAt;
                    $date = now();
                }
            }

            $assessment->update([
                'tp_satisfied_at' => $date->toDateString(),
                'tp_findings_closed' => $assessment->tp_findings_total > 0 ? $assessment->tp_findings_total : $assessment->tp_findings_closed,
                'status' => 'COMPLETED',
            ]);

            $fresh = $assessment->fresh();
            $this->calendarService->syncAssessmentEvents($fresh);

            return $fresh;
        });
    }
}
