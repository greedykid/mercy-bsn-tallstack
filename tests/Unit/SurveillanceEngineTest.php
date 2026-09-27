<?php

declare(strict_types=1);

use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Models\Lpk;
use App\Models\User;
use App\Services\SurveillanceEngineService;
use Carbon\Carbon;

beforeEach(function () {
    $this->engine = new SurveillanceEngineService;
    $this->user = User::factory()->create(['role' => 'admin']);
});

test('it calculates correct surveillance milestones across 5-year cycle', function () {
    $certificateDate = Carbon::parse('2024-01-15');
    $milestones = $this->engine->calculateMilestones($certificateDate);

    expect($milestones)->toHaveCount(5);
    expect($milestones[0]['label'])->toBe('Surveilen 1 (Tahun 1)');
    expect($milestones[0]['due_date'])->toBe('2025-01-15');
    expect($milestones[0]['tolerance_date'])->toBe('2025-01-31');

    expect($milestones[1]['label'])->toBe('Surveilen 2 (Tahun 2)');
    expect($milestones[1]['due_date'])->toBe('2026-01-15');
    expect($milestones[1]['tolerance_date'])->toBe('2026-01-31');

    expect($milestones[4]['label'])->toBe('Re-Akreditasi (Tahun 5)');
    expect($milestones[4]['due_date'])->toBe('2029-01-15');
});

test('it marks LPK as SUSPENDED when tolerance date expires', function () {
    $lpk = Lpk::create([
        'no_reg' => 'LP-TEST-001',
        'name' => 'Lab Pengujian Toleransi Expired',
        'schema' => KanSchema::LP,
        'certificate_date' => '2024-01-15',
        'expired_at' => '2029-01-15',
        'status' => LpkStatus::ACTIVE,
    ]);

    // Simulate current date is past tolerance date of Year 1 (e.g. 2025-02-10)
    Carbon::setTestNow('2025-02-10');

    $status = $this->engine->evaluateCompliance($lpk);

    expect($status)->toBe(LpkStatus::SUSPENDED);
    expect($lpk->fresh()->status)->toBe(LpkStatus::SUSPENDED);
    expect($lpk->fresh()->suspended_at)->not->toBeNull();

    Carbon::setTestNow();
});

test('it revokes LPK after 1 year of unresolved suspension', function () {
    $lpk = Lpk::create([
        'no_reg' => 'LP-TEST-002',
        'name' => 'Lab Pengujian Suspended 1 Year',
        'schema' => KanSchema::LP,
        'certificate_date' => '2023-01-15',
        'expired_at' => '2028-01-15',
        'status' => LpkStatus::SUSPENDED,
        'suspended_at' => '2024-02-01 10:00:00',
    ]);

    // Fast forward 1 year and 1 day after suspension
    Carbon::setTestNow('2025-02-05');

    $status = $this->engine->evaluateCompliance($lpk);

    expect($status)->toBe(LpkStatus::REVOKED);
    expect($lpk->fresh()->status)->toBe(LpkStatus::REVOKED);
    expect($lpk->fresh()->revoked_at)->not->toBeNull();

    Carbon::setTestNow();
});

test('active LPK auto-realizes prior surveillance cycles', function () {
    $lpk = Lpk::create([
        'no_reg' => 'LP-TEST-003',
        'name' => 'Lab Pengujian Aktif Auto-Realized',
        'schema' => KanSchema::LP,
        'certificate_date' => '2023-01-15',
        'expired_at' => '2028-01-15',
        'status' => LpkStatus::ACTIVE,
    ]);

    // Currently in year 2 (2025)
    Carbon::setTestNow('2025-03-01');

    $milestones = $this->engine->calculateMilestones($lpk->certificate_date, $lpk);

    // Year 1 (due 2024) must be auto-realized because LPK is currently ACTIVE
    expect($milestones[0]['status'])->toBe('REALIZED');

    Carbon::setTestNow();
});

