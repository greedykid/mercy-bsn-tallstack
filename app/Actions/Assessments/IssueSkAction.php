<?php

declare(strict_types=1);

namespace App\Actions\Assessments;

use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IssueSkAction
{
    public function execute(Assessment $assessment, string $skIssuedAt, string $skNumber): Assessment
    {
        return DB::transaction(function () use ($assessment, $skIssuedAt, $skNumber) {
            $issuedDate = Carbon::parse($skIssuedAt);
            $startDate = Carbon::parse($assessment->start_at);
            $leadTimeDays = (int) $startDate->diffInDays($issuedDate);

            $assessment->update([
                'sk_issued_at' => $issuedDate->toDateString(),
                'sk_number' => $skNumber,
                'sk_lead_time_days' => $leadTimeDays,
                'status' => 'COMPLETED',
            ]);

            return $assessment->fresh();
        });
    }
}
