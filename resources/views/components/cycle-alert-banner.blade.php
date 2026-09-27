@php
    $user = Auth::user();
    $urgentCount = 0;
    if ($user) {
        $surveillanceService = app(\App\Services\SurveillanceEngineService::class);
        $urgentCount = $surveillanceService
            ->getProcessNotifications($user->isPic() ? $user->id : null)
            ->whereIn('status', ['OVERDUE', 'DUE'])
            ->count();
    }
@endphp

@if($urgentCount > 0)
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50/80 p-4 sm:p-5 dark:border-red-900/60 dark:bg-red-950/30">
        <div class="flex items-start gap-3">
            <x-icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
            <div class="space-y-1 min-w-0 flex-1">
                <h3 class="text-sm sm:text-base font-bold text-red-700 dark:text-red-400 leading-snug break-words">
                    Peringatan Siklus Pengawasan Akreditasi: {{ $urgentCount }} Kunjungan Memerlukan Tindak Lanjut!
                </h3>
                <p class="text-xs sm:text-sm text-red-600/90 dark:text-red-300/90 leading-relaxed break-words">
                    Sesuai siklus KAN, notifikasi aktif di Bulan ke-14 (S1), Bulan ke-35 (S2), dan 1 Bulan sebelum kedaluwarsa (Re-Akreditasi). Notifikasi ini persisten hingga jadwal kunjungan diagendakan.
                </p>
                <div class="pt-2">
                    <a 
                        href="{{ route('deadlines.index') }}" 
                        wire:navigate 
                        class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-red-700 hover:bg-red-800 text-white shadow-xs transition-colors cursor-pointer"
                    >
                        <span>Tinjau LP Jatuh Tempo</span>
                        <span aria-hidden="true">&gt;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
