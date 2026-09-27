<?php

declare(strict_types=1);

namespace App\Livewire\Lpks;

use App\Actions\Lpks\CreateLpkAction;
use App\Actions\Lpks\UpdateLpkAction;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Enums\UserRole;
use App\Livewire\Concerns\HasToast;
use App\Livewire\Forms\LpkForm as LpkFormObject;
use App\Models\Lpk;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LpkForm extends Component
{
    use HasToast;

    public LpkFormObject $form;

    public ?Lpk $lpk = null;

    public bool $isEditing = false;

    public function mount(?int $id = null): void
    {
        $user = Auth::user();

        if ($id) {
            $this->lpk = Lpk::findOrFail($id);
            $this->authorize('update', $this->lpk);
            $this->isEditing = true;
            $this->form->setLpk($this->lpk);

            if ($user && $user->isPic() && ! $this->form->pic_user_id) {
                $this->form->pic_user_id = $user->id;
            }
        } else {
            $this->authorize('create', Lpk::class);
            $this->isEditing = false;
            if ($user && $user->isPic()) {
                $this->form->pic_user_id = $user->id;
            }
        }
    }

    public function save(CreateLpkAction $createAction, UpdateLpkAction $updateAction)
    {
        $user = Auth::user();
        if ($user && $user->isPic()) {
            if (! $this->isEditing || ! $this->form->pic_user_id) {
                $this->form->pic_user_id = $user->id;
            }
        }

        $this->form->validate();

        if ($this->isEditing && $this->lpk) {
            $this->authorize('update', $this->lpk);
            $lpk = $updateAction->execute($this->lpk, $this->form->toDto());
            $this->dialog()->success('Berhasil Diperbarui', "Data Laboratorium Penguji {$lpk->no_reg} berhasil diperbarui.")->flash()->send();
        } else {
            $this->authorize('create', Lpk::class);
            $lpk = $createAction->execute($this->form->toDto());
            $this->dialog()->success('Berhasil Didaftarkan', "Laboratorium Penguji {$lpk->no_reg} ({$lpk->name}) berhasil didaftarkan.")->flash()->send();
        }

        return redirect()->route('lpks.show', $lpk->id);
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();
        $pics = User::where('role', UserRole::PIC->value)->orderBy('name')->get();

        $picOptions = $pics->map(fn ($p) => [
            'label' => "{$p->name} ({$p->email})",
            'value' => (int) $p->id,
        ])->values()->all();

        return view('livewire.lpks.lpk-form', [
            'isAdmin' => $isAdmin,
            'schemas' => KanSchema::cases(),
            'statuses' => LpkStatus::cases(),
            'pics' => $pics,
            'picOptions' => $picOptions,
        ]);
    }
}
