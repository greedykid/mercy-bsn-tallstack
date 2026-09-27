<?php

declare(strict_types=1);

namespace App\Actions\Lpks;

use App\Models\Lpk;
use Illuminate\Support\Facades\DB;

class DeleteLpkAction
{
    public function execute(Lpk $lpk): bool
    {
        return (bool) DB::transaction(function () use ($lpk) {
            return $lpk->delete();
        });
    }
}
