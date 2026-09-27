<?php

declare(strict_types=1);

use App\Enums\AssessmentType;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Exceptions\InvalidTpExtensionException;
use App\Models\Assessment;
use App\Models\Lpk;
use App\Models\User;
use App\Services\TpDeadlineService;
use Carbon\Carbon;

beforeEach(function () {
    $this->deadlineService = new TpDeadlineService;
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->lpk = Lpk::create([
        'no_reg' => 'LP-TP-001',
        'name' => 'Lab Pengujian TP Test',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
    ]);
});

test('Akreditasi Awal has 3 months base deadline while Surveilen has 2 months', function () {
    $endDate = Carbon::parse('2026-03-01');

    $aaDueDate = $this->deadlineService->calculateDueDate(AssessmentType::INITIAL, $endDate);
    expect($aaDueDate->toDateString())->toBe('2026-06-01');

    $survDueDate = $this->deadlineService->calculateDueDate(AssessmentType::SURVEILLANCE, $endDate);
    expect($survDueDate->toDateString())->toBe('2026-05-01');
});

test('allows 1 month extension when lab made progress with partially closed findings', function () {
    // Assessment with 10 findings, 8 closed, 2 remaining
    $assessment = Assessment::create([
        'lpk_id' => $this->lpk->id,
        'title' => 'Surveilen Tahunan',
        'assessment_type' => AssessmentType::SURVEILLANCE,
        'start_at' => '2026-01-10',
        'end_at' => '2026-01-12',
        'tp_due_date' => '2026-03-12',
        'tp_deadline_months' => 2,
        'tp_findings_total' => 10,
        'tp_findings_closed' => 8,
        'status' => 'IN_PROGRESS',
        'created_by' => $this->user->id,
    ]);

    expect($this->deadlineService->canExtend($assessment))->toBeTrue();

    $extended = $this->deadlineService->extendDeadline($assessment, 'Sisa 2 temuan sedang dalam proses kalibrasi ulang');

    expect($extended->tp_extended_months)->toBe(1);
    expect($extended->effectiveMonths())->toBe(3); // 2 months base + 1 month extension = 3 months
    expect($extended->tp_extended_due_date->toDateString())->toBe('2026-04-12');
});

test('AA total becomes 4 months with extension when progress exists', function () {
    $assessment = Assessment::create([
        'lpk_id' => $this->lpk->id,
        'title' => 'Akreditasi Awal Lab Kimia',
        'assessment_type' => AssessmentType::INITIAL,
        'start_at' => '2026-01-10',
        'end_at' => '2026-01-12',
        'tp_due_date' => '2026-04-12',
        'tp_deadline_months' => 3,
        'tp_findings_total' => 5,
        'tp_findings_closed' => 3,
        'status' => 'IN_PROGRESS',
        'created_by' => $this->user->id,
    ]);

    expect($this->deadlineService->canExtend($assessment))->toBeTrue();

    $extended = $this->deadlineService->extendDeadline($assessment, 'Progres 3 dari 5 temuan selesai');

    expect($extended->tp_extended_months)->toBe(1);
    expect($extended->effectiveMonths())->toBe(4); // AA 3 months base + 1 month extension = 4 months
    expect($extended->tp_extended_due_date->toDateString())->toBe('2026-05-12');
});

test('prohibits extension when lab has zero progress and throws exception', function () {
    // 0 findings closed out of 10 findings
    $assessment = Assessment::create([
        'lpk_id' => $this->lpk->id,
        'title' => 'Surveilen Tanpa Perbaikan',
        'assessment_type' => AssessmentType::SURVEILLANCE,
        'start_at' => '2026-01-10',
        'end_at' => '2026-01-12',
        'tp_due_date' => '2026-03-12',
        'tp_deadline_months' => 2,
        'tp_findings_total' => 10,
        'tp_findings_closed' => 0, // Zero progress!
        'status' => 'IN_PROGRESS',
        'created_by' => $this->user->id,
    ]);

    expect($this->deadlineService->canExtend($assessment))->toBeFalse();

    expect(fn () => $this->deadlineService->extendDeadline($assessment, 'Alasan keterlambatan'))
        ->toThrow(InvalidTpExtensionException::class);
});
