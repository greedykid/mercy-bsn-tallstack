<?php

declare(strict_types=1);

namespace App\Livewire\Lpks;

use App\Actions\Lpks\DeleteLpkAction;
use App\Actions\Lpks\UpdatePicNotesAction;
use App\Actions\Lpks\UpsertLpkBatchAction;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Livewire\Concerns\HasToast;
use App\Models\Lpk;
use App\Services\Importers\CsvLpkImporter;
use App\Services\SurveillanceEngineService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class LpkIndex extends Component
{
    use HasToast, WithFileUploads, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public ?string $selectedStatus = null;

    public array $headers = [
        ['index' => 'no_reg', 'label' => 'NO. AKREDITASI', 'sortable' => true],
        ['index' => 'name', 'label' => 'NAMA LPK', 'sortable' => true],
        ['index' => 'expired_at', 'label' => '<div><div>MASA BERLAKU</div><div class="text-[10px] font-normal text-neutral-400 dark:text-neutral-500 tracking-normal mt-0.5">AWAL & AKHIR</div></div>', 'unescaped' => true, 'sortable' => true],
        ['index' => 'status', 'label' => 'KETERANGAN', 'sortable' => false],
        ['index' => 'action', 'label' => 'AKSI', 'sortable' => false, 'align' => 'right'],
    ];

    public array $sort = [
        'column' => 'no_reg',
        'direction' => 'asc',
    ];

    #[Url(as: 'per_page')]
    public int $quantity = 10;

    #[Url(as: 'view')]
    public string $viewMode = 'table';

    public array $selected = [];

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['table', 'grid'], true)) {
            $this->viewMode = $mode;
        }
    }

    public bool $showImportModal = false;

    public string $importMode = 'file'; // 'file', 'link', 'paste'

    public $importFile = null;

    public string $googleSheetsUrl = '';

    public string $csvContent = '';

    public bool $showPicNoteModal = false;

    public ?int $selectedLpkIdForNote = null;

    public string $selectedLpkNoRegForNote = '';

    public string $picNoteContent = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedSelectedStatus(): void
    {
        if ($this->selectedStatus === '') {
            $this->selectedStatus = null;
        }
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedQuantity(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearStatus(): void
    {
        $this->selectedStatus = null;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedStatus', 'quantity', 'selected']);
        $this->sort = ['column' => 'no_reg', 'direction' => 'asc'];
        $this->resetPage();
    }

    public function openImportModal(): void
    {
        $this->authorize('import', Lpk::class);
        $this->reset(['importFile', 'googleSheetsUrl', 'csvContent', 'importMode']);
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'googleSheetsUrl', 'csvContent']);
    }

    public function calculateCycleInfo(Lpk $lpk): array
    {
        return app(SurveillanceEngineService::class)->determineActiveCycle($lpk);
    }

    public function openPicNoteModal(int $lpkId): void
    {
        $lpk = Lpk::findOrFail($lpkId);
        $this->authorize('update', $lpk);

        $this->selectedLpkIdForNote = $lpk->id;
        $this->selectedLpkNoRegForNote = $lpk->no_reg;
        $this->picNoteContent = (string) ($lpk->pic_notes ?? '');
        $this->showPicNoteModal = true;
    }

    public function savePicNote(UpdatePicNotesAction $action): void
    {
        if (! $this->selectedLpkIdForNote) {
            return;
        }

        $lpk = Lpk::findOrFail($this->selectedLpkIdForNote);
        $this->authorize('update', $lpk);

        $action->execute($lpk, $this->picNoteContent);

        $this->showPicNoteModal = false;
        $this->dialogSuccess('Catatan Disimpan', 'Catatan khusus PIC berhasil diperbarui.');
    }

    public function executeImport(CsvLpkImporter $importer, UpsertLpkBatchAction $action): void
    {
        $this->authorize('import', Lpk::class);

        $user = Auth::user();
        $defaultPicUserId = ($user && $user->isPic()) ? $user->id : null;
        $parsed = collect();

        if ($this->importMode === 'file') {
            $file = is_array($this->importFile) ? ($this->importFile[0] ?? null) : $this->importFile;

            if (! $file) {
                $this->addError('importFile', 'Silakan pilih berkas CSV atau Excel terlebih dahulu.');
                $this->toastWarning('Silakan pilih berkas CSV atau Excel terlebih dahulu.');

                return;
            }

            if (! ($file instanceof \Illuminate\Http\UploadedFile || $file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)) {
                $this->addError('importFile', 'Format berkas tidak valid.');
                $this->dialogError('Gagal Mengunggah Berkas', 'Berkas yang diunggah tidak valid.');

                return;
            }

            $path = $file->getRealPath();
            $ext = strtolower($file->getClientOriginalExtension());

            if (! in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
                $this->addError('importFile', 'Hanya berkas format .xlsx, .xls, atau .csv yang didukung.');
                $this->dialogError('Format Berkas Tidak Didukung', 'Silakan unggah berkas berformat .xlsx, .xls, atau .csv.');

                return;
            }

            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $parsed = $importer->parseExcel($path);
            } else {
                $parsed = $importer->parse((string) file_get_contents($path));
            }
        } elseif ($this->importMode === 'link') {
            $this->validate([
                'googleSheetsUrl' => ['required', 'url'],
            ], [
                'googleSheetsUrl.required' => 'Tautan Google Sheets wajib diisi.',
                'googleSheetsUrl.url' => 'Format URL Google Sheets tidak valid.',
            ]);

            $parsed = $importer->parseGoogleSheets($this->googleSheetsUrl);
        } else {
            // Paste mode
            if (empty(trim($this->csvContent))) {
                $this->toastWarning('Konten data tidak boleh kosong.');

                return;
            }
            $parsed = $importer->parse($this->csvContent);
        }

        if ($parsed->isEmpty()) {
            $this->dialogError(
                'Gagal Memproses Data',
                'Tidak ada data yang dapat dibaca dari berkas. Pastikan berkas memiliki baris header dengan kolom nomor registrasi (no_reg_lpk) dan nama laboratorium (nama_lpk).'
            );

            return;
        }

        $result = $action->execute($parsed, $defaultPicUserId);

        $this->showImportModal = false;
        $this->reset(['importFile', 'googleSheetsUrl', 'csvContent']);

        if ($result->created === 0 && $result->updated === 0) {
            $errMsg = ! empty($result->errors) ? implode("\n", array_slice($result->errors, 0, 3)) : 'Tidak ada baris data yang berhasil disimpan.';
            $this->dialogError('Impor Tidak Menghasilkan Data', $errMsg);
        } else {
            $msg = "Sebanyak {$result->created} data baru berhasil dibuat dan {$result->updated} data berhasil diperbarui.";
            if ($result->skipped > 0) {
                $msg .= " ({$result->skipped} baris dilewati karena kendala validasi).";
            }
            $this->dialogSuccess('Impor Selesai', $msg);
        }
    }

    public function confirmDeleteLpk(int $id): void
    {
        $lpk = Lpk::findOrFail($id);
        $this->authorize('delete', $lpk);

        $this->dialog()
            ->question('Hapus Data Laboratorium?', "Yakin ingin menghapus data Laboratorium {$lpk->no_reg} ({$lpk->name})? Seluruh riwayat asesmen dan agenda terkait juga akan dihapus.")
            ->confirm('Ya, Hapus Data', 'deleteLpk', $id)
            ->cancel('Batal')
            ->send();
    }

    public function deleteLpk(int $id, ?DeleteLpkAction $action = null): void
    {
        try {
            $lpk = Lpk::findOrFail($id);
            $this->authorize('delete', $lpk);

            $action = $action ?? app(DeleteLpkAction::class);
            $noReg = $lpk->no_reg;
            $name = $lpk->name;
            $action->execute($lpk);

            $this->dialogSuccess('Berhasil Dihapus', "Data Laboratorium {$noReg} ({$name}) beserta seluruh riwayat asesmennya telah berhasil dihapus dari sistem.");
        } catch (\Throwable $e) {
            $this->dialogError('Gagal Menghapus Data', $e->getMessage());
        }
    }

    public function confirmDeleteSelected(): void
    {
        if (empty($this->selected)) {
            $this->toastWarning('Tidak ada data laboratorium yang dipilih.');

            return;
        }

        $count = count($this->selected);

        $this->dialog()
            ->question("Hapus {$count} Data Laboratorium?", "Yakin ingin menghapus {$count} data laboratorium terpilih? Seluruh riwayat asesmen dan agenda terkait juga akan dihapus.")
            ->confirm('Ya, Hapus Data', 'deleteSelected')
            ->cancel('Batal')
            ->send();
    }

    public function deleteSelected(?DeleteLpkAction $action = null): void
    {
        if (empty($this->selected)) {
            return;
        }

        try {
            $action = $action ?? app(DeleteLpkAction::class);
            $user = Auth::user();
            $query = Lpk::whereIn('id', $this->selected);
            if ($user && $user->isPic()) {
                $query->where('pic_user_id', $user->id);
            }

            $lpks = $query->get();
            $deletedCount = 0;

            foreach ($lpks as $lpk) {
                $this->authorize('delete', $lpk);
                $action->execute($lpk);
                $deletedCount++;
            }

            $this->selected = [];
            $this->dialogSuccess('Berhasil Dihapus', "Sebanyak {$deletedCount} data laboratorium terpilih telah berhasil dihapus dari sistem.");
        } catch (\Throwable $e) {
            $this->dialogError('Gagal Menghapus Data', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();

        $sortColumn = match ($this->sort['column'] ?? 'no_reg') {
            'registration_number', 'no_reg' => 'registration_number',
            'name' => 'name',
            'expired_at' => 'expired_at',
            'status' => 'status',
            'created_at' => 'created_at',
            default => 'registration_number',
        };
        $sortDirection = ($this->sort['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query = Lpk::query()
            ->with(['pic', 'assessments' => fn ($q) => $q->latest('start_at')])
            ->forPic($user)
            ->search($this->search)
            ->status($this->selectedStatus ? LpkStatus::tryFrom($this->selectedStatus) : null);

        if ($sortColumn === 'registration_number') {
            $query->orderByRaw("COALESCE(registration_number, no_reg) {$sortDirection}");
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $lpks = $query->paginate($this->quantity);

        $statusOptions = [];
        foreach (LpkStatus::cases() as $s) {
            $statusOptions[] = [
                'label' => $s->label(),
                'value' => $s->value,
            ];
        }

        $quantityOptions = [
            ['label' => '10 per hal.', 'value' => 10],
            ['label' => '25 per hal.', 'value' => 25],
            ['label' => '50 per hal.', 'value' => 50],
            ['label' => '100 per hal.', 'value' => 100],
        ];

        return view('livewire.lpks.lpk-index', [
            'lpks' => $lpks,
            'statuses' => LpkStatus::cases(),
            'statusOptions' => $statusOptions,
            'quantityOptions' => $quantityOptions,
            'isAdmin' => $user && $user->isAdmin(),
        ]);
    }
}
