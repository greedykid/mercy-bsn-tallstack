<?php

declare(strict_types=1);

namespace App\Actions\Lpks;

use App\DTOs\ImportResultDto;
use App\DTOs\LpkData;
use App\Models\Lpk;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpsertLpkBatchAction
{
    /**
     * @param  Collection<int, LpkData>  $items
     */
    public function execute(Collection $items, ?int $defaultPicUserId = null): ImportResultDto
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            try {
                DB::transaction(function () use ($item, $defaultPicUserId, &$created, &$updated) {
                    $expiredAt = $item->expired_at;
                    if (empty($expiredAt) && ! empty($item->certificate_date)) {
                        $expiredAt = Carbon::parse($item->certificate_date)->addYears(5)->toDateString();
                    }

                    $attributes = $item->toArray();
                    $attributes['expired_at'] = $expiredAt;

                    if ($defaultPicUserId && empty($attributes['pic_user_id'])) {
                        $attributes['pic_user_id'] = $defaultPicUserId;
                    }

                    $existing = Lpk::where('no_reg', $item->no_reg)
                        ->when(! empty($item->registration_number), function ($q) use ($item) {
                            $q->orWhere('registration_number', $item->registration_number)
                                ->orWhere('no_reg', $item->registration_number);
                        })
                        ->first();

                    if ($existing) {
                        $existing->update($attributes);
                        $targetLpk = $existing;
                        $updated++;
                    } else {
                        $targetLpk = Lpk::create($attributes);
                        $created++;
                    }

                    if ($targetLpk) {
                        app(\App\Services\CalendarFeedService::class)->syncLpkCycleEvents($targetLpk);
                    }

                    if ($targetLpk && ! empty($item->assessment_date)) {
                        $type = \App\Enums\AssessmentType::parse($item->assessment_type);

                        $startDate = Carbon::parse($item->assessment_date)->toDateString();
                        $endDate = ! empty($item->assessment_end_date)
                            ? Carbon::parse($item->assessment_end_date)->toDateString()
                            : $startDate;

                        $lpkCode = $targetLpk->registration_number ?: $targetLpk->no_reg;
                        $assessmentTitle = $item->assessment_title ?: "Asesmen {$type->label()} {$lpkCode}";

                        $assessmentData = new \App\DTOs\AssessmentData(
                            lpk_id: $targetLpk->id,
                            title: $assessmentTitle,
                            assessment_type: $type,
                            start_at: $startDate,
                            end_at: $endDate,
                            lead_assessor: $item->lead_assessor ?: ($item->assessor_team ?: 'Tim Asesor KAN'),
                            assessor_team: $item->assessor_team ?: $item->lead_assessor,
                            location: $targetLpk->address ?: ($targetLpk->city ?: 'Laboratorium Penguji'),
                            tp_due_date: $item->tp_due_date,
                            tp_findings_total: $item->tp_findings_total ?? 0,
                            tp_findings_closed: $item->tp_findings_closed ?? 0,
                            status: $item->assessment_status ?: 'IN_PROGRESS',
                        );

                        $existingAssessment = \App\Models\Assessment::where('lpk_id', $targetLpk->id)
                            ->where('start_at', $startDate)
                            ->first();

                        if ($existingAssessment) {
                            $updateAssessmentAction = app(\App\Actions\Assessments\UpdateAssessmentAction::class);
                            $updateAssessmentAction->execute($existingAssessment, $assessmentData);
                        } else {
                            $createAssessmentAction = app(\App\Actions\Assessments\CreateAssessmentAction::class);
                            $createAssessmentAction->execute($assessmentData, $defaultPicUserId ?? 1);
                        }
                    }
                });
            } catch (Throwable $e) {
                $skipped++;
                $errors[] = 'Baris '.($index + 1)." ({$item->no_reg}): ".$e->getMessage();
            }
        }

        return new ImportResultDto(
            created: $created,
            updated: $updated,
            skipped: $skipped,
            errors: $errors,
        );
    }
}
