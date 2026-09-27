<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">Dasbor Pemantauan Akreditasi</h1>
            <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                Komite Akreditasi Nasional (KAN) dan Badan Standardisasi Nasional (BSN)
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium relative" href="{{ route('deadlines.index') }}" wire:navigate icon="bell">
                Notifikasi Deadline
                @if($urgentNotificationsCount > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-red-600 text-white">
                        {{ $urgentNotificationsCount }}
                    </span>
                @endif
            </x-button>
            <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" href="{{ route('calendar') }}" wire:navigate icon="calendar">
                Kalender Siklus
            </x-button>
            <x-button color="primary" href="{{ route('assessments.create') }}" wire:navigate icon="clipboard-document-check">
                Input Realisasi Asesmen
            </x-button>
        </div>
    </div>

    {{-- 4 Top Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        {{-- Card 1: Total LPK --}}
        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Total Laboratorium Penguji</span>
                <x-icon name="building-office-2" class="size-5 text-neutral-400" />
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">{{ $totalLpks }}</span>
                <span class="text-xs text-neutral-500">lembaga binaan</span>
            </div>
        </x-card>

        {{-- Card 2: LPK Aktif --}}
        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Status Aktif dan Patuh</span>
                <x-badge color="emerald" text="Patuh" />
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-700 dark:text-emerald-400">{{ $activeLpks }}</span>
                <span class="text-xs text-neutral-500">LP memenuhi</span>
            </div>
        </x-card>

        {{-- Card 3: Jatuh Tempo Surveilen --}}
        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Tenggat Surveilen (S1/S2)</span>
                <x-badge color="amber" text="Bulan 14/35" />
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">{{ $dueLpks }}</span>
                <span class="text-xs text-neutral-500">perlu agenda</span>
            </div>
        </x-card>

        {{-- Card 4: Status Dibekukan --}}
        <x-card bordered shadowless>
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold tracking-wider text-neutral-500">Dibekukan Sementara</span>
                <x-badge color="purple" text="1 Tahun Max" />
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold tracking-tight text-purple-700 dark:text-purple-400">{{ $suspendedLpks }}</span>
                <span class="text-xs text-neutral-500">tenggat berjalan</span>
            </div>
        </x-card>
    </div>

    {{-- Main Grid: 2 Columns on Desktop --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left Column: Pelacakan Asesmen Lapangan Terkini --}}
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-neutral-100">Pelacakan Asesmen Lapangan Terkini</h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Realisasi asesmen dan batas waktu tindakan perbaikan (TP)</p>
                </div>
                <x-button color="neutral" light size="sm" href="{{ route('assessments.index') }}" wire:navigate>
                    Lihat Semua &rarr;
                </x-button>
            </div>

            <x-card bordered shadowless paddingless class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-neutral-100 dark:bg-neutral-800/60 text-neutral-600 dark:text-neutral-400 text-xs uppercase font-medium">
                            <tr>
                                <th class="py-3 px-4">No. Akreditasi</th>
                                <th class="py-3 px-4">Proses dan Lembaga</th>
                                <th class="py-3 px-4">Tanggal Realisasi</th>
                                <th class="py-3 px-4">Batas Waktu TP</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            @forelse($recentAssessments as $ass)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="py-3 px-4 font-mono font-semibold text-neutral-900 dark:text-neutral-100 whitespace-nowrap">
                                        {{ $ass->lpk?->registration_number ?: ($ass->lpk?->no_reg ?? '-') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $ass->assessment_type->label() }}</div>
                                        <div class="text-xs text-neutral-500 line-clamp-1">{{ $ass->lpk?->name }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-xs text-neutral-600 dark:text-neutral-300">
                                        {{ $ass->start_at->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if($ass->isTpSatisfied())
                                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Selesai (Memenuhi)</span>
                                        @else
                                            <div class="text-xs font-medium text-neutral-800 dark:text-neutral-200">
                                                {{ $ass->effectiveTpDueDate()?->translatedFormat('d M Y') ?? 'Belum ditentukan' }}
                                            </div>
                                            @if($ass->hasExtension())
                                                <span class="text-[10px] text-purple-600 dark:text-purple-400">+1 bln (ext)</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if($ass->isTpSatisfied())
                                            <x-badge color="emerald" text="Selesai" />
                                        @elseif($ass->effectiveTpDueDate() && now()->greaterThan($ass->effectiveTpDueDate()))
                                            <x-badge color="red" text="Terlewat" />
                                        @else
                                            <x-badge color="amber" text="Proses" />
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" href="{{ route('assessments.show', $ass->id) }}" wire:navigate>
                                            Detail
                                        </x-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-neutral-500 text-xs">
                                        Belum ada data asesmen lapangan yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- Right Column: Ringkasan Deadline, Agenda & Quick Links --}}
        <div class="lg:col-span-5 xl:col-span-4 space-y-6">
            {{-- Ringkasan Notifikasi Deadline Siklus Berjalan --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <x-icon name="bell-alert" class="size-4 text-blue-600 dark:text-blue-400" />
                        <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Deadline Siklus KAN</h2>
                    </div>
                    <a href="{{ route('deadlines.index') }}" wire:navigate class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        Buka Semua &rarr;
                    </a>
                </div>

                <x-card bordered shadowless class="space-y-3">
                    @forelse($urgentNotifications as $item)
                        <div class="pb-3 border-b border-neutral-100 dark:border-neutral-800 last:border-none last:pb-0 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                        {{ $item['process_label'] }}
                                    </span>
                                    <span @class([
                                        'text-[10px] font-semibold px-1.5 py-0.5 rounded',
                                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $item['status'] === 'OVERDUE',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $item['status'] === 'DUE',
                                    ])>
                                        {{ $item['status_label'] }}
                                    </span>
                                </div>
                                <span class="text-[11px] font-medium {{ $item['status'] === 'OVERDUE' ? 'text-red-600' : 'text-amber-600' }}">
                                    @if($item['status'] === 'OVERDUE')
                                        Terlewat {{ abs($item['days_remaining']) }} hr
                                    @else
                                        Sisa {{ $item['days_remaining'] }} hr
                                    @endif
                                </span>
                            </div>
                            <div class="text-xs font-semibold text-neutral-900 dark:text-neutral-100 line-clamp-1">
                                <a href="{{ route('lpks.show', $item['lpk_id']) }}" wire:navigate class="hover:underline hover:text-blue-600">
                                    {{ $item['lpk_name'] }}
                                </a>
                            </div>
                            <div class="text-[11px] text-neutral-500 flex items-center justify-between">
                                <span>Batas: {{ $item['target_date_formatted'] }}</span>
                                <a href="{{ $item['action_url'] }}" wire:navigate class="text-blue-600 hover:underline">
                                    Tindak Lanjut
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-neutral-500">
                            Semua siklus akreditasi dalam kondisi patuh dan terjadwal.
                        </div>
                    @endforelse

                    <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800 text-center">
                        <x-button color="neutral" light class="w-full text-xs justify-center" href="{{ route('deadlines.index') }}" wire:navigate>
                            Lihat Seluruh Notifikasi ({{ $totalNotificationsCount }}) &rarr;
                        </x-button>
                    </div>
                </x-card>
            </div>

            {{-- Upcoming Agenda Card --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Agenda Mendatang</h2>
                    <x-button color="neutral" light size="sm" href="{{ route('calendar') }}" wire:navigate>
                        Kalender &rarr;
                    </x-button>
                </div>

                <x-card bordered shadowless class="space-y-3">
                    @forelse($upcomingEvents as $evt)
                        <div class="flex items-start gap-3 pb-3 border-b border-neutral-100 dark:border-neutral-800 last:border-none last:pb-0">
                            <div class="flex flex-col items-center justify-center p-2 rounded-md bg-neutral-100 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 shrink-0 min-w-12 text-center">
                                <span class="text-[10px] uppercase font-medium text-neutral-500">{{ $evt->start_at->translatedFormat('M') }}</span>
                                <span class="text-base font-bold leading-none">{{ $evt->start_at->translatedFormat('d') }}</span>
                            </div>
                            <div class="space-y-0.5 min-w-0">
                                <div class="text-xs font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                    {{ $evt->title }}
                                </div>
                                <div class="text-[11px] text-neutral-500 truncate">
                                    {{ $evt->description ?? $evt->event_type->label() }}
                                </div>
                                <x-badge color="{{ $evt->color ?? 'neutral' }}" size="sm" text="{{ $evt->event_type->label() }}" />
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-neutral-500">
                            Tidak ada agenda terjadwal dalam waktu dekat.
                        </div>
                    @endforelse
                </x-card>
            </div>

            {{-- Quick Links Card --}}
            <x-card bordered shadowless class="space-y-2">
                <span class="text-xs font-medium uppercase tracking-wider text-neutral-500">Tautan Akses Cepat</span>
                <div class="flex flex-col gap-1.5 pt-1">
                    <x-button color="neutral" light class="justify-start text-xs" href="{{ route('deadlines.index') }}" wire:navigate icon="bell">
                        Pusat Notifikasi Deadline Siklus
                    </x-button>
                    <x-button color="neutral" light class="justify-start text-xs" href="{{ route('lpks.index') }}" wire:navigate icon="building-office">
                        Master Database LP Binaan
                    </x-button>
                    <x-button color="neutral" light class="justify-start text-xs" href="{{ route('assessments.create') }}" wire:navigate icon="pencil-square">
                        Input Realisasi Asesmen
                    </x-button>
                    <x-button color="neutral" light class="justify-start text-xs" href="{{ route('calendar') }}" wire:navigate icon="calendar-days">
                        Kalender Agenda dan Batas Waktu
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
</div>