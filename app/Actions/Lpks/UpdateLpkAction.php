<?php

declare(strict_types=1);

namespace App\Actions\Lpks;

use App\DTOs\LpkData;
use App\Models\Lpk;
use Illuminate\Support\Facades\DB;

class UpdateLpkAction
{
    public function execute(Lpk $lpk, LpkData $data): Lpk
    {
        return DB::transaction(function () use ($lpk, $data) {
            $lpk->update($data->toArray());

            $fresh = $lpk->fresh();
            app(\App\Services\CalendarFeedService::class)->syncLpkCycleEvents($fresh);

            return $fresh;
        });
    }
}
