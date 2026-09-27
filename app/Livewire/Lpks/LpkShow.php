<?php

declare(strict_types=1);

namespace App\Livewire\Lpks;

use App\Actions\Lpks\UpdatePicNotesAction;
use App\Enums\LpkStatus;
use App\Livewire\Concerns\HasToast;
use App\Livewire\Forms\PicNoteForm;
use App\Models\Lpk;
use App\Services\SurveillanceEngineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LpkShow extends Component
{
    use HasToast;

    public Lpk $lpk;

    public PicNoteForm $noteForm;

    public bool $showNoteModal = false;

    public function mount(int $id): void
    {
        $this->lpk = Lpk::with(['pic', 'assessments.creator'])->findOrFail($id);
        $this->authorize('view', $this->lpk);
        $this->noteForm->notes = (string) $this->lpk->notes;
    }

    public function openNoteModal(): void
    {
        $this->authorize('update', $this->lpk);
        $this->noteForm->notes = (string) $this->lpk->notes;
        $this->showNoteModal = true;
    }

    public function closeNoteModal(): void
    {
        $this->showNoteModal = false;
    }

    public function saveNotes(UpdatePicNotesAction $action): void
    {
        $this->authorize('update', $this->lpk);

        $action->execute($this->lpk, $this->noteForm->notes);
        $this->lpk->refresh();
        $this->showNoteModal = false;

        $this->dialogSuccess('Catatan Disimpan', 'Catatan khusus PIC pendamping KAN berhasil diperbarui.');
    }

    public function copySystemSummaryToNotes(SurveillanceEngineService $service): void
    {
        $summary = $this->generateSystemSummary($service);
        $this->noteForm->notes = $summary;
        $this->dialogInfo('Keterangan Sistem', 'Keterangan sistem berhasil disalin ke draf catatan.');
    }

    public function confirmDeleteLpk(): void
    {
        $this->authorize('delete', $this->lpk);

        $this->dialog()
            ->question('Hapus Data Laboratorium?', "Yakin ingin menghapus data Laboratorium {$this->lpk->no_reg} ({$this->lpk->name})? Seluruh riwayat asesmen dan agenda terkait juga akan dihapus permanen.")
            ->confirm('Ya, Hapus Data', 'deleteLpk')
            ->cancel('Batal')
            ->send();
    }

    public function deleteLpk(?\App\Actions\Lpks\DeleteLpkAction $action = null): void
    {
        try {
            $this->authorize('delete', $this->lpk);

            $action = $action ?? app(\App\Actions\Lpks\DeleteLpkAction::class);
            $noReg = $this->lpk->no_reg;
            $action->execute($this->lpk);

            $this->dialog()->success('Berhasil Dihapus', "Data Laboratorium {$noReg} telah berhasil dihapus dari sistem.")->flash()->send();
            $this->redirectRoute('lpks.index', navigate: true);
        } catch (\Throwable $e) {
            $this->dialogError('Gagal Menghapus Data', $e->getMessage());
        }
    }

    protected function generateSystemSummary(SurveillanceEngineService $service): string
    {
        $status = $service->evaluateLpkStatus($this->lpk);
        $certDate = $this->lpk->certificate_date?->format('d/m/Y') ?? '-';
        $expDate = $this->lpk->expired_at?->format('d/m/Y') ?? '-';

        return "[SISTEM KAN] Status Terhitung: {$status->label()} | Tgl Sertifikat: {$certDate} | Kedaluwarsa: {$expDate}. Diperbarui otomatis pada ".Carbon::now()->format('d/m/Y H:i');
    }

    public function render(SurveillanceEngineService $surveillanceService)
    {
        $dynamicStatus = $surveillanceService->evaluateLpkStatus($this->lpk);

        $suspensionCountdown = null;
        if ($dynamicStatus === LpkStatus::SUSPENDED && $this->lpk->certificate_date) {
            $latestAssessment = $this->lpk->assessments->first();
            $baseDate = $latestAssessment ? $latestAssessment->end_at : $this->lpk->certificate_date;
            $submissionDue = $surveillanceService->calculateSubmissionDueDate($baseDate);
            $suspensionCountdown = $surveillanceService->calculateSuspensionCountdown($submissionDue);
        }

        $lpkNotifications = $surveillanceService->getProcessNotifications(null, $this->lpk->id);

        return view('livewire.lpks.lpk-show', [
            'dynamicStatus' => $dynamicStatus,
            'suspensionCountdown' => $suspensionCountdown,
            'systemSummary' => $this->generateSystemSummary($surveillanceService),
            'lpkNotifications' => $lpkNotifications,
            'user' => Auth::user(),
        ]);
    }
}
