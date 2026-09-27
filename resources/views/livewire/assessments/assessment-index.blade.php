<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">Rekapitulasi Asesmen Lapangan</h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                Pemantauan proses asesmen KAN, tindakan perbaikan, dan evaluasi hasil asesmen laboratorium penguji
            </p>
        </div>
        <div>
            <x-button color="primary" href="{{ route('assessments.create') }}" wire:navigate icon="plus">
                Input Realisasi Baru
            </x-button>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <x-card bordered shadowless class="rounded-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-center">
            <div class="sm:col-span-2">
                <x-input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari judul asesmen, no registrasi, asesor, atau lokasi..." 
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            <div>
                <x-select.styled 
                    wire:model.live="selectedType" 
                    :options="$typeOptions" 
                    select="label:label|value:value" 
                    placeholder="Semua Tipe Asesmen" 
                />
            </div>

            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <x-select.styled 
                        wire:model.live="selectedStatus" 
                        :options="$statusOptions" 
                        select="label:label|value:value" 
                        placeholder="Semua Status" 
                    />
                </div>

                @if($search || $selectedType || $selectedStatus)
                    <x-button color="neutral" light size="sm" wire:click="resetFilters" tooltip="Reset Filter" icon="x-mark" />
                @endif
            </div>
        </div>

        {{-- Active Filter Badges --}}
        @if($search || $selectedType || $selectedStatus)
            <div class="flex flex-wrap items-center gap-2 pt-3 mt-3 border-t border-neutral-200/80 dark:border-neutral-800">
                <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider flex items-center gap-1.5">
                    <x-icon name="funnel" class="size-3.5 text-neutral-400" />
                    Filter Aktif:
                </span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60 shadow-xs">
                        <x-icon name="magnifying-glass" class="size-3 text-blue-600 dark:text-blue-400" />
                        <span>Pencarian: <strong>"{{ $search }}"</strong></span>
                        <button type="button" wire:click="clearSearch" class="hover:bg-blue-200/60 dark:hover:bg-blue-800 p-0.5 rounded-full text-blue-700 dark:text-blue-300 transition-colors" title="Hapus pencarian">
                            <x-icon name="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($selectedType)
                    @php
                        $typeEnum = \App\Enums\AssessmentType::tryFrom($selectedType);
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-xs">
                        <x-icon name="clipboard-document-check" class="size-3 text-emerald-600 dark:text-emerald-400" />
                        <span>Tipe: <strong>{{ $typeEnum?->label() ?? $selectedType }}</strong></span>
                        <button type="button" wire:click="clearType" class="hover:bg-emerald-200/60 dark:hover:bg-emerald-800 p-0.5 rounded-full text-emerald-700 dark:text-emerald-300 transition-colors" title="Hapus filter tipe">
                            <x-icon name="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($selectedStatus)
                    @php
                        $statusLabelsMap = [
                            'PLANNED' => 'Direncanakan',
                            'IN_PROGRESS' => 'Sedang Berlangsung',
                            'REPORT_SUBMITTED' => 'Laporan Diterima',
                            'EHA_MEETING' => 'Sidang EHA',
                            'SK_ISSUED' => 'SK Diterbitkan',
                            'COMPLETED' => 'Selesai',
                            'CANCELLED' => 'Dibatalkan',
                        ];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 shadow-xs">
                        <span class="size-2 rounded-full {{ match($selectedStatus) {
                            'PLANNED' => 'bg-amber-500',
                            'IN_PROGRESS' => 'bg-blue-500',
                            'REPORT_SUBMITTED' => 'bg-indigo-500',
                            'EHA_MEETING' => 'bg-purple-500',
                            'SK_ISSUED' => 'bg-emerald-500',
                            'COMPLETED' => 'bg-teal-500',
                            default => 'bg-neutral-400'
                        } }}"></span>
                        <span>Status: <strong>{{ $statusLabelsMap[$selectedStatus] ?? $selectedStatus }}</strong></span>
                        <button type="button" wire:click="clearStatus" class="hover:bg-neutral-200 dark:hover:bg-neutral-700 p-0.5 rounded-full text-neutral-600 dark:text-neutral-300 transition-colors" title="Hapus filter status">
                            <x-icon name="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                <x-button color="neutral" light size="xs" wire:click="resetFilters" class="border border-neutral-200 dark:border-neutral-700 font-medium ml-1">
                    Reset Semua
                </x-button>
            </div>
        @endif
    </x-card>

    {{-- Main Table --}}
    <x-card bordered shadowless paddingless class="rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-100 dark:bg-neutral-800/60 text-neutral-600 dark:text-neutral-400 text-xs uppercase font-medium border-b border-neutral-200 dark:border-neutral-800">
                    <tr>
                        <th class="py-3 px-4">Laboratorium Penguji</th>
                        <th class="py-3 px-4">Proses & Agenda Asesmen</th>
                        <th class="py-3 px-4">Tanggal Realisasi</th>
                        <th class="py-3 px-4">Tim Asesmen</th>
                        <th class="py-3 px-4">Batas Waktu TP</th>
                        <th class="py-3 px-4 text-center">Temuan Tertutup</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800" wire:loading.class="opacity-50">
                    @forelse($assessments as $ass)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ route('lpks.show', $ass->lpk_id) }}" class="font-mono font-semibold text-neutral-900 dark:text-neutral-100 hover:underline" wire:navigate>
                                    {{ $ass->lpk?->registration_number && $ass->lpk?->registration_number !== $ass->lpk?->no_reg ? $ass->lpk?->registration_number : $ass->lpk?->no_reg }}
                                </a>
                                <div class="text-[11px] text-neutral-400 truncate max-w-44">{{ $ass->lpk?->name }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('assessments.show', $ass->id) }}" class="font-medium text-neutral-900 dark:text-neutral-100 hover:underline" wire:navigate>
                                    {{ $ass->title }}
                                </a>
                                <div class="mt-1">
                                    <x-badge color="neutral" outline size="sm" text="{{ $ass->assessment_type->label() }}" />
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-neutral-700 dark:text-neutral-300">
                                {{ $ass->start_at->translatedFormat('d M Y') }}
                                <div class="text-[11px] text-neutral-400">s/d {{ $ass->end_at->translatedFormat('d M Y') }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-neutral-700 dark:text-neutral-300">
                                <div class="font-medium">{{ $ass->lead_assessor ?? 'Belum Ditugaskan' }}</div>
                                <div class="text-[11px] text-neutral-400">{{ $ass->location ?? 'Tidak ada lokasi' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                @if($ass->isTpSatisfied())
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Selesai (Memenuhi)</span>
                                @else
                                    <div class="font-medium text-neutral-800 dark:text-neutral-200">
                                        {{ $ass->effectiveTpDueDate()?->translatedFormat('d M Y') ?? 'Belum ditentukan' }}
                                    </div>
                                    @if($ass->hasExtension())
                                        <span class="text-[10px] text-purple-600 dark:text-purple-400 font-medium">Perpanjangan +1 bln</span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($ass->tp_findings_total > 0)
                                    <div class="text-xs font-semibold {{ $ass->tp_findings_closed >= $ass->tp_findings_total ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $ass->tp_findings_closed }} / {{ $ass->tp_findings_total }}
                                    </div>
                                @else
                                    <span class="text-xs text-neutral-400">0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($ass->isTpSatisfied())
                                    <x-badge color="emerald" text="Selesai" />
                                @elseif($ass->effectiveTpDueDate() && now()->greaterThan($ass->effectiveTpDueDate()))
                                    <x-badge color="red" text="Terlewat" />
                                @else
                                    <x-badge color="amber" text="Proses" />
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" href="{{ route('assessments.show', $ass->id) }}" wire:navigate>
                                        Kelola
                                    </x-button>
                                    <x-button.circle 
                                        color="red" 
                                        light 
                                        size="sm" 
                                        icon="trash"
                                        wire:click="confirmDeleteAssessment({{ $ass->id }})"
                                        tooltip="Hapus Agenda"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <x-icon name="clipboard-document-check" class="size-8 text-neutral-400" />
                                    <div class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Belum ada asesmen lapangan</div>
                                    <div class="text-xs text-neutral-500">Mulai catat pelaksanaan riil asesmen untuk memantau batas waktu tindakan perbaikan.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assessments->hasPages())
            <div class="p-4 border-t border-neutral-200 dark:border-neutral-800">
                {{ $assessments->links() }}
            </div>
        @endif
    </x-card>
</div>
