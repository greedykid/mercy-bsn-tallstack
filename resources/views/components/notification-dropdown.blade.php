@php
    $user = Auth::user();
    $surveillanceService = app(\App\Services\SurveillanceEngineService::class);
    $notifications = $surveillanceService->getProcessNotifications($user && $user->isPic() ? $user->id : null);
    $urgentCount = $notifications->whereIn('status', ['OVERDUE', 'DUE'])->count();
    $topNotifications = $notifications->take(8);
@endphp

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:click.outside="open = false" class="relative">
    {{-- Trigger Bell Button --}}
    <button 
        type="button" 
        x-on:click="open = !open" 
        class="relative p-2.5 sm:p-2 min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 inline-flex items-center justify-center rounded-xl text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors focus:outline-hidden cursor-pointer"
        aria-label="Notifikasi Deadline Siklus Akreditasi"
    >
        <x-icon name="bell" class="size-5" />
        @if($urgentCount > 0)
            <span class="absolute top-1.5 right-1.5 sm:top-1 sm:right-1 flex size-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full size-4 bg-red-600 text-[10px] font-bold text-white items-center justify-center leading-none">
                    {{ $urgentCount > 9 ? '9+' : $urgentCount }}
                </span>
            </span>
        @endif
    </button>

    {{-- Mobile Backdrop Overlay --}}
    <div 
        x-show="open" 
        x-transition:enter="transition-opacity ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="open = false" 
        class="fixed inset-0 bg-neutral-900/40 backdrop-blur-xs z-40 sm:hidden"
        style="display: none;"
        aria-hidden="true"
    ></div>

    {{-- Dropdown Container --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="fixed left-3 right-3 top-[4.25rem] sm:absolute sm:left-auto sm:right-0 sm:top-auto sm:mt-2 w-auto sm:w-96 max-h-[calc(100dvh-5.5rem)] sm:max-h-none rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-2xl z-50 overflow-hidden flex flex-col"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="p-3 sm:p-3.5 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-800/50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <x-icon name="bell-alert" class="size-4 text-blue-600 dark:text-blue-400 shrink-0" />
                <span class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Notifikasi Siklus Akreditasi</span>
            </div>
            <div class="flex items-center gap-2">
                @if($urgentCount > 0)
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                        {{ $urgentCount }} Mendesak
                    </span>
                @else
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        Semua Patuh
                    </span>
                @endif
                <button 
                    type="button" 
                    x-on:click="open = false" 
                    class="sm:hidden p-1 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 hover:bg-neutral-200/50 dark:hover:bg-neutral-700/50 cursor-pointer"
                    aria-label="Tutup notifikasi"
                >
                    <x-icon name="x-mark" class="size-4" />
                </button>
            </div>
        </div>

        {{-- Notification Items List --}}
        <div class="overflow-y-auto divide-y divide-neutral-100 dark:divide-neutral-800 max-h-[55dvh] sm:max-h-80 flex-1">
            @forelse($topNotifications as $notif)
                <a 
                    href="{{ $notif['action_url'] }}" 
                    wire:navigate 
                    x-on:click="open = false" 
                    class="block p-3 sm:p-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition-colors group active:bg-neutral-100 dark:active:bg-neutral-800"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                {{ $notif['process_label'] }}
                            </span>
                            <span @class([
                                'text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded',
                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $notif['status'] === 'OVERDUE',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $notif['status'] === 'DUE',
                                'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' => $notif['status'] === 'IN_PROGRESS',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $notif['status'] === 'UPCOMING',
                            ])>
                                {{ $notif['status_label'] }}
                            </span>
                        </div>
                        <span class="text-[10px] sm:text-[11px] font-mono font-semibold text-neutral-500 dark:text-neutral-400 shrink-0">
                            {{ $notif['no_akreditasi'] ?? $notif['no_reg'] }}
                        </span>
                    </div>

                    <div class="text-xs sm:text-sm font-semibold text-neutral-900 dark:text-neutral-100 mt-1.5 line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                        {{ $notif['lpk_name'] }}
                    </div>

                    <p class="text-[11px] sm:text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2 leading-relaxed">
                        {{ $notif['description'] }}
                    </p>

                    <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-neutral-400 mt-2 pt-1.5 border-t border-dashed border-neutral-100 dark:border-neutral-800/80">
                        <span>Batas: {{ $notif['target_date_formatted'] }}</span>
                        <span @class([
                            'font-semibold',
                            'text-red-600 dark:text-red-400' => $notif['status'] === 'OVERDUE',
                            'text-amber-600 dark:text-amber-400' => $notif['status'] === 'DUE',
                            'text-blue-600 dark:text-blue-400' => $notif['status'] === 'UPCOMING',
                        ])>
                            @if($notif['status'] === 'OVERDUE')
                                Terlewat {{ abs($notif['days_remaining']) }} hari
                            @elseif($notif['days_remaining'] > 0)
                                Sisa {{ $notif['days_remaining'] }} hari
                            @else
                                Perlu Penjadwalan
                            @endif
                        </span>
                    </div>
                </a>
            @empty
                <div class="py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                    Tidak ada notifikasi deadline proses yang tertunda.
                </div>
            @endforelse
        </div>

        {{-- Footer CTA --}}
        <div class="p-3 border-t border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-800/30 text-center shrink-0">
            <a 
                href="{{ route('deadlines.index') }}" 
                wire:navigate 
                x-on:click="open = false" 
                class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 inline-flex items-center justify-center gap-1.5 py-1 px-3 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors"
            >
                <span>Buka Pusat Notifikasi Deadline</span>
                <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>
    </div>
</div>
