<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use TallStackUi\Traits\Interactions;

trait HasToast
{
    use Interactions;

    public function toastSuccess(string $content, ?string $title = 'Berhasil'): void
    {
        $this->toast()->success($title, $content)->send();
    }

    public function toastWarning(string $content, ?string $title = 'Perhatian'): void
    {
        $this->toast()->warning($title, $content)->send();
    }

    public function toastError(string $content, ?string $title = 'Terjadi Kesalahan'): void
    {
        $this->toast()->error($title, $content)->send();
    }

    public function toastInfo(string $content, ?string $title = 'Informasi'): void
    {
        $this->toast()->info($title, $content)->send();
    }

    public function dialogSuccess(string $title, ?string $description = null): void
    {
        $this->dialog()->success($title, $description)->send();
    }

    public function dialogWarning(string $title, ?string $description = null): void
    {
        $this->dialog()->warning($title, $description)->send();
    }

    public function dialogError(string $title, ?string $description = null): void
    {
        $this->dialog()->error($title, $description)->send();
    }

    public function dialogInfo(string $title, ?string $description = null): void
    {
        $this->dialog()->info($title, $description)->send();
    }
}
