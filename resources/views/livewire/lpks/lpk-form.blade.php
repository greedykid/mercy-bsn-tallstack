<div class="w-full space-y-6">
    {{-- Header Form --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-neutral-200 dark:border-neutral-800">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                {{ $isEditing ? 'Ubah Profil Laboratorium: ' . ($lpk->registration_number ?: $lpk->no_reg) : 'Pendaftaran Laboratorium Penguji Baru' }}
            </h1>
            <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                {{ $isEditing ? 'Perbarui data identitas, masa berlaku sertifikat, dan penugasan PIC lab' : 'Daftarkan entitas Laboratorium Penguji (LP) ke dalam direktori KAN' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-button color="neutral" light href="{{ $isEditing ? route('lpks.show', $lpk->id) : route('lpks.index') }}" wire:navigate icon="chevron-left">
                Kembali
            </x-button>
        </div>
    </div>

    {{-- Form Container with 2-Column Grid Layout --}}
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Main Form Sections (8 Cols) --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Seksi 1: Identitas Resmi KAN --}}
                <x-card bordered shadowless class="rounded-xl space-y-4">
                    <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Identitas Resmi & Skema Akreditasi</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Nomor Registrasi LPK --}}
                        <x-input 
                            label="Nomor Registrasi LPK (No. Reg) *" 
                            wire:model="form.no_reg" 
                            placeholder="Contoh: 344" 
                            class="font-mono"
                            hint="Nomor registrasi urut LPK di KAN (berupa angka seperti 344)"
                            required 
                        />

                        {{-- Nomor Akreditasi KAN --}}
                        <x-input 
                            label="Nomor Akreditasi KAN" 
                            wire:model="form.registration_number" 
                            placeholder="Contoh: LP-101-IDN" 
                            class="font-mono"
                            hint="Kode akreditasi resmi KAN (format: LP-xxx-IDN)"
                        />

                        {{-- Skema Akreditasi (Tetap LP) --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Skema Akreditasi KAN *
                            </label>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-neutral-50/80 dark:bg-neutral-800/40 border border-neutral-200/80 dark:border-neutral-700/60">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">LP</span>
                                    <span class="text-xs font-medium text-neutral-800 dark:text-neutral-200">Laboratorium Penguji (SNI ISO/IEC 17025:2017)</span>
                                </div>
                                <span class="text-[11px] text-neutral-500 dark:text-neutral-400">Standar Baku KAN</span>
                            </div>
                        </div>

                        {{-- Nama Laboratorium --}}
                        <div class="md:col-span-2">
                            <x-input 
                                label="Nama Laboratorium Penguji *" 
                                wire:model="form.name" 
                                placeholder="Contoh: Balai Pengujian Material & Fisika Logam" 
                                required 
                            />
                        </div>

                        {{-- Ruang Lingkup Pengujian --}}
                        <div class="md:col-span-2">
                            <x-textarea 
                                label="Bidang & Ruang Lingkup Pengujian" 
                                wire:model="form.scope" 
                                placeholder="Uraikan parameter dan matriks uji (contoh: Pengujian Kuat Tarik Baja, Uji Emisi Udara, Cemaran Logam)..." 
                                rows="3" 
                            />
                        </div>
                    </div>
                </x-card>

                {{-- Seksi 2: Lokasi & Personel Penghubung --}}
                <x-card bordered shadowless class="rounded-xl space-y-4">
                    <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Lokasi & Kontak Narahubung</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-textarea 
                                label="Alamat Lengkap Laboratorium (Jalan, Kota, Provinsi)" 
                                wire:model="form.address" 
                                placeholder="Contoh: Kawasan Sains dan Teknologi BJ Habibie, Gedung 420, Tangerang Selatan, Banten" 
                                rows="2"
                                hint="Format satu kesatuan alamat lengkap mencakup jalan, gedung, kota, dan provinsi"
                            />
                        </div>

                        <x-input 
                            label="Kota / Kabupaten" 
                            wire:model="form.city" 
                            placeholder="Contoh: Tangerang Selatan" 
                        />

                        <x-input 
                            label="Provinsi" 
                            wire:model="form.province" 
                            placeholder="Contoh: Banten" 
                        />

                        <x-input 
                            label="Personel Penghubung (Contact Person)" 
                            wire:model="form.contact_person" 
                            placeholder="Nama pejabat / manajer mutu lab..." 
                        />

                        <x-input 
                            label="Nomor Telepon / Kontak" 
                            wire:model="form.phone" 
                            placeholder="Contoh: 021-7560555" 
                        />

                        <div class="md:col-span-2">
                            <x-input 
                                label="Alamat Email Resmi Lab" 
                                type="email" 
                                wire:model="form.email" 
                                placeholder="lab@instansi.go.id" 
                            />
                        </div>
                    </div>
                </x-card>

                {{-- Seksi 3: Masa Berlaku Akreditasi & Dokumen Terpadu --}}
                <x-card bordered shadowless class="rounded-xl space-y-4">
                    <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Masa Akreditasi & Tautan Google Drive</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-date 
                            label="Tanggal Terbit Sertifikat Akreditasi" 
                            wire:model="form.certificate_date" 
                            format="YYYY-MM-DD"
                            placeholder="Pilih tanggal terbit..." 
                        />

                        <x-date 
                            label="Tanggal Kedaluwarsa Sertifikat" 
                            wire:model="form.expired_at" 
                            format="YYYY-MM-DD"
                            placeholder="Otomatis +5 tahun jika dikosongkan" 
                            hint="Kosongkan untuk perhitungan otomatis +5 tahun dari tanggal terbit"
                        />

                        <div class="md:col-span-2">
                            <x-input 
                                label="Tautan Berkas Google Drive Terpadu" 
                                wire:model="form.drive_url" 
                                placeholder="https://drive.google.com/drive/folders/..." 
                                hint="Tautan folder Google Drive berisi sertifikat, lampiran ruang lingkup, dan SK KAN"
                            />
                        </div>
                    </div>
                </x-card>

                {{-- Seksi 4: Penugasan PIC & Catatan Khusus --}}
                <x-card bordered shadowless class="rounded-xl space-y-4">
                    <div class="pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Penugasan PIC Pendamping & Catatan Administrasi</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($isAdmin)
                            <div class="md:col-span-2">
                                <x-select.styled 
                                    label="PIC Pendamping KAN *" 
                                    wire:model="form.pic_user_id" 
                                    :options="$picOptions" 
                                    select="label:label|value:value" 
                                    placeholder="Pilih PIC Pendamping..." 
                                    searchable 
                                    required 
                                />
                            </div>
                        @else
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                                    PIC Pendamping
                                </label>
                                <div class="p-3 rounded-lg bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-xs text-neutral-700 dark:text-neutral-300 flex items-center gap-2">
                                    <x-icon name="user" class="size-4 text-blue-600 dark:text-blue-400" />
                                    <span>{{ $lpk?->pic?->name ?? Auth::user()->name }} (PIC Penanggung Jawab)</span>
                                </div>
                            </div>
                        @endif

                        <div class="md:col-span-2">
                            <x-textarea 
                                label="Catatan Administrasi Umum" 
                                wire:model="form.notes" 
                                placeholder="Catatan riwayat atau status administratif umum..." 
                                rows="3" 
                            />
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Sidebar Column (4 Cols, Sticky) --}}
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-4">
                {{-- Aksi Submit Card --}}
                <x-card bordered shadowless class="rounded-xl space-y-4 bg-neutral-50/60 dark:bg-neutral-900/40">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ $isEditing ? 'Konfirmasi Perubahan' : 'Konfirmasi Pendaftaran' }}
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Pastikan data identitas lab dan masa berlaku akreditasi telah terisi dengan benar.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-neutral-200 dark:border-neutral-800">
                        <x-button type="submit" color="primary" class="w-full justify-center shadow-xs" loading icon="check">
                            {{ $isEditing ? 'Simpan Perubahan' : 'Daftarkan Laboratorium' }}
                        </x-button>
                        <x-button color="neutral" light class="w-full justify-center border border-neutral-200/90 dark:border-neutral-700" href="{{ $isEditing ? route('lpks.show', $lpk->id) : route('lpks.index') }}" wire:navigate>
                            Batal
                        </x-button>
                    </div>
                </x-card>

                {{-- Ringkasan Entitas Lab (Info Box) --}}
                <x-card bordered shadowless class="rounded-xl space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        <x-icon name="building-office-2" class="size-4 text-blue-600 dark:text-blue-400" />
                        <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-900 dark:text-neutral-100">
                            Ringkasan Entitas Lab
                        </h4>
                    </div>

                    <div class="space-y-2.5 text-xs text-neutral-600 dark:text-neutral-400">
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Skema Akreditasi</span>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100">LP (ISO 17025)</span>
                        </div>
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Status Entitas</span>
                            <x-badge color="emerald" size="sm" text="Aktif" />
                        </div>
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Siklus Akreditasi</span>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100">5 Tahun Siklus</span>
                        </div>
                        <div class="flex items-center justify-between pb-1.5 border-b border-dashed border-neutral-100 dark:border-neutral-800">
                            <span>Survailen Berkala</span>
                            <span class="font-medium text-neutral-800 dark:text-neutral-200">Siklus 1 & Siklus 2</span>
                        </div>
                    </div>

                    <div class="pt-2 text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed border-t border-neutral-100 dark:border-neutral-800">
                        Entitas baru akan langsung dihubungkan dengan engine pengawasan siklus akreditasi KAN secara otomatis.
                    </div>
                </x-card>

                {{-- Panduan Registrasi KAN --}}
                <x-card bordered shadowless class="rounded-xl space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-neutral-100 dark:border-neutral-800">
                        <x-icon name="information-circle" class="size-4 text-emerald-600 dark:text-emerald-400" />
                        <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-900 dark:text-neutral-100">
                            Pedoman Entitas KAN
                        </h4>
                    </div>

                    <ul class="space-y-2 text-xs text-neutral-600 dark:text-neutral-400 list-disc list-inside">
                        <li>Nomor Reg diisi angka urutan registrasi lab di KAN.</li>
                        <li>Kode akreditasi resmi berformat standar <span class="font-mono text-neutral-800 dark:text-neutral-200">LP-xxx-IDN</span>.</li>
                        <li>Pastikan tautan Google Drive dapat diakses asesor dan sekretariat KAN.</li>
                    </ul>
                </x-card>
            </div>
        </div>
    </form>
</div>
