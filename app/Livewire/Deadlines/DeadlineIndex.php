<?php

declare(strict_types=1);

namespace App\Livewire\Deadlines;

use App\Services\SurveillanceEngineService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Notifikasi Deadline Siklus Akreditasi')]
class DeadlineIndex extends Component
{
    #[Url(as: 'proses')]
    public string $filterProcess = 'ALL';

    #[Url(as: 'status')]
    public string $filterNotificationStatus = 'ALL';

    #[Url(as: 'cari')]
    public string $search = '';

    public function setFilterProcess(string $process): void
    {
        $this->filterProcess = $process;
    }

    public function setFilterNotificationStatus(string $status): void
    {
        $this->filterNotificationStatus = $status;
    }

    public function resetFilters(): void
    {
        $this->filterProcess = 'ALL';
        $this->filterNotificationStatus = 'ALL';
        $this->search = '';
    }

    public function render(SurveillanceEngineService $surveillanceService)
    {
        $user = Auth::user();
        $isPic = $user && $user->isPic();

        $allNotifications = $surveillanceService->getProcessNotifications($isPic ? $user->id : null);

        $processCounts = [
            'ALL' => $allNotifications->count(),
            'AA' => $allNotifications->where('process_code', 'AA')->count(),
            'S1' => $allNotifications->where('process_code', 'S1')->count(),
            'S2' => $allNotifications->where('process_code', 'S2')->count(),
            'PRL' => $allNotifications->where('process_code', 'PRL')->count(),
            'STT' => $allNotifications->where('process_code', 'STT')->count(),
            'RA' => $allNotifications->where('process_code', 'RA')->count(),
        ];

        $statusCounts = [
            'ALL' => $allNotifications->count(),
            'OVERDUE' => $allNotifications->where('status', 'OVERDUE')->count(),
            'DUE' => $allNotifications->where('status', 'DUE')->count(),
            'IN_PROGRESS' => $allNotifications->where('status', 'IN_PROGRESS')->count(),
            'UPCOMING' => $allNotifications->where('status', 'UPCOMING')->count(),
        ];

        $filtered = $allNotifications;

        if ($this->filterProcess !== 'ALL') {
            $filtered = $filtered->where('process_code', $this->filterProcess);
        }

        if ($this->filterNotificationStatus !== 'ALL') {
            $filtered = $filtered->where('status', $this->filterNotificationStatus);
        }

        if (trim($this->search) !== '') {
            $term = mb_strtolower(trim($this->search));
            $filtered = $filtered->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item['lpk_name'] ?? ''), $term)
                    || str_contains(mb_strtolower($item['no_akreditasi'] ?? $item['no_reg'] ?? ''), $term)
                    || str_contains(mb_strtolower($item['description'] ?? ''), $term);
            });
        }

        return view('livewire.deadlines.deadline-index', [
            'notifications' => $filtered,
            'processCounts' => $processCounts,
            'statusCounts' => $statusCounts,
            'totalCount' => $allNotifications->count(),
            'urgentCount' => $allNotifications->whereIn('status', ['OVERDUE', 'DUE'])->count(),
            'overdueCount' => $allNotifications->where('status', 'OVERDUE')->count(),
            'dueCount' => $allNotifications->where('status', 'DUE')->count(),
        ]);
    }
}
