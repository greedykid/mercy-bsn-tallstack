<?php

declare(strict_types=1);

namespace App\Livewire\Assessments;

use App\Actions\Assessments\CreateAssessmentAction;
use App\Actions\Assessments\UpdateAssessmentAction;
use App\Enums\AssessmentType;
use App\Livewire\Concerns\HasToast;
use App\Livewire\Forms\AssessmentForm as AssessmentFormObject;
use App\Models\Assessment;
use App\Models\Lpk;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AssessmentForm extends Component
{
    use HasToast;

    public AssessmentFormObject $form;

    public ?Assessment $assessment = null;

    public bool $isEditing = false;

    #[Url]
    public ?int $lpk_id = null;

    #[Url]
    public ?string $type = null;

    public ?int $preselectedLpkId = null;

    public function mount(?int $id = null, ?int $lpk_id = null): void
    {
        $targetLpkId = $lpk_id ?? $this->lpk_id ?? (request()->has('lpk_id') ? (int) request()->query('lpk_id') : null);
        $targetType = $this->type ?? request()->query('type');

        $this->preselectedLpkId = $targetLpkId;

        if ($id) {
            $this->assessment = Assessment::with('lpk')->findOrFail($id);
            $this->authorize('update', $this->assessment);
            $this->isEditing = true;
            $this->form->setAssessment($this->assessment);
        } else {
            $this->authorize('create', Assessment::class);
            $this->isEditing = false;

            $selectedLpk = null;
            if ($this->preselectedLpkId) {
                $this->form->lpk_id = (int) $this->preselectedLpkId;
                $this->lpk_id = (int) $this->preselectedLpkId;
                $selectedLpk = Lpk::find($this->preselectedLpkId);
            }

            if ($targetType) {
                $matchedType = AssessmentType::tryFrom(strtoupper((string) $targetType));
                if ($matchedType) {
                    $this->form->assessment_type = $matchedType->value;
                }
            }

            $currentType = AssessmentType::tryFrom($this->form->assessment_type) ?? AssessmentType::SURVEILLANCE;

            if ($selectedLpk) {
                if (empty($this->form->title)) {
                    $this->form->title = "{$currentType->label()} {$selectedLpk->name}";
                }
                if (empty($this->form->location) && ($selectedLpk->city || $selectedLpk->address)) {
                    $this->form->location = $selectedLpk->city ?? $selectedLpk->address;
                }
            }

            $this->form->start_at = now()->addDays(7)->format('Y-m-d');
            $this->form->end_at = now()->addDays(9)->format('Y-m-d');
        }
    }

    public function updatedFormLpkId(mixed $value): void
    {
        $id = (int) $value;
        if ($id > 0) {
            $this->lpk_id = $id;
            $this->preselectedLpkId = $id;
            $lpk = Lpk::find($id);
            if ($lpk && ! $this->isEditing) {
                $currentType = AssessmentType::tryFrom($this->form->assessment_type) ?? AssessmentType::SURVEILLANCE;
                if (empty($this->form->title) || str_contains($this->form->title, 'Akreditasi Awal') || str_contains($this->form->title, 'Survailen') || str_contains($this->form->title, 'Re-Akreditasi') || str_contains($this->form->title, 'PRL')) {
                    $this->form->title = "{$currentType->label()} {$lpk->name}";
                }
                if (empty($this->form->location) && ($lpk->city || $lpk->address)) {
                    $this->form->location = $lpk->city ?? $lpk->address;
                }
            }
        }
    }

    public function save(CreateAssessmentAction $createAction, UpdateAssessmentAction $updateAction)
    {
        $this->form->validate();

        if ($this->isEditing && $this->assessment) {
            $this->authorize('update', $this->assessment);
            $assessment = $updateAction->execute($this->assessment, $this->form->toDto());
            $this->dialog()->success('Berhasil Diperbarui', "Jadwal Asesmen '{$assessment->title}' berhasil diperbarui.")->flash()->send();
        } else {
            $this->authorize('create', Assessment::class);
            $assessment = $createAction->execute($this->form->toDto(), Auth::id() ?? 1);
            $this->dialog()->success('Berhasil Dibuat', "Jadwal Asesmen '{$assessment->title}' berhasil dibuat.")->flash()->send();
        }

        return redirect()->route('assessments.show', $assessment->id);
    }

    public function render()
    {
        $user = Auth::user();
        $lpkQuery = Lpk::query()->orderBy('no_reg');

        if ($user && $user->isPic()) {
            $lpkQuery->where('pic_user_id', $user->id);
        }

        $lpks = $lpkQuery->get(['id', 'no_reg', 'registration_number', 'name', 'kan_schema']);

        $lpkOptions = $lpks->map(fn ($l) => [
            'label' => '[' . ($l->registration_number ?: $l->no_reg) . "] {$l->name}",
            'value' => (int) $l->id,
        ])->values()->all();

        $typeOptions = collect(AssessmentType::cases())->map(fn ($t) => [
            'label' => "{$t->label()} (Batas Waktu TP: {$t->normalDeadlineMonths()} Bulan)",
            'value' => $t->value,
        ])->values()->all();

        $statusOptions = [
            ['label' => 'Direncanakan (Planned)', 'value' => 'PLANNED'],
            ['label' => 'Berjalan (In Progress)', 'value' => 'IN_PROGRESS'],
            ['label' => 'Laporan Diterima (Report Submitted)', 'value' => 'REPORT_SUBMITTED'],
            ['label' => 'Sidang EHA (EHA Meeting)', 'value' => 'EHA_MEETING'],
            ['label' => 'SK Diterbitkan (SK Issued)', 'value' => 'SK_ISSUED'],
            ['label' => 'Selesai (Completed)', 'value' => 'COMPLETED'],
            ['label' => 'Dibatalkan (Cancelled)', 'value' => 'CANCELLED'],
        ];

        $selectedLpk = $this->form->lpk_id ? Lpk::find($this->form->lpk_id) : null;

        return view('livewire.assessments.assessment-form', [
            'lpks' => $lpks,
            'lpkOptions' => $lpkOptions,
            'selectedLpk' => $selectedLpk,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
            'assessmentTypes' => AssessmentType::cases(),
            'statuses' => [
                'PLANNED' => 'Direncanakan (Planned)',
                'IN_PROGRESS' => 'Berjalan (In Progress)',
                'REPORT_SUBMITTED' => 'Laporan Diterima (Report Submitted)',
                'EHA_MEETING' => 'Sidang EHA (EHA Meeting)',
                'SK_ISSUED' => 'SK Diterbitkan (SK Issued)',
                'COMPLETED' => 'Selesai (Completed)',
                'CANCELLED' => 'Dibatalkan (Cancelled)',
            ],
        ]);
    }
}
