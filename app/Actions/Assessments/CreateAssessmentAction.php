<?php

declare(strict_types=1);

namespace App\Actions\Assessments;

use App\DTOs\AssessmentData;
use App\Models\Assessment;
use App\Services\CalendarFeedService;
use App\Services\TpDeadlineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateAssessmentAction
{
    public function __construct(
        protected TpDeadlineService $deadlineService,
        protected CalendarFeedService $calendarService,
    ) {}

    public function execute(AssessmentData $data, int $userId): Assessment
    {
        return DB::transaction(function () use ($data, $userId) {
            $tpDueDate = $data->tp_due_date;
            if (empty($tpDueDate)) {
                $tpDueDate = $this->deadlineService->calculateDueDate(
                    $data->assessment_type,
                    Carbon::parse($data->end_at)
                )->toDateString();
            }

            $attributes = $data->toArray();
            $attributes['created_by'] = $userId;
            $attributes['tp_due_date'] = $tpDueDate;
            $attributes['tp_deadline_months'] = $data->assessment_type->normalDeadlineMonths();

            /** @var Assessment $assessment */
            $assessment = Assessment::create($attributes);

            $this->calendarService->syncAssessmentEvents($assessment);

            return $assessment;
        });
    }
}
