<div class="space-y-6 relative" x-data="{
    popoverOpen: false,
    popoverEvent: null,
    popoverPlacement: 'right',
    popoverStyle: '',
    showDetail(el, data) {
        this.popoverEvent = data;
        this.popoverOpen = true;
        this.$nextTick(() => {
            const card = this.$refs.detailPopoverCard;
            if (!card || !el) return;
            const targetRect = el.getBoundingClientRect();
            const cardRect = card.getBoundingClientRect();
            const cardWidth = cardRect.width || 380;
            const cardHeight = cardRect.height || 300;
            const margin = 12;

            // Horizontal position: prefer right, fallback to left
            let left = targetRect.right + 12;
            let placement = 'right';

            if (left + cardWidth > window.innerWidth - margin) {
                left = targetRect.left - cardWidth - 12;
                placement = 'left';
            }

            // Narrow / mobile screens
            if (left < margin) {
                left = Math.max(margin, Math.round((window.innerWidth - cardWidth) / 2));
                placement = 'center';
            }

            // Vertical position: align near top of element, keep in viewport
            let top = targetRect.top - 8;
            if (top + cardHeight > window.innerHeight - margin) {
                top = window.innerHeight - cardHeight - margin;
            }
            if (top < margin) {
                top = margin;
            }

            this.popoverPlacement = placement;
            this.popoverStyle = 'top: ' + Math.round(top) + 'px; left: ' + Math.round(left) + 'px;';
        });
    },
    closeDetail() {
        this.popoverOpen = false;
        this.popoverEvent = null;
    }
}">
    {{-- Main Google Calendar 2-Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left Sidebar Panel (~280px / 3 cols) --}}
        <div class="lg:col-span-3 space-y-5">
            {{-- &quot;+ Tambah Agenda Baru&quot; Button --}}
            <x-button 
                color="primary" 
                class="w-full justify-center shadow-xs font-semibold" 
                icon="plus" 
                wire:click="openCreateEventModal()"
            >
                Tambah Agenda Baru
            </x-button>

            {{-- Mini Inline Month Picker (<x-calendar>) --}}
            <div class="w-full [&_[class*='w-\[17rem\]']]:w-full [&>div]:w-full overflow-hidden">
                <x-calendar wire:model.live="selectedCalendarDate" helpers bordered shadowless class="w-full" />
            </div>

            {{-- Category Filter Checkboxes with Color Legend Dots --}}
            <div class="p-4 border border-neutral-200/80 dark:border-neutral-800 rounded-xl bg-white dark:bg-neutral-900 shadow-xs space-y-3">
                <div class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    Kategori Kalender
                </div>
                <div class="space-y-2 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            wire:click="toggleCategory('ASSESSMENT')" 
                            {{ in_array('ASSESSMENT', $selectedCategories, true) ? 'checked' : '' }}
                            class="rounded border-neutral-300 text-emerald-600 focus:ring-emerald-500 size-3.5"
                        />
                        <span class="size-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-neutral-700 dark:text-neutral-300 font-medium">Pelaksanaan Asesmen</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            wire:click="toggleCategory('TP_DEADLINE')" 
                            {{ in_array('TP_DEADLINE', $selectedCategories, true) ? 'checked' : '' }}
                            class="rounded border-neutral-300 text-cyan-600 focus:ring-cyan-500 size-3.5"
                        />
                        <span class="size-2.5 rounded-full bg-cyan-500"></span>
                        <span class="text-neutral-700 dark:text-neutral-300 font-medium">Batas Waktu TP</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            wire:click="toggleCategory('EHA_MEETING')" 
                            {{ in_array('EHA_MEETING', $selectedCategories, true) ? 'checked' : '' }}
                            class="rounded border-neutral-300 text-purple-600 focus:ring-purple-500 size-3.5"
                        />
                        <span class="size-2.5 rounded-full bg-purple-500"></span>
                        <span class="text-neutral-700 dark:text-neutral-300 font-medium">Sidang Panitia / EHA</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            wire:click="toggleCategory('SURVEILLANCE_DUE')" 
                            {{ in_array('SURVEILLANCE_DUE', $selectedCategories, true) ? 'checked' : '' }}
                            class="rounded border-neutral-300 text-amber-600 focus:ring-amber-500 size-3.5"
                        />
                        <span class="size-2.5 rounded-full bg-amber-500"></span>
                        <span class="text-neutral-700 dark:text-neutral-300 font-medium">Reminder Surveilen</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            wire:click="toggleCategory('SK_EXPIRY')" 
                            {{ in_array('SK_EXPIRY', $selectedCategories, true) ? 'checked' : '' }}
                            class="rounded border-neutral-300 text-rose-600 focus:ring-rose-500 size-3.5"
                        />
                        <span class="size-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-neutral-700 dark:text-neutral-300 font-medium">Jatuh Tempo / Kedaluwarsa</span>
                    </label>
                </div>
            </div>

            {{-- Filter Lembaga (LPK) --}}
            <div class="p-4 border border-neutral-200 dark:border-neutral-800 rounded-lg bg-white dark:bg-neutral-900 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        Filter Lembaga (LP)
                    </span>
                    @if($filterLpk)
                        <button type="button" wire:click="clearFilterLpk" class="text-[10px] font-medium text-neutral-500 hover:text-red-600 transition-colors">
                            Reset
                        </button>
                    @endif
                </div>
                <x-select.styled 
                    wire:model.live="filterLpk" 
                    :options="$lpkOptions" 
                    select="label:label|value:value" 
                    placeholder="Semua Laboratorium ({{ count($lpkOptions) }})" 
                    searchable 
                />
                @if($filterLpk)
                    @php
                        $activeLpkLeft = $lpks->firstWhere('id', $filterLpk);
                    @endphp
                    @if($activeLpkLeft)
                        <div class="pt-1">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60 shadow-xs max-w-full">
                                <x-icon name="building-office-2" class="size-3 shrink-0" />
                                <span class="truncate">{{ $activeLpkLeft->no_reg }}</span>
                                <button type="button" wire:click="clearFilterLpk" class="hover:bg-blue-200/60 p-0.5 rounded-full ml-auto shrink-0" title="Hapus filter">
                                    <x-icon name="x-mark" class="size-3" />
                                </button>
                            </span>
                        </div>
                    @endif
                @endif
            </div>

            {{-- User Profile Card --}}
            <div class="p-3 border border-neutral-200/80 dark:border-neutral-800 rounded-xl bg-neutral-50 dark:bg-neutral-900/50 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <div class="size-8 rounded-full bg-neutral-800 text-white dark:bg-white dark:text-neutral-900 flex items-center justify-center font-bold text-xs">
                        {{ strtoupper(substr($user?->name ?? 'PL', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $user?->name ?? 'PIC Laboratorium' }}</div>
                        <x-badge color="cyan" outline size="sm" text="{{ $user?->role?->label() ?? 'PIC LAB' }}" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Main Interactive Calendar Grid (9 cols) --}}
        <div class="lg:col-span-9 space-y-4">
            {{-- Bilah Navigasi Header Kalender --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 rounded-xl shadow-xs">
                <div class="flex flex-wrap items-center gap-2">
                    <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" wire:click="today">
                        Hari Ini
                    </x-button>

                    <div class="flex items-center">
                        <x-button.circle color="neutral" light size="sm" icon="chevron-left" wire:click="previous" tooltip="Sebelumnya" />
                        <x-button.circle color="neutral" light size="sm" icon="chevron-right" wire:click="next" tooltip="Selanjutnya" />
                    </div>

                    <div class="flex items-center gap-2 ml-1">
                        <div class="w-44">
                            <x-select.styled wire:model.live="month" :options="$monthOptions" select="label:label|value:value" required />
                        </div>
                        <div class="w-28">
                            <x-select.styled wire:model.live="year" :options="$yearOptions" select="label:label|value:value" required />
                        </div>
                    </div>

                    <span class="text-sm font-bold text-neutral-900 dark:text-neutral-100 hidden md:inline ml-2">
                        {{ $headerTitle }}
                    </span>
                </div>

                {{-- Mode Tampilan: Segmented Button Group --}}
                <div class="flex items-center justify-end">
                    <x-button.group>
                        <x-button 
                            size="sm" 
                            :color="$viewMode === 'month' ? 'primary' : 'neutral'" 
                            :light="$viewMode !== 'month'" 
                            wire:click="setViewMode('month')"
                        >
                            Bulan
                        </x-button>
                        <x-button 
                            size="sm" 
                            :color="$viewMode === 'week' ? 'primary' : 'neutral'" 
                            :light="$viewMode !== 'week'" 
                            wire:click="setViewMode('week')"
                        >
                            Minggu
                        </x-button>
                        <x-button 
                            size="sm" 
                            :color="$viewMode === 'day' ? 'primary' : 'neutral'" 
                            :light="$viewMode !== 'day'" 
                            wire:click="setViewMode('day')"
                        >
                            Hari
                        </x-button>
                        <x-button 
                            size="sm" 
                            :color="$viewMode === 'agenda' ? 'primary' : 'neutral'" 
                            :light="$viewMode !== 'agenda'" 
                            wire:click="setViewMode('agenda')"
                        >
                            Agenda
                        </x-button>
                    </x-button.group>
                </div>
            </div>

            {{-- Active Filter Badges --}}
            @php
                $hasCustomCategories = count($selectedCategories) < 5;
                $hasActiveCalendarFilters = !empty($filterLpk) || $hasCustomCategories;
                $activeLpkCalendar = $filterLpk ? $lpks->firstWhere('id', $filterLpk) : null;
            @endphp
            @if($hasActiveCalendarFilters)
                <div class="flex flex-wrap items-center gap-2 p-2.5 bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 rounded-xl shadow-xs text-xs">
                    <span class="font-semibold text-neutral-500 uppercase tracking-wider flex items-center gap-1.5">
                        <x-icon name="funnel" class="size-3.5 text-neutral-400" />
                        Filter Aktif:
                    </span>

                    @if($filterLpk && $activeLpkCalendar)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60 shadow-xs">
                            <x-icon name="building-office-2" class="size-3 text-blue-600 dark:text-blue-400" />
                            <span>LP: <strong>[{{ $activeLpkCalendar->no_reg }}] {{ $activeLpkCalendar->name }}</strong></span>
                            <button type="button" wire:click="clearFilterLpk" class="hover:bg-blue-200/60 dark:hover:bg-blue-800 p-0.5 rounded-full text-blue-700 dark:text-blue-300 transition-colors" title="Hapus filter laboratorium">
                                <x-icon name="x-mark" class="size-3" />
                            </button>
                        </span>
                    @endif

                    @if($hasCustomCategories)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-900/60 shadow-xs">
                            <x-icon name="tag" class="size-3 text-purple-600 dark:text-purple-400" />
                            <span>Kategori: <strong>{{ count($selectedCategories) }} dari 5 aktif</strong></span>
                            <button type="button" wire:click="resetCategories" class="hover:bg-purple-200/60 dark:hover:bg-purple-800 p-0.5 rounded-full text-purple-700 dark:text-purple-300 transition-colors" title="Tampilkan semua kategori">
                                <x-icon name="x-mark" class="size-3" />
                            </button>
                        </span>
                    @endif

                    <x-button color="neutral" light size="xs" wire:click="resetCalendarFilters" class="border border-neutral-200 dark:border-neutral-700 font-medium ml-1">
                        Reset Semua
                    </x-button>
                </div>
            @endif

            @if($viewMode === 'month')
                {{-- Month View Grid --}}
                <div wire:key="calendar-view-mode-month" class="w-full border border-neutral-200/80 dark:border-neutral-800 rounded-xl overflow-hidden bg-white dark:bg-neutral-900 shadow-xs">
                    {{-- Days Header --}}
                    <div class="grid grid-cols-7 border-b border-neutral-200 dark:border-neutral-800 bg-neutral-100/75 dark:bg-neutral-950/75 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-300 py-2.5">
                        <div>Senin</div>
                        <div>Selasa</div>
                        <div>Rabu</div>
                        <div>Kamis</div>
                        <div>Jumat</div>
                        <div>Sabtu</div>
                        <div>Minggu</div>
                    </div>

                    {{-- Days Cells --}}
                    <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-neutral-200 dark:divide-neutral-800">
                        @foreach($grid as $cell)
                            <div 
                                wire:key="month-cell-{{ $cell['date'] }}"
                                class="min-h-28 p-1.5 flex flex-col transition-colors cursor-pointer {{ $cell['isCurrentMonth'] ? 'bg-white dark:bg-neutral-900 hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30' : 'bg-neutral-50/50 dark:bg-neutral-950/40 opacity-40' }} {{ $cell['isToday'] ? 'bg-emerald-50/30 dark:bg-emerald-950/20' : '' }}"
                                wire:click.self="openCreateEventModal('{{ $cell['date'] }}')"
                            >
                                {{-- Day Number --}}
                                <div class="flex items-center justify-between mb-1" wire:click.self="openCreateEventModal('{{ $cell['date'] }}')">
                                    <span class="text-xs font-mono font-medium {{ $cell['isToday'] ? 'size-6 flex items-center justify-center rounded-full bg-emerald-600 text-white font-bold' : ($cell['isCurrentMonth'] ? 'text-neutral-700 dark:text-neutral-300' : 'text-neutral-400 dark:text-neutral-600') }}">
                                        {{ $cell['day'] }}
                                    </span>
                                    @if($cell['isToday'])
                                        <span class="text-[10px] uppercase font-bold tracking-tight text-emerald-600 dark:text-emerald-400 mr-1">Hari Ini</span>
                                    @endif
                                </div>

                                {{-- Events List inside Cell --}}
                                <div class="flex-1 space-y-1 overflow-y-auto max-h-24">
                                    @foreach($cell['events'] as $event)
                                        @php
                                            $chipStyle = match($event->event_type->category()) {
                                                'ASSESSMENT' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60 hover:bg-emerald-100',
                                                'TP_DEADLINE' => 'bg-cyan-50 text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800/60 hover:bg-cyan-100',
                                                'EHA_MEETING' => 'bg-purple-50 text-purple-800 dark:bg-purple-950/50 dark:text-purple-300 border-purple-200 dark:border-purple-800/60 hover:bg-purple-100',
                                                'SURVEILLANCE_DUE' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800/60 hover:bg-amber-100',
                                                'SK_EXPIRY' => 'bg-rose-50 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-800/60 hover:bg-rose-100',
                                                default => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border-neutral-300',
                                            };
                                        @endphp
                                        <button 
                                            wire:key="month-event-{{ $event->id }}"
                                            type="button"
                                            @click.stop="showDetail($el, {{ \Illuminate\Support\Js::from($event->toPopoverPayload()) }})"
                                            class="w-full text-left px-1.5 py-0.5 rounded text-[11px] font-medium leading-snug border truncate block transition-all {{ $chipStyle }} {{ $highlight == $event->id ? 'ring-2 ring-emerald-500 font-bold' : '' }}"
                                            title="{{ $event->title }}"
                                        >
                                            {{ $event->title }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($viewMode === 'week')
                {{-- Week View Grid --}}
                <div wire:key="calendar-view-mode-week" class="w-full border border-neutral-200/80 dark:border-neutral-800 rounded-xl overflow-hidden bg-white dark:bg-neutral-900 shadow-xs">
                    {{-- 7 Columns for the week --}}
                    <div class="grid grid-cols-1 sm:grid-cols-7 divide-y sm:divide-y-0 sm:divide-x divide-neutral-200 dark:divide-neutral-800">
                        @foreach($weekDays as $wDay)
                            <div wire:key="week-day-{{ $wDay['date'] }}" class="min-h-96 flex flex-col p-2 {{ $wDay['isToday'] ? 'bg-emerald-50/20 dark:bg-emerald-950/15' : 'bg-white dark:bg-neutral-900' }}">
                                {{-- Column Header --}}
                                <div 
                                    class="pb-2 mb-2 border-b border-neutral-100 dark:border-neutral-800 text-center cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/40 rounded transition-colors"
                                    wire:click="openCreateEventModal('{{ $wDay['date'] }}')"
                                    title="Tambah agenda pada {{ $wDay['dayName'] }}, {{ $wDay['formattedDate'] }}"
                                >
                                    <div class="text-[11px] font-semibold uppercase text-neutral-500 dark:text-neutral-400">
                                        {{ $wDay['dayName'] }}
                                    </div>
                                    <div class="mt-1 flex items-center justify-center">
                                        <span class="text-sm font-bold font-mono {{ $wDay['isToday'] ? 'size-7 flex items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm' : 'text-neutral-800 dark:text-neutral-200' }}">
                                            {{ $wDay['day'] }}
                                        </span>
                                    </div>
                                    @if($wDay['isToday'])
                                        <span class="text-[9px] uppercase tracking-wider font-bold text-emerald-600 dark:text-emerald-400 block mt-0.5">Hari Ini</span>
                                    @endif
                                </div>

                                {{-- Events List --}}
                                <div class="flex-1 space-y-2 overflow-y-auto">
                                    @forelse($wDay['events'] as $wEvent)
                                        @php
                                            $chipBg = match($wEvent->event_type->category()) {
                                                'ASSESSMENT' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                                'TP_DEADLINE' => 'bg-cyan-50 text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
                                                'EHA_MEETING' => 'bg-purple-50 text-purple-800 dark:bg-purple-950/50 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                                'SURVEILLANCE_DUE' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                                'SK_EXPIRY' => 'bg-rose-50 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                                default => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border-neutral-300',
                                            };
                                        @endphp
                                        <div 
                                            wire:key="week-event-{{ $wEvent->id }}"
                                            @click.stop="showDetail($el, {{ \Illuminate\Support\Js::from($wEvent->toPopoverPayload()) }})"
                                            class="p-2 rounded border text-left cursor-pointer transition-all hover:shadow-sm {{ $chipBg }}"
                                        >
                                            <div class="text-[10px] font-mono font-semibold opacity-75">
                                                {{ $wEvent->start_at->format('H:i') }}
                                            </div>
                                            <div class="text-xs font-semibold leading-snug line-clamp-2 mt-0.5">
                                                {{ $wEvent->title }}
                                            </div>
                                            @if($wEvent->lpk)
                                                <div class="text-[10px] font-mono opacity-80 truncate mt-1">
                                                    {{ $wEvent->lpk->no_reg }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div 
                                            class="h-full flex items-center justify-center py-6 text-neutral-300 dark:text-neutral-700 text-xs italic cursor-pointer"
                                            wire:click="openCreateEventModal('{{ $wDay['date'] }}')"
                                        >
                                            Kosong
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($viewMode === 'day')
                {{-- Day View: Google Calendar style with 07:00 - 19:00 hourly timeline --}}
                <div wire:key="calendar-view-mode-day" class="w-full border border-neutral-200/80 dark:border-neutral-800 rounded-xl overflow-hidden bg-white dark:bg-neutral-900 shadow-2xs">
                    {{-- Day View Header --}}
                    <div class="p-4 border-b border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-base sm:text-lg font-bold text-neutral-900 dark:text-neutral-100">
                                {{ $activeCarbon->translatedFormat('l, d F Y') }}
                            </span>
                            @if($isToday)
                                <span class="text-xs font-medium text-neutral-500 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">
                                    Hari Ini (WIB)
                                </span>
                            @endif
                        </div>
                        <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" icon="plus" wire:click="openCreateEventModal('{{ $activeCarbon->toDateString() }}')">
                            Tambah Agenda Hari Ini
                        </x-button>
                    </div>

                    {{-- Non-working hours / All-day events if any --}}
                    @if(!empty($dayOtherEvents))
                        <div class="p-3 bg-neutral-50/70 dark:bg-neutral-800/40 border-b border-neutral-200/70 dark:border-neutral-800 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Agenda di luar jam kerja (07:00 - 19:00):</span>
                            @foreach($dayOtherEvents as $otherEvent)
                                <button wire:key="day-other-{{ $otherEvent->id }}" type="button" @click.stop="showDetail($el, {{ \Illuminate\Support\Js::from($otherEvent->toPopoverPayload()) }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 shadow-2xs transition-colors">
                                    <span class="font-mono font-semibold text-neutral-700 dark:text-neutral-300">{{ $otherEvent->start_at->format('H:i') }}</span>
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $otherEvent->title }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Timeline Container --}}
                    <div class="overflow-x-auto">
                        <div class="min-w-[650px] flex">
                            {{-- Left Column: Time Labels --}}
                            <div class="w-16 sm:w-20 shrink-0 select-none border-r border-neutral-200/80 dark:border-neutral-800">
                                @foreach($hoursRange as $hour)
                                    <div class="h-16 flex items-start justify-end pr-3 pt-1 text-[11px] font-mono text-neutral-400 dark:text-neutral-500">
                                        {{ sprintf('%02d:00', $hour) }}
                                    </div>
                                @endforeach
                            </div>

                            {{-- Right Column: Hourly Grid with Current Time Red Indicator --}}
                            <div class="flex-1 relative">
                                {{-- Red Indicator Line for Current Time (Google Calendar style) --}}
                                @if($showNowIndicator)
                                    <div class="absolute left-0 right-0 z-20 pointer-events-none flex items-center -translate-y-1/2" style="top: {{ $nowTopPx }}px;">
                                        <div class="size-2.5 rounded-full bg-red-500 -ml-1.5 shrink-0 ring-2 ring-white dark:ring-neutral-900"></div>
                                        <div class="h-0.5 bg-red-500 w-full"></div>
                                    </div>
                                @endif

                                {{-- Hourly Rows --}}
                                @foreach($hoursRange as $hour)
                                    @php
                                        $eventsInHour = $dayEventsByHour[$hour] ?? [];
                                    @endphp
                                    <div wire:key="day-hour-{{ $hour }}" class="h-16 border-b border-neutral-100 dark:border-neutral-800/70 px-2 py-1 flex items-center gap-2 hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20 transition-colors group cursor-pointer"
                                         wire:click="openCreateEventModal('{{ $activeCarbon->toDateString() }}', '{{ sprintf('%02d:00', $hour) }}')"
                                         title="Klik untuk tambah agenda pada jam {{ sprintf('%02d:00', $hour) }}">
                                        @forelse($eventsInHour as $hEvent)
                                            @php
                                                $hChip = match($hEvent->event_type->category()) {
                                                    'ASSESSMENT' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200/90 dark:border-emerald-800',
                                                    'TP_DEADLINE' => 'bg-cyan-50 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300 border-cyan-200/90 dark:border-cyan-800',
                                                    'EHA_MEETING' => 'bg-purple-50 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/90 dark:border-purple-800',
                                                    'SURVEILLANCE_DUE' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200/90 dark:border-amber-800',
                                                    'SK_EXPIRY' => 'bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200/90 dark:border-rose-800',
                                                    default => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border-neutral-300',
                                                };
                                            @endphp
                                            <div wire:key="day-event-{{ $hEvent->id }}" class="flex-1 max-w-md px-3 py-1.5 rounded-lg border text-xs font-medium shadow-2xs transition-all hover:shadow-xs flex items-center justify-between gap-2 {{ $hChip }}"
                                                 @click.stop="showDetail($el, {{ \Illuminate\Support\Js::from($hEvent->toPopoverPayload()) }})">
                                                <div class="truncate">
                                                    <div class="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                                        {{ $hEvent->title }}
                                                    </div>
                                                    <div class="text-[11px] opacity-80 truncate">
                                                        {{ $hEvent->start_at->format('H:i') }} - {{ $hEvent->end_at->format('H:i') }}
                                                        @if($hEvent->lpk)
                                                            &bull; {{ $hEvent->lpk->name }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-white/70 dark:bg-black/30 shrink-0">
                                                    {{ $hEvent->event_type->label() }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="hidden group-hover:flex items-center gap-1.5 text-xs text-neutral-400 dark:text-neutral-500 pl-2">
                                                <x-icon name="plus" class="size-3.5" />
                                                <span>Tambah agenda {{ sprintf('%02d:00', $hour) }}</span>
                                            </div>
                                        @endforelse
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Agenda List View --}}
                <div wire:key="calendar-view-mode-agenda" class="w-full">
                    <x-card bordered shadowless paddingless class="rounded-xl overflow-hidden">
                        <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            @php
                                $allMonthEvents = collect($grid)->pluck('events')->flatten()->unique('id')->sortBy('start_at');
                            @endphp
                            @forelse($allMonthEvents as $event)
                                <div wire:key="agenda-event-{{ $event->id }}" class="p-4 flex items-center justify-between hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col items-center justify-center p-2 rounded-md bg-neutral-100 dark:bg-neutral-800 min-w-12 text-center">
                                            <span class="text-[10px] uppercase font-medium text-neutral-500">{{ $event->start_at->translatedFormat('M') }}</span>
                                            <span class="text-base font-bold leading-none">{{ $event->start_at->translatedFormat('d') }}</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-sm text-neutral-900 dark:text-neutral-100">{{ $event->title }}</div>
                                            <div class="text-xs text-neutral-500 mt-0.5">
                                                {{ $event->start_at->translatedFormat('d M Y, H:i') }} - {{ $event->end_at->translatedFormat('d M Y, H:i') }}
                                                @if($event->lpk)
                                                    &bull; {{ $event->lpk->name }} ({{ $event->lpk->no_reg }})
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-badge color="{{ $event->color ?? 'neutral' }}" text="{{ $event->event_type->label() }}" />
                                        <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" @click.stop="showDetail($el, {{ \Illuminate\Support\Js::from($event->toPopoverPayload()) }})">
                                            Detail
                                        </x-button>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-xs text-neutral-500">
                                    Tidak ada agenda untuk filter dan bulan yang dipilih.
                                </div>
                            @endforelse
                        </div>
                    </x-card>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL 1: Detail Agenda (<x-modal>) --}}
    @if($selectedEvent)
        <x-modal id="calendar-event-detail" wire="showDetailModal" title="Detail Agenda Asesmen" center size="lg">
            <div class="space-y-4 text-sm">
                <div class="flex items-center gap-2">
                    <x-badge color="{{ $selectedEvent->color ?? 'neutral' }}" text="{{ $selectedEvent->event_type->label() }}" />
                </div>

                <div>
                    <div class="text-xs text-neutral-500 uppercase font-medium">Judul Agenda</div>
                    <div class="text-base font-bold text-neutral-900 dark:text-neutral-100 mt-0.5">
                        {{ $selectedEvent->title }}
                    </div>
                </div>

                @if($selectedEvent->description)
                    <div>
                        <div class="text-xs text-neutral-500 uppercase font-medium">Deskripsi atau Keterangan</div>
                        <div class="text-neutral-700 dark:text-neutral-300 mt-0.5 text-xs bg-neutral-50 dark:bg-neutral-800/60 p-2.5 rounded-md border border-neutral-200 dark:border-neutral-800">
                            {{ $selectedEvent->description }}
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-neutral-500 uppercase font-medium">Tanggal Mulai</div>
                        <div class="font-mono text-neutral-800 dark:text-neutral-200 mt-0.5">
                            {{ $selectedEvent->start_at->translatedFormat('d F Y, H:i') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 uppercase font-medium">Tanggal Selesai</div>
                        <div class="font-mono text-neutral-800 dark:text-neutral-200 mt-0.5">
                            {{ $selectedEvent->end_at->translatedFormat('d F Y, H:i') }}
                        </div>
                    </div>
                </div>

                @if($selectedEvent->lpk)
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-800/40 rounded-md border border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-neutral-500">Laboratorium Terkait</div>
                            <div class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $selectedEvent->lpk->name }}</div>
                            <div class="font-mono text-xs text-neutral-500">{{ $selectedEvent->lpk->no_reg }}</div>
                        </div>
                        <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" href="{{ route('lpks.show', $selectedEvent->lpk->id) }}" wire:navigate>
                            Profil LP
                        </x-button>
                    </div>
                @endif
            </div>

            <x-slot:footer class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($selectedEvent->assessment_id)
                        <x-button color="primary" href="{{ route('assessments.show', $selectedEvent->assessment_id) }}" wire:navigate>
                            Buka Detail Asesmen
                        </x-button>
                    @endif
                    <x-button color="red" light class="border border-red-200 dark:border-red-900 font-medium" wire:click="confirmDeleteEvent({{ $selectedEvent->id }})">
                        Hapus Agenda
                    </x-button>
                </div>
                <x-button color="neutral" light wire:click="closeDetailModal">
                    Tutup
                </x-button>
            </x-slot:footer>
        </x-modal>
    @endif

    {{-- Popover Detail Agenda Mengambang di Samping Agenda yang Diklik (Google Calendar Style) --}}
    <div 
        x-show="popoverOpen"
        x-ref="detailPopoverCard"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="closeDetail()"
        @keydown.escape.window="closeDetail()"
        class="fixed z-50 w-[380px] max-w-[calc(100vw-24px)] bg-white dark:bg-neutral-900 rounded-xl shadow-2xl border border-neutral-200/90 dark:border-neutral-700/80 overflow-hidden text-neutral-900 dark:text-neutral-100"
        :style="popoverStyle"
    >
        <template x-if="popoverEvent">
            <div>
                {{-- Header Bar Kategori & Tombol Tutup --}}
                <div class="px-4 pt-3.5 pb-2.5 flex items-start justify-between gap-3 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/70 dark:bg-neutral-800/40">
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full" :class="{
                            'bg-emerald-500': popoverEvent.event_type_category === 'ASSESSMENT',
                            'bg-cyan-500': popoverEvent.event_type_category === 'TP_DEADLINE',
                            'bg-purple-500': popoverEvent.event_type_category === 'EHA_MEETING',
                            'bg-amber-500': popoverEvent.event_type_category === 'SURVEILLANCE_DUE',
                            'bg-rose-500': popoverEvent.event_type_category === 'SK_EXPIRY'
                        }"></span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-400" x-text="popoverEvent.event_type_label"></span>
                    </div>
                    <button 
                        type="button" 
                        @click="closeDetail()" 
                        class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1 rounded-md hover:bg-neutral-200/60 dark:hover:bg-neutral-700 transition-colors"
                        title="Tutup (Esc)"
                    >
                        <x-icon name="x-mark" class="size-4" />
                    </button>
                </div>

                {{-- Konten Utama Popover --}}
                <div class="p-4 space-y-3.5 text-xs">
                    {{-- Judul Agenda --}}
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 leading-snug" x-text="popoverEvent.title"></h3>
                    </div>

                    {{-- Waktu & Jadwal --}}
                    <div class="flex items-start gap-2.5 text-neutral-700 dark:text-neutral-300">
                        <x-icon name="calendar-days" class="size-4 text-neutral-400 dark:text-neutral-500 shrink-0 mt-0.5" />
                        <div>
                            <div class="font-medium text-neutral-800 dark:text-neutral-200" x-text="popoverEvent.date_formatted"></div>
                            <div class="font-mono text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5" x-text="popoverEvent.time_formatted"></div>
                        </div>
                    </div>

                    {{-- Informasi Laboratorium Penguji (LP) --}}
                    <template x-if="popoverEvent.lpk_id">
                        <div class="p-2.5 rounded-lg border border-neutral-200/80 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-800/40 flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="text-[10px] uppercase font-semibold text-neutral-400 dark:text-neutral-500 tracking-wider">Laboratorium Penguji</div>
                                <div class="font-semibold text-neutral-900 dark:text-neutral-100 truncate text-xs mt-0.5" x-text="popoverEvent.lpk_name"></div>
                                <div class="font-mono text-[11px] text-neutral-500 dark:text-neutral-400" x-text="popoverEvent.lpk_no_reg"></div>
                            </div>
                            <template x-if="popoverEvent.lpk_url">
                                <a :href="popoverEvent.lpk_url" wire:navigate class="shrink-0 px-2.5 py-1 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 rounded border border-neutral-300/80 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-600 transition-colors shadow-2xs">
                                    Profil LP
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- Deskripsi / Catatan Tambahan --}}
                    <template x-if="popoverEvent.description">
                        <div class="p-2.5 rounded-lg border border-neutral-200/60 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-800/30 text-neutral-600 dark:text-neutral-300 text-[11px] leading-relaxed" x-text="popoverEvent.description">
                        </div>
                    </template>
                </div>

                {{-- Footer Tombol Aksi --}}
                <div class="px-4 py-3 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <template x-if="popoverEvent.assessment_url">
                            <a :href="popoverEvent.assessment_url" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-2xs">
                                <span>Buka Asesmen</span>
                                <x-icon name="arrow-right" class="size-3" />
                            </a>
                        </template>
                        <button 
                            type="button" 
                            @click="const id = popoverEvent.id; closeDetail(); $wire.confirmDeleteEvent(id)" 
                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 border border-transparent hover:border-red-200 dark:hover:border-red-900 transition-colors"
                        >
                            Hapus
                        </button>
                    </div>
                    <button 
                        type="button" 
                        @click="closeDetail()" 
                        class="px-3 py-1.5 rounded-lg text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200 hover:bg-neutral-200/50 dark:hover:bg-neutral-700 transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- MODAL 2: Tambah Agenda Baru (<x-modal>) --}}
    <x-modal id="create-calendar-event" wire="showCreateEventModal" title="Tambah Agenda Asesmen Baru" center size="lg">
        <div class="space-y-4">
            <x-select.styled 
                id="createLpkId" 
                label="Laboratorium Penguji (LP) *" 
                wire:model="createLpkId" 
                :options="$lpkOptions" 
                select="label:label|value:value" 
                placeholder="Pilih Laboratorium Penguji..." 
                searchable 
                required 
            />

            <x-input 
                id="createTitle" 
                label="Judul Agenda *" 
                wire:model="createTitle" 
                placeholder="Contoh: Opening Meeting Surveilen 1 Laboratorium" 
                required 
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-date 
                    id="createDate" 
                    label="Tanggal Pelaksanaan *" 
                    wire:model="createDate" 
                    format="YYYY-MM-DD"
                    helpers
                    required 
                />

                <x-time 
                    id="createTime" 
                    label="Waktu Pelaksanaan *" 
                    wire:model="createTime" 
                    format="24"
                    helper
                    required 
                />
            </div>

            <x-select.styled 
                id="createType" 
                label="Jenis Agenda *" 
                wire:model="createType" 
                :options="$eventTypeOptions" 
                select="label:label|value:value" 
                required 
            />

            <x-textarea 
                id="createDescription" 
                label="Deskripsi atau Keterangan" 
                wire:model="createDescription" 
                rows="2" 
                placeholder="Catatan pelaksanaan agenda..." 
            />
        </div>

        <x-slot:footer class="flex justify-end gap-2">
            <x-button color="neutral" light wire:click="$set('showCreateEventModal', false)">
                Batal
            </x-button>
            <x-button color="primary" wire:click="saveCalendarEvent" loading>
                Simpan Agenda
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>
