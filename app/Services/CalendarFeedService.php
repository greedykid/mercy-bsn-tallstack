<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CalendarEventType;
use App\Models\Assessment;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarFeedService
{
    /**
     * Sinkronisasi agenda asesmen ke tabel calendar_events
     */
    public function syncAssessmentEvents(Assessment $assessment): void
    {
        // 1. Event Pelaksanaan Asesmen Lapangan
        CalendarEvent::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'event_type' => CalendarEventType::ASSESSMENT,
            ],
            [
                'lpk_id' => $assessment->lpk_id,
                'user_id' => $assessment->created_by,
                'title' => "Asesmen: {$assessment->title} ({$assessment->lpk->no_reg})",
                'description' => "Lead Assessor: {$assessment->lead_assessor}, Lokasi: {$assessment->location}",
                'start_at' => Carbon::parse($assessment->start_at)->startOfDay(),
                'end_at' => Carbon::parse($assessment->end_at)->endOfDay(),
                'is_all_day' => true,
                'color' => CalendarEventType::ASSESSMENT->color(),
            ]
        );

        // 2. Event Batas Waktu Tindakan Perbaikan
        $tpDue = $assessment->effectiveTpDueDate();
        if ($tpDue && ! $assessment->isTpSatisfied()) {
            CalendarEvent::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'event_type' => CalendarEventType::TP_DEADLINE,
                ],
                [
                    'lpk_id' => $assessment->lpk_id,
                    'user_id' => $assessment->created_by,
                    'title' => "Batas Waktu TP: {$assessment->lpk->no_reg}",
                    'description' => "Jatuh tempo tindakan perbaikan untuk {$assessment->title}",
                    'start_at' => Carbon::parse($tpDue)->startOfDay(),
                    'end_at' => Carbon::parse($tpDue)->endOfDay(),
                    'is_all_day' => true,
                    'color' => CalendarEventType::TP_DEADLINE->color(),
                ]
            );
        } else {
            CalendarEvent::where('assessment_id', $assessment->id)
                ->where('event_type', CalendarEventType::TP_DEADLINE)
                ->delete();
        }

        // 3. Event Sidang EHA jika terjadwal
        if ($assessment->eha_scheduled_date && empty($assessment->eha_completed_at)) {
            CalendarEvent::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'event_type' => CalendarEventType::EHA_MEETING,
                ],
                [
                    'lpk_id' => $assessment->lpk_id,
                    'user_id' => $assessment->created_by,
                    'title' => "Sidang EHA: {$assessment->lpk->no_reg}",
                    'description' => "Evaluasi Hasil Asesmen untuk {$assessment->title}",
                    'start_at' => Carbon::parse($assessment->eha_scheduled_date)->startOfDay(),
                    'end_at' => Carbon::parse($assessment->eha_scheduled_date)->endOfDay(),
                    'is_all_day' => true,
                    'color' => CalendarEventType::EHA_MEETING->color(),
                ]
            );
        }
    }

    /**
     * Sinkronisasi batas waktu proses siklus akreditasi sertifikat (S1, S2, RA, SK Expiry) ke kalender
     */
    public function syncLpkCycleEvents(\App\Models\Lpk $lpk): void
    {
        $userId = $lpk->pic_user_id ?? 1;

        if ($lpk->certificate_date) {
            $certDate = Carbon::parse($lpk->certificate_date);

            // S1 Due (Bulan 14)
            $s1Date = $certDate->copy()->addMonths(14);
            CalendarEvent::updateOrCreate(
                [
                    'lpk_id' => $lpk->id,
                    'title' => "Batas Waktu Survailen 1 (S1): {$lpk->no_reg}",
                ],
                [
                    'user_id' => $userId,
                    'description' => "Jatuh tempo siklus pengawasan Survailen 1 (Bulan ke-14) untuk {$lpk->name}",
                    'start_at' => $s1Date->startOfDay(),
                    'end_at' => $s1Date->endOfDay(),
                    'is_all_day' => true,
                    'event_type' => CalendarEventType::SURVEILLANCE_DUE,
                    'color' => CalendarEventType::SURVEILLANCE_DUE->color(),
                ]
            );

            // S2 Due (Bulan 35)
            $s2Date = $certDate->copy()->addMonths(35);
            CalendarEvent::updateOrCreate(
                [
                    'lpk_id' => $lpk->id,
                    'title' => "Batas Waktu Survailen 2 (S2): {$lpk->no_reg}",
                ],
                [
                    'user_id' => $userId,
                    'description' => "Jatuh tempo siklus pengawasan Survailen 2 (Bulan ke-35) untuk {$lpk->name}",
                    'start_at' => $s2Date->startOfDay(),
                    'end_at' => $s2Date->endOfDay(),
                    'is_all_day' => true,
                    'event_type' => CalendarEventType::SURVEILLANCE_DUE,
                    'color' => CalendarEventType::SURVEILLANCE_DUE->color(),
                ]
            );
        }

        if ($lpk->expired_at) {
            $expDate = Carbon::parse($lpk->expired_at);

            // Re-Akreditasi Notice (6 Bulan sebelum expired)
            $raDate = $expDate->copy()->subMonths(6);
            CalendarEvent::updateOrCreate(
                [
                    'lpk_id' => $lpk->id,
                    'title' => "Batas Pengajuan Re-Akreditasi (RA): {$lpk->no_reg}",
                ],
                [
                    'user_id' => $userId,
                    'description' => "Batas pengajuan permohonan Re-Akreditasi (6 bulan sebelum kedaluwarsa) untuk {$lpk->name}",
                    'start_at' => $raDate->startOfDay(),
                    'end_at' => $raDate->endOfDay(),
                    'is_all_day' => true,
                    'event_type' => CalendarEventType::SURVEILLANCE_DUE,
                    'color' => 'red',
                ]
            );

            // Kedaluwarsa Sertifikat
            CalendarEvent::updateOrCreate(
                [
                    'lpk_id' => $lpk->id,
                    'title' => "Kedaluwarsa Sertifikat KAN: {$lpk->no_reg}",
                ],
                [
                    'user_id' => $userId,
                    'description' => "Masa berlaku sertifikat akreditasi berakhir untuk {$lpk->name}",
                    'start_at' => $expDate->startOfDay(),
                    'end_at' => $expDate->endOfDay(),
                    'is_all_day' => true,
                    'event_type' => CalendarEventType::SK_EXPIRY,
                    'color' => CalendarEventType::SK_EXPIRY->color(),
                ]
            );
        }
    }

    /**
     * Ambil seluruh agenda dalam rentang tanggal tertentu
     */
    public function getEventsForRange(Carbon $start, Carbon $end, ?int $picUserId = null): Collection
    {
        $query = CalendarEvent::with(['lpk', 'assessment'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('start_at', '<=', $start)
                            ->where('end_at', '>=', $end);
                    });
            });

        if ($picUserId) {
            $query->where(function ($q) use ($picUserId) {
                $q->where('user_id', $picUserId)
                    ->orWhereHas('lpk', fn ($lq) => $lq->where('pic_user_id', $picUserId));
            });
        }

        return $query->orderBy('start_at')->get();
    }

    /**
     * Ambil seluruh agenda untuk bulan tertentu (termasuk buffer grid)
     */
    public function getEventsForMonth(int $year, int $month, ?int $picUserId = null): Collection
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->subDays(7);
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->addDays(7);

        return $this->getEventsForRange($start, $end, $picUserId);
    }
}
