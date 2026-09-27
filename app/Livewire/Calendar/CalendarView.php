<?php

declare(strict_types=1);

namespace App\Livewire\Calendar;

use App\Enums\CalendarEventType;
use App\Livewire\Concerns\HasToast;
use App\Models\CalendarEvent;
use App\Models\Lpk;
use App\Services\CalendarFeedService;
use App\Services\SurveillanceEngineService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CalendarView extends Component
{
    use HasToast;

    #[Url]
    public int $year;

    #[Url]
    public int $month;

    #[Url(as: 'view')]
    public string $viewMode = 'month'; // 'month', 'week', 'day', 'agenda'

    #[Url(as: 'lpk')]
    public ?int $filterLpk = null;

    #[Url(as: 'type')]
    public string $type = 'ALL';

    #[Url]
    public ?int $highlight = null;

    public ?string $selectedCalendarDate = null;

    #[Url(as: 'date')]
    public string $activeDate = '';

    public array $selectedCategories = [
        'ASSESSMENT',
        'TP_DEADLINE',
        'EHA_MEETING',
        'SURVEILLANCE_DUE',
        'SK_EXPIRY',
    ];

    public ?CalendarEvent $selectedEvent = null;

    public bool $showDetailModal = false;

    // Create event modal state
    public bool $showCreateEventModal = false;

    public ?int $createLpkId = null;

    public string $createTitle = '';

    public string $createDate = '';

    public string $createTime = '09:00';

    public string $createType = 'INITIAL';

    public string $createDescription = '';

    public function mount(?int $year = null, ?int $month = null, ?int $lpk = null): void
    {
        $now = now();
        $this->year = $year ?? (int) $now->year;
        $this->month = $month ?? (int) $now->month;
        $this->activeDate = $this->activeDate ?: $now->toDateString();
        $this->selectedCalendarDate = $this->activeDate;
        $this->createDate = $this->activeDate;

        if ($lpk !== null) {
            $this->filterLpk = $lpk;
        }

        if ($this->highlight) {
            $event = CalendarEvent::with(['lpk', 'assessment'])->find($this->highlight);
            if ($event) {
                $this->selectedEvent = $event;
                $this->showDetailModal = true;
                $this->year = (int) $event->start_at->year;
                $this->month = (int) $event->start_at->month;
                $this->activeDate = $event->start_at->toDateString();
                $this->selectedCalendarDate = $this->activeDate;
            }
        }
    }

    public function updatedSelectedCalendarDate(?string $value): void
    {
        if ($value) {
            try {
                $date = Carbon::parse($value);
                $this->activeDate = $date->toDateString();
                $this->year = (int) $date->year;
                $this->month = (int) $date->month;
            } catch (\Throwable) {
                // Ignore parse errors
            }
        }
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['month', 'week', 'day', 'agenda'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function previous(): void
    {
        if ($this->viewMode === 'day') {
            $date = Carbon::parse($this->activeDate)->subDay();
            $this->activeDate = $date->toDateString();
            $this->selectedCalendarDate = $this->activeDate;
            $this->year = (int) $date->year;
            $this->month = (int) $date->month;
        } elseif ($this->viewMode === 'week') {
            $date = Carbon::parse($this->activeDate)->subWeek();
            $this->activeDate = $date->toDateString();
            $this->selectedCalendarDate = $this->activeDate;
            $this->year = (int) $date->year;
            $this->month = (int) $date->month;
        } else {
            $this->previousMonth();
        }
    }

    public function next(): void
    {
        if ($this->viewMode === 'day') {
            $date = Carbon::parse($this->activeDate)->addDay();
            $this->activeDate = $date->toDateString();
            $this->selectedCalendarDate = $this->activeDate;
            $this->year = (int) $date->year;
            $this->month = (int) $date->month;
        } elseif ($this->viewMode === 'week') {
            $date = Carbon::parse($this->activeDate)->addWeek();
            $this->activeDate = $date->toDateString();
            $this->selectedCalendarDate = $this->activeDate;
            $this->year = (int) $date->year;
            $this->month = (int) $date->month;
        } else {
            $this->nextMonth();
        }
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
        $this->activeDate = Carbon::createFromDate($this->year, $this->month, 1)->toDateString();
        $this->selectedCalendarDate = $this->activeDate;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
        $this->activeDate = Carbon::createFromDate($this->year, $this->month, 1)->toDateString();
        $this->selectedCalendarDate = $this->activeDate;
    }

    public function today(): void
    {
        $now = now();
        $this->year = (int) $now->year;
        $this->month = (int) $now->month;
        $this->activeDate = $now->toDateString();
        $this->selectedCalendarDate = $now->toDateString();
    }

    public function clearFilterLpk(): void
    {
        $this->filterLpk = null;
    }

    public function resetCategories(): void
    {
        $this->selectedCategories = [
            'ASSESSMENT',
            'TP_DEADLINE',
            'EHA_MEETING',
            'SURVEILLANCE_DUE',
            'SK_EXPIRY',
        ];
    }

    public function resetCalendarFilters(): void
    {
        $this->filterLpk = null;
        $this->resetCategories();
    }

    public function selectEvent(int $id): void
    {
        $this->selectedEvent = CalendarEvent::with(['lpk', 'assessment'])->find($id);
        if ($this->selectedEvent) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedEvent = null;
    }

    public function openCreateEventModal(?string $date = null, ?string $time = null): void
    {
        $this->createDate = $date ?? now()->toDateString();
        $this->createTime = $time ?? '09:00';
        $this->createTitle = '';
        $this->createDescription = '';
        $this->createType = 'ASSESSMENT';
        $this->createLpkId = $this->filterLpk ?? null;
        $this->showCreateEventModal = true;
    }

    public function saveCalendarEvent(): void
    {
        $this->validate([
            'createLpkId' => ['required', 'exists:lpks,id'],
            'createTitle' => ['required', 'string', 'max:255'],
            'createDate' => ['required', 'date'],
            'createType' => ['required', 'string'],
        ]);

        $lpk = Lpk::findOrFail($this->createLpkId);
        $startAt = Carbon::parse("{$this->createDate} {$this->createTime}");
        $eventType = CalendarEventType::tryFrom($this->createType) ?? CalendarEventType::INITIAL;

        CalendarEvent::create([
            'lpk_id' => $lpk->id,
            'user_id' => Auth::id() ?? 1,
            'title' => $this->createTitle,
            'description' => $this->createDescription,
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addHours(2),
            'is_all_day' => false,
            'event_type' => $eventType,
            'color' => $eventType->color(),
        ]);

        $this->showCreateEventModal = false;
        $this->dialogSuccess('Agenda Ditambahkan', "Agenda '{$this->createTitle}' berhasil dijadwalkan ke kalender kegiatan.");
    }

    public function confirmDeleteEvent(int $id): void
    {
        $event = CalendarEvent::findOrFail($id);

        $this->dialog()
            ->question('Hapus Agenda Kalender?', "Yakin ingin menghapus agenda '{$event->title}' dari kalender kegiatan?")
            ->confirm('Ya, Hapus Agenda', 'deleteEvent', $id)
            ->cancel('Batal')
            ->send();
    }

    public function deleteEvent(int $id): void
    {
        try {
            $event = CalendarEvent::findOrFail($id);
            $title = $event->title;
            $event->delete();

            $this->showDetailModal = false;
            $this->selectedEvent = null;
            $this->dialogSuccess('Berhasil Dihapus', "Agenda '{$title}' telah berhasil dihapus dari kalender kegiatan.");
        } catch (\Throwable $e) {
            $this->dialogError('Gagal Menghapus Agenda', $e->getMessage());
        }
    }

    public function toggleCategory(string $category): void
    {
        if (in_array($category, $this->selectedCategories, true)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$category]));
        } else {
            $this->selectedCategories[] = $category;
        }
    }

    public function render(CalendarFeedService $calendarService, SurveillanceEngineService $surveillanceService)
    {
        $user = Auth::user();
        $picUserId = ($user && $user->isPic()) ? $user->id : null;

        /** @var Collection<int, CalendarEvent> $events */
        $events = $calendarService->getEventsForMonth($this->year, $this->month, $picUserId);

        if ($this->filterLpk) {
            $events = $events->filter(fn (CalendarEvent $e) => $e->lpk_id === $this->filterLpk);
        }

        if (! empty($this->selectedCategories)) {
            $events = $events->filter(fn (CalendarEvent $e) => in_array($e->event_type->category(), $this->selectedCategories, true));
        }

        // Build calendar grid days for Month view
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        $startDayOfWeek = $startOfMonth->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $daysInMonth = $endOfMonth->day;

        $grid = [];

        // Preceding days from previous month
        $prevMonthLastDay = $startOfMonth->copy()->subDay();
        for ($i = $startDayOfWeek - 1; $i > 0; $i--) {
            $dayDate = $prevMonthLastDay->copy()->subDays($i - 1);
            $grid[] = [
                'date' => $dayDate->toDateString(),
                'day' => $dayDate->day,
                'isCurrentMonth' => false,
                'isToday' => $dayDate->isToday(),
                'events' => collect(),
            ];
        }

        // Current month days
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDate = Carbon::createFromDate($this->year, $this->month, $d);
            $dateString = $currentDate->toDateString();

            $dayEvents = $events->filter(function (CalendarEvent $e) use ($currentDate) {
                return $currentDate->between(
                    $e->start_at->copy()->startOfDay(),
                    $e->end_at->copy()->endOfDay()
                );
            });

            $grid[] = [
                'date' => $dateString,
                'day' => $d,
                'isCurrentMonth' => true,
                'isToday' => $currentDate->isToday(),
                'events' => $dayEvents,
            ];
        }

        // Trailing days to fill 7 columns
        $totalCells = count($grid);
        $remaining = (7 - ($totalCells % 7)) % 7;
        for ($j = 1; $j <= $remaining; $j++) {
            $dayDate = $endOfMonth->copy()->addDays($j);
            $grid[] = [
                'date' => $dayDate->toDateString(),
                'day' => $dayDate->day,
                'isCurrentMonth' => false,
                'isToday' => $dayDate->isToday(),
                'events' => collect(),
            ];
        }

        $monthName = Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F Y');

        $lpkListQuery = Lpk::query()->orderBy('no_reg');
        if ($picUserId) {
            $lpkListQuery->where('pic_user_id', $picUserId);
        }
        $lpks = $lpkListQuery->get(['id', 'no_reg', 'name']);

        $activeCarbon = Carbon::parse($this->activeDate ?: now()->toDateString());

        // Calculate Week Days
        $startOfWeek = $activeCarbon->copy()->startOfWeek();
        $endOfWeek = $activeCarbon->copy()->endOfWeek();
        $weekDays = [];
        for ($w = 0; $w < 7; $w++) {
            $wDay = $startOfWeek->copy()->addDays($w);
            $wDayStr = $wDay->toDateString();
            $wEvents = $events->filter(function (CalendarEvent $e) use ($wDay) {
                return $wDay->between(
                    $e->start_at->copy()->startOfDay(),
                    $e->end_at->copy()->endOfDay()
                );
            });

            $weekDays[] = [
                'date' => $wDayStr,
                'day' => $wDay->day,
                'dayName' => $wDay->translatedFormat('l'),
                'formattedDate' => $wDay->translatedFormat('d M'),
                'isToday' => $wDay->isToday(),
                'events' => $wEvents,
            ];
        }

        // Calculate Day Events
        $dayEvents = $events->filter(function (CalendarEvent $e) use ($activeCarbon) {
            return $activeCarbon->between(
                $e->start_at->copy()->startOfDay(),
                $e->end_at->copy()->endOfDay()
            );
        })->sortBy('start_at');

        // Hourly breakdown (07:00 to 19:00) for Google Calendar-style Day View
        $hoursRange = range(7, 19);
        $dayEventsByHour = [];
        $dayOtherEvents = [];
        foreach ($dayEvents as $dEvent) {
            $h = (int) $dEvent->start_at->hour;
            if ($h >= 7 && $h <= 19) {
                $dayEventsByHour[$h][] = $dEvent;
            } else {
                $dayOtherEvents[] = $dEvent;
            }
        }

        $nowJakarta = now()->setTimezone('Asia/Jakarta');
        $isToday = $activeCarbon->isToday();
        $nowHour = (int) $nowJakarta->hour;
        $nowMinute = (int) $nowJakarta->minute;
        $showNowIndicator = $isToday && ($nowHour >= 7 && $nowHour <= 19);
        $nowTopPx = $showNowIndicator ? ((($nowHour - 7) * 64) + (($nowMinute / 60) * 64)) : 0;

        // Dynamic Header Title
        $headerTitle = match ($this->viewMode) {
            'day' => $activeCarbon->translatedFormat('l, d F Y'),
            'week' => $startOfWeek->translatedFormat('d M') . ' - ' . $endOfWeek->translatedFormat('d M Y'),
            'agenda' => 'Agenda ' . $monthName,
            default => $monthName,
        };

        // Formatted Options for TallStackUI Select Components
        $lpkOptions = $lpks->map(fn ($l) => [
            'label' => "[{$l->no_reg}] {$l->name}",
            'value' => (int) $l->id,
        ])->values()->all();

        $eventTypeOptions = collect(CalendarEventType::cases())->map(fn ($et) => [
            'label' => $et->label(),
            'value' => $et->value,
        ])->values()->all();

        $monthOptions = collect(range(1, 12))->map(fn ($m) => [
            'label' => Carbon::createFromDate(2026, $m, 1)->translatedFormat('F'),
            'value' => $m,
        ])->values()->all();

        $yearOptions = collect(range(2020, 2030))->map(fn ($y) => [
            'label' => (string) $y,
            'value' => $y,
        ])->values()->all();

        return view('livewire.calendar.calendar-view', [
            'monthName' => $monthName,
            'headerTitle' => $headerTitle,
            'grid' => $grid,
            'weekDays' => $weekDays,
            'dayEvents' => $dayEvents,
            'hoursRange' => $hoursRange,
            'dayEventsByHour' => $dayEventsByHour,
            'dayOtherEvents' => $dayOtherEvents,
            'isToday' => $isToday,
            'showNowIndicator' => $showNowIndicator,
            'nowTopPx' => $nowTopPx,
            'activeCarbon' => $activeCarbon,
            'eventTypes' => CalendarEventType::cases(),
            'totalEvents' => $events->count(),
            'lpks' => $lpks,
            'lpkOptions' => $lpkOptions,
            'eventTypeOptions' => $eventTypeOptions,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'user' => $user,
        ]);
    }
}
