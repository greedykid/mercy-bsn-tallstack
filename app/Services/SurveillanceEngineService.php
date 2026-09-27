<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LpkStatus;
use App\Models\Assessment;
use App\Models\Lpk;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class SurveillanceEngineService
{
    /**
     * Cache in-memory per request lifecycle untuk getProcessNotifications
     *
     * @var array<string, \Illuminate\Support\Collection>
     */
    protected array $memoizedNotifications = [];

    /**
     * Cache in-memory per request lifecycle untuk determineActiveCycle
     *
     * @var array<string, array>
     */
    protected array $memoizedActiveCycles = [];

    public function clearNotificationCache(): void
    {
        $this->memoizedNotifications = [];
        $this->memoizedActiveCycles = [];

        $cachedKeys = Cache::get('surveillance_cached_keys', []);
        if (is_array($cachedKeys)) {
            foreach ($cachedKeys as $key) {
                Cache::forget($key);
            }
        }
        Cache::forget('surveillance_cached_keys');
        Cache::forget('sidebar_lpk_count');
    }

    protected function trackCacheKey(string $key): void
    {
        $cachedKeys = Cache::get('surveillance_cached_keys', []);
        if (! is_array($cachedKeys)) {
            $cachedKeys = [];
        }
        if (! in_array($key, $cachedKeys, true)) {
            $cachedKeys[] = $key;
            Cache::put('surveillance_cached_keys', $cachedKeys, 86400);
        }
    }

    /**
     * Hitung tonggak surveilen 5 tahun KAN beserta tanggal toleransi dan status auto-realisasi
     *
     * @return array<int, array{year: int, label: string, due_date: string, tolerance_date: string, status: string}>
     */
    public function calculateMilestones(CarbonInterface $certificateDate, ?Lpk $lpk = null): array
    {
        $milestones = [];
        $base = Carbon::parse($certificateDate);

        for ($year = 1; $year <= 5; $year++) {
            $dueDate = $base->copy()->addYears($year);
            $toleranceDate = $dueDate->copy()->endOfMonth();
            $label = $year === 5 ? 'Re-Akreditasi (Tahun 5)' : "Surveilen {$year} (Tahun {$year})";

            $isRealized = false;
            if ($lpk) {
                if ($year === 1) {
                    $hasAss = $lpk->assessments->contains(fn ($a) => in_array($a->assessment_type, [\App\Enums\AssessmentType::SURVEILLANCE, \App\Enums\AssessmentType::SURVEILLANCE_PRL], true) && $a->isTpSatisfied());
                    if ($hasAss || ($lpk->status === LpkStatus::ACTIVE && $dueDate->lessThan(Carbon::now()))) {
                        $isRealized = true;
                    }
                } elseif ($year === 2) {
                    $hasAss = $lpk->assessments->contains(fn ($a) => in_array($a->assessment_type, [\App\Enums\AssessmentType::SURVEILLANCE_2, \App\Enums\AssessmentType::SURVEILLANCE_2_PRL], true) && $a->isTpSatisfied());
                    if ($hasAss || ($lpk->status === LpkStatus::ACTIVE && $dueDate->lessThan(Carbon::now()))) {
                        $isRealized = true;
                    }
                } elseif ($year === 5) {
                    $hasAss = $lpk->assessments->contains(fn ($a) => $a->assessment_type === \App\Enums\AssessmentType::REASSESSMENT && $a->isTpSatisfied());
                    if ($hasAss) {
                        $isRealized = true;
                    }
                } elseif ($lpk->status === LpkStatus::ACTIVE && $dueDate->lessThan(Carbon::now())) {
                    $isRealized = true;
                }
            }

            $milestones[] = [
                'year' => $year,
                'label' => $label,
                'due_date' => $dueDate->toDateString(),
                'tolerance_date' => $toleranceDate->toDateString(),
                'status' => $isRealized ? 'REALIZED' : 'PENDING',
            ];
        }

        return $milestones;
    }

    /**
     * Evaluasi kepatuhan surveilen, pembekuan (suspension), dan pencabutan (revocation)
     */
    public function evaluateCompliance(Lpk $lpk): LpkStatus
    {
        if ($lpk->status === LpkStatus::REVOKED) {
            return LpkStatus::REVOKED;
        }

        // Jika saat ini berstatus SUSPENDED, cek batas waktu 1 tahun
        if ($lpk->status === LpkStatus::SUSPENDED) {
            $suspendedAt = $lpk->suspended_at ? Carbon::parse($lpk->suspended_at) : Carbon::parse($lpk->updated_at);
            if (Carbon::now()->greaterThanOrEqualTo($suspendedAt->copy()->addYear())) {
                $lpk->update([
                    'status' => LpkStatus::REVOKED,
                    'revoked_at' => Carbon::now(),
                ]);

                return LpkStatus::REVOKED;
            }

            return LpkStatus::SUSPENDED;
        }

        // Cek toleransi dari tanggal sertifikat (Year 1)
        if ($lpk->certificate_date) {
            $milestones = $this->calculateMilestones($lpk->certificate_date, $lpk);
            $year1Tolerance = Carbon::parse($milestones[0]['tolerance_date']);

            if (Carbon::now()->greaterThan($year1Tolerance)) {
                $lpk->update([
                    'status' => LpkStatus::SUSPENDED,
                    'suspended_at' => Carbon::now(),
                ]);

                return LpkStatus::SUSPENDED;
            }
        }

        return $lpk->status;
    }

    public function calculateSubmissionDueDate(CarbonInterface $endAt): Carbon
    {
        return Carbon::parse($endAt)->endOfMonth();
    }

    public function calculateSuspensionExpiryDate(CarbonInterface $submissionDueDate): Carbon
    {
        return Carbon::parse($submissionDueDate)->addYear();
    }

    public function isToleranceExpired(CarbonInterface $endAt): bool
    {
        return Carbon::now()->greaterThan($this->calculateSubmissionDueDate($endAt));
    }

    public function isSuspensionExpired(CarbonInterface $submissionDueDate): bool
    {
        return Carbon::now()->greaterThan($this->calculateSuspensionExpiryDate($submissionDueDate));
    }

    /**
     * Hitung sisa hari dan bulan masa pembekuan 1 tahun
     *
     * @return array{months: int, days: int, is_expired: bool}
     */
    public function calculateSuspensionCountdown(CarbonInterface $submissionDueDate): array
    {
        $expiry = $this->calculateSuspensionExpiryDate($submissionDueDate);
        $now = Carbon::now();

        if ($now->greaterThanOrEqualTo($expiry)) {
            return ['months' => 0, 'days' => 0, 'is_expired' => true];
        }

        $diffMonths = (int) $now->diffInMonths($expiry);
        $diffDays = (int) $now->copy()->addMonths($diffMonths)->diffInDays($expiry);

        return [
            'months' => $diffMonths,
            'days' => $diffDays,
            'is_expired' => false,
        ];
    }

    /**
     * Evaluasi status LPK dinamis berdasarkan siklus akreditasi KAN
     */
    public function evaluateLpkStatus(Lpk $lpk): LpkStatus
    {
        if ($lpk->status === LpkStatus::REVOKED) {
            return LpkStatus::REVOKED;
        }

        if ($lpk->status === LpkStatus::INACTIVE) {
            return LpkStatus::INACTIVE;
        }

        // Cek kedaluwarsa sertifikat
        if ($lpk->expired_at && Carbon::now()->greaterThan($lpk->expired_at)) {
            return LpkStatus::EXPIRED;
        }

        // Evaluasi toleransi asesmen aktif berjalan
        /** @var Assessment|null $latestAssessment */
        $latestAssessment = $lpk->relationLoaded('assessments')
            ? $lpk->assessments->sortByDesc('start_at')->first()
            : $lpk->assessments()->latest('start_at')->first();

        if ($latestAssessment && ! $latestAssessment->isTpSatisfied()) {
            $submissionDue = $this->calculateSubmissionDueDate($latestAssessment->end_at);

            // Jika melewati batas akhir bulan kunjungan tanpa laporan/pemenuhan
            if (Carbon::now()->greaterThan($submissionDue)) {
                if ($this->isSuspensionExpired($submissionDue)) {
                    return LpkStatus::REVOKED;
                }

                return LpkStatus::SUSPENDED;
            }

            // Jika batas waktu TP terlewati tanpa pemenuhan
            $effectiveTpDue = $latestAssessment->effectiveTpDueDate();
            if ($effectiveTpDue && Carbon::now()->greaterThan($effectiveTpDue)) {
                $tpSuspensionDue = $effectiveTpDue->copy()->addYear();
                if (Carbon::now()->greaterThan($tpSuspensionDue)) {
                    return LpkStatus::REVOKED;
                }

                return LpkStatus::SUSPENDED;
            }
        }

        // Evaluasi tonggak siklus pengawasan dari tanggal sertifikat
        if ($lpk->certificate_date) {
            $certDate = Carbon::parse($lpk->certificate_date);
            $monthsSinceCert = (int) $certDate->diffInMonths(Carbon::now());
            $isActiveInDb = ($lpk->status === LpkStatus::ACTIVE);

            // Surveilen 1: Bulan 14 (due) s.d. Bulan 15 (target batas)
            $hasS1 = $lpk->relationLoaded('assessments')
                ? $lpk->assessments->contains(fn ($a) => str_starts_with($a->assessment_type->value ?? (string) $a->assessment_type, 'SURVEILLANCE'))
                : $lpk->assessments()
                    ->where('assessment_type', 'like', 'SURVEILLANCE%')
                    ->exists();

            if (! $hasS1) {
                if ($monthsSinceCert >= 15) {
                    if (! $isActiveInDb) {
                        return LpkStatus::SURVEILLANCE_OVERDUE;
                    }
                    // Jika LPK aktif, siklus sebelum fase saat ini otomatis terealisasi
                } elseif ($monthsSinceCert >= 14) {
                    return LpkStatus::SURVEILLANCE_DUE;
                }
            }

            // Surveilen 2: Bulan 35 (due) s.d. Bulan 36 (target batas)
            if ($monthsSinceCert >= 35) {
                $hasS2 = $lpk->relationLoaded('assessments')
                    ? $lpk->assessments->contains(fn ($a) => str_starts_with($a->assessment_type->value ?? (string) $a->assessment_type, 'SURVEILLANCE_2'))
                    : $lpk->assessments()
                        ->where('assessment_type', 'like', 'SURVEILLANCE_2%')
                        ->exists();

                if (! $hasS2) {
                    if ($monthsSinceCert >= 36) {
                        if (! $isActiveInDb) {
                            return LpkStatus::SURVEILLANCE_OVERDUE;
                        }
                        // Jika LPK aktif, siklus S2 sebelum fase saat ini otomatis terealisasi
                    } else {
                        return LpkStatus::SURVEILLANCE_DUE;
                    }
                }
            }
        }

        return LpkStatus::ACTIVE;
    }

    /**
     * Tentukan siklus asesmen berjalan atau siklus yang perlu diisi berikutnya untuk LPK.
     * Untuk LPK yang masih aktif, siklus asesmen sebelumnya otomatis sudah terealisasi
     * dan sistem hanya memunculkan asesmen yang perlu diisi di siklus ke depannya.
     *
     * @return array{
     *     code: string,
     *     label: string,
     *     status: string,
     *     description: string,
     *     target_date: ?\Carbon\CarbonInterface,
     *     assessment: ?Assessment
     * }
     */
    public function determineActiveCycle(Lpk $lpk): array
    {
        $cacheKey = (string) $lpk->id;
        if (isset($this->memoizedActiveCycles[$cacheKey])) {
            return $this->memoizedActiveCycles[$cacheKey];
        }

        $certDate = $lpk->certificate_date ? Carbon::parse($lpk->certificate_date) : null;
        $now = Carbon::now();
        $assessments = $lpk->assessments;

        // Kasus 1: Belum memiliki sertifikat -> Akreditasi Awal (AA)
        if (! $certDate) {
            $aa = $assessments->first(fn ($a) => $a->assessment_type === \App\Enums\AssessmentType::INITIAL);
            if ($aa && ! $aa->isTpSatisfied()) {
                $tpDue = $aa->effectiveTpDueDate();
                return $this->memoizedActiveCycles[$cacheKey] = [
                    'code' => 'AA',
                    'label' => 'Akreditasi Awal',
                    'status' => 'PELAKSANAAN',
                    'description' => 'Realisasi ' . $aa->start_at->format('d/m/Y') . ($tpDue ? ' (Batas TP: ' . $tpDue->format('d/m/Y') . ')' : '.'),
                    'target_date' => $tpDue,
                    'assessment' => $aa,
                ];
            }

            return $this->memoizedActiveCycles[$cacheKey] = [
                'code' => 'AA',
                'label' => 'Akreditasi Awal',
                'status' => 'PELAKSANAAN',
                'description' => 'Dalam proses pengajuan akreditasi awal.',
                'target_date' => null,
                'assessment' => null,
            ];
        }

        // Kasus 2: Ada asesmen aktif yang sedang berjalan dan belum selesai TP
        $ongoing = $assessments->first(fn ($a) => ! $a->isTpSatisfied());
        if ($ongoing) {
            $tpDue = $ongoing->effectiveTpDueDate();
            $code = match ($ongoing->assessment_type) {
                \App\Enums\AssessmentType::SURVEILLANCE, \App\Enums\AssessmentType::SURVEILLANCE_PRL => 'S1',
                \App\Enums\AssessmentType::SURVEILLANCE_2, \App\Enums\AssessmentType::SURVEILLANCE_2_PRL => 'S2',
                \App\Enums\AssessmentType::REASSESSMENT => 'RA',
                \App\Enums\AssessmentType::SCOPE_EXTENSION => 'PRL',
                \App\Enums\AssessmentType::UNSCHEDULED_SURVEILLANCE => 'STT',
                default => 'AA',
            };

            return $this->memoizedActiveCycles[$cacheKey] = [
                'code' => $code,
                'label' => $ongoing->assessment_type->label(),
                'status' => 'PELAKSANAAN',
                'description' => 'Realisasi ' . $ongoing->start_at->format('d/m/Y') . ($tpDue ? ' (Batas TP: ' . $tpDue->format('d/m/Y') . ')' : '.'),
                'target_date' => $tpDue,
                'assessment' => $ongoing,
            ];
        }

        // Kasus 3: Tidak ada asesmen berjalan, tentukan siklus ke depan
        $isActive = ($lpk->status === LpkStatus::ACTIVE);
        $months = (int) $certDate->diffInMonths($now);

        // Evaluasi realisasi S1
        $hasS1Completed = $assessments->contains(function ($a) {
            return in_array($a->assessment_type, [
                \App\Enums\AssessmentType::SURVEILLANCE,
                \App\Enums\AssessmentType::SURVEILLANCE_PRL,
            ], true) && $a->isTpSatisfied();
        });
        $s1Realized = $hasS1Completed || ($isActive && $months >= 15);

        // Evaluasi realisasi S2
        $hasS2Completed = $assessments->contains(function ($a) {
            return in_array($a->assessment_type, [
                \App\Enums\AssessmentType::SURVEILLANCE_2,
                \App\Enums\AssessmentType::SURVEILLANCE_2_PRL,
            ], true) && $a->isTpSatisfied();
        });
        $s2Realized = $hasS2Completed || ($isActive && $months >= 36);

        if (! $s1Realized) {
            $s1Deadline = $certDate->copy()->addMonths(15)->endOfMonth();
            $isOverdue = ! $isActive && $now->greaterThan($s1Deadline);

            return $this->memoizedActiveCycles[$cacheKey] = [
                'code' => 'S1',
                'label' => 'Surveilen 1',
                'status' => $isOverdue ? 'TERLEWAT' : 'TERJADWAL',
                'description' => $isOverdue
                    ? 'Melewati batas toleransi ' . $s1Deadline->format('d/m/Y') . '.'
                    : 'Terjadwal ' . $s1Deadline->format('d/m/Y') . '.',
                'target_date' => $s1Deadline,
                'assessment' => null,
            ];
        }

        if (! $s2Realized) {
            $s2Deadline = $certDate->copy()->addMonths(36)->endOfMonth();
            $isOverdue = ! $isActive && $now->greaterThan($s2Deadline);

            return $this->memoizedActiveCycles[$cacheKey] = [
                'code' => 'S2',
                'label' => 'Surveilen 2',
                'status' => $isOverdue ? 'TERLEWAT' : 'TERJADWAL',
                'description' => $isOverdue
                    ? 'Melewati batas toleransi ' . $s2Deadline->format('d/m/Y') . '.'
                    : 'Terjadwal ' . $s2Deadline->format('d/m/Y') . '.',
                'target_date' => $s2Deadline,
                'assessment' => null,
            ];
        }

        // Siklus Re-Akreditasi
        $raTarget = $lpk->expired_at ? Carbon::parse($lpk->expired_at) : $certDate->copy()->addYears(5);
        $isExpired = $now->greaterThan($raTarget);

        return $this->memoizedActiveCycles[$cacheKey] = [
            'code' => 'RA',
            'label' => 'Re-Akreditasi',
            'status' => $isExpired ? 'KEDALUWARSA' : 'TERJADWAL',
            'description' => $isExpired
                ? 'Masa akreditasi habis sejak ' . $raTarget->format('d/m/Y') . '.'
                : 'Terjadwal ' . $raTarget->format('d/m/Y') . '.',
            'target_date' => $raTarget,
            'assessment' => null,
        ];
    }

    /**
     * Dapatkan daftar notifikasi deadline proses akreditasi berjalan:
     * 1. Akreditasi Awal (AA)
     * 2. Survailen 1 (S1)
     * 3. Survailen 2 (S2)
     * 4. Perluasan Ruang Lingkup (PRL)
     * 5. Survailen Tidak Terjadwal (STT)
     * 6. Re-Akreditasi (RA)
     *
     * Berpatokan dari masa akreditasi sertifikat (certificate_date & expired_at).
     *
     * @return \Illuminate\Support\Collection<int, array{
     *     lpk_id: int,
     *     lpk_name: string,
     *     no_reg: string,
     *     certificate_date: ?string,
     *     expired_at: ?string,
     *     process_type: string,
     *     process_code: string,
     *     process_label: string,
     *     status: string,
     *     status_label: string,
     *     status_color: string,
     *     target_date: ?string,
     *     target_date_formatted: string,
     *     days_remaining: int,
     *     description: string,
     *     action_url: string,
     *     has_assessment: bool,
     *     assessment_id: ?int
     * }>
     */
    public function getProcessNotifications(?int $picUserId = null, ?int $lpkId = null): \Illuminate\Support\Collection
    {
        $cacheKey = ($picUserId ?? 'all') . ':' . ($lpkId ?? 'all');
        if (isset($this->memoizedNotifications[$cacheKey])) {
            return $this->memoizedNotifications[$cacheKey];
        }

        $fullKey = "surveillance_notifs:{$cacheKey}";
        $cached = Cache::get($fullKey);
        if ($cached !== null && is_array($cached)) {
            return $this->memoizedNotifications[$cacheKey] = collect($cached);
        }

        $query = Lpk::with(['assessments' => function ($q) {
            $q->latest('start_at');
        }]);

        if ($lpkId) {
            $query->where('id', $lpkId);
        }

        if ($picUserId) {
            $query->where('pic_user_id', $picUserId);
        }

        $lpks = $query->get();
        $notifications = collect();
        $now = Carbon::now();

        foreach ($lpks as $lpk) {
            $certDate = $lpk->certificate_date ? Carbon::parse($lpk->certificate_date) : null;
            $expDate = $lpk->expired_at ? Carbon::parse($lpk->expired_at) : null;
            $assessments = $lpk->assessments;
            $months = $certDate ? (int) $certDate->diffInMonths($now) : 0;
            $noAkreditasi = $lpk->registration_number ?: $lpk->no_reg;

            // Kasus A: Belum Memiliki Tanggal Sertifikat -> Proses Akreditasi Awal (AA)
            if (! $certDate) {
                $aaAssessment = $assessments->first(fn ($a) => $a->assessment_type === \App\Enums\AssessmentType::INITIAL);

                if ($aaAssessment && ! $aaAssessment->isTpSatisfied()) {
                    $tpDue = $aaAssessment->effectiveTpDueDate();
                    $isOverdue = $tpDue && $now->greaterThan($tpDue);
                    $days = $tpDue ? (int) $now->diffInDays($tpDue, false) : 0;

                    $notifications->push([
                        'lpk_id' => $lpk->id,
                        'lpk_name' => $lpk->name,
                        'no_akreditasi' => $noAkreditasi,
                        'no_reg' => $noAkreditasi,
                        'certificate_date' => null,
                        'expired_at' => $expDate?->toDateString(),
                        'process_type' => 'INITIAL',
                        'process_code' => 'AA',
                        'process_label' => 'Akreditasi Awal (AA)',
                        'status' => $isOverdue ? 'OVERDUE' : 'IN_PROGRESS',
                        'status_label' => $isOverdue ? 'TP Terlewat' : 'TP Berjalan',
                        'status_color' => $isOverdue ? 'red' : 'amber',
                        'target_date' => $tpDue?->toDateString(),
                        'target_date_formatted' => $tpDue?->translatedFormat('d M Y') ?? '-',
                        'days_remaining' => $days,
                        'description' => $isOverdue 
                            ? 'Batas waktu Tindakan Perbaikan Akreditasi Awal terlewat ' . abs($days) . ' hari.' 
                            : 'Tindakan perbaikan Akreditasi Awal sedang berjalan (Target: ' . ($tpDue?->translatedFormat('d M Y') ?? '-') . ').',
                        'action_url' => route('assessments.show', $aaAssessment->id),
                        'has_assessment' => true,
                        'assessment_id' => $aaAssessment->id,
                    ]);
                } else {
                    $notifications->push([
                        'lpk_id' => $lpk->id,
                        'lpk_name' => $lpk->name,
                        'no_akreditasi' => $noAkreditasi,
                        'no_reg' => $noAkreditasi,
                        'certificate_date' => null,
                        'expired_at' => $expDate?->toDateString(),
                        'process_type' => 'INITIAL',
                        'process_code' => 'AA',
                        'process_label' => 'Akreditasi Awal (AA)',
                        'status' => 'DUE',
                        'status_label' => 'Proses Pengajuan',
                        'status_color' => 'amber',
                        'target_date' => $expDate?->toDateString(),
                        'target_date_formatted' => $expDate?->translatedFormat('d M Y') ?? '-',
                        'days_remaining' => 0,
                        'description' => 'Laboratorium dalam tahap proses akreditasi awal, belum terbit sertifikat akreditasi KAN.',
                        'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'INITIAL']),
                        'has_assessment' => false,
                        'assessment_id' => null,
                    ]);
                }
            }
            // Kasus B: Memiliki Sertifikat -> Cek Siklus Berdasarkan Usia Sertifikat & Status Realisasi
            else {
                $isActive = ($lpk->status === LpkStatus::ACTIVE);

                // Cek asesmen aktif yang belum selesai pemenuhan TP
                $s1Active = $assessments->first(function ($a) {
                    return in_array($a->assessment_type, [
                        \App\Enums\AssessmentType::SURVEILLANCE,
                        \App\Enums\AssessmentType::SURVEILLANCE_PRL,
                    ], true) && ! $a->isTpSatisfied();
                });

                $s2Active = $assessments->first(function ($a) {
                    return in_array($a->assessment_type, [
                        \App\Enums\AssessmentType::SURVEILLANCE_2,
                        \App\Enums\AssessmentType::SURVEILLANCE_2_PRL,
                    ], true) && ! $a->isTpSatisfied();
                });

                $raActive = $assessments->first(function ($a) {
                    return $a->assessment_type === \App\Enums\AssessmentType::REASSESSMENT && ! $a->isTpSatisfied();
                });

                // Cek status realisasi siklus: untuk LPK aktif, siklus sebelum fase saat ini otomatis terealisasi
                $hasS1Completed = $assessments->contains(function ($a) {
                    return in_array($a->assessment_type, [
                        \App\Enums\AssessmentType::SURVEILLANCE,
                        \App\Enums\AssessmentType::SURVEILLANCE_PRL,
                    ], true) && $a->isTpSatisfied();
                });
                $s1Realized = $hasS1Completed || ($isActive && $months >= 15);

                $hasS2Completed = $assessments->contains(function ($a) {
                    return in_array($a->assessment_type, [
                        \App\Enums\AssessmentType::SURVEILLANCE_2,
                        \App\Enums\AssessmentType::SURVEILLANCE_2_PRL,
                    ], true) && $a->isTpSatisfied();
                });
                $s2Realized = $hasS2Completed || ($isActive && $months >= 36);

                if ($s1Active) {
                    $tpDue = $s1Active->effectiveTpDueDate();
                    $isOverdue = $tpDue && $now->greaterThan($tpDue);
                    $days = $tpDue ? (int) $now->diffInDays($tpDue, false) : 0;

                    $notifications->push([
                        'lpk_id' => $lpk->id,
                        'lpk_name' => $lpk->name,
                        'no_akreditasi' => $noAkreditasi,
                        'no_reg' => $noAkreditasi,
                        'certificate_date' => $certDate->toDateString(),
                        'expired_at' => $expDate?->toDateString(),
                        'process_type' => 'SURVEILLANCE',
                        'process_code' => 'S1',
                        'process_label' => 'Survailen 1 (S1)',
                        'status' => $isOverdue ? 'OVERDUE' : 'IN_PROGRESS',
                        'status_label' => $isOverdue ? 'TP Terlewat' : 'TP Berjalan',
                        'status_color' => $isOverdue ? 'red' : 'amber',
                        'target_date' => $tpDue?->toDateString(),
                        'target_date_formatted' => $tpDue?->translatedFormat('d M Y') ?? '-',
                        'days_remaining' => $days,
                        'description' => $isOverdue 
                            ? 'Batas waktu TP Survailen 1 terlewat ' . abs($days) . ' hari.' 
                            : 'Tindakan perbaikan Survailen 1 sedang berjalan.',
                        'action_url' => route('assessments.show', $s1Active->id),
                        'has_assessment' => true,
                        'assessment_id' => $s1Active->id,
                    ]);
                } elseif ($s2Active) {
                    $tpDue = $s2Active->effectiveTpDueDate();
                    $isOverdue = $tpDue && $now->greaterThan($tpDue);
                    $days = $tpDue ? (int) $now->diffInDays($tpDue, false) : 0;

                    $notifications->push([
                        'lpk_id' => $lpk->id,
                        'lpk_name' => $lpk->name,
                        'no_akreditasi' => $noAkreditasi,
                        'no_reg' => $noAkreditasi,
                        'certificate_date' => $certDate->toDateString(),
                        'expired_at' => $expDate?->toDateString(),
                        'process_type' => 'SURVEILLANCE_2',
                        'process_code' => 'S2',
                        'process_label' => 'Survailen 2 (S2)',
                        'status' => $isOverdue ? 'OVERDUE' : 'IN_PROGRESS',
                        'status_label' => $isOverdue ? 'TP Terlewat' : 'TP Berjalan',
                        'status_color' => $isOverdue ? 'red' : 'amber',
                        'target_date' => $tpDue?->toDateString(),
                        'target_date_formatted' => $tpDue?->translatedFormat('d M Y') ?? '-',
                        'days_remaining' => $days,
                        'description' => $isOverdue 
                            ? 'Batas waktu TP Survailen 2 terlewat ' . abs($days) . ' hari.' 
                            : 'Tindakan perbaikan Survailen 2 sedang berjalan.',
                        'action_url' => route('assessments.show', $s2Active->id),
                        'has_assessment' => true,
                        'assessment_id' => $s2Active->id,
                    ]);
                } elseif ($raActive) {
                    $tpDue = $raActive->effectiveTpDueDate();
                    $isOverdue = $tpDue && $now->greaterThan($tpDue);
                    $days = $tpDue ? (int) $now->diffInDays($tpDue, false) : 0;

                    $notifications->push([
                        'lpk_id' => $lpk->id,
                        'lpk_name' => $lpk->name,
                        'no_akreditasi' => $noAkreditasi,
                        'no_reg' => $noAkreditasi,
                        'certificate_date' => $certDate->toDateString(),
                        'expired_at' => $expDate?->toDateString(),
                        'process_type' => 'REASSESSMENT',
                        'process_code' => 'RA',
                        'process_label' => 'Re-Akreditasi (RA)',
                        'status' => $isOverdue ? 'OVERDUE' : 'IN_PROGRESS',
                        'status_label' => $isOverdue ? 'TP Terlewat' : 'TP Berjalan',
                        'status_color' => $isOverdue ? 'red' : 'purple',
                        'target_date' => $tpDue?->toDateString(),
                        'target_date_formatted' => $tpDue?->translatedFormat('d M Y') ?? '-',
                        'days_remaining' => $days,
                        'description' => $isOverdue 
                            ? 'Batas waktu TP Re-Akreditasi terlewat ' . abs($days) . ' hari.' 
                            : 'Tindakan perbaikan Re-Akreditasi sedang berjalan.',
                        'action_url' => route('assessments.show', $raActive->id),
                        'has_assessment' => true,
                        'assessment_id' => $raActive->id,
                    ]);
                } else {
                    // Tidak ada asesmen aktif berjalan: Tampilkan siklus yang perlu diisi ke depannya
                    if (! $s1Realized) {
                        $s1Due = $certDate->copy()->addMonths(14);
                        $s1Deadline = $certDate->copy()->addMonths(15)->endOfMonth();

                        if (! $isActive && $now->greaterThan($s1Deadline)) {
                            $daysOver = (int) $s1Deadline->diffInDays($now);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate?->toDateString(),
                                'process_type' => 'SURVEILLANCE',
                                'process_code' => 'S1',
                                'process_label' => 'Survailen 1 (S1)',
                                'status' => 'OVERDUE',
                                'status_label' => 'Melewati Batas Waktu',
                                'status_color' => 'red',
                                'target_date' => $s1Deadline->toDateString(),
                                'target_date_formatted' => $s1Deadline->translatedFormat('d M Y'),
                                'days_remaining' => -$daysOver,
                                'description' => 'Melewati batas toleransi bulan ke-15 sertifikat sejak ' . $s1Deadline->translatedFormat('d M Y') . ' (' . $daysOver . ' hari lalu).',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'SURVEILLANCE']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        } elseif ($now->greaterThanOrEqualTo($s1Due)) {
                            $daysLeft = (int) $now->diffInDays($s1Deadline);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate?->toDateString(),
                                'process_type' => 'SURVEILLANCE',
                                'process_code' => 'S1',
                                'process_label' => 'Survailen 1 (S1)',
                                'status' => 'DUE',
                                'status_label' => 'Jatuh Tempo Siklus',
                                'status_color' => 'amber',
                                'target_date' => $s1Deadline->toDateString(),
                                'target_date_formatted' => $s1Deadline->translatedFormat('d M Y'),
                                'days_remaining' => $daysLeft,
                                'description' => 'Siklus bulan ke-14 telah tercapai. Batas akhir toleransi bulan ke-15 tersisa ' . $daysLeft . ' hari.',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'SURVEILLANCE']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        } else {
                            $daysToDue = (int) $now->diffInDays($s1Due);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate?->toDateString(),
                                'process_type' => 'SURVEILLANCE',
                                'process_code' => 'S1',
                                'process_label' => 'Survailen 1 (S1)',
                                'status' => 'UPCOMING',
                                'status_label' => 'Menjelang Siklus',
                                'status_color' => 'blue',
                                'target_date' => $s1Due->toDateString(),
                                'target_date_formatted' => $s1Due->translatedFormat('d M Y'),
                                'days_remaining' => $daysToDue,
                                'description' => 'Mempersiapkan pengawasan S1 dalam ' . $daysToDue . ' hari (Jatuh tempo bulan ke-14: ' . $s1Due->translatedFormat('d M Y') . ').',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'SURVEILLANCE']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        }
                    } elseif (! $s2Realized) {
                        $s2Due = $certDate->copy()->addMonths(35);
                        $s2Deadline = $certDate->copy()->addMonths(36)->endOfMonth();

                        if (! $isActive && $now->greaterThan($s2Deadline)) {
                            $daysOver = (int) $s2Deadline->diffInDays($now);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate?->toDateString(),
                                'process_type' => 'SURVEILLANCE_2',
                                'process_code' => 'S2',
                                'process_label' => 'Survailen 2 (S2)',
                                'status' => 'OVERDUE',
                                'status_label' => 'Melewati Batas Waktu',
                                'status_color' => 'red',
                                'target_date' => $s2Deadline->toDateString(),
                                'target_date_formatted' => $s2Deadline->translatedFormat('d M Y'),
                                'days_remaining' => -$daysOver,
                                'description' => 'Melewati batas toleransi bulan ke-36 sertifikat sejak ' . $s2Deadline->translatedFormat('d M Y') . ' (' . $daysOver . ' hari lalu).',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'SURVEILLANCE_2']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        } elseif ($now->greaterThanOrEqualTo($s2Due)) {
                            $daysLeft = (int) $now->diffInDays($s2Deadline);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate?->toDateString(),
                                'process_type' => 'SURVEILLANCE_2',
                                'process_code' => 'S2',
                                'process_label' => 'Survailen 2 (S2)',
                                'status' => 'DUE',
                                'status_label' => 'Jatuh Tempo Siklus',
                                'status_color' => 'amber',
                                'target_date' => $s2Deadline->toDateString(),
                                'target_date_formatted' => $s2Deadline->translatedFormat('d M Y'),
                                'days_remaining' => $daysLeft,
                                'description' => 'Siklus bulan ke-35 telah tercapai. Batas toleransi bulan ke-36 tersisa ' . $daysLeft . ' hari.',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'SURVEILLANCE_2']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        } else {
                            $daysToDue = (int) $now->diffInDays($s2Due);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate?->toDateString(),
                                'process_type' => 'SURVEILLANCE_2',
                                'process_code' => 'S2',
                                'process_label' => 'Survailen 2 (S2)',
                                'status' => 'UPCOMING',
                                'status_label' => 'Menjelang Siklus',
                                'status_color' => 'blue',
                                'target_date' => $s2Due->toDateString(),
                                'target_date_formatted' => $s2Due->translatedFormat('d M Y'),
                                'days_remaining' => $daysToDue,
                                'description' => 'Mempersiapkan pengawasan S2 dalam ' . $daysToDue . ' hari (Jatuh tempo bulan ke-35: ' . $s2Due->translatedFormat('d M Y') . ').',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'SURVEILLANCE_2']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        }
                    } else {
                        $raTargetNotice = $expDate ? $expDate->copy()->subMonths(6) : $certDate->copy()->addMonths(54);

                        if ($expDate && $now->greaterThan($expDate)) {
                            $daysExpired = (int) $expDate->diffInDays($now);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate->toDateString(),
                                'process_type' => 'REASSESSMENT',
                                'process_code' => 'RA',
                                'process_label' => 'Re-Akreditasi (RA)',
                                'status' => 'OVERDUE',
                                'status_label' => 'Masa Berlaku Habis',
                                'status_color' => 'red',
                                'target_date' => $expDate->toDateString(),
                                'target_date_formatted' => $expDate->translatedFormat('d M Y'),
                                'days_remaining' => -$daysExpired,
                                'description' => 'Masa akreditasi sertifikat telah habis sejak ' . $expDate->translatedFormat('d M Y') . ' (' . $daysExpired . ' hari lalu).',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'REASSESSMENT']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        } elseif ($expDate && $now->greaterThanOrEqualTo($raTargetNotice)) {
                            $daysToExpiry = (int) $now->diffInDays($expDate);
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate->toDateString(),
                                'process_type' => 'REASSESSMENT',
                                'process_code' => 'RA',
                                'process_label' => 'Re-Akreditasi (RA)',
                                'status' => 'DUE',
                                'status_label' => 'Wajib Re-Akreditasi',
                                'status_color' => 'red',
                                'target_date' => $expDate->toDateString(),
                                'target_date_formatted' => $expDate->translatedFormat('d M Y'),
                                'days_remaining' => $daysToExpiry,
                                'description' => 'Masa sertifikat berakhir dalam ' . $daysToExpiry . ' hari (' . $expDate->translatedFormat('d M Y') . '). Wajib diajukan sebelum habis.',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'REASSESSMENT']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        } else {
                            $daysToNotice = $expDate ? (int) $now->diffInDays($raTargetNotice) : 30;
                            $notifications->push([
                                'lpk_id' => $lpk->id,
                                'lpk_name' => $lpk->name,
                                'no_akreditasi' => $noAkreditasi,
                                'no_reg' => $noAkreditasi,
                                'certificate_date' => $certDate->toDateString(),
                                'expired_at' => $expDate?->toDateString(),
                                'process_type' => 'REASSESSMENT',
                                'process_code' => 'RA',
                                'process_label' => 'Re-Akreditasi (RA)',
                                'status' => 'UPCOMING',
                                'status_label' => 'Persiapan Re-Akreditasi',
                                'status_color' => 'blue',
                                'target_date' => $raTargetNotice->toDateString(),
                                'target_date_formatted' => $raTargetNotice->translatedFormat('d M Y'),
                                'days_remaining' => $daysToNotice,
                                'description' => 'Siklus tahun ke-5 (Re-Akreditasi) mendekat dalam ' . $daysToNotice . ' hari.',
                                'action_url' => route('assessments.create', ['lpk_id' => $lpk->id, 'type' => 'REASSESSMENT']),
                                'has_assessment' => false,
                                'assessment_id' => null,
                            ]);
                        }
                    }
                }
            }

            // Proses Tambahan: Perluasan Ruang Lingkup (PRL) jika ada asesmen berjalan
            $prlAssessments = $assessments->filter(function ($a) {
                return in_array($a->assessment_type, [
                    \App\Enums\AssessmentType::SCOPE_EXTENSION,
                    \App\Enums\AssessmentType::SURVEILLANCE_PRL,
                    \App\Enums\AssessmentType::SURVEILLANCE_2_PRL,
                ], true) && ! $a->isTpSatisfied();
            });

            foreach ($prlAssessments as $prlAss) {
                $tpDue = $prlAss->effectiveTpDueDate();
                $isOverdue = $tpDue && $now->greaterThan($tpDue);
                $days = $tpDue ? (int) $now->diffInDays($tpDue, false) : 0;

                $notifications->push([
                    'lpk_id' => $lpk->id,
                    'lpk_name' => $lpk->name,
                    'no_akreditasi' => $noAkreditasi,
                    'no_reg' => $noAkreditasi,
                    'certificate_date' => $certDate?->toDateString(),
                    'expired_at' => $expDate?->toDateString(),
                    'process_type' => 'SCOPE_EXTENSION',
                    'process_code' => 'PRL',
                    'process_label' => 'Perluasan Ruang Lingkup (PRL)',
                    'status' => $isOverdue ? 'OVERDUE' : 'IN_PROGRESS',
                    'status_label' => $isOverdue ? 'TP Terlewat' : 'TP Berjalan',
                    'status_color' => $isOverdue ? 'red' : 'purple',
                    'target_date' => $tpDue?->toDateString(),
                    'target_date_formatted' => $tpDue?->translatedFormat('d M Y') ?? '-',
                    'days_remaining' => $days,
                    'description' => $isOverdue 
                        ? 'Batas waktu TP Perluasan Ruang Lingkup terlewat ' . abs($days) . ' hari.' 
                        : 'Tindakan perbaikan proses PRL sedang berjalan.',
                    'action_url' => route('assessments.show', $prlAss->id),
                    'has_assessment' => true,
                    'assessment_id' => $prlAss->id,
                ]);
            }

            // Proses Tambahan: Survailen Tidak Terjadwal (STT) jika ada asesmen berjalan
            $sttAssessments = $assessments->filter(function ($a) {
                return $a->assessment_type === \App\Enums\AssessmentType::UNSCHEDULED_SURVEILLANCE && ! $a->isTpSatisfied();
            });

            foreach ($sttAssessments as $sttAss) {
                $tpDue = $sttAss->effectiveTpDueDate();
                $isOverdue = $tpDue && $now->greaterThan($tpDue);
                $days = $tpDue ? (int) $now->diffInDays($tpDue, false) : 0;

                $notifications->push([
                    'lpk_id' => $lpk->id,
                    'lpk_name' => $lpk->name,
                    'no_akreditasi' => $noAkreditasi,
                    'no_reg' => $noAkreditasi,
                    'certificate_date' => $certDate?->toDateString(),
                    'expired_at' => $expDate?->toDateString(),
                    'process_type' => 'UNSCHEDULED_SURVEILLANCE',
                    'process_code' => 'STT',
                    'process_label' => 'Survailen Tidak Terjadwal (STT)',
                    'status' => $isOverdue ? 'OVERDUE' : 'IN_PROGRESS',
                    'status_label' => $isOverdue ? 'TP Terlewat' : 'TP Berjalan',
                    'status_color' => $isOverdue ? 'red' : 'purple',
                    'target_date' => $tpDue?->toDateString(),
                    'target_date_formatted' => $tpDue?->translatedFormat('d M Y') ?? '-',
                    'days_remaining' => $days,
                    'description' => $isOverdue 
                        ? 'Batas waktu TP Survailen Khusus terlewat ' . abs($days) . ' hari.' 
                        : 'Tindakan perbaikan STT sedang berjalan.',
                    'action_url' => route('assessments.show', $sttAss->id),
                    'has_assessment' => true,
                    'assessment_id' => $sttAss->id,
                ]);
            }
        }

        $statusWeights = [
            'OVERDUE' => 1,
            'DUE' => 2,
            'IN_PROGRESS' => 3,
            'UPCOMING' => 4,
            'COMPLIANT' => 5,
        ];

        $sorted = $notifications->sortBy(function ($item) use ($statusWeights) {
            $weight = $statusWeights[$item['status']] ?? 99;
            return sprintf('%02d_%05d', $weight, abs($item['days_remaining']));
        })->values();

        $this->trackCacheKey($fullKey);
        Cache::put($fullKey, $sorted->all(), 300);

        return $this->memoizedNotifications[$cacheKey] = $sorted;
    }
}
