<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class PicNoteForm extends Form
{
    #[Validate('nullable|string')]
    public string $notes = '';
}
