<?php

declare(strict_types=1);

namespace App\Livewire\Assessments;

use App\Actions\Assessments\CompleteEhaAction;
use App\Actions\Assessments\ExtendTpDeadlineAction;
use App\Actions\Assessments\IssueSkAction;
use App\Actions\Assessments\RecordAssessmentReportAction;
use App\Actions\Assessments\SatisfyTpAction;
use App\Exceptions\InvalidTpExtensionException;
use App\Livewire\Concerns\HasToast;
use App\Models\Assessment;
use App\Services\TpDeadlineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AssessmentShow extends Component
{
    use HasToast;

    public Assessment $assessment;

    // Modal state: Perpanjangan Batas Waktu
    public bool $showExtensionModal = false;

    public string $extensionLetterNo = '';

    // Modal state: Pemenuhan TP
    public bool $showSatisfyModal = false;

    public string $satisfiedDate = '';

    // Modal state: Laporan Asesmen
    public bool $showReportModal = false;

    public string $reportDate = '';

    // Modal state: Evaluasi Hasil Asesmen (EHA)
    public bool $showEhaModal = false;

    public string $ehaDate = '';

    public string $ehaNotes = '';

    // Modal state: Terbit SK KAN
    public bool $showSkModal = false;

    public string $skDate = '';

    public string $skNumber = '';

    // Update Temuan Langsung
    public int $editFindingsTotal = 0;

    public int $editFindingsClosed = 0;

    public bool $showFindingsModal = false;

    public function mount(int $id): void
    {
        $this->assessment = Assessment::with(['lpk.pic', 'creator'])->findOrFail($id);
        $this->authorize('view', $this->assessment);

        $this->editFindingsTotal = $this->assessment->tp_findings_total;
        $this->editFindingsClosed = $this->assessment->tp_findings_closed;
        $this->satisfiedDate = Carbon::now()->toDateString();
        $this->reportDate = Carbon::now()->toDateString();
        $this->ehaDate = Carbon::now()->toDateString();
        $this->skDate = Carbon::now()->toDateString();
    }

    public function openFindingsModal(): void
    {
        $this->authorize('update', $this->assessment);
        $this->editFindingsTotal = $this->assessment->tp_findings_total;
        $this->editFindingsClosed = $this->assessment->tp_findings_closed;
        $this->showFindingsModal = true;
    }

    public function saveFindings(): void
    {
        $this->authorize('update', $this->assessment);

        if ($this->editFindingsClosed > $this->editFindingsTotal) {
            $this->dialogError('Kesalahan Input', 'Temuan yang tertutup tidak boleh melebihi total temuan.');

            return;
        }

        $this->assessment->update([
            'tp_findings_total' => $this->editFindingsTotal,
            'tp_findings_closed' => $this->editFindingsClosed,
        ]);

        $this->showFindingsModal = false;
        $this->dialogSuccess('Progres Disimpan', 'Jumlah progres temuan ketidaksesuaian berhasil diperbarui.');
    }

    public function openExtensionModal(): void
    {
        $this->authorize('update', $this->assessment);
        $this->reset('extensionLetterNo');
        $this->showExtensionModal = true;
    }

    public function applyExtension(ExtendTpDeadlineAction $action): void
    {
        $this->authorize('update', $this->assessment);

        if (empty(trim($this->extensionLetterNo))) {
            $this->dialogWarning('Data Kurang', 'Nomor surat perpanjangan KAN wajib diisi.');

            return;
        }

        try {
            $this->assessment = $action->execute($this->assessment, $this->extensionLetterNo);
            $this->showExtensionModal = false;
            $this->dialogSuccess('Perpanjangan Disetujui', 'Perpanjangan batas waktu tindakan perbaikan (+1 bulan kalender) berhasil disetujui.');
        } catch (InvalidTpExtensionException $e) {
            $this->dialogError('Perpanjangan Ditolak', $e->getMessage());
        }
    }

    public function openSatisfyModal(): void
    {
        $this->authorize('update', $this->assessment);
        $this->satisfiedDate = Carbon::now()->toDateString();
        $this->showSatisfyModal = true;
    }

    public function saveSatisfied(SatisfyTpAction $action): void
    {
        $this->authorize('update', $this->assessment);

        if (empty($this->satisfiedDate)) {
            $this->dialogWarning('Data Kurang', 'Tanggal pemenuhan wajib dipilih.');

            return;
        }

        $this->assessment = $action->execute($this->assessment, $this->satisfiedDate);
        $this->showSatisfyModal = false;
        $this->dialogSuccess('Tindakan Perbaikan Memenuhi', 'Seluruh temuan ketidaksesuaian dinyatakan selesai dan memenuhi standar.');
    }

    public function openReportModal(): void
    {
        $this->authorize('update', $this->assessment);
        $this->reportDate = Carbon::now()->toDateString();
        $this->showReportModal = true;
    }

    public function saveReport(RecordAssessmentReportAction $action): void
    {
        $this->authorize('update', $this->assessment);

        if (empty($this->reportDate)) {
            $this->dialogWarning('Data Kurang', 'Tanggal laporan asesmen wajib dipilih.');

            return;
        }

        $this->assessment = $action->execute($this->assessment, $this->reportDate);
        $this->showReportModal = false;
        $this->dialogSuccess('Laporan Dicatat', 'Tanggal laporan asesmen lapangan berhasil dicatat.');
    }

    public function openEhaModal(): void
    {
        $this->authorize('update', $this->assessment);
        $this->ehaDate = Carbon::now()->toDateString();
        $this->ehaNotes = (string) $this->assessment->eha_notes;
        $this->showEhaModal = true;
    }

    public function saveEha(CompleteEhaAction $action): void
    {
        $this->authorize('update', $this->assessment);

        if (empty($this->ehaDate)) {
            $this->dialogWarning('Data Kurang', 'Tanggal realisasi sidang EHA wajib diisi.');

            return;
        }

        $this->assessment = $action->execute($this->assessment, $this->ehaDate, $this->ehaNotes);
        $this->showEhaModal = false;
        $this->dialogSuccess('Hasil EHA Dicatat', 'Evaluasi Hasil Asesmen (EHA) berhasil dicatat ke siklus akreditasi.');
    }

    public function openSkModal(): void
    {
        $this->authorize('update', $this->assessment);
        $this->skDate = Carbon::now()->toDateString();
        $this->skNumber = (string) $this->assessment->sk_number;
        $this->showSkModal = true;
    }

    public function saveSk(IssueSkAction $action): void
    {
        $this->authorize('update', $this->assessment);

        if (empty($this->skDate) || empty(trim($this->skNumber))) {
            $this->dialogWarning('Data Kurang', 'Tanggal terbit dan nomor SK resmi KAN wajib diisi.');

            return;
        }

        $this->assessment = $action->execute($this->assessment, $this->skDate, $this->skNumber);
        $this->showSkModal = false;
        $this->dialogSuccess('SK KAN Dicatat', "SK KAN {$this->assessment->sk_number} berhasil dicatat.");
    }

    public function confirmDeleteAssessment(): void
    {
        $this->authorize('delete', $this->assessment);

        $this->dialog()
            ->question('Hapus Data Asesmen?', "Yakin ingin menghapus seluruh data asesmen lapangan {$this->assessment->assessment_type->label()} ini? Seluruh riwayat temuan ketidaksesuaian terkait juga akan dihapus permanen.")
            ->confirm('Ya, Hapus Asesmen', 'deleteAssessment')
            ->cancel('Batal')
            ->send();
    }

    public function deleteAssessment(): void
    {
        try {
            $this->authorize('delete', $this->assessment);
            $typeLabel = $this->assessment->assessment_type->label();
            $this->assessment->delete();

            $this->dialog()->success('Berhasil Dihapus', "Data asesmen {$typeLabel} telah berhasil dihapus dari sistem.")->flash()->send();
            $this->redirectRoute('assessments.index', navigate: true);
        } catch (\Throwable $e) {
            $this->dialogError('Gagal Menghapus Asesmen', $e->getMessage());
        }
    }

    public function render(TpDeadlineService $deadlineService)
    {
        $isEligibleForExtension = $deadlineService->isEligibleForExtension($this->assessment);
        $effectiveDueDate = $this->assessment->effectiveTpDueDate();
        $earlyReminderDate = $deadlineService->calculateEarlyReminderDate(
            $this->assessment->assessment_type,
            $this->assessment->end_at
        );

        $tpStatus = $deadlineService->evaluateTpStatus($this->assessment);

        return view('livewire.assessments.assessment-show', [
            'isEligibleForExtension' => $isEligibleForExtension,
            'effectiveDueDate' => $effectiveDueDate,
            'earlyReminderDate' => $earlyReminderDate,
            'tpStatus' => $tpStatus,
            'user' => Auth::user(),
        ]);
    }
}
