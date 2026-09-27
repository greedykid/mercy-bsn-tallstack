<?php

declare(strict_types=1);

use App\Enums\CalendarEventType;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Livewire\Calendar\CalendarView;
use App\Models\CalendarEvent;
use App\Models\Lpk;
use App\Models\User;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'PIC Laboratorium',
        'email' => 'pic@test.local',
        'role' => 'pic',
    ]);

    $this->lpk = Lpk::create([
        'no_reg' => 'LP-CALENDAR-001',
        'name' => 'Balai Pengujian Mutu Kalender',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
        'pic_user_id' => $this->user->id,
    ]);

    $this->event = CalendarEvent::create([
        'lpk_id' => $this->lpk->id,
        'user_id' => $this->user->id,
        'title' => 'Opening Meeting Asesmen Lapangan',
        'description' => 'Asesmen awal di laboratorium',
        'start_at' => now()->startOfMonth()->addDays(5)->setHour(9)->setMinute(0),
        'end_at' => now()->startOfMonth()->addDays(5)->setHour(11)->setMinute(0),
        'is_all_day' => false,
        'event_type' => CalendarEventType::ASSESSMENT,
        'color' => CalendarEventType::ASSESSMENT->color(),
    ]);
});

test('calendar view renders successfully for authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('calendar'))
        ->assertOk()
        ->assertSee('Opening Meeting Asesmen Lapangan')
        ->assertSee('Kategori Kalender')
        ->assertSee('Filter Lembaga (LP)');
});

test('calendar view can filter events by LP', function () {
    $otherLpk = Lpk::create([
        'no_reg' => 'LP-OTHER-002',
        'name' => 'Laboratorium Terpisah',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
        'pic_user_id' => $this->user->id,
    ]);

    CalendarEvent::create([
        'lpk_id' => $otherLpk->id,
        'user_id' => $this->user->id,
        'title' => 'Agenda Khusus Lab Terpisah',
        'start_at' => now()->startOfMonth()->addDays(10),
        'end_at' => now()->startOfMonth()->addDays(10)->addHours(2),
        'is_all_day' => false,
        'event_type' => CalendarEventType::SURVEILLANCE_DUE,
        'color' => CalendarEventType::SURVEILLANCE_DUE->color(),
    ]);

    $this->actingAs($this->user);

    livewire(CalendarView::class, ['lpk' => $this->lpk->id])
        ->assertSee('Opening Meeting Asesmen Lapangan')
        ->assertDontSee('Agenda Khusus Lab Terpisah');
});

test('user can create a calendar event through the modal', function () {
    $this->actingAs($this->user);

    livewire(CalendarView::class)
        ->set('createLpkId', $this->lpk->id)
        ->set('createTitle', 'Sidang Komite Teknis Pengujian')
        ->set('createDate', now()->format('Y-m-d'))
        ->set('createTime', '13:30')
        ->set('createType', CalendarEventType::EHA_MEETING->value)
        ->set('createDescription', 'Evaluasi tindak lanjut hasil asesmen')
        ->call('saveCalendarEvent')
        ->assertHasNoErrors()
        ->assertDispatched('ts-ui:dialog');

    expect(CalendarEvent::where('title', 'Sidang Komite Teknis Pengujian')->exists())->toBeTrue();
});

test('calendar view can switch to week and day view modes', function () {
    $this->actingAs($this->user);

    livewire(CalendarView::class)
        ->call('setViewMode', 'week')
        ->assertSet('viewMode', 'week')
        ->assertSee('Bulan')
        ->assertSee('Minggu')
        ->assertSee('Hari')
        ->call('setViewMode', 'day')
        ->assertSet('viewMode', 'day');
});

test('calendar view displays active filter badges when LP or categories are filtered', function () {
    $this->actingAs($this->user);

    livewire(CalendarView::class)
        ->assertDontSee('Filter Aktif:')
        ->set('filterLpk', $this->lpk->id)
        ->assertSee('Filter Aktif:')
        ->assertSee('LP:')
        ->call('clearFilterLpk')
        ->assertDontSee('Filter Aktif:')
        ->call('toggleCategory', 'SK_EXPIRY')
        ->assertSee('Filter Aktif:')
        ->assertSee('Kategori:')
        ->call('resetCalendarFilters')
        ->assertDontSee('Filter Aktif:');
});

test('calendar event types include requested assessment types and can be scheduled', function () {
    $this->actingAs($this->user);

    $testCases = [
        ['type' => CalendarEventType::INITIAL->value, 'title' => 'Asesmen Akreditasi Awal Lab'],
        ['type' => CalendarEventType::SURVEILLANCE->value, 'title' => 'Kunjungan S1 Terjadwal'],
        ['type' => CalendarEventType::SURVEILLANCE_PRL->value, 'title' => 'Survailen 1 dan PRL Gabungan'],
        ['type' => CalendarEventType::SURVEILLANCE_2->value, 'title' => 'Kunjungan S2 Terjadwal'],
        ['type' => CalendarEventType::SURVEILLANCE_2_PRL->value, 'title' => 'Survailen 2 dan PRL Gabungan'],
        ['type' => CalendarEventType::UNSCHEDULED_SURVEILLANCE->value, 'title' => 'Audit Survailen Tidak Terjadwal'],
        ['type' => CalendarEventType::SCOPE_EXTENSION->value, 'title' => 'Asesmen Lapangan PRL'],
    ];

    foreach ($testCases as $case) {
        livewire(CalendarView::class)
            ->set('createLpkId', $this->lpk->id)
            ->set('createTitle', $case['title'])
            ->set('createDate', now()->format('Y-m-d'))
            ->set('createTime', '10:00')
            ->set('createType', $case['type'])
            ->call('saveCalendarEvent')
            ->assertHasNoErrors();

        $event = CalendarEvent::where('title', $case['title'])->first();
        expect($event)->not->toBeNull();
        expect($event->event_type->value)->toBe($case['type']);
        expect($event->event_type->category())->toBe('ASSESSMENT');
    }
});

