<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\DTOs\AssessmentData;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AssessmentForm extends Form
{
    public ?Assessment $assessment = null;

    #[Validate('required|exists:lpks,id')]
    public int $lpk_id = 0;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required')]
    public string $assessment_type = 'SURVEILLANCE';

    #[Validate('required|date')]
    public string $start_at = '';

    #[Validate('required|date|after_or_equal:start_at')]
    public string $end_at = '';

    #[Validate('nullable|string|max:255')]
    public ?string $location = null;

    #[Validate('nullable|string|max:150')]
    public ?string $lead_assessor = null;

    #[Validate('nullable|string')]
    public ?string $assessor_team = null;

    #[Validate('required|string')]
    public string $status = 'PLANNED';

    #[Validate('nullable|integer|min:0')]
    public int $tp_findings_total = 0;

    #[Validate('nullable|integer|min:0')]
    public int $tp_findings_closed = 0;

    public function setAssessment(Assessment $assessment): void
    {
        $this->assessment = $assessment;
        $this->lpk_id = $assessment->lpk_id;
        $this->title = $assessment->title;
        $this->assessment_type = $assessment->assessment_type->value;
        $this->start_at = $assessment->start_at->format('Y-m-d');
        $this->end_at = $assessment->end_at->format('Y-m-d');
        $this->location = $assessment->location;
        $this->lead_assessor = $assessment->lead_assessor;
        $this->assessor_team = $assessment->assessor_team;
        $this->status = $assessment->status;
        $this->tp_findings_total = $assessment->tp_findings_total;
        $this->tp_findings_closed = $assessment->tp_findings_closed;
    }

    public function toDto(): AssessmentData
    {
        $type = AssessmentType::from($this->assessment_type);

        return new AssessmentData(
            lpk_id: $this->lpk_id,
            title: $this->title,
            assessment_type: $type,
            start_at: $this->start_at,
            end_at: $this->end_at,
            location: $this->location,
            lead_assessor: $this->lead_assessor,
            assessor_team: $this->assessor_team,
            status: $this->status,
            tp_deadline_months: $type->normalDeadlineMonths(),
            tp_findings_total: $this->tp_findings_total,
            tp_findings_closed: $this->tp_findings_closed,
        );
    }
}
