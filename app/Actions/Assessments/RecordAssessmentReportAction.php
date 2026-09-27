<?php

declare(strict_types=1);

namespace App\Actions\Assessments;

use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecordAssessmentReportAction
{
    public function execute(Assessment $assessment, string $reportDate): Assessment
    {
        return DB::transaction(function () use ($assessment, $reportDate) {
            $assessment->update([
                'assessment_report_date' => Carbon::parse($reportDate)->toDateString(),
            ]);

            return $assessment->fresh();
        });
    }
}