test('it generates process deadline notifications according to certificate cycle', function () {
    // 1. Lab in S1 phase (month 14)
    $lpkS1 = Lpk::create([
        'no_reg' => 'LP-NOTIF-001',
        'name' => 'Lab Survailen 1 Due',
        'schema' => KanSchema::LP,
        'certificate_date' => '2025-01-01',
        'expired_at' => '2030-01-01',
        'status' => LpkStatus::ACTIVE,
    ]);

    // 2. Lab near certificate expiration (Re-Akreditasi phase, within 3 months of expiry)
    $lpkRa = Lpk::create([
        'no_reg' => 'LP-NOTIF-002',
        'name' => 'Lab Re-Akreditasi Due',
        'schema' => KanSchema::LP,
        'certificate_date' => '2021-06-01',
        'expired_at' => '2026-06-01',
        'status' => LpkStatus::ACTIVE,
    ]);

    // Simulate now is 2026-03-15
    // lpkS1 is at month 14.5 -> S1 is DUE
    // lpkRa expires in 2.5 months -> RA is DUE
    Carbon::setTestNow('2026-03-15');

    $notifsS1 = $this->engine->getProcessNotifications(null, $lpkS1->id);
    expect($notifsS1)->not->toBeEmpty();
    expect($notifsS1->first()['process_code'])->toBe('S1');
    expect($notifsS1->first()['status'])->toBe('DUE');

    $notifsRa = $this->engine->getProcessNotifications(null, $lpkRa->id);
    expect($notifsRa)->not->toBeEmpty();
    expect($notifsRa->first()['process_code'])->toBe('RA');
    expect($notifsRa->first()['status'])->toBe('DUE');

    Carbon::setTestNow();
});

test('active LPK auto-realizes prior cycles and only surfaces next cycle to be filled', function () {
    // Lab aktif pada usia sertifikat bulan ke-20 (melewati batas S1 bulan ke-15)
    $lpkMonth20 = Lpk::create([
        'no_reg' => 'LP-AUTO-001',
        'name' => 'Lab Aktif Usia 20 Bulan',
        'schema' => KanSchema::LP,
        'certificate_date' => '2024-06-01',
        'expired_at' => '2029-06-01',
        'status' => LpkStatus::ACTIVE,
    ]);

    // Lab aktif pada usia sertifikat bulan ke-40 (melewati batas S1 dan S2)
    $lpkMonth40 = Lpk::create([
        'no_reg' => 'LP-AUTO-002',
        'name' => 'Lab Aktif Usia 40 Bulan',
        'schema' => KanSchema::LP,
        'certificate_date' => '2022-10-01',
        'expired_at' => '2027-10-01',
        'status' => LpkStatus::ACTIVE,
    ]);

    // Set waktu saat ini ke 2026-02-15:
    // lpkMonth20 (cert 2024-06-01): usia ~20.5 bulan -> S1 auto-realized, siklus berikutnya adalah S2 (UPCOMING)
    // lpkMonth40 (cert 2022-10-01): usia ~40.5 bulan -> S1 & S2 auto-realized, siklus berikutnya adalah RA (UPCOMING)
    Carbon::setTestNow('2026-02-15');

    // Cek lpkMonth20
    $notifs20 = $this->engine->getProcessNotifications(null, $lpkMonth20->id);
    expect($notifs20)->not->toBeEmpty();
    expect($notifs20->first()['process_code'])->toBe('S2');
    expect($notifs20->first()['status'])->toBe('UPCOMING');

    $activeCycle20 = $this->engine->determineActiveCycle($lpkMonth20);
    expect($activeCycle20['code'])->toBe('S2');
    expect($activeCycle20['label'])->toBe('Surveilen 2');
    expect($activeCycle20['status'])->toBe('TERJADWAL');

    // Cek lpkMonth40
    $notifs40 = $this->engine->getProcessNotifications(null, $lpkMonth40->id);
    expect($notifs40)->not->toBeEmpty();
    expect($notifs40->first()['process_code'])->toBe('RA');
    expect($notifs40->first()['status'])->toBe('UPCOMING');

    $activeCycle40 = $this->engine->determineActiveCycle($lpkMonth40);
    expect($activeCycle40['code'])->toBe('RA');
    expect($activeCycle40['label'])->toBe('Re-Akreditasi');
    expect($activeCycle40['status'])->toBe('TERJADWAL');

    Carbon::setTestNow();
});

test('non-active LPK retains OVERDUE status for missed past cycles', function () {
    // Lab dengan status SURVEILLANCE_OVERDUE pada usia sertifikat bulan ke-20 tanpa S1
    $lpkOverdue = Lpk::create([
        'no_reg' => 'LP-OVERDUE-001',
        'name' => 'Lab Terlewat Usia 20 Bulan',
        'schema' => KanSchema::LP,
        'certificate_date' => '2024-06-01',
        'expired_at' => '2029-06-01',
        'status' => LpkStatus::SURVEILLANCE_OVERDUE,
    ]);

    Carbon::setTestNow('2026-02-15');

    $notifs = $this->engine->getProcessNotifications(null, $lpkOverdue->id);
    expect($notifs)->not->toBeEmpty();
    expect($notifs->first()['process_code'])->toBe('S1');
    expect($notifs->first()['status'])->toBe('OVERDUE');

    $activeCycle = $this->engine->determineActiveCycle($lpkOverdue);
    expect($activeCycle['code'])->toBe('S1');
    expect($activeCycle['status'])->toBe('TERLEWAT');

    Carbon::setTestNow();
});
