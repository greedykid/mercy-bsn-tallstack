<div class="space-y-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-100">
                Daftar Laboratorium Penguji (LP)
            </h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                Direktori kepatuhan siklus akreditasi SNI ISO/IEC 17025:2017 Komite Akreditasi Nasional (KAN)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" wire:click="openImportModal" icon="arrow-up-tray">
                Impor Data LP
            </x-button>
            <x-button color="primary" href="{{ route('lpks.create') }}" wire:navigate icon="plus">
                Tambah LP Baru
            </x-button>
        </div>
    </div>

    {{-- Filter Toolbar Terpadu --}}
    <x-card bordered shadowless class="rounded-xl p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            {{-- Pencarian Cepat --}}
            <div class="sm:col-span-2 lg:col-span-8">
                <x-input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari No. Reg, No. Akreditasi, Nama Lab, Alamat..." 
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            {{-- Filter Status Lembaga --}}
            <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-1.5">
                <div class="w-full">
                    <x-select.styled 
                        wire:model.live="selectedStatus" 
                        :options="$statusOptions" 
                        select="label:label|value:value" 
                        placeholder="Semua Status Lembaga" 
                    />
                </div>
                @if($search || $selectedStatus || $quantity !== 10)
                    <x-button.circle icon="x-mark" size="sm" color="neutral" light wire:click="resetFilters" tooltip="Reset Semua Filter" />
                @endif
            </div>
        </div>

        {{-- Active Filter Badges --}}
        @if($search || $selectedStatus || $quantity !== 10)
            <div class="flex flex-wrap items-center gap-2 pt-3 mt-3 border-t border-neutral-200/80 dark:border-neutral-800">
                <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider flex items-center gap-1.5">
                    <x-icon name="funnel" class="size-3.5 text-neutral-400" />
                    Filter Aktif:
                </span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60 shadow-xs">
                        <x-icon name="magnifying-glass" class="size-3 text-blue-600 dark:text-blue-400" />
                        <span>Pencarian: <strong>"{{ $search }}"</strong></span>
                        <button type="button" wire:click="clearSearch" class="hover:bg-blue-200/60 dark:hover:bg-blue-800 p-0.5 rounded-full text-blue-700 dark:text-blue-300 transition-colors" title="Hapus kata kunci pencarian">
                            <x-icon name="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($selectedStatus)
                    @php
                        $statusEnum = \App\Enums\LpkStatus::tryFrom($selectedStatus);
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 shadow-xs">
                        <span class="size-2 rounded-full {{ match($selectedStatus) {
                            'ACTIVE' => 'bg-emerald-500',
                            'SURVEILLANCE_DUE' => 'bg-amber-500',
                            'SURVEILLANCE_OVERDUE' => 'bg-orange-500',
                            'SUSPENDED' => 'bg-purple-500',
                            'REVOKED' => 'bg-rose-500',
                            default => 'bg-neutral-400'
                        } }}"></span>
                        <span>Status: <strong>{{ $statusEnum?->label() ?? $selectedStatus }}</strong></span>
                        <button type="button" wire:click="clearStatus" class="hover:bg-neutral-200 dark:hover:bg-neutral-700 p-0.5 rounded-full text-neutral-600 dark:text-neutral-300 transition-colors" title="Hapus filter status">
                            <x-icon name="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($quantity !== 10)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-700 shadow-xs">
                        <span>Jumlah: <strong>{{ $quantity }} / halaman</strong></span>
                    </span>
                @endif

                <x-button color="neutral" light size="xs" wire:click="resetFilters" class="border border-neutral-200 dark:border-neutral-700 font-medium ml-1">
                    Reset Semua
                </x-button>
            </div>
        @endif
    </x-card>

    {{-- Banner Aksi Massal (Bulk Selection) --}}
    @if(count($selected) > 0)
        <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 rounded-xl flex flex-wrap items-center justify-between gap-3 text-sm shadow-xs">
            <div class="flex items-center gap-2.5 text-blue-950 dark:text-blue-200 font-semibold">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs text-white font-bold">
                    {{ count($selected) }}
                </span>
                <span>Data laboratorium dipilih</span>
            </div>
            <div class="flex items-center gap-2">
                <x-button size="xs" color="neutral" light wire:click="clearSelection">
                    Batalkan Pilihan
                </x-button>
                <x-button size="xs" color="red" wire:click="confirmDeleteSelected" icon="trash">
                    Hapus Terpilih ({{ count($selected) }})
                </x-button>
            </div>
        </div>
    @endif

    {{-- Bilah Toolbar: Kontrol Quantity & Toggle Mode Tampilan (Tabel / Grid) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 py-1">
        <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Tampilkan:</span>
            <div class="w-32">
                <x-select.styled 
                    wire:model.live="quantity" 
                    :options="[
                        ['label' => '10 / hal', 'value' => 10],
                        ['label' => '25 / hal', 'value' => 25],
                        ['label' => '50 / hal', 'value' => 50],
                        ['label' => '100 / hal', 'value' => 100],
                    ]" 
                    select="label:label|value:value"
                    required
                />
            </div>
            <span class="text-xs text-neutral-400 dark:text-neutral-500 hidden sm:inline">
                ({{ $lpks->total() }} total laboratorium)
            </span>
        </div>

        <div class="flex items-center gap-1 p-1 bg-neutral-100 dark:bg-neutral-800/80 rounded-xl border border-neutral-200/80 dark:border-neutral-700/60 shadow-2xs">
            <button 
                type="button" 
                wire:click="setViewMode('table')" 
                @class([
                    'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
                    'bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 shadow-xs' => $viewMode === 'table',
                    'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' => $viewMode !== 'table',
                ])
                title="Tampilan Tabel"
            >
                <x-icon name="table-cells" class="size-4 text-blue-600 dark:text-blue-400" />
                <span>Tabel</span>
            </button>
            <button 
                type="button" 
                wire:click="setViewMode('grid')" 
                @class([
                    'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
                    'bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 shadow-xs' => $viewMode === 'grid',
                    'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' => $viewMode !== 'grid',
                ])
                title="Tampilan Grid"
            >
                <x-icon name="squares-2x2" class="size-4 text-indigo-600 dark:text-indigo-400" />
                <span>Grid</span>
            </button>
        </div>
    </div>

    @if($viewMode === 'table')
        {{-- Tabel Master TallStackUI --}}
        <x-table 
            :headers="$headers" 
            :rows="$lpks" 
            :sort="$sort"
            selectable
            selectable-property="id"
            wire:model.live="selected"
            link="/lpks/{id}"
            paginate
            paginator="simple"
            loading
        >
            {{-- Kolom 1: NO. AKREDITASI --}}
            @interact('column_no_reg', $row)
                @php
                    $displayCode = $row->registration_number ?: $row->no_reg;
                @endphp
                <div class="font-mono font-bold text-sm text-neutral-900 dark:text-neutral-100 whitespace-nowrap">
                    {{ $displayCode }}
                </div>
            @endinteract

            {{-- Kolom 2: NAMA LPK --}}
            @interact('column_name', $row)
                <div class="min-w-[260px] max-w-lg whitespace-normal">
                    <div class="font-bold text-sm text-neutral-900 dark:text-neutral-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 leading-normal break-words" title="{{ $row->name }}">
                        {{ $row->name }}
                    </div>
                    <div class="mt-2">
                        <x-badge :color="$row->status->color()" sm light>
                            {{ $row->status->label() }}
                        </x-badge>
                    </div>
                </div>
            @endinteract

            {{-- Kolom 3: MASA BERLAKU --}}
            @interact('column_expired_at', $row)
                <div class="flex flex-col gap-1 text-xs whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <span class="text-neutral-400 dark:text-neutral-500 font-normal text-[11px] w-9">Awal:</span>
                        <span class="font-mono font-medium text-neutral-800 dark:text-neutral-200">
                            {{ $row->certificate_date ? $row->certificate_date->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-neutral-400 dark:text-neutral-500 font-normal text-[11px] w-9">Akhir:</span>
                        <span class="font-mono font-semibold {{ $row->isExpired() ? 'text-red-600 dark:text-red-400' : 'text-neutral-900 dark:text-neutral-100' }}">
                            {{ $row->expired_at ? $row->expired_at->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                </div>
            @endinteract

            {{-- Kolom 4: KETERANGAN (Kotak Hijau Siklus & Kotak Kuning Catatan PIC) --}}
            @interact('column_status', $row)
                @php
                    $cycleInfo = app(\App\Services\SurveillanceEngineService::class)->determineActiveCycle($row);
                @endphp
                <div class="flex flex-col gap-2 min-w-[240px] max-w-sm">
                    {{-- Kartu Hijau: Status Siklus & Pelaksanaan --}}
                    <div class="p-2.5 rounded-lg border border-emerald-200 bg-emerald-50/60 dark:border-emerald-800/60 dark:bg-emerald-950/20 text-xs space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded font-semibold text-[11px] bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                {{ $cycleInfo['label'] }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider {{ match($cycleInfo['status']) {
                                'TERLEWAT', 'KEDALUWARSA' => 'text-red-700 dark:text-red-400',
                                'SELESAI' => 'text-blue-700 dark:text-blue-400',
                                default => 'text-emerald-700 dark:text-emerald-400',
                            } }}">
                                <span class="size-1.5 rounded-full {{ match($cycleInfo['status']) {
                                    'TERLEWAT', 'KEDALUWARSA' => 'bg-red-500',
                                    'SELESAI' => 'bg-blue-500',
                                    default => 'bg-emerald-500',
                                } }}"></span>
                                {{ $cycleInfo['status'] }}
                            </span>
                        </div>
                        <div class="text-[11px] text-neutral-700 dark:text-neutral-300 font-medium">
                            {{ $cycleInfo['description'] }}
                        </div>
                    </div>

                    {{-- Kartu Kuning: Catatan PIC --}}
                    <div class="p-2.5 rounded-lg border border-amber-200 bg-amber-50/50 dark:border-amber-800/60 dark:bg-amber-950/20 text-xs flex items-start justify-between gap-2" x-on:click.stop>
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider block mb-0.5">
                                CATATAN PIC
                            </span>
                            <p class="text-neutral-700 dark:text-neutral-300 leading-snug line-clamp-2" title="{{ $row->pic_notes }}">
                                {{ $row->pic_notes ?: 'ruang lingkup diperhatikan' }}
                            </p>
                        </div>
                        <x-button.circle 
                            icon="pencil-square" 
                            size="xs" 
                            color="neutral" 
                            light 
                            wire:click="openPicNoteModal({{ $row->id }})" 
                            tooltip="Edit Catatan PIC" 
                            class="shrink-0 text-amber-700 hover:text-amber-800 dark:text-amber-400"
                        />
                    </div>
                </div>
            @endinteract

            {{-- Kolom 5: Aksi Cepat --}}
            @interact('column_action', $row)
                <div class="flex items-center justify-end gap-1.5 whitespace-nowrap min-w-[148px] ml-auto" x-on:click.stop>
                    <x-button.circle 
                        icon="calendar-days" 
                        color="primary" 
                        light 
                        href="{{ route('assessments.create', ['lpk_id' => $row->id]) }}" 
                        wire:navigate 
                        tooltip="Jadwalkan Asesmen" 
                        class="!size-8 shrink-0 aspect-square rounded-full inline-flex items-center justify-center" 
                    />
                    <x-button.circle 
                        icon="eye" 
                        color="neutral" 
                        light 
                        href="{{ route('lpks.show', $row->id) }}" 
                        wire:navigate 
                        tooltip="Lihat Detail" 
                        class="!size-8 shrink-0 aspect-square rounded-full inline-flex items-center justify-center" 
                    />
                    <x-button.circle 
                        icon="pencil-square" 
                        color="neutral" 
                        light 
                        href="{{ route('lpks.edit', $row->id) }}" 
                        wire:navigate 
                        tooltip="Ubah Profil Lab" 
                        class="!size-8 shrink-0 aspect-square rounded-full inline-flex items-center justify-center" 
                    />
                    <x-button.circle 
                        icon="trash" 
                        color="red" 
                        light 
                        wire:click="confirmDeleteLpk({{ $row->id }})" 
                        tooltip="Hapus Lab" 
                        class="!size-8 shrink-0 aspect-square rounded-full inline-flex items-center justify-center" 
                    />
                </div>
            @endinteract

            <x-slot:empty>
                <div class="py-12 text-center">
                    <div class="max-w-xs mx-auto text-neutral-400 dark:text-neutral-500">
                        <x-icon name="building-office-2" class="size-10 mx-auto stroke-1" />
                        <p class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">Tidak ada data Laboratorium Penguji</p>
                        <p class="text-xs text-neutral-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status kepatuhan.</p>
                    </div>
                </div>
            </x-slot:empty>
        </x-table>
    @else
        {{-- Tampilan Mode Grid / Kartu Responsif --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" wire:loading.class="opacity-60">
            @forelse($lpks as $lpk)
                @php
                    $displayCode = $lpk->registration_number ?: $lpk->no_reg;
                    $cycleInfo = $this->calculateCycleInfo($lpk);
                    $isSelected = in_array((string)$lpk->id, array_map('strval', $selected), true);
                @endphp
                <div @class([
                    'relative flex flex-col justify-between p-4 rounded-xl border transition-all duration-200 bg-white dark:bg-neutral-900 shadow-xs hover:shadow-md',
                    'border-blue-500 ring-2 ring-blue-500/30' => $isSelected,
                    'border-neutral-200/90 dark:border-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700' => !$isSelected,
                ]) wire:key="lpk-card-{{ $lpk->id }}">
                    <div>
                        {{-- Card Header: Checkbox + No Akreditasi + Status Badge --}}
                        <div class="flex items-start justify-between gap-2 pb-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div class="flex items-center gap-2.5">
                                <input 
                                    type="checkbox" 
                                    value="{{ $lpk->id }}" 
                                    wire:model.live="selected"
                                    class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500 size-4 cursor-pointer"
                                />
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ $displayCode }}
                                    </span>
                                    @if($lpk->registration_number && $lpk->no_reg && $lpk->registration_number !== $lpk->no_reg)
                                        <span class="text-[10px] font-mono text-neutral-400">
                                            No. Reg: {{ $lpk->no_reg }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <x-badge :color="$lpk->status->color()" sm light>
                                    {{ $lpk->status->label() }}
                                </x-badge>
                            </div>
                        </div>

                        {{-- Lab Name --}}
                        <div class="py-3">
                            <a 
                                href="{{ route('lpks.show', $lpk->id) }}" 
                                wire:navigate 
                                class="font-bold text-sm text-neutral-900 dark:text-neutral-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors leading-snug block line-clamp-2" 
                                title="{{ $lpk->name }}"
                            >
                                {{ $lpk->name }}
                            </a>
                            <div class="mt-2 flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                                <x-icon name="map-pin" class="size-3.5 shrink-0 text-neutral-400" />
                                <span class="truncate">{{ $lpk->city ?: ($lpk->address ?: 'Lokasi belum diisi') }}</span>
                            </div>
                        </div>

                        {{-- Validity Info & Cycle Status --}}
                        <div class="space-y-2 py-2.5 border-t border-dashed border-neutral-100 dark:border-neutral-800 text-xs">
                            <div class="flex items-center justify-between text-neutral-600 dark:text-neutral-400">
                                <span class="text-[11px] text-neutral-400">Masa Sertifikat:</span>
                                <span class="font-mono text-[11px]">
                                    {{ $lpk->certificate_date ? $lpk->certificate_date->format('d/m/Y') : '-' }} s/d {{ $lpk->expired_at ? $lpk->expired_at->format('d/m/Y') : '-' }}
                                </span>
                            </div>

                            {{-- Cycle Box --}}
                            <div class="p-2 rounded-lg border text-xs {{ match($cycleInfo['status']) {
                                'TERLEWAT', 'KEDALUWARSA' => 'border-red-200 bg-red-50/50 dark:border-red-900/40 dark:bg-red-950/20 text-red-900 dark:text-red-300',
                                'SELESAI' => 'border-blue-200 bg-blue-50/50 dark:border-blue-900/40 dark:bg-blue-950/20 text-blue-900 dark:text-blue-300',
                                default => 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/40 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-300',
                            } }}">
                                <div class="flex items-center justify-between font-bold text-[10px] uppercase">
                                    <span>{{ $cycleInfo['label'] }}</span>
                                    <span>{{ $cycleInfo['status'] }}</span>
                                </div>
                                <div class="text-[11px] mt-0.5 font-medium leading-tight">
                                    {{ $cycleInfo['description'] }}
                                </div>
                            </div>

                            {{-- PIC Notes preview --}}
                            @if($lpk->pic_notes)
                                <div class="p-2 rounded-lg bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40 text-[11px] text-amber-900 dark:text-amber-300">
                                    <span class="font-bold text-[10px] uppercase block">Catatan PIC:</span>
                                    <p class="line-clamp-1 mt-0.5">{{ $lpk->pic_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Card Actions Footer --}}
                    <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between gap-2 mt-2">
                        <x-button 
                            size="xs" 
                            color="primary" 
                            light 
                            href="{{ route('assessments.create', ['lpk_id' => $lpk->id]) }}" 
                            wire:navigate 
                            icon="calendar-days"
                        >
                            Jadwalkan
                        </x-button>

                        <div class="flex items-center gap-1.5">
                            <x-button.circle 
                                icon="eye" 
                                color="neutral" 
                                light 
                                href="{{ route('lpks.show', $lpk->id) }}" 
                                wire:navigate 
                                tooltip="Lihat Detail" 
                                class="!size-8 shrink-0 aspect-square rounded-full inline-flex items-center justify-center" 
                            />
                            <x-button.circle 
                                icon="pencil-square" 
                                color="neutral" 
                                light 
                                href="{{ route('lpks.edit', $lpk->id) }}" 
                                wire:navigate 
                                tooltip="Ubah Profil Lab" 
                                class="!size-8 shrink-0 aspect-square rounded-full inline-flex items-center justify-center" 
                            />
                            <x-button.circle 
                                icon="trash" 
                                color="red" 
                                light 
                                wire:click="confirmDeleteLpk({{ $lpk->id }})" 
                                tooltip="Hapus Lab" 
                                class="!size-8 shrink-0 aspect-square rounded-full inline-flex items-center justify-center" 
                            />
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800">
                    <div class="max-w-xs mx-auto text-neutral-400 dark:text-neutral-500">
                        <x-icon name="building-office-2" class="size-10 mx-auto stroke-1" />
                        <p class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">Tidak ada data Laboratorium Penguji</p>
                        <p class="text-xs text-neutral-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status kepatuhan.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination Footer Mode Grid --}}
        <div class="pt-2">
            {{ $lpks->links() }}
        </div>
    @endif


    {{-- Modal Edit Catatan PIC --}}
    <x-modal id="pic-note-modal" wire="showPicNoteModal" title="Edit Catatan Khusus PIC" center>
        <div class="space-y-4">
            <x-alert color="blue" light>
                Laboratorium: <strong class="font-mono">{{ $selectedLpkNoRegForNote }}</strong>
            </x-alert>

            <x-textarea 
                wire:model="picNoteContent" 
                label="Catatan Teknis & Koordinasi Internal *" 
                rows="4" 
                placeholder="Tuliskan catatan teknis pendampingan laboratorium penguji..." 
            />
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end gap-2">
                <x-button color="neutral" light wire:click="$set('showPicNoteModal', false)">
                    Batal
                </x-button>
                <x-button color="primary" wire:click="savePicNote">
                    Simpan Catatan
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>

    {{-- Modal Impor LP (CSV, Excel, Google Sheets) --}}
    <x-modal id="import-lpk-modal" wire="showImportModal" title="Impor Data Laboratorium Penguji (LP)" center size="lg">
        <div class="space-y-4">
            <div class="rounded-lg bg-blue-50/70 p-3 text-xs text-blue-900 dark:bg-blue-950/40 dark:text-blue-300 space-y-2 border border-blue-200/60 dark:border-blue-900/40">
                <div class="font-semibold text-blue-900 dark:text-blue-200">Panduan Kolom Header Import (Format Resmi KAN):</div>
                <div class="text-[11px] leading-relaxed">
                    <span class="font-medium text-neutral-800 dark:text-neutral-200">1. Identitas LP:</span> 
                    <span class="font-mono text-blue-800 dark:text-blue-300 font-semibold">nama_lpk, jenis_akreditasi, no_reg_lpk, no_akreditasi</span>
                    <span class="text-neutral-500 dark:text-neutral-400 block text-[10px] mt-0.5">(no_reg_lpk berupa angka seperti 344, no_akreditasi berupa kode resmi seperti LP-101-IDN)</span>
                </div>
                <div class="text-[11px] leading-relaxed">
                    <span class="font-medium text-neutral-800 dark:text-neutral-200">2. Masa Akreditasi & Alamat (Opsi 1):</span> 
                    <span class="font-mono text-blue-800 dark:text-blue-300 font-semibold">tgl_sertifikat, tgl_kedaluwarsa, alamat</span>
                    <span class="text-neutral-500 dark:text-neutral-400 block text-[10px] mt-0.5">(alamat mencakup satu kesatuan teks jalan, gedung, kota, dan provinsi)</span>
                </div>
                <div class="text-[11px] leading-relaxed">
                    <span class="font-medium text-neutral-800 dark:text-neutral-200">3. Data Kunjungan Asesmen:</span> 
                    <span class="font-mono text-blue-800 dark:text-blue-300 font-semibold">tgl_asesmen, tgl_selesai_asesmen, jenis_asesmen, tim_asesmen, batas_tp</span>
                </div>
                <div class="text-[10px] text-neutral-700 dark:text-neutral-300 bg-white/70 dark:bg-neutral-900/60 p-2.5 rounded-md border border-blue-100 dark:border-neutral-800">
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 block mb-1">Pilihan 8 Jenis Asesmen Resmi KAN:</span>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 font-mono text-[10px] text-neutral-700 dark:text-neutral-300">
                        <span>• AA (Akreditasi Awal)</span>
                        <span>• S1 (Survailen 1)</span>
                        <span>• S1_PRL (Survailen 1 + PRL)</span>
                        <span>• S2 (Survailen 2)</span>
                        <span>• S2_PRL (Survailen 2 + PRL)</span>
                        <span>• STT (Survailen Tidak Terjadwal)</span>
                        <span>• PRL (Perluasan Ruang Lingkup)</span>
                        <span>• RA (Re-Akreditasi)</span>
                    </div>
                </div>
                @if(!auth()->user()?->isAdmin())
                    <div class="mt-1 text-[11px] font-medium text-emerald-700 dark:text-emerald-400">
                        Data LP baru yang diimpor akan otomatis dihubungkan ke akun PIC Anda.
                    </div>
                @endif
            </div>

            {{-- Mode Selector --}}
            <div class="grid grid-cols-3 gap-2">
                <button 
                    type="button" 
                    wire:click="$set('importMode', 'file')"
                    class="flex flex-col items-center justify-center p-3 text-xs font-semibold rounded-lg border transition-colors {{ $importMode === 'file' ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 dark:border-primary-500' : 'bg-white dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50' }}"
                >
                    <x-icon name="document-arrow-up" class="h-5 w-5 mb-1" />
                    <span>Upload Berkas</span>
                    <span class="text-[10px] font-normal opacity-75">.csv / .xlsx</span>
                </button>
                <button 
                    type="button" 
                    wire:click="$set('importMode', 'link')"
                    class="flex flex-col items-center justify-center p-3 text-xs font-semibold rounded-lg border transition-colors {{ $importMode === 'link' ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 dark:border-primary-500' : 'bg-white dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50' }}"
                >
                    <x-icon name="link" class="h-5 w-5 mb-1" />
                    <span>Google Sheets</span>
                    <span class="text-[10px] font-normal opacity-75">Tautan Publik</span>
                </button>
                <button 
                    type="button" 
                    wire:click="$set('importMode', 'paste')"
                    class="flex flex-col items-center justify-center p-3 text-xs font-semibold rounded-lg border transition-colors {{ $importMode === 'paste' ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 dark:border-primary-500' : 'bg-white dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50' }}"
                >
                    <x-icon name="clipboard-document-list" class="h-5 w-5 mb-1" />
                    <span>Tempel Teks</span>
                    <span class="text-[10px] font-normal opacity-75">CSV Raw</span>
                </button>
            </div>

            @if($importMode === 'file')
                <div class="space-y-2">
                    <x-upload 
                        wire:model="importFile" 
                        label="Pilih Berkas CSV atau Excel (.xlsx)" 
                        hint="Format yang didukung: .csv, .xlsx, .xls (Maksimal 10 MB)"
                        accept=".csv, .xlsx, .xls, text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    />
                    <div wire:loading wire:target="importFile" class="text-xs text-blue-600 dark:text-blue-400 flex items-center gap-1.5 font-medium mt-1">
                        <x-icon name="arrow-path" class="w-4 h-4 animate-spin" /> Sedang membaca berkas, mohon tunggu...
                    </div>
                    @error('importFile')
                        <p class="text-xs text-red-600 dark:text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @elseif($importMode === 'link')
                <div class="space-y-2">
                    <x-input 
                        wire:model="googleSheetsUrl" 
                        label="Tautan Spreadsheet Google Sheets" 
                        placeholder="https://docs.google.com/spreadsheets/d/.../edit?usp=sharing"
                        hint="Pastikan tautan Google Sheets memiliki izin akses 'Siapa saja yang memiliki link dapat melihat' (Viewer)."
                    />
                </div>
            @else
                <div class="space-y-2">
                    <x-textarea 
                        wire:model="csvContent" 
                        label="Konten Teks CSV"
                        placeholder="nama_lpk,jenis_akreditasi,no_reg_lpk,no_akreditasi,alamat,tgl_sertifikat,tgl_asesmen,jenis_asesmen,tim_asesmen&#10;Balai Uji Mutu Bahan,LP,344,LP-101-IDN,&quot;Jl. M.H. Thamrin No. 8, Jakarta Pusat, DKI Jakarta&quot;,2024-01-15,2025-02-10,S1,Dr. Hendra Gunawan" 
                        rows="6" 
                        class="font-mono text-xs" 
                    />
                </div>
            @endif
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end gap-2">
                <x-button color="neutral" light wire:click="closeImportModal">
                    Batal
                </x-button>
                <x-button 
                    color="primary" 
                    wire:click="executeImport" 
                    wire:target="importFile,executeImport" 
                    wire:loading.attr="disabled"
                    loading="executeImport"
                >
                    Mulai Impor Data
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
