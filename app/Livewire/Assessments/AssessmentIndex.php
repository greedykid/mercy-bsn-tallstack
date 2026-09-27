<?php

declare(strict_types=1);

namespace App\Livewire\Assessments;

use App\Enums\AssessmentType;
use App\Livewire\Concerns\HasToast;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AssessmentIndex extends Component
{
    use HasToast, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $selectedType = '';

    #[Url(as: 'status')]
    public string $selectedStatus = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedType(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearType(): void
    {
        $this->selectedType = '';
        $this->resetPage();
    }

    public function clearStatus(): void
    {
        $this->selectedStatus = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedType', 'selectedStatus']);
        $this->resetPage();
    }

    public function confirmDeleteAssessment(int $id): void
    {
        $assessment = Assessment::with('lpk')->findOrFail($id);
        $this->authorize('delete', $assessment);

        $this->dialog()
            ->question('Hapus Agenda Asesmen?', "Yakin ingin menghapus agenda asesmen {$assessment->assessment_type->label()} untuk {$assessment->lpk?->name}? Seluruh riwayat temuan terkait asesmen ini akan dihapus.")
            ->confirm('Ya, Hapus Asesmen', 'deleteAssessment', $id)
            ->cancel('Batal')
            ->send();
    }

    public function deleteAssessment(int $id): void
    {
        try {
            $assessment = Assessment::with('lpk')->findOrFail($id);
            $this->authorize('delete', $assessment);

            $typeLabel = $assessment->assessment_type->label();
            $labName = $assessment->lpk?->name ?? 'Laboratorium';
            $assessment->delete();

            $this->dialogSuccess('Berhasil Dihapus', "Agenda asesmen {$typeLabel} untuk {$labName} telah berhasil dihapus dari sistem.");
        } catch (\Throwable $e) {
            $this->dialogError('Gagal Menghapus Asesmen', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Assessment::query()
            ->with(['lpk.pic', 'creator'])
            ->forPic($user);

        if (! empty($this->search)) {
            $term = "%{$this->search}%";
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('lead_assessor', 'like', $term)
                    ->orWhere('location', 'like', $term)
                    ->orWhereHas('lpk', fn ($lq) => $lq->where('no_reg', 'like', $term)->orWhere('name', 'like', $term));
            });
        }

        if (! empty($this->selectedType)) {
            $query->where('assessment_type', $this->selectedType);
        }

        if (! empty($this->selectedStatus)) {
            $query->where('status', $this->selectedStatus);
        }

        $assessments = $query->orderByDesc('start_at')->paginate(15);

        $typeOptions = collect(AssessmentType::cases())->map(fn ($t) => [
            'label' => $t->label(),
            'value' => $t->value,
        ])->values()->all();

        $statusOptions = [
            ['label' => 'Direncanakan (Planned)', 'value' => 'PLANNED'],
            ['label' => 'Sedang Berlangsung (In Progress)', 'value' => 'IN_PROGRESS'],
            ['label' => 'Laporan Diterima', 'value' => 'REPORT_SUBMITTED'],
            ['label' => 'Sidang EHA', 'value' => 'EHA_MEETING'],
            ['label' => 'SK Diterbitkan', 'value' => 'SK_ISSUED'],
            ['label' => 'Selesai (Completed)', 'value' => 'COMPLETED'],
        ];

        return view('livewire.assessments.assessment-index', [
            'assessments' => $assessments,
            'types' => AssessmentType::cases(),
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
        ]);
    }
}
