<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                    Notifikasi Deadline Siklus Akreditasi
                </h1>
                @if($urgentCount > 0)
                    <x-badge color="red" sm light>
                        {{ $urgentCount }} Mendesak
                    </x-badge>
                @else
                    <x-badge color="emerald" sm light>
                        Semua Patuh
                    </x-badge>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                Monitoring otomatis 6 proses siklus akreditasi KAN berdasarkan masa berlaku sertifikat (AA, S1, S2, PRL, STT, RA).
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" href="{{ route('calendar') }}" wire:navigate icon="calendar">
                Lihat di Kalender
            </x-button>
            <x-button color="primary" href="{{ route('assessments.create') }}" wire:navigate icon="clipboard-document-check">
                Input Realisasi Asesmen
            </x-button>
        </div>
    </div>

    {{-- Metric Stat Cards for Deadlines --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Total Notifikasi Siklus</span>
                <x-icon name="bell" class="size-5 text-neutral-400" />
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">{{ $totalCount }}</span>
                <span class="text-xs text-neutral-500">agenda proses</span>
            </div>
        </x-card>

        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Melewati Batas (Overdue)</span>
                <x-badge color="red" text="Tindakan Segera" />
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold tracking-tight text-red-600 dark:text-red-400">{{ $overdueCount }}</span>
                <span class="text-xs text-neutral-500">siklus terlewat</span>
            </div>
        </x-card>

        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Jatuh Tempo (Due)</span>
                <x-badge color="amber" text="Prioritas" />
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">{{ $dueCount }}</span>
                <span class="text-xs text-neutral-500">bulan ini</span>
            </div>
        </x-card>

        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Menjelang Siklus (Upcoming)</span>
                <x-badge color="blue" text="Terjadwal" />
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold tracking-tight text-blue-600 dark:text-blue-400">{{ $statusCounts['UPCOMING'] ?? 0 }}</span>
                <span class="text-xs text-neutral-500">siklus berikutnya</span>
            </div>
        </x-card>
    </div>

    {{-- Filter & Search Controls --}}
    <x-card bordered shadowless class="space-y-4">
        {{-- Search and Reset Row --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="w-full sm:w-80">
                <x-input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari nama laboratorium atau No. Akreditasi..." 
                    icon="magnifying-glass"
                    clearable
                />
            </div>
            @if($filterProcess !== 'ALL' || $filterNotificationStatus !== 'ALL' || $search !== '')
                <x-button color="neutral" light size="sm" wire:click="resetFilters" icon="x-mark">
                    Reset Semua Filter
                </x-button>
            @endif
        </div>

        {{-- Filter Tabs Proses (6 Siklus KAN) --}}
        <div>
            <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider block mb-2">Pilihan Siklus Akreditasi:</span>
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs scrollbar-none">
                <button 
                    type="button" 
                    wire:click="setFilterProcess('ALL')" 
                    @class([
                        'px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors cursor-pointer',
                        'bg-blue-600 text-white shadow-xs' => $filterProcess === 'ALL',
                        'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300' => $filterProcess !== 'ALL',
                    ])
                >
                    Semua Proses ({{ $processCounts['ALL'] }})
                </button>
                <button 
                    type="button" 
                    wire:click="setFilterProcess('AA')" 
                    @class([
                        'px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors cursor-pointer',
                        'bg-blue-600 text-white shadow-xs' => $filterProcess === 'AA',
                        'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300' => $filterProcess !== 'AA',
                    ])
                >
                    Akreditasi Awal ({{ $processCounts['AA'] }})
                </button>
                <button 
                    type="button" 
                    wire:click="setFilterProcess('S1')" 
                    @class([
                        'px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors cursor-pointer',
                        'bg-blue-600 text-white shadow-xs' => $filterProcess === 'S1',
                        'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300' => $filterProcess !== 'S1',
                    ])
                >
                    Survailen 1 ({{ $processCounts['S1'] }})
                </button>
                <button 
                    type="button" 
                    wire:click="setFilterProcess('S2')" 
                    @class([
                        'px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors cursor-pointer',
                        'bg-blue-600 text-white shadow-xs' => $filterProcess === 'S2',
                        'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300' => $filterProcess !== 'S2',
                    ])
                >
                    Survailen 2 ({{ $processCounts['S2'] }})
                </button>
                <button 
                    type="button" 
                    wire:click="setFilterProcess('PRL')" 
                    @class([
                        'px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors cursor-pointer',
                        'bg-blue-600 text-white shadow-xs' => $filterProcess === 'PRL',
                        'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300' => $filterProcess !== 'PRL',
                    ])
                >
                    Perluasan Ruang Lingkup ({{ $processCounts['PRL'] }})
                </button>
                <button 
                    type="button" 
                    wire:click="setFilterProcess('STT')" 
                    @class([
                        'px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors cursor-pointer',
                        'bg-blue-600 text-white shadow-xs' => $filterProcess === 'STT',
                        'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300' => $filterProcess !== 'STT',
                    ])
                >
                    Survailen Tidak Terjadwal ({{ $processCounts['STT'] }})
                </button>
                <button 
                    type="button" 
                    wire:click="setFilterProcess('RA')" 
                    @class([
                        'px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors cursor-pointer',
                        'bg-blue-600 text-white shadow-xs' => $filterProcess === 'RA',
                        'bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300' => $filterProcess !== 'RA',
                    ])
                >
                    Re-Akreditasi ({{ $processCounts['RA'] }})
                </button>
            </div>
        </div>

        {{-- Filter Status Pills --}}
        <div class="flex items-center gap-2 text-xs flex-wrap pt-1 border-t border-neutral-100 dark:border-neutral-800">
            <span class="text-[11px] text-neutral-500 font-medium">Status Urgensi:</span>
            <button 
                type="button" 
                wire:click="setFilterNotificationStatus('ALL')"
                @class([
                    'px-2 py-1 rounded text-xs transition-colors cursor-pointer',
                    'font-bold text-neutral-900 dark:text-white underline decoration-2' => $filterNotificationStatus === 'ALL',
                    'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' => $filterNotificationStatus !== 'ALL',
                ])
            >
                Semua ({{ $statusCounts['ALL'] }})
            </button>
            <button 
                type="button" 
                wire:click="setFilterNotificationStatus('OVERDUE')"
                @class([
                    'px-2 py-1 rounded text-xs transition-colors cursor-pointer',
                    'font-bold text-red-600 underline decoration-2' => $filterNotificationStatus === 'OVERDUE',
                    'text-neutral-600 dark:text-neutral-400 hover:text-red-600' => $filterNotificationStatus !== 'OVERDUE',
                ])
            >
                Terlewat ({{ $statusCounts['OVERDUE'] }})
            </button>
            <button 
                type="button" 
                wire:click="setFilterNotificationStatus('DUE')"
                @class([
                    'px-2 py-1 rounded text-xs transition-colors cursor-pointer',
                    'font-bold text-amber-600 underline decoration-2' => $filterNotificationStatus === 'DUE',
                    'text-neutral-600 dark:text-neutral-400 hover:text-amber-600' => $filterNotificationStatus !== 'DUE',
                ])
            >
                Jatuh Tempo ({{ $statusCounts['DUE'] }})
            </button>
            <button 
                type="button" 
                wire:click="setFilterNotificationStatus('IN_PROGRESS')"
                @class([
                    'px-2 py-1 rounded text-xs transition-colors cursor-pointer',
                    'font-bold text-purple-600 underline decoration-2' => $filterNotificationStatus === 'IN_PROGRESS',
                    'text-neutral-600 dark:text-neutral-400 hover:text-purple-600' => $filterNotificationStatus !== 'IN_PROGRESS',
                ])
            >
                TP Berjalan ({{ $statusCounts['IN_PROGRESS'] }})
            </button>
            <button 
                type="button" 
                wire:click="setFilterNotificationStatus('UPCOMING')"
                @class([
                    'px-2 py-1 rounded text-xs transition-colors cursor-pointer',
                    'font-bold text-blue-600 underline decoration-2' => $filterNotificationStatus === 'UPCOMING',
                    'text-neutral-600 dark:text-neutral-400 hover:text-blue-600' => $filterNotificationStatus !== 'UPCOMING',
                ])
            >
                Menjelang Siklus ({{ $statusCounts['UPCOMING'] }})
            </button>
        </div>
    </x-card>

    {{-- Main Notifications Table --}}
    <x-card bordered shadowless paddingless class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-100 dark:bg-neutral-800/60 text-neutral-600 dark:text-neutral-400 text-xs uppercase font-medium">
                    <tr>
                        <th class="py-3 px-4">Proses & Status</th>
                        <th class="py-3 px-4">Laboratorium Penguji (LP)</th>
                        <th class="py-3 px-4">Masa Sertifikat</th>
                        <th class="py-3 px-4">Batas Siklus / Deadline</th>
                        <th class="py-3 px-4">Keterangan Siklus KAN</th>
                        <th class="py-3 px-4 text-right">Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                    @forelse($notifications as $item)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <x-badge color="blue" sm light>
                                        {{ $item['process_label'] }}
                                    </x-badge>
                                    <x-badge :color="$item['status_color']" sm light>
                                        {{ $item['status_label'] }}
                                    </x-badge>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-neutral-900 dark:text-neutral-100">
                                    <a href="{{ route('lpks.show', $item['lpk_id']) }}" wire:navigate class="hover:text-blue-600 hover:underline">
                                        {{ $item['lpk_name'] }}
                                    </a>
                                </div>
                                <div class="text-xs font-mono font-semibold text-neutral-600 dark:text-neutral-400">
                                    {{ $item['no_akreditasi'] ?? $item['no_reg'] }}
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-xs text-neutral-600 dark:text-neutral-400">
                                @if($item['certificate_date'])
                                    <div>Mulai: {{ \Carbon\Carbon::parse($item['certificate_date'])->translatedFormat('d M Y') }}</div>
                                    <div class="text-[11px] text-neutral-400">Selesai: {{ $item['expired_at'] ? \Carbon\Carbon::parse($item['expired_at'])->translatedFormat('d M Y') : '-' }}</div>
                                @else
                                    <span class="text-neutral-400 italic">Belum bersertifikat</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="text-xs font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ $item['target_date_formatted'] }}
                                </div>
                                <div @class([
                                    'text-[11px] font-medium',
                                    'text-red-600 dark:text-red-400' => $item['status'] === 'OVERDUE',
                                    'text-amber-600 dark:text-amber-400' => $item['status'] === 'DUE',
                                    'text-purple-600 dark:text-purple-400' => $item['status'] === 'IN_PROGRESS',
                                    'text-blue-600 dark:text-blue-400' => $item['status'] === 'UPCOMING',
                                ])>
                                    @if($item['status'] === 'OVERDUE')
                                        Terlewat {{ abs($item['days_remaining']) }} hari
                                    @elseif($item['days_remaining'] > 0)
                                        Sisa {{ $item['days_remaining'] }} hari
                                    @else
                                        Perlu Tindakan
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-xs text-neutral-600 dark:text-neutral-300 max-w-xs">
                                {{ $item['description'] }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($item['has_assessment'])
                                    <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="xs" href="{{ $item['action_url'] }}" wire:navigate>
                                        Detail Asesmen
                                    </x-button>
                                @else
                                    <x-button color="primary" size="xs" href="{{ $item['action_url'] }}" wire:navigate icon="pencil-square">
                                        Input Asesmen
                                    </x-button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-neutral-400 dark:text-neutral-500">
                                    <x-icon name="bell-slash" class="size-10 mb-2 stroke-1" />
                                    <span class="text-sm font-medium">Tidak ada notifikasi proses yang sesuai</span>
                                    <span class="text-xs mt-1">Coba sesuaikan kata kunci pencarian atau filter yang dipilih.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
