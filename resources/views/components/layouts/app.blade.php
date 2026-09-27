<x-slot:title>
    {{ $title ?? 'MERCY - Manajemen Akreditasi Lab Penguji KAN' }}
</x-slot:title>

<x-layouts.base>
    <x-layout>
        <x-slot:menu>
            <x-side-bar navigate-hover smart collapsible thin-scroll>
                <x-slot:brand>
                    <div class="mb-2">
                        <a href="{{ route('dashboard') }}" wire:navigate.hover class="flex items-center gap-3 px-4 pt-3 pb-1 h-full w-full">
                            <div class="flex items-center justify-center shrink-0">
                                <img src="{{ asset('images/logo-bsn.png') }}" alt="Logo BSN" class="h-9 w-auto object-contain" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold tracking-tight text-neutral-900 dark:text-neutral-100 leading-none truncate">MERCY</span>
                                <span class="text-[10px] text-neutral-500 dark:text-neutral-400 font-medium leading-tight mt-1 truncate">Lab Penguji KAN/BSN</span>
                            </div>
                        </a>
                    </div>
                </x-slot:brand>

                <x-slot:brand-collapsed>
                    <div class="mb-2">
                        <a href="{{ route('dashboard') }}" wire:navigate.hover class="flex items-center justify-center px-2 pt-3 pb-1 h-full w-full">
                            <img src="{{ asset('images/logo-bsn.png') }}" alt="Logo BSN" class="h-6 w-auto object-contain" />
                        </a>
                    </div>
                </x-slot:brand-collapsed>

                <x-side-bar.item class="mt-1.5" text="Dashboard" :route="route('dashboard')" match="dashboard" icon="home" />

                <x-side-bar.separator text="Fitur Utama" />
                <x-side-bar.item text="Notifikasi Deadline" :route="route('deadlines.index')" match="deadlines.*" icon="bell" badge-color="red">
                    @php
                        $urgentNotifsCount = app(\App\Services\SurveillanceEngineService::class)->getProcessNotifications(Auth::user()?->isPic() ? Auth::id() : null)->whereIn('status', ['OVERDUE', 'DUE'])->count();
                    @endphp
                    @if($urgentNotifsCount > 0)
                        <x-slot:badge>{{ $urgentNotifsCount }}</x-slot:badge>
                    @endif
                </x-side-bar.item>

                <x-side-bar.item text="Data Laboratorium (LP)" :route="route('lpks.index')" match="lpks.*" icon="building-office-2" badge-color="blue">
                    @php
                        $lpkCount = \Illuminate\Support\Facades\Cache::remember('sidebar_lpk_count', 30, fn () => \App\Models\Lpk::count());
                    @endphp
                    @if($lpkCount > 0)
                        <x-slot:badge>{{ $lpkCount }}</x-slot:badge>
                    @endif
                </x-side-bar.item>

                <x-side-bar.item text="Asesmen & Batas Waktu" :route="route('assessments.index')" match="assessments.*" icon="clipboard-document-check" />
                <x-side-bar.item text="Kalender Kegiatan" :route="route('calendar')" match="calendar*" icon="calendar-days" />

                <x-side-bar.separator text="Referensi" line-right />
                <x-side-bar.item text="Portal KAN BSN" icon="book-open" href="https://kan.or.id" target="_blank" />

                <x-slot:footer>
                    <ul role="list" class="flex flex-col gap-y-0.5">
                        <x-side-bar.item text="Pengaturan" :route="route('settings.account')" match="settings.*" icon="cog-6-tooth" />
                    </ul>
                </x-slot:footer>
            </x-side-bar>
        </x-slot:menu>

        <x-slot:header>
            <x-layout.header>
                <x-slot:left>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                            {{ $headerTitle ?? 'Sistem Manajemen Akreditasi LP' }}
                        </span>
                    </div>
                </x-slot:left>

                <x-slot:right>
                    <div class="flex items-center gap-3">
                        @auth
                            <div class="hidden sm:flex items-center gap-1.5">
                                <span class="text-xs text-neutral-500">Peran:</span>
                                <x-badge :color="Auth::user()->isAdmin() ? 'blue' : 'emerald'" sm light>
                                    {{ Auth::user()->isAdmin() ? 'ADMIN LP' : 'PIC LP' }}
                                </x-badge>
                            </div>
                            <div x-on:click="setTimeout(syncMercyTheme, 40)" class="flex items-center">
                                <x-theme-switch simple only-icons />
                            </div>
                            <x-notification-dropdown />
                            <x-user-dropdown />
                        @endauth
                    </div>
                </x-slot:right>
            </x-layout.header>
        </x-slot:header>

        <div class="w-full px-3 sm:px-5 lg:px-6 xl:px-8 pt-3 sm:pt-4 pb-10">
            @auth
                <x-cycle-alert-banner />
            @endauth
            {{ $slot }}
        </div>
    </x-layout>
</x-layouts.base>
