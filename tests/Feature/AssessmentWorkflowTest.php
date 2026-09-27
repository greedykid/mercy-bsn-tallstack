<?php

declare(strict_types=1);

use App\Actions\Assessments\CreateAssessmentAction;
use App\Actions\Assessments\ExtendTpDeadlineAction;
use App\Actions\Assessments\SatisfyTpAction;
use App\DTOs\AssessmentData;
use App\Enums\AssessmentType;
use App\Enums\CalendarEventType;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Exceptions\InvalidTpExtensionException;
use App\Models\Assessment;
use App\Models\CalendarEvent;
use App\Models\Lpk;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->lpk = Lpk::create([
        'no_reg' => 'LP-FLOW-001',
        'name' => 'Lab Pengujian Flow Test',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
    ]);
});

test('creating assessment sets deadline and syncs calendar events', function () {
    $createAction = app(CreateAssessmentAction::class);

    $dto = new AssessmentData(
        lpk_id: $this->lpk->id,
        title: 'Asesmen Lapangan Flow',
        assessment_type: AssessmentType::SURVEILLANCE,
        start_at: '2026-04-10',
        end_at: '2026-04-12',
        location: 'Jakarta',
        status: 'IN_PROGRESS',
        tp_deadline_months: 2,
        tp_findings_total: 6,
        tp_findings_closed: 2,
    );

    $assessment = $createAction->execute($dto, $this->admin->id);

    expect($assessment)->not->toBeNull();
    expect($assessment->tp_deadline_months)->toBe(2);
    expect($assessment->tp_due_date->toDateString())->toBe('2026-06-12');

    // Calendar events created
    $calendarEvent = CalendarEvent::where('assessment_id', $assessment->id)
        ->where('event_type', CalendarEventType::ASSESSMENT)
        ->first();
    expect($calendarEvent)->not->toBeNull();
});

test('assessment create form can be rendered by admin and pic', function () {
    $this->actingAs($this->admin)
        ->get(route('assessments.create'))
        ->assertOk()
        ->assertSee('Jadwalkan Asesmen');
});

test('assessment create form automatically preselects lab and type when query parameters are present', function () {
    $this->actingAs($this->admin)
        ->get(route('assessments.create', [
            'lpk_id' => $this->lpk->id,
            'type' => 'INITIAL',
        ]))
        ->assertOk()
        ->assertSee($this->lpk->name);

    $test = \Livewire\Livewire::actingAs($this->admin)
        ->withQueryParams([
            'lpk_id' => $this->lpk->id,
            'type' => 'INITIAL',
        ])
        ->test(\App\Livewire\Assessments\AssessmentForm::class);

    expect($test->get('form.lpk_id'))->toBe($this->lpk->id);
    expect($test->get('form.assessment_type'))->toBe('INITIAL');
    expect($test->get('form.title'))->toContain($this->lpk->name);
});

test('extending TP deadline with progress succeeds and updates calendar', function () {
    $assessment = Assessment::create([
        'lpk_id' => $this->lpk->id,
        'title' => 'Asesmen Perpanjangan',
        'assessment_type' => AssessmentType::SURVEILLANCE,
        'start_at' => '2026-01-10',
        'end_at' => '2026-01-12',
        'tp_due_date' => '2026-03-12',
        'tp_deadline_months' => 2,
        'tp_findings_total' => 10,
        'tp_findings_closed' => 8,
        'status' => 'IN_PROGRESS',
        'created_by' => $this->admin->id,
    ]);

    $extendAction = app(ExtendTpDeadlineAction::class);
    $extended = $extendAction->execute($assessment, 'Sisa 2 temuan sedang dikonfirmasi');

    expect($extended->tp_extended_months)->toBe(1);
    expect($extended->tp_extended_due_date->toDateString())->toBe('2026-04-12');
});

test('extending TP deadline with zero progress throws exception', function () {
    $assessment = Assessment::create([
        'lpk_id' => $this->lpk->id,
        'title' => 'Asesmen Tanpa Perbaikan',
        'assessment_type' => AssessmentType::SURVEILLANCE,
        'start_at' => '2026-01-10',
        'end_at' => '2026-01-12',
        'tp_due_date' => '2026-03-12',
        'tp_deadline_months' => 2,
        'tp_findings_total' => 10,
        'tp_findings_closed' => 0, // Zero progress
        'status' => 'IN_PROGRESS',
        'created_by' => $this->admin->id,
    ]);

    $extendAction = app(ExtendTpDeadlineAction::class);

    expect(fn () => $extendAction->execute($assessment, 'Minta perpanjangan'))
        ->toThrow(InvalidTpExtensionException::class);
});

test('satisfying TP completes assessment and updates status', function () {
    $assessment = Assessment::create([
        'lpk_id' => $this->lpk->id,
        'title' => 'Asesmen Untuk Diselesaikan',
        'assessment_type' => AssessmentType::SURVEILLANCE,
        'start_at' => '2026-01-10',
        'end_at' => '2026-01-12',
        'tp_due_date' => '2026-03-12',
        'tp_deadline_months' => 2,
        'tp_findings_total' => 5,
        'tp_findings_closed' => 5,
        'status' => 'IN_PROGRESS',
        'created_by' => $this->admin->id,
    ]);

    $satisfyAction = app(SatisfyTpAction::class);
    $completed = $satisfyAction->execute($assessment, 'Seluruh perbaikan telah diverifikasi asesor');

    expect($completed->tp_satisfied_at)->not->toBeNull();
    expect($completed->status)->toBe('COMPLETED');
});

test('assessment index displays active filter badges when filtering', function () {
    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Assessments\AssessmentIndex::class)
        ->assertDontSee('Filter Aktif:')
        ->set('search', 'Asesmen')
        ->assertSee('Filter Aktif:')
        ->assertSee('Pencarian:')
        ->assertSee('Asesmen')
        ->set('selectedType', 'SURVEILLANCE')
        ->assertSee('Tipe:')
        ->assertSee('Survailen')
        ->set('selectedStatus', 'IN_PROGRESS')
        ->assertSee('Status:')
        ->assertSee('Sedang Berlangsung')
        ->call('clearType')
        ->assertDontSee('Tipe:')
        ->call('resetFilters')
        ->assertDontSee('Filter Aktif:');
});

test('saving assessment form flashes success dialog and redirects to show', function () {
    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Assessments\AssessmentForm::class)
        ->set('form.lpk_id', $this->lpk->id)
        ->set('form.title', 'Asesmen Survailen Lab Flow Baru')
        ->set('form.assessment_type', 'SURVEILLANCE')
        ->set('form.start_at', '2026-05-10')
        ->set('form.end_at', '2026-05-12')
        ->set('form.location', 'Jakarta')
        ->set('form.lead_assessor', 'Dr. Pratama')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $created = Assessment::where('title', 'Asesmen Survailen Lab Flow Baru')->first();
    expect($created)->not->toBeNull();

    $dialog = session()->get('ts-ui:dialog');
    expect($dialog)->not->toBeNull();
    expect($dialog['type'])->toBe('success');
    expect($dialog['title'])->toBe('Berhasil Dibuat');
});

