<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-neutral-200 dark:border-neutral-800">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <a href="{{ route('lpks.show', $assessment->lpk_id) }}" class="font-mono font-medium hover:underline text-neutral-700 dark:text-neutral-300" wire:navigate>
                    {{ $assessment->lpk->no_reg }}
                </a>
                <span>&bull;</span>
                <span class="truncate max-w-sm">{{ $assessment->lpk->name }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">{{ $assessment->title }}</h1>
                <x-badge color="neutral" outline text="{{ $assessment->assessment_type->label() }}" />
                @if($assessment->isTpSatisfied())
                    <x-badge color="emerald" text="Selesai (Memenuhi)" />
                @elseif($effectiveDueDate && now()->greaterThan($effectiveDueDate))
                    <x-badge color="red" text="Melewati Batas Waktu" />
                @else
                    <x-badge color="amber" text="Dalam Proses" />
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" href="{{ route('lpks.show', $assessment->lpk_id) }}" wire:navigate icon="chevron-left">
                Profil LP
            </x-button>
            <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" href="{{ route('assessments.edit', $assessment->id) }}" wire:navigate icon="pencil">
                Ubah
            </x-button>
            <x-button color="red" light class="border border-red-200 dark:border-red-900 font-medium" wire:click="confirmDeleteAssessment" icon="trash">
                Hapus
            </x-button>
        </div>
    </div>

    {{-- Assessment General Details --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-card bordered shadowless class="rounded-xl">
            <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Tanggal Realisasi</span>
            <div class="mt-1 font-semibold text-sm text-neutral-900 dark:text-neutral-100">
                {{ $assessment->start_at->translatedFormat('d M Y') }}
            </div>
            <div class="text-[11px] text-neutral-400">s/d {{ $assessment->end_at->translatedFormat('d M Y') }}</div>
        </x-card>

        <x-card bordered shadowless class="rounded-xl">
            <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Lead Assessor</span>
            <div class="mt-1 font-semibold text-sm text-neutral-900 dark:text-neutral-100 truncate">
                {{ $assessment->lead_assessor ?? 'Belum Ditugaskan' }}
            </div>
            <div class="text-[11px] text-neutral-400 truncate">{{ $assessment->location ?? 'Lokasi belum diisi' }}</div>
        </x-card>

        <x-card bordered shadowless class="rounded-xl">
            <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Batas Waktu TP dan VTP</span>
            <div class="mt-1 font-semibold text-sm text-neutral-900 dark:text-neutral-100">
                {{ $effectiveDueDate?->translatedFormat('d M Y') ?? 'Belum ditentukan' }}
            </div>
            <div class="text-[11px] text-neutral-400">
                @if($assessment->hasExtension())
                    <span class="text-purple-600 dark:text-purple-400 font-medium">+1 Bulan Kalender</span>
                @else
                    Normal ({{ $assessment->tp_deadline_months }} Bulan)
                @endif
            </div>
        </x-card>

        <x-card bordered shadowless class="rounded-xl">
            <span class="text-[11px] font-medium text-neutral-500 uppercase tracking-wider">Pengingat Awal (Notifikasi 1)</span>
            <div class="mt-1 font-semibold text-sm text-neutral-900 dark:text-neutral-100">
                {{ $earlyReminderDate->translatedFormat('d M Y') }}
            </div>
            <div class="text-[11px] text-neutral-400">
                {{ $assessment->assessment_type->earlyReminderMonths() }} bulan pasca realisasi
            </div>
        </x-card>
    </div>

    {{-- MILESTONE TIMELINE VIEW --}}
    <x-card bordered shadowless class="rounded-xl">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Garis Waktu Siklus Asesmen</h2>
            <span class="text-xs text-neutral-500 hidden lg:inline">Alur Siklus Akreditasi ISO/IEC 17025 (5 Tahap)</span>
        </div>

        {{-- Desktop Horizontal Milestone Timeline (Left to Right) --}}
        <div class="hidden lg:block">
            <div class="grid grid-cols-5 gap-2 relative">
                {{-- Stage 1: Asesmen Lapangan --}}
                <div class="flex flex-col p-3 rounded-lg border transition-all {{ $assessment->start_at ? 'bg-emerald-50/40 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/60' : 'bg-neutral-50 border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="size-6 rounded-full flex items-center justify-center text-xs font-bold bg-emerald-600 text-white">1</span>
                        <x-icon name="clipboard-document-check" class="size-4 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Pelaksanaan Asesmen</div>
                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-snug">
                        {{ $assessment->start_at->translatedFormat('d M Y') }} s/d {{ $assessment->end_at->translatedFormat('d M Y') }}
                    </div>
                    <div class="mt-auto pt-2">
                        <span class="inline-flex items-center text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">
                            Terlaksana
                        </span>
                    </div>
                </div>

                {{-- Stage 2: Tindakan Perbaikan (TP & VTP) --}}
                @php
                    $isTpDone = $assessment->isTpSatisfied();
                    $isTpLate = $effectiveDueDate && now()->greaterThan($effectiveDueDate) && ! $isTpDone;
                @endphp
                <div class="flex flex-col p-3 rounded-lg border transition-all {{ $isTpDone ? 'bg-emerald-50/40 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/60' : ($isTpLate ? 'bg-rose-50/40 border-rose-200 dark:bg-rose-950/20 dark:border-rose-800/60' : 'bg-amber-50/40 border-amber-200 dark:bg-amber-950/20 dark:border-amber-800/60') }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="size-6 rounded-full flex items-center justify-center text-xs font-bold {{ $isTpDone ? 'bg-emerald-600 text-white' : ($isTpLate ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white') }}">2</span>
                        <x-icon name="clock" class="size-4 {{ $isTpDone ? 'text-emerald-600' : ($isTpLate ? 'text-rose-600' : 'text-amber-600') }}" />
                    </div>
                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Tindakan Perbaikan</div>
                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-snug">
                        Tenggat: {{ $effectiveDueDate?->translatedFormat('d M Y') ?? 'Belum ada' }}
                    </div>
                    <div class="mt-auto pt-2">
                        <span class="inline-flex items-center text-[10px] font-semibold {{ $isTpDone ? 'text-emerald-700 dark:text-emerald-400' : ($isTpLate ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400') }}">
                            {{ $assessment->tp_findings_closed }}/{{ $assessment->tp_findings_total }} Temuan Tertutup
                        </span>
                    </div>
                </div>

                {{-- Stage 3: Laporan Asesmen --}}
                @php
                    $isReportDone = (bool) $assessment->assessment_report_date;
                @endphp
                <div class="flex flex-col p-3 rounded-lg border transition-all {{ $isReportDone ? 'bg-emerald-50/40 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/60' : 'bg-neutral-50 border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="size-6 rounded-full flex items-center justify-center text-xs font-bold {{ $isReportDone ? 'bg-emerald-600 text-white' : 'bg-neutral-300 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300' }}">3</span>
                        <x-icon name="document-text" class="size-4 {{ $isReportDone ? 'text-emerald-600' : 'text-neutral-400' }}" />
                    </div>
                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Laporan Asesmen</div>
                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-snug">
                        {{ $isReportDone ? $assessment->assessment_report_date->translatedFormat('d M Y') : 'Menunggu laporan' }}
                    </div>
                    <div class="mt-auto pt-2">
                        <span class="inline-flex items-center text-[10px] font-semibold {{ $isReportDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-neutral-400' }}">
                            {{ $isReportDone ? 'Laporan Final' : 'Belum Selesai' }}
                        </span>
                    </div>
                </div>

                {{-- Stage 4: Evaluasi Hasil Asesmen (EHA) --}}
                @php
                    $isEhaDone = (bool) $assessment->eha_completed_at;
                @endphp
                <div class="flex flex-col p-3 rounded-lg border transition-all {{ $isEhaDone ? 'bg-emerald-50/40 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/60' : 'bg-neutral-50 border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="size-6 rounded-full flex items-center justify-center text-xs font-bold {{ $isEhaDone ? 'bg-emerald-600 text-white' : 'bg-neutral-300 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300' }}">4</span>
                        <x-icon name="chat-bubble-bottom-center-text" class="size-4 {{ $isEhaDone ? 'text-emerald-600' : 'text-neutral-400' }}" />
                    </div>
                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Evaluasi Hasil (EHA)</div>
                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-snug">
                        {{ $isEhaDone ? $assessment->eha_completed_at->translatedFormat('d M Y') : ($assessment->eha_scheduled_date ? 'Jadwal: ' . $assessment->eha_scheduled_date->translatedFormat('d M Y') : 'Menunggu jadwal') }}
                    </div>
                    <div class="mt-auto pt-2">
                        <span class="inline-flex items-center text-[10px] font-semibold {{ $isEhaDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-neutral-400' }}">
                            {{ $isEhaDone ? 'Sidang Selesai' : 'Belum Sidang' }}
                        </span>
                    </div>
                </div>

                {{-- Stage 5: Penerbitan SK Akreditasi --}}
                @php
                    $isSkDone = (bool) $assessment->sk_issued_at;
                @endphp
                <div class="flex flex-col p-3 rounded-lg border transition-all {{ $isSkDone ? 'bg-emerald-50/40 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/60' : 'bg-neutral-50 border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="size-6 rounded-full flex items-center justify-center text-xs font-bold {{ $isSkDone ? 'bg-emerald-600 text-white' : 'bg-neutral-300 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300' }}">5</span>
                        <x-icon name="shield-check" class="size-4 {{ $isSkDone ? 'text-emerald-600' : 'text-neutral-400' }}" />
                    </div>
                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Penerbitan SK KAN</div>
                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-snug">
                        {{ $isSkDone ? 'No: ' . $assessment->sk_number : 'Menunggu SK' }}
                    </div>
                    <div class="mt-auto pt-2">
                        <span class="inline-flex items-center text-[10px] font-semibold {{ $isSkDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-neutral-400' }}">
                            {{ $isSkDone ? 'SK Terbit' : 'Belum Terbit' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Vertical Timeline --}}
        <div class="block lg:hidden">
            <x-timeline color="emerald" style="light">
                <x-timeline.items 
                    title="Pelaksanaan Asesmen Lapangan" 
                    description="{{ $assessment->start_at->translatedFormat('d M Y') }} s/d {{ $assessment->end_at->translatedFormat('d M Y') }}" 
                    date="Tahap 1" 
                    icon="clipboard-document-check" 
                    color="emerald" 
                />
                <x-timeline.items 
                    title="Tindakan Perbaikan (TP dan VTP)" 
                    description="Tenggat: {{ $effectiveDueDate?->translatedFormat('d M Y') ?? 'Belum ditentukan' }} ({{ $assessment->tp_findings_closed }}/{{ $assessment->tp_findings_total }} temuan tertutup)" 
                    date="Tahap 2" 
                    icon="clock" 
                    color="{{ $assessment->isTpSatisfied() ? 'emerald' : ($effectiveDueDate && now()->greaterThan($effectiveDueDate) ? 'rose' : 'amber') }}" 
                />
                <x-timeline.items 
                    title="Laporan Asesmen Lapangan" 
                    description="{{ $assessment->assessment_report_date ? 'Diselesaikan ' . $assessment->assessment_report_date->translatedFormat('d M Y') : 'Menunggu penyelesaian laporan resmi' }}" 
                    date="Tahap 3" 
                    icon="document-text" 
                    color="{{ $assessment->assessment_report_date ? 'emerald' : 'neutral' }}" 
                />
                <x-timeline.items 
                    title="Evaluasi Hasil Asesmen (EHA)" 
                    description="{{ $assessment->eha_completed_at ? 'Sidang selesai ' . $assessment->eha_completed_at->translatedFormat('d M Y') : 'Menunggu jadwal sidang panitia teknis' }}" 
                    date="Tahap 4" 
                    icon="chat-bubble-bottom-center-text" 
                    color="{{ $assessment->eha_completed_at ? 'emerald' : 'neutral' }}" 
                />
                <x-timeline.items 
                    title="Penerbitan SK Akreditasi KAN" 
                    description="{{ $assessment->sk_issued_at ? 'SK ' . $assessment->sk_number . ' terbit ' . $assessment->sk_issued_at->translatedFormat('d M Y') : 'Menunggu penerbitan SK resmi KAN' }}" 
                    date="Tahap 5" 
                    icon="shield-check" 
                    color="{{ $assessment->sk_issued_at ? 'emerald' : 'neutral' }}" 
                />
            </x-timeline>
        </div>
    </x-card>

    {{-- MILESTONE WORKFLOW TRACKER CARDS --}}
    <div class="space-y-4">
        <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Tahapan Pengawasan dan Tindak Lanjut</h2>

        {{-- STAGE 1: Tindakan Perbaikan (TP & VTP) Pelacak Temuan --}}
        <x-card bordered shadowless class="rounded-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800 gap-2">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center size-6 rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-xs font-bold">1</span>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Pelacakan Tindakan Perbaikan (TP dan VTP)</h3>
                    <x-badge color="{{ $tpStatus->color() }}" text="{{ $tpStatus->label() }}" />
                </div>
                <div class="flex items-center gap-2">
                    <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" wire:click="openFindingsModal" icon="pencil">
                        Perbarui Temuan
                    </x-button>
                    @if(! $assessment->isTpSatisfied())
                        <x-button color="primary" size="sm" wire:click="openSatisfyModal" icon="check-circle">
                            Dinyatakan Memenuhi
                        </x-button>
                    @endif
                </div>
            </div>

            {{-- Progress Counter --}}
            <div class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200 dark:border-neutral-800 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-neutral-500 font-medium uppercase tracking-wider">Progres Penyelesaian Temuan Ketidaksesuaian</span>
                        <div class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mt-0.5">
                            {{ $assessment->tp_findings_closed }} dari {{ $assessment->tp_findings_total }} Temuan Telah Diperbaiki
                            @if($assessment->tp_findings_total > 0 && $assessment->tp_findings_closed < $assessment->tp_findings_total)
                                <span class="text-xs font-normal text-amber-600 dark:text-amber-400">
                                    (Tersisa {{ $assessment->tpFindingsPending() }} temuan)
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        @php
                            $percentage = $assessment->tp_findings_total > 0 
                                ? (int) round(($assessment->tp_findings_closed / $assessment->tp_findings_total) * 100) 
                                : 0;
                        @endphp
                        <span class="text-2xl font-extrabold text-neutral-800 dark:text-neutral-200">{{ $percentage }}%</span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-neutral-200 dark:bg-neutral-700 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                </div>

                {{-- Extension Banner / Button --}}
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs border-t border-neutral-200 dark:border-neutral-700">
                    <div>
                        @if($assessment->hasExtension())
                            <div class="text-purple-700 dark:text-purple-300 font-medium">
                                Perpanjangan batas waktu +1 bulan aktif (No. Surat: {{ $assessment->tp_extension_letter_no }}).
                            </div>
                            <div class="text-[11px] text-neutral-500">
                                Total batas waktu akumulatif: {{ $assessment->isInitial() ? '4 Bulan Kalender' : '3 Bulan Kalender' }}
                            </div>
                        @else
                            <div class="text-neutral-600 dark:text-neutral-300">
                                Batas waktu normal: <strong>{{ $assessment->tp_deadline_months }} Bulan Kalender</strong> (Jatuh tempo: {{ $assessment->tp_due_date?->translatedFormat('d M Y') ?? 'Belum ditentukan' }})
                            </div>
                        @endif
                    </div>

                    @if(! $assessment->hasExtension() && ! $assessment->isTpSatisfied())
                        <div>
                            @if($isEligibleForExtension)
                                <x-button color="purple" outline size="sm" wire:click="openExtensionModal" icon="plus-circle">
                                    Ajukan Perpanjangan +1 Bulan
                                </x-button>
                            @else
                                <div class="text-right">
                                    <span class="inline-flex items-center gap-1 text-[11px] text-red-600 dark:text-red-400 font-medium">
                                        <x-icon name="no-symbol" class="size-3.5" />
                                        <span>Perpanjangan Dilarang (Belum Ada Progres)</span>
                                    </span>
                                    <div class="text-[10px] text-neutral-500">Wajib menyelesaikan minimal 1 temuan sebelum berhak perpanjangan</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </x-card>

        {{-- STAGE 2: Laporan Asesmen --}}
        <x-card bordered shadowless class="rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center size-6 rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-xs font-bold">2</span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Laporan Asesmen Lapangan</h3>
                    <div class="text-xs text-neutral-500">
                        @if($assessment->assessment_report_date)
                            Dokumen laporan asesmen resmi diselesaikan pada <strong>{{ $assessment->assessment_report_date->translatedFormat('d F Y') }}</strong>
                        @else
                            Belum ada tanggal laporan yang dicatat
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" wire:click="openReportModal" icon="document-text">
                    {{ $assessment->assessment_report_date ? 'Ubah Tgl Laporan' : 'Catat Tgl Laporan' }}
                </x-button>
            </div>
        </x-card>

        {{-- STAGE 3: Evaluasi Hasil Asesmen (EHA) --}}
        <x-card bordered shadowless class="rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center size-6 rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-xs font-bold">3</span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Evaluasi Hasil Asesmen (EHA)</h3>
                    <div class="text-xs text-neutral-500">
                        @if($assessment->eha_completed_at)
                            Sidang panitia teknis atau EHA selesai pada <strong>{{ $assessment->eha_completed_at->translatedFormat('d F Y') }}</strong>
                            @if($assessment->eha_notes)
                                <div class="mt-0.5 text-neutral-700 dark:text-neutral-300 italic">"{{ $assessment->eha_notes }}"</div>
                            @endif
                        @else
                            Sidang evaluasi belum terlaksana
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" wire:click="openEhaModal" icon="chat-bubble-bottom-center-text">
                    {{ $assessment->eha_completed_at ? 'Ubah Hasil EHA' : 'Catat Sidang EHA' }}
                </x-button>
            </div>
        </x-card>

        {{-- STAGE 4: Penerbitan SK Akreditasi KAN --}}
        <x-card bordered shadowless class="rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center size-6 rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-xs font-bold">4</span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Surat Keputusan (SK) Akreditasi KAN</h3>
                    <div class="text-xs text-neutral-500">
                        @if($assessment->sk_issued_at)
                            Nomor SK: <strong>{{ $assessment->sk_number }}</strong> &bull; Terbit: <strong>{{ $assessment->sk_issued_at->translatedFormat('d F Y') }}</strong>
                            @if($assessment->sk_lead_time_days)
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">(Lead time: {{ $assessment->sk_lead_time_days }} hari)</span>
                            @endif
                        @else
                            SK resmi belum diterbitkan
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <x-button color="neutral" light class="border border-neutral-200/90 dark:border-neutral-700 font-medium" size="sm" wire:click="openSkModal" icon="shield-check">
                    {{ $assessment->sk_issued_at ? 'Ubah SK KAN' : 'Catat Terbit SK' }}
                </x-button>
            </div>
        </x-card>
    </div>

    {{-- MODAL 1: Perbarui Progres Temuan --}}
    <x-modal id="modal-findings" wire="showFindingsModal" title="Perbarui Temuan Ketidaksesuaian" center>
        <div class="space-y-4">
            <x-input 
                id="editFindingsTotal" 
                type="number" 
                min="0" 
                label="Total Temuan Ketidaksesuaian" 
                wire:model="editFindingsTotal" 
            />
            <x-input 
                id="editFindingsClosed" 
                type="number" 
                min="0" 
                label="Temuan yang Sudah Diperbaiki (Sesuai)" 
                wire:model="editFindingsClosed" 
                hint="Jumlah temuan tertutup tidak boleh melebihi total temuan"
            />
        </div>
        <x-slot:footer class="flex justify-end gap-2">
            <x-button color="neutral" light wire:click="$set('showFindingsModal', false)">Batal</x-button>
            <x-button color="primary" wire:click="saveFindings" loading>Simpan Progres</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- MODAL 2: Pengajuan Perpanjangan Batas Waktu (+1 Bulan) --}}
    <x-modal id="modal-extension" wire="showExtensionModal" title="Perpanjangan Batas Waktu (+1 Bulan)" center>
        <div class="space-y-3">
            <p class="text-xs text-neutral-600 dark:text-neutral-300">
                Laboratorium telah menyelesaikan {{ $assessment->tp_findings_closed }} dari {{ $assessment->tp_findings_total }} temuan (memenuhi syarat perpanjangan). Masukkan nomor surat resmi permohonan atau persetujuan perpanjangan.
            </p>
            <x-input 
                id="extensionLetterNo" 
                label="Nomor Surat Rekomendasi atau Permohonan" 
                wire:model="extensionLetterNo" 
                placeholder="Contoh: KAN-EXT/102/2026" 
                required 
            />
        </div>
        <x-slot:footer class="flex justify-end gap-2">
            <x-button color="neutral" light wire:click="$set('showExtensionModal', false)">Batal</x-button>
            <x-button color="primary" wire:click="applyExtension" loading>Setujui Perpanjangan</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- MODAL 3: Pemenuhan Tindakan Perbaikan --}}
    <x-modal id="modal-satisfy" wire="showSatisfyModal" title="Konfirmasi Pemenuhan Tindakan Perbaikan" center>
        <div class="space-y-3">
            <p class="text-xs text-neutral-600 dark:text-neutral-300">
                Tindakan perbaikan akan ditandai selesai dan seluruh ketidaksesuaian dinyatakan telah ditutup.
            </p>
            <x-date 
                id="satisfiedDate" 
                label="Tanggal Dinyatakan Memenuhi" 
                wire:model="satisfiedDate" 
                format="YYYY-MM-DD"
                helpers
                required 
            />
        </div>
        <x-slot:footer class="flex justify-end gap-2">
            <x-button color="neutral" light wire:click="$set('showSatisfyModal', false)">Batal</x-button>
            <x-button color="primary" wire:click="saveSatisfied" loading>Tandai Memenuhi</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- MODAL 4: Laporan Asesmen --}}
    <x-modal id="modal-report" wire="showReportModal" title="Tanggal Laporan Asesmen" center>
        <div class="space-y-3">
            <x-date 
                id="reportDate" 
                label="Tanggal Dokumen Laporan" 
                wire:model="reportDate" 
                format="YYYY-MM-DD"
                helpers
                required 
            />
        </div>
        <x-slot:footer class="flex justify-end gap-2">
            <x-button color="neutral" light wire:click="$set('showReportModal', false)">Batal</x-button>
            <x-button color="primary" wire:click="saveReport" loading>Simpan Laporan</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- MODAL 5: Evaluasi Hasil Asesmen (EHA) --}}
    <x-modal id="modal-eha" wire="showEhaModal" title="Realisasi Evaluasi Hasil Asesmen (EHA)" center>
        <div class="space-y-3">
            <x-date 
                id="ehaDate" 
                label="Tanggal Realisasi Sidang EHA" 
                wire:model="ehaDate" 
                format="YYYY-MM-DD"
                helpers
                required 
            />
            <x-textarea 
                id="ehaNotes" 
                label="Catatan atau Rekomendasi Sidang" 
                wire:model="ehaNotes" 
                rows="3" 
                placeholder="Rekomendasi panitia teknis atau tim evaluasi..." 
            />
        </div>
        <x-slot:footer class="flex justify-end gap-2">
            <x-button color="neutral" light wire:click="$set('showEhaModal', false)">Batal</x-button>
            <x-button color="primary" wire:click="saveEha" loading>Simpan EHA</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- MODAL 6: Surat Keputusan (SK) KAN --}}
    <x-modal id="modal-sk" wire="showSkModal" title="Pencatatan SK KAN" center>
        <div class="space-y-3">
            <x-input 
                id="skNumber" 
                label="Nomor SK KAN" 
                wire:model="skNumber" 
                placeholder="Contoh: SK-KAN.089/LP/2026" 
                required 
            />
            <x-date 
                id="skDate" 
                label="Tanggal Terbit SK" 
                wire:model="skDate" 
                format="YYYY-MM-DD"
                helpers
                required 
            />
        </div>
        <x-slot:footer class="flex justify-end gap-2">
            <x-button color="neutral" light wire:click="$set('showSkModal', false)">Batal</x-button>
            <x-button color="primary" wire:click="saveSk" loading>Simpan SK KAN</x-button>
        </x-slot:footer>
    </x-modal>
</div>
