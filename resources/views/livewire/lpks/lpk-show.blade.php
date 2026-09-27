<div class="space-y-6">
    {{-- Header Rincian Lab --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-neutral-200 dark:border-neutral-800">
        <div class="space-y-1.5 min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ $lpk->registration_number && $lpk->registration_number !== $lpk->no_reg ? $lpk->registration_number : $lpk->no_reg }}
                </span>
                @if($lpk->registration_number && $lpk->no_reg && $lpk->registration_number !== $lpk->no_reg)
                    <span class="text-xs font-mono px-2 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 font-semibold border border-neutral-200 dark:border-neutral-700">
                        No. Reg: {{ $lpk->no_reg }}
                    </span>
                @endif
                <x-badge color="blue" sm light>
                    Laboratorium Penguji
                </x-badge>
                <x-badge :color="$dynamicStatus->color()" sm light>
                    {{ $dynamicStatus->label() }}
                </x-badge>
            </div>
            <h1 class="text-lg sm:text-xl font-semibold text-neutral-900 dark:text-neutral-100 break-words">
                {{ $lpk->name }}
            </h1>
            <div class="text-xs text-neutral-500 dark:text-neutral-400 break-words">
                Standar: <span class="font-medium text-neutral-700 dark:text-neutral-300">SNI ISO/IEC 17025:2017</span>
                &bull; Masa Sertifikat: {{ $lpk->certificate_date ? $lpk->certificate_date->format('d/m/Y') : '-' }} s/d {{ $lpk->expired_at ? $lpk->expired_at->format('d/m/Y') : '-' }}
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <x-button size="sm" color="neutral" light href="{{ route('lpks.index') }}" wire:navigate icon="chevron-left">
                Daftar LP
            </x-button>
            @if($lpk->drive_url)
                <x-button size="sm" color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" href="{{ $lpk->drive_url }}" target="_blank" icon="arrow-top-right-on-square">
                    Google Drive
                </x-button>
            @endif
            <x-button size="sm" color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" href="{{ route('lpks.edit', $lpk->id) }}" wire:navigate icon="pencil-square">
                Ubah Profil
            </x-button>
            <x-button size="sm" color="primary" href="{{ route('assessments.create', ['lpk_id' => $lpk->id]) }}" wire:navigate icon="plus">
                Tambah Asesmen
            </x-button>
            <x-button size="sm" color="red" light class="border border-red-200 dark:border-red-900 font-medium" wire:click="confirmDeleteLpk" icon="trash">
                Hapus
            </x-button>
        </div>
    </div>

    {{-- Banner Pembekuan jika berstatus SUSPENDED --}}
    @if($suspensionCountdown && ! $suspensionCountdown['is_expired'])
        <x-alert color="purple" light icon="clock" title="Masa Toleransi Pembekuan 1 Tahun Berjalan">
            Tersisa {{ $suspensionCountdown['months'] }} bulan {{ $suspensionCountdown['days'] }} hari untuk menyelesaikan seluruh kewajiban surveilen dan tindakan perbaikan. Jika batas waktu ini terlewati tanpa pemenuhan, status akreditasi akan otomatis DICABUT (REVOKED).
        </x-alert>
    @endif

    {{-- Notifikasi Deadline Proses Siklus Akreditasi --}}
    @if(isset($lpkNotifications) && $lpkNotifications->isNotEmpty())
        <div class="space-y-2.5">
            @foreach($lpkNotifications as $notif)
                <x-alert 
                    :color="$notif['status_color']" 
                    light 
                    :icon="$notif['status'] === 'OVERDUE' ? 'exclamation-circle' : ($notif['status'] === 'DUE' ? 'exclamation-triangle' : 'clock')"
                    :title="$notif['process_label'] . ': ' . $notif['status_label'] . ' (Target: ' . $notif['target_date_formatted'] . ')'"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mt-1">
                        <span class="text-xs break-words">{{ $notif['description'] }}</span>
                        @if($notif['has_assessment'])
                            <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium shrink-0 self-start sm:self-auto" size="xs" href="{{ $notif['action_url'] }}" wire:navigate>
                                Lihat Asesmen
                            </x-button>
                        @else
                            <x-button color="primary" size="xs" class="shrink-0 self-start sm:self-auto" href="{{ $notif['action_url'] }}" wire:navigate icon="pencil-square">
                                Input Asesmen {{ $notif['process_code'] }}
                            </x-button>
                        @endif
                    </div>
                </x-alert>
            @endforeach
        </div>
    @endif

    {{-- Grid 3 Kolom: Profil (1 kol) & Dual Notes Stack + Timeline (2 kol) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Informasi Lembaga & Kontak --}}
        <x-card bordered shadowless class="rounded-xl space-y-4">
            <x-slot:header>
                <div class="flex items-center gap-2.5">
                    <div class="size-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-900/40">
                        <x-icon name="building-office-2" class="size-4" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">
                            Profil & Kontak Laboratorium
                        </h3>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">
                            Identitas legal dan saluran narahubung resmi
                        </p>
                    </div>
                </div>
            </x-slot:header>

            {{-- Ruang Lingkup Akreditasi --}}
            <div class="rounded-xl p-3.5 bg-neutral-50/70 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 space-y-1.5">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-neutral-500 dark:text-neutral-400">
                    <x-icon name="beaker" class="size-3.5 text-blue-600 dark:text-blue-400" />
                    <span>Ruang Lingkup Pengujian</span>
                </div>
                <p class="text-xs sm:text-sm font-medium text-neutral-800 dark:text-neutral-200 leading-relaxed break-words">
                    {{ $lpk->scope ?? 'Belum ada rincian ruang lingkup.' }}
                </p>
            </div>

            {{-- Daftar Rincian Kontak & Lokasi --}}
            <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800 space-y-3.5 text-xs">
                {{-- Alamat --}}
                <div class="flex items-start gap-3">
                    <div class="size-7 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center shrink-0 mt-0.5">
                        <x-icon name="map-pin" class="size-3.5" />
                    </div>
                    <div class="space-y-0.5 flex-1 min-w-0">
                        <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500 block">Alamat Laboratorium</span>
                        <p class="text-xs font-medium text-neutral-700 dark:text-neutral-300 leading-normal break-words">
                            {{ implode(', ', array_filter([$lpk->address, $lpk->city, $lpk->province])) ?: '-' }}
                        </p>
                    </div>
                </div>

                {{-- Personel Penghubung --}}
                <div class="flex items-start gap-3">
                    <div class="size-7 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center shrink-0 mt-0.5">
                        <x-icon name="user" class="size-3.5" />
                    </div>
                    <div class="space-y-0.5 flex-1 min-w-0">
                        <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500 block">Personel Penghubung (CP)</span>
                        <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200 break-words">
                            {{ $lpk->contact_person ?? 'Belum diisi' }}
                        </p>
                    </div>
                </div>

                {{-- Telepon --}}
                <div class="flex items-start gap-3">
                    <div class="size-7 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center shrink-0 mt-0.5">
                        <x-icon name="phone" class="size-3.5" />
                    </div>
                    <div class="space-y-0.5 flex-1 min-w-0">
                        <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500 block">Nomor Telepon Kantor</span>
                        @if($lpk->phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $lpk->phone) }}" class="text-xs font-mono font-medium text-blue-600 dark:text-blue-400 hover:underline inline-block break-words">
                                {{ $lpk->phone }}
                            </a>
                        @else
                            <span class="text-xs text-neutral-400">-</span>
                        @endif
                    </div>
                </div>

                {{-- Email --}}
                <div class="flex items-start gap-3">
                    <div class="size-7 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center shrink-0 mt-0.5">
                        <x-icon name="envelope" class="size-3.5" />
                    </div>
                    <div class="space-y-0.5 flex-1 min-w-0">
                        <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500 block">Email Resmi Laboratorium</span>
                        @if($lpk->email)
                            <a href="mailto:{{ $lpk->email }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline break-all inline-block">
                                {{ $lpk->email }}
                            </a>
                        @else
                            <span class="text-xs text-neutral-400">-</span>
                        @endif
                    </div>
                </div>

                {{-- PIC Pendamping --}}
                <div class="flex items-start gap-3 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <div class="size-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-100 dark:border-emerald-900/40">
                        <x-icon name="shield-check" class="size-3.5" />
                    </div>
                    <div class="space-y-1 flex-1 min-w-0">
                        <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500 block">PIC Pendamping Unit Teknis</span>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200 break-words">
                                {{ $lpk->pic?->name ?? 'Belum Ditugaskan' }}
                            </span>
                            @if($lpk->pic)
                                <x-badge color="emerald" xs light>Ditugaskan</x-badge>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Kolom Kanan: Catatan Siklus & Koordinasi Teknis --}}
        <div class="lg:col-span-2 space-y-6 min-w-0">
            <x-card bordered shadowless class="rounded-xl space-y-4">
                <x-slot:header>
                    <div class="flex items-center gap-2.5">
                        <div class="size-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/40">
                            <x-icon name="clipboard-document-list" class="size-4" />
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">
                                Keterangan Siklus & Catatan Khusus
                            </h3>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">
                                Status kepatuhan pengawasan dan koordinasi pendampingan
                            </p>
                        </div>
                    </div>
                </x-slot:header>

                {{-- Sub-card 1: Kartu Status Otomatis Sistem --}}
                @php
                    $isOverdueOrSuspended = in_array($dynamicStatus->value, ['SURVEILLANCE_OVERDUE', 'SUSPENDED', 'EXPIRED', 'REVOKED'], true);
                    $isDue = ($dynamicStatus->value === 'SURVEILLANCE_DUE');
                @endphp
                <div @class([
                    'p-4 rounded-xl border transition-colors space-y-3',
                    'border-red-200 bg-red-50/40 dark:border-red-900/40 dark:bg-red-950/15' => $isOverdueOrSuspended,
                    'border-amber-200 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/15' => $isDue,
                    'border-neutral-200/80 bg-neutral-50/70 dark:border-neutral-800 dark:bg-neutral-900/40' => ! $isOverdueOrSuspended && ! $isDue,
                ])>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span @class([
                                'size-2 rounded-full',
                                'bg-red-500 animate-pulse' => $isOverdueOrSuspended,
                                'bg-amber-500' => $isDue,
                                'bg-emerald-500' => ! $isOverdueOrSuspended && ! $isDue,
                            ])></span>
                            <span class="font-bold text-xs sm:text-sm text-neutral-900 dark:text-neutral-100">
                                Ringkasan Status Siklus Pengawasan
                            </span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-neutral-200/80 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400">
                                Otomatis
                            </span>
                        </div>
                        <x-badge :color="$dynamicStatus->color()" sm light>
                            {{ $dynamicStatus->label() }}
                        </x-badge>
                    </div>

                    {{-- Parameter Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                        <div class="p-2.5 rounded-lg bg-white/80 dark:bg-neutral-950/60 border border-neutral-200/60 dark:border-neutral-800/80 space-y-1">
                            <span class="text-[10px] text-neutral-400 dark:text-neutral-500 block font-medium">Tgl. Terbit Sertifikat</span>
                            <span class="font-mono text-xs font-semibold text-neutral-800 dark:text-neutral-200 block">
                                {{ $lpk->certificate_date ? $lpk->certificate_date->format('d/m/Y') : '-' }}
                            </span>
                        </div>

                        <div class="p-2.5 rounded-lg bg-white/80 dark:bg-neutral-950/60 border border-neutral-200/60 dark:border-neutral-800/80 space-y-1">
                            <span class="text-[10px] text-neutral-400 dark:text-neutral-500 block font-medium">Batas Akhir Sertifikat</span>
                            <span class="font-mono text-xs font-semibold {{ $lpk->isExpired() ? 'text-red-600 dark:text-red-400' : 'text-neutral-800 dark:text-neutral-200' }} block">
                                {{ $lpk->expired_at ? $lpk->expired_at->format('d/m/Y') : '-' }}
                            </span>
                        </div>

                        <div class="p-2.5 rounded-lg bg-white/80 dark:bg-neutral-950/60 border border-neutral-200/60 dark:border-neutral-800/80 space-y-1">
                            <span class="text-[10px] text-neutral-400 dark:text-neutral-500 block font-medium">Sinkronisasi Sistem</span>
                            <span class="font-mono text-[11px] text-neutral-600 dark:text-neutral-400 block truncate" title="Diperbarui otomatis">
                                Real-time KAN
                            </span>
                        </div>
                    </div>

                    {{-- Pesan/Catatan Administratif Siklus --}}
                    @if($lpk->notes)
                        <div class="pt-2 border-t border-neutral-200/60 dark:border-neutral-800/80 flex items-start gap-2 text-xs text-neutral-700 dark:text-neutral-300 leading-relaxed">
                            <x-icon name="information-circle" class="size-4 text-neutral-400 shrink-0 mt-0.5" />
                            <p class="break-words">{{ $lpk->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Sub-card 2: Kartu Catatan Khusus PIC Pendamping --}}
                <div class="p-4 rounded-xl border border-amber-200/90 dark:border-amber-900/40 bg-gradient-to-br from-amber-50/70 via-white to-amber-50/20 dark:from-amber-950/20 dark:via-neutral-900 dark:to-neutral-900 space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div class="size-6 rounded-md bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <x-icon name="chat-bubble-bottom-center-text" class="size-3.5" />
                            </div>
                            <span class="font-bold text-xs sm:text-sm text-amber-900 dark:text-amber-300">
                                Catatan Khusus PIC Pendamping
                            </span>
                        </div>
                        <x-button size="xs" color="amber" light wire:click="openNoteModal" icon="pencil-square" class="border border-amber-300 dark:border-amber-700/60 shadow-2xs">
                            Edit Catatan
                        </x-button>
                    </div>

                    @php
                        $activeNote = $lpk->pic_notes ?? $lpk->notes;
                    @endphp

                    @if($activeNote)
                        <div class="p-3.5 rounded-lg bg-white/90 dark:bg-neutral-950/70 border-l-4 border-amber-500 border-y border-r border-neutral-200/70 dark:border-neutral-800 shadow-2xs space-y-2">
                            <p class="text-xs text-neutral-800 dark:text-neutral-200 leading-relaxed whitespace-pre-line break-words">
                                {{ $activeNote }}
                            </p>
                            <div class="flex items-center gap-1.5 text-[10px] text-neutral-400 dark:text-neutral-500 pt-1.5 border-t border-neutral-100 dark:border-neutral-800">
                                <x-icon name="user" class="size-3" />
                                <span>Penulis: {{ $lpk->pic?->name ?? 'PIC Pendamping KAN' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 text-center rounded-lg border border-dashed border-amber-200 dark:border-amber-900/50 bg-amber-50/20 dark:bg-amber-950/10">
                            <p class="text-xs text-amber-900/80 dark:text-amber-400 font-medium">
                                Belum ada catatan khusus untuk laboratorium ini.
                            </p>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                Klik tombol Edit Catatan di atas untuk mencatat rencana koordinasi, tindak lanjut temuan, atau konfirmasi jadwal.
                            </p>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Riwayat Asesmen Lapangan --}}
            <x-card header="Riwayat Asesmen Lapangan & Batas Waktu" bordered shadowless paddingless class="rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-neutral-200 dark:divide-neutral-800">
                        <thead class="bg-neutral-50 dark:bg-neutral-900 text-neutral-500 font-medium uppercase">
                            <tr>
                                <th class="py-2.5 px-3">Jenis Kunjungan</th>
                                <th class="py-2.5 px-3">Tanggal Realisasi</th>
                                <th class="py-2.5 px-3">Tim Asesmen</th>
                                <th class="py-2.5 px-3">Batas Waktu TP</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                            @forelse($lpk->assessments as $ass)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/40">
                                    <td class="py-3 px-3 font-semibold text-neutral-900 dark:text-neutral-100">
                                        {{ $ass->assessment_type->label() }}
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        {{ $ass->start_at->format('d/m/Y') }}
                                        @if($ass->start_at != $ass->end_at)
                                            s/d {{ $ass->end_at->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-medium text-neutral-800 dark:text-neutral-200 block">{{ $ass->lead_assessor ?? '-' }}</span>
                                        <span class="text-neutral-400 text-[11px] block">{{ $ass->assessor_team ?? '' }}</span>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        @php $due = $ass->effectiveTpDueDate(); @endphp
                                        @if($due)
                                            <span class="font-mono {{ $ass->isTpSatisfied() ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-700 dark:text-neutral-300' }}">
                                                {{ \Carbon\Carbon::parse($due)->format('d/m/Y') }}
                                            </span>
                                            @if($ass->hasExtension())
                                                <span class="text-[10px] text-amber-600 block">(Perpanjangan +1 bln)</span>
                                            @endif
                                        @else
                                            <span class="text-neutral-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        @if($ass->isTpSatisfied())
                                            <x-badge color="emerald" sm light>Memenuhi</x-badge>
                                        @else
                                            <x-badge color="amber" sm light>{{ $ass->status }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <x-button.circle icon="eye" size="sm" color="neutral" light href="{{ route('assessments.show', $ass->id) }}" wire:navigate tooltip="Detail Asesmen" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-neutral-400">
                                        Belum ada riwayat asesmen yang tercatat untuk laboratorium ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Modal Edit Catatan PIC --}}
    <x-modal id="modal-pic-note-show" wire="showNoteModal" title="Edit Catatan Khusus PIC" center>
        <div class="space-y-4">
            <x-alert color="blue" light>
                Laboratorium: <strong class="font-mono">{{ $lpk->no_reg }}</strong> ({{ $lpk->name }})
            </x-alert>

            <div class="flex items-center justify-between">
                <label class="text-xs font-medium text-neutral-700 dark:text-neutral-300">
                    Catatan Koordinasi & Teknis
                </label>
                <button type="button" wire:click="copySystemSummaryToNotes" class="text-xs text-blue-600 hover:underline cursor-pointer">
                    Salin Ringkasan Sistem
                </button>
            </div>

            <x-textarea 
                wire:model="noteForm.notes" 
                rows="5" 
                placeholder="Tuliskan catatan teknis untuk laboratorium binaan..." 
            />
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end gap-2">
                <x-button color="neutral" light wire:click="closeNoteModal">
                    Batal
                </x-button>
                <x-button color="primary" wire:click="saveNotes">
                    Simpan Catatan
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
