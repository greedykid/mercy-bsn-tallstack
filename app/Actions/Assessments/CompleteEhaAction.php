<?php

declare(strict_types=1);

namespace App\Actions\Assessments;

use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompleteEhaAction
{
    public function execute(
        Assessment $assessment,
        string $completedAt,
        ?string $notes = null,
        ?string $scheduledDate = null
    ): Assessment {
        return DB::transaction(function () use ($assessment, $completedAt, $notes, $scheduledDate) {
            $data = [
                'eha_completed_at' => Carbon::parse($completedAt)->toDateString(),
                'eha_notes' => $notes ?? $assessment->eha_notes,
            ];

            if ($scheduledDate) {
                $data['eha_scheduled_date'] = Carbon::parse($scheduledDate)->toDateString();
            }

            $assessment->update($data);

            return $assessment->fresh();
        });
    }
}
