<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\AssessmentType;

readonly class AssessmentData
{
    public function __construct(
        public int $lpk_id,
        public string $title,
        public AssessmentType $assessment_type,
        public string $start_at,
        public string $end_at,
        public ?string $location = null,
        public ?string $lead_assessor = null,
        public ?string $assessor_team = null,
        public string $status = 'PLANNED',
        public int $tp_deadline_months = 2,
        public ?string $tp_due_date = null,
        public int $tp_findings_total = 0,
        public int $tp_findings_closed = 0,
        public bool $tp_has_extension = false,
        public int $tp_extension_months = 0,
        public ?string $tp_extension_letter_no = null,
        public ?string $tp_extended_due_date = null,
        public ?string $tp_submitted_at = null,
        public ?string $tp_satisfied_at = null,
        public ?string $assessment_report_date = null,
        public ?string $eha_scheduled_date = null,
        public ?string $eha_completed_at = null,
        public ?string $eha_notes = null,
        public ?string $sk_issued_at = null,
        public ?string $sk_number = null,
        public ?int $sk_lead_time_days = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = $data['assessment_type'] instanceof AssessmentType
            ? $data['assessment_type']
            : AssessmentType::from((string) ($data['assessment_type'] ?? 'SURVEILLANCE'));

        return new self(
            lpk_id: (int) $data['lpk_id'],
            title: (string) ($data['title'] ?? ''),
            assessment_type: $type,
            start_at: (string) $data['start_at'],
            end_at: (string) ($data['end_at'] ?? $data['start_at']),
            location: $data['location'] ?? null,
            lead_assessor: $data['lead_assessor'] ?? null,
            assessor_team: $data['assessor_team'] ?? null,
            status: (string) ($data['status'] ?? 'PLANNED'),
            tp_deadline_months: isset($data['tp_deadline_months']) ? (int) $data['tp_deadline_months'] : $type->normalDeadlineMonths(),
            tp_due_date: $data['tp_due_date'] ?? null,
            tp_findings_total: (int) ($data['tp_findings_total'] ?? 0),
            tp_findings_closed: (int) ($data['tp_findings_closed'] ?? 0),
            tp_has_extension: (bool) ($data['tp_has_extension'] ?? false),
            tp_extension_months: (int) ($data['tp_extension_months'] ?? 0),
            tp_extension_letter_no: $data['tp_extension_letter_no'] ?? null,
            tp_extended_due_date: $data['tp_extended_due_date'] ?? null,
            tp_submitted_at: $data['tp_submitted_at'] ?? null,
            tp_satisfied_at: $data['tp_satisfied_at'] ?? null,
            assessment_report_date: $data['assessment_report_date'] ?? null,
            eha_scheduled_date: $data['eha_scheduled_date'] ?? null,
            eha_completed_at: $data['eha_completed_at'] ?? null,
            eha_notes: $data['eha_notes'] ?? null,
            sk_issued_at: $data['sk_issued_at'] ?? null,
            sk_number: $data['sk_number'] ?? null,
            sk_lead_time_days: isset($data['sk_lead_time_days']) ? (int) $data['sk_lead_time_days'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'lpk_id' => $this->lpk_id,
            'title' => $this->title,
            'assessment_type' => $this->assessment_type->value,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'location' => $this->location,
            'lead_assessor' => $this->lead_assessor,
            'assessor_team' => $this->assessor_team,
            'status' => $this->status,
            'tp_deadline_months' => $this->tp_deadline_months,
            'tp_due_date' => $this->tp_due_date,
            'tp_findings_total' => $this->tp_findings_total,
            'tp_findings_closed' => $this->tp_findings_closed,
            'tp_has_extension' => $this->tp_has_extension,
            'tp_extension_months' => $this->tp_extension_months,
            'tp_extension_letter_no' => $this->tp_extension_letter_no,
            'tp_extended_due_date' => $this->tp_extended_due_date,
            'tp_submitted_at' => $this->tp_submitted_at,
            'tp_satisfied_at' => $this->tp_satisfied_at,
            'assessment_report_date' => $this->assessment_report_date,
            'eha_scheduled_date' => $this->eha_scheduled_date,
            'eha_completed_at' => $this->eha_completed_at,
            'eha_notes' => $this->eha_notes,
            'sk_issued_at' => $this->sk_issued_at,
            'sk_number' => $this->sk_number,
            'sk_lead_time_days' => $this->sk_lead_time_days,
        ];
    }
}
