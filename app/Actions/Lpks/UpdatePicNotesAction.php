<?php

declare(strict_types=1);

namespace App\Actions\Lpks;

use App\Models\Lpk;

class UpdatePicNotesAction
{
    public function execute(Lpk $lpk, string $notes): Lpk
    {
        $lpk->update(['pic_notes' => $notes]);

        return $lpk->fresh();
    }
}
