<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\LpkStatus;
use App\Models\Assessment;
use App\Models\CalendarEvent;
use App\Models\Lpk;
use App\Services\SurveillanceEngineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(SurveillanceEngineService $surveillanceService)
    {
        $user = Auth::user();
        $isPic = $user && $user->isPic();

        $lpkQuery = Lpk::query();
        if ($isPic) {
            $lpkQuery->where('pic_user_id', $user->id);
        }

        $statusCounts = (clone $lpkQuery)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'SURVEILLANCE_DUE' THEN 1 ELSE 0 END) as due,
                SUM(CASE WHEN status = 'SURVEILLANCE_OVERDUE' THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN status = 'SUSPENDED' THEN 1 ELSE 0 END) as suspended
            ")->first();

        $totalLpks = (int) ($statusCounts->total ?? 0);
        $activeLpks = (int) ($statusCounts->active ?? 0);
        $dueLpks = (int) ($statusCounts->due ?? 0);
        $overdueLpks = (int) ($statusCounts->overdue ?? 0);
        $suspendedLpks = (int) ($statusCounts->suspended ?? 0);

        // Notifikasi siklus akreditasi untuk widget ringkasan
        $allNotifications = $surveillanceService->getProcessNotifications($isPic ? $user->id : null);
        $urgentNotifications = $allNotifications->whereIn('status', ['OVERDUE', 'DUE']);
        $urgentCount = $urgentNotifications->count();

        // Asesmen aktif/in progress
        $assessmentQuery = Assessment::with('lpk');
        if ($isPic) {
            $assessmentQuery->whereHas('lpk', fn ($q) => $q->where('pic_user_id', $user->id));
        }

        $inProgressAssessments = (clone $assessmentQuery)
            ->whereNull('tp_satisfied_at')
            ->count();

        $recentAssessments = (clone $assessmentQuery)
            ->latest('start_at')
            ->take(5)
            ->get();

        // LPK memerlukan perhatian segera (due, overdue, suspended)
        $urgentLpks = (clone $lpkQuery)
            ->whereIn('status', [
                LpkStatus::SURVEILLANCE_DUE->value,
                LpkStatus::SURVEILLANCE_OVERDUE->value,
                LpkStatus::SUSPENDED->value,
            ])
            ->take(6)
            ->get();

        // Agenda kalender mendatang
        $calendarQuery = CalendarEvent::with(['lpk', 'assessment'])
            ->where('start_at', '>=', Carbon::now()->startOfDay());

        if ($isPic) {
            $calendarQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('lpk', fn ($lq) => $lq->where('pic_user_id', $user->id));
            });
        }

        $upcomingEvents = $calendarQuery->orderBy('start_at')->take(5)->get();

        return view('livewire.dashboard', [
            'totalLpks' => $totalLpks,
            'activeLpks' => $activeLpks,
            'dueLpks' => $dueLpks,
            'overdueLpks' => $overdueLpks,
            'suspendedLpks' => $suspendedLpks,
            'inProgressAssessments' => $inProgressAssessments,
            'recentAssessments' => $recentAssessments,
            'urgentLpks' => $urgentLpks,
            'upcomingEvents' => $upcomingEvents,
            'urgentNotifications' => $urgentNotifications->take(4)->values(),
            'urgentNotificationsCount' => $urgentCount,
            'totalNotificationsCount' => $allNotifications->count(),
        ]);
    }
}
