<?php

declare(strict_types=1);

namespace App\Actions\Assessments;

use App\DTOs\AssessmentData;
use App\Models\Assessment;
use App\Services\CalendarFeedService;
use Illuminate\Support\Facades\DB;

class UpdateAssessmentAction
{
    public function __construct(
        protected CalendarFeedService $calendarService,
    ) {}

    public function execute(Assessment $assessment, AssessmentData $data): Assessment
    {
        return DB::transaction(function () use ($assessment, $data) {
            $assessment->update($data->toArray());

            $fresh = $assessment->fresh();
            $this->calendarService->syncAssessmentEvents($fresh);

            return $fresh;
        });
    }
}
