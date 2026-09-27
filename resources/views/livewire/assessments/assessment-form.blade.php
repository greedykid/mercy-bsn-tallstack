<div class="w-full space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-neutral-200 dark:border-neutral-800">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                {{ $isEditing ? 'Ubah Asesmen: ' . $assessment->title : 'Jadwalkan Asesmen' }}
            </h1>
            <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                {{ $isEditing ? 'Perbarui informasi agenda asesmen, tim asesor, dan status pelaksanaan' : 'Daftarkan agenda asesmen lapangan, surveilen, atau re-akreditasi' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-button color="neutral" light href="{{ $isEditing ? route('assessments.show', $assessment->id) : route('assessments.index') }}" wire:navigate icon="chevron-left">
                Kembali
            </x-button>
        </div>
    </div>

    {{-- Form Container --}}
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Main Form Columns (8 Cols) --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Section 1: LPK & Jenis Asesmen --}}
                <x-card bordered shadowless class="rounded-xl space-y-4">
                    <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Informasi Lembaga dan Skema Asesmen</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- LPK Selection --}}
                        <div class="md:col-span-2">
                            <x-select.styled 
                                id="lpk_id"
                                label="Pilih Laboratorium Penguji (LP) *"
                                wire:model.live="form.lpk_id"
                                :options="$lpkOptions"
                                select="label:label|value:value"
                                placeholder="Pilih Laboratorium Penguji..."
                                searchable
                                required
                            />
                            @if($selectedLpk)
                                <div class="mt-2.5 p-3 rounded-lg bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/60 text-xs flex flex-wrap items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 font-mono font-bold text-blue-800 dark:text-blue-300">
                                            <x-icon name="building-office-2" class="size-4" />
                                            {{ $selectedLpk->registration_number ?: $selectedLpk->no_reg }}
                                        </span>
                                        <span class="text-neutral-800 dark:text-neutral-200 font-semibold">
                                            {{ $selectedLpk->name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-neutral-600 dark:text-neutral-300">
                                        @if($selectedLpk->certificate_date)
                                            <span>Masa Sertifikat: {{ $selectedLpk->certificate_date->format('d/m/Y') }} s/d {{ $selectedLpk->expired_at?->format('d/m/Y') ?? 'Aktif' }}</span>
                                        @endif
                                        <x-badge color="{{ $selectedLpk->status?->color() ?? 'neutral' }}" size="sm" text="{{ $selectedLpk->status?->label() ?? 'Aktif' }}" />
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Judul Asesmen --}}
                        <div class="md:col-span-2">
                            <x-input 
                                id="title" 
                                label="Judul atau Agenda Asesmen *" 
                                wire:model="form.title" 
                                placeholder="Contoh: Surveilen Tahun 1 Balai Pengujian Mutu Nusantara" 
                                required 
                            />
                        </div>

                        {{-- Jenis Asesmen --}}
                        <div>
                            <x-select.styled 
                                id="assessment_type"
                                label="Jenis Asesmen KAN *"
                                wire:model="form.assessment_type"
                                :options="$typeOptions"
                                select="label:label|value:value"
                                required
                            />
                        </div>

                        {{-- Status Asesmen --}}
                        <div>
                            <x-select.styled 
                                id="status"
                                label="Status Alur Asesmen *"
                                wire:model="form.status"
                                :options="$statusOptions"
                                select="label:label|value:value"
                                required
                            />
                        </div>
                    </div>
                </x-card>

                {{-- Section 2: Jadwal Pelaksanaan --}}
                <x-card bordered shadowless class="rounded-xl space-y-4">
                    <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Jadwal dan Batas Waktu Pelaksanaan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Tanggal Mulai --}}
                        <x-date 
                            id="start_at"
                            label="Tanggal Mulai Asesmen *"
                            wire:model="form.start_at"
                            format="YYYY-MM-DD"
                            helpers
                            required
                        />

                        {{-- Tanggal Selesai --}}
                        <x-date 
                            id="end_at"
                            label="Tanggal Selesai Asesmen (Closing Meeting) *"
                            wire:model="form.end_at"
                            format="YYYY-MM-DD"
                            helpers
                            required
                        />
                    </div>

                    <div class="p-3 bg-neutral-50 dark:bg-neutral-900/50 rounded-lg border border-neutral-200 dark:border-neutral-800 flex items-start gap-2 text-xs text-neutral-600 dark:text-neutral-400">
                        <x-icon name="information-circle" class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                        <div>
                            <span class="font-medium text-neutral-800 dark:text-neutral-200">Perhitungan Batas Waktu Tindakan Perbaikan (TP):</span>
                            Batas waktu penyelesaian tindakan perbaikan dihitung otomatis dari tanggal selesai asesmen. Akreditasi Awal (AA) mendapatkan batas waktu normal 3 bulan kalender, sedangkan surveilen dan jenis lainnya mendapatkan 2 bulan kalender.
                        </div>
                    </div>
                </x-card>

                {{-- Section 3: Tim Asesor & Lokasi --}}
                <x-card bordered shadowless class="rounded-xl space-y-4">
                    <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Tim Asesor dan Lokasi</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Lokasi Asesmen --}}
                        <div class="md:col-span-2">
                            <x-input 
                                id="location" 
                                label="Lokasi atau Tempat Pelaksanaan Asesmen"
                                wire:model="form.location" 
                                placeholder="Contoh: Gedung Laboratorium Uji Kimia Lt. 2, Serpong / Onsite" 
                            />
                        </div>

                        {{-- Asesor Kepala --}}
                        <x-input 
                            id="lead_assessor" 
                            label="Asesor Kepala (Lead Assessor)"
                            wire:model="form.lead_assessor" 
                            placeholder="Nama Asesor Kepala KAN" 
                        />

                        {{-- Anggota Tim Asesor --}}
                        <x-input 
                            id="assessor_team" 
                            label="Anggota Tim Asesor atau Tenaga Ahli"
                            wire:model="form.assessor_team" 
                            placeholder="Contoh: Dr. Budi (Asesor), Ir. Siti (Tenaga Ahli)" 
                        />
                    </div>
                </x-card>

                {{-- Section 4: Temuan Ketidaksesuaian (jika dalam mode edit) --}}
                @if($isEditing)
                    <x-card bordered shadowless class="rounded-xl space-y-4">
                        <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Progres Temuan Ketidaksesuaian (TP)</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input 
                                type="number"
                                min="0"
                                id="tp_findings_total" 
                                label="Jumlah Total Temuan Ketidaksesuaian"
                                wire:model="form.tp_findings_total" 
                            />

                            <x-input 
                                type="number"
                                min="0"
                                id="tp_findings_closed" 
                                label="Jumlah Temuan Yang Telah Selesai (Closed)"
                                wire:model="form.tp_findings_closed" 
                                hint="Syarat perpanjangan batas waktu 1 bulan: minimal 1 temuan sudah selesai" 
                            />
                        </div>
                    </x-card>
                @endif
            </div>

            {{-- Sidebar Column (4 Cols) --}}
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-4">
                {{-- Aksi Submit Card --}}
                <x-card bordered shadowless class="rounded-xl space-y-4 bg-neutral-50/60 dark:bg-neutral-900/40">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Konfirmasi Agenda</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Pastikan data agenda dan tanggal pelaksanaan closing meeting sudah sesuai.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-neutral-200 dark:border-neutral-800">
                        <x-button type="submit" color="primary" class="w-full justify-center shadow-xs" loading icon="check">
                            {{ $isEditing ? 'Simpan Perubahan Asesmen' : 'Jadwalkan Asesmen' }}
                        </x-button>
                        <x-button color="neutral" light class="w-full justify-center border border-neutral-200/90 dark:border-neutral-700" href="{{ $isEditing ? route('assessments.show', $assessment->id) : route('assessments.index') }}" wire:navigate>
                            Batal
                        </x-button>
                    </div>
                </x-card>

                {{-- Ketentuan Waktu Tindakan Perbaikan (TP) --}}
                <x-card bordered shadowless class="rounded-xl space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        <x-icon name="clock" class="size-4 text-blue-600 dark:text-blue-400" />
                        <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-900 dark:text-neutral-100">
                            Aturan Batas Waktu TP KAN
                        </h4>
                    </div>

                    <div class="space-y-2 text-xs text-neutral-600 dark:text-neutral-400">
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Akreditasi Awal (AA)</span>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100">3 Bulan</span>
                        </div>
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Survailen 1 & 2 (S1/S2)</span>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100">2 Bulan</span>
                        </div>
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Re-Akreditasi (RA)</span>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100">2 Bulan</span>
                        </div>
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Perluasan Lingkup (PRL)</span>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100">2 Bulan</span>
                        </div>
                    </div>

                    <div class="pt-2 text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed border-t border-neutral-100 dark:border-neutral-800">
                        Perpanjangan toleransi maksimal 1 bulan kalender dapat diberikan jika laboratorium telah menyelesaikan minimal sebagian tindakan perbaikan.
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</div>
