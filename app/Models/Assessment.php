<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssessmentType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpk_id',
        'created_by',
        'title',
        'assessment_type',
        'start_at',
        'end_at',
        'location',
        'lead_assessor',
        'assessor_team',
        'status',
        'tp_deadline_months',
        'tp_due_date',
        'tp_findings_total',
        'tp_findings_closed',
        'tp_has_extension',
        'tp_extension_months',
        'tp_extension_letter_no',
        'tp_extended_due_date',
        'tp_submitted_at',
        'tp_satisfied_at',
        'assessment_report_date',
        'eha_scheduled_date',
        'eha_completed_at',
        'eha_notes',
        'sk_issued_at',
        'sk_number',
        'sk_lead_time_days',
    ];

    protected function casts(): array
    {
        return [
            'assessment_type' => AssessmentType::class,
            'start_at' => 'date',
            'end_at' => 'date',
            'tp_due_date' => 'date',
            'tp_extended_due_date' => 'date',
            'tp_submitted_at' => 'date',
            'tp_satisfied_at' => 'date',
            'assessment_report_date' => 'date',
            'eha_scheduled_date' => 'date',
            'eha_completed_at' => 'date',
            'sk_issued_at' => 'date',
            'tp_has_extension' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(\App\Services\SurveillanceEngineService::class)->clearNotificationCache();
        });

        static::deleted(function () {
            app(\App\Services\SurveillanceEngineService::class)->clearNotificationCache();
        });
    }

    public function lpk(): BelongsTo
    {
        return $this->belongsTo(Lpk::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function scopeForPic(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('lpk', function (Builder $q) use ($user) {
            $q->where('pic_user_id', $user->id);
        });
    }

    public function isInitial(): bool
    {
        return $this->assessment_type === AssessmentType::INITIAL;
    }

    public function isSurveillance(): bool
    {
        return in_array($this->assessment_type, [
            AssessmentType::SURVEILLANCE,
            AssessmentType::SURVEILLANCE_PRL,
            AssessmentType::SURVEILLANCE_2,
            AssessmentType::SURVEILLANCE_2_PRL,
            AssessmentType::UNSCHEDULED_SURVEILLANCE,
        ], true);
    }

    public function isTpSatisfied(): bool
    {
        return ! empty($this->tp_satisfied_at);
    }

    public function hasExtension(): bool
    {
        return (bool) $this->tp_has_extension;
    }

    public function effectiveTpDueDate(): ?CarbonInterface
    {
        return $this->hasExtension() && $this->tp_extended_due_date
            ? $this->tp_extended_due_date
            : $this->tp_due_date;
    }

    public function tpFindingsPending(): int
    {
        return max(0, $this->tp_findings_total - $this->tp_findings_closed);
    }

    public function getTpExtendedMonthsAttribute(): int
    {
        return (int) ($this->tp_extension_months ?? 0);
    }

    public function effectiveMonths(): int
    {
        $base = (int) ($this->tp_deadline_months ?? $this->assessment_type->normalDeadlineMonths());
        if ($this->hasExtension()) {
            $base += (int) ($this->tp_extension_months ?? 1);
        }

        return $base;
    }
}
