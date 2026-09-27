<?php

declare(strict_types=1);

namespace App\Actions\Lpks;

use App\DTOs\LpkData;
use App\Models\Lpk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateLpkAction
{
    public function execute(LpkData $data): Lpk
    {
        return DB::transaction(function () use ($data) {
            $expiredAt = $data->expired_at;

            // Aturan KAN: Jika expired_at kosong, dihitung +5 tahun dari tanggal sertifikat
            if (empty($expiredAt) && ! empty($data->certificate_date)) {
                $expiredAt = Carbon::parse($data->certificate_date)->addYears(5)->toDateString();
            }

            $attributes = $data->toArray();
            $attributes['expired_at'] = $expiredAt;

            $lpk = Lpk::create($attributes);
            app(\App\Services\CalendarFeedService::class)->syncLpkCycleEvents($lpk);

            return $lpk;
        });
    }
}
