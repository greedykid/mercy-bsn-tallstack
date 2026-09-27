<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Enums\CalendarEventType;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\CalendarEvent;
use App\Models\Lpk;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@mercy.local'],
            [
                'name' => 'Administrator Akreditasi Lab Penguji',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
                'email_verified_at' => now(),
            ]
        );

        $pic1 = User::firstOrCreate(
            ['email' => 'pic@mercy.local'],
            [
                'name' => 'Dr. Budi Santoso (PIC Lab Penguji)',
                'password' => Hash::make('password'),
                'role' => UserRole::PIC,
                'email_verified_at' => now(),
            ]
        );

        $pic2 = User::firstOrCreate(
            ['email' => 'pic2@mercy.local'],
            [
                'name' => 'Siti Rahmawati, S.T. (PIC Unit Teknis LP)',
                'password' => Hash::make('password'),
                'role' => UserRole::PIC,
                'email_verified_at' => now(),
            ]
        );

        // LP 1: LP-001-IDN (Pengujian Kimia & Lingkungan)
        $lpk1 = Lpk::create([
            'no_reg' => 'LP-001-IDN',
            'registration_number' => 'LP-001-IDN',
            'name' => 'Balai Pengujian Kimia & Lingkungan Nusantara',
            'kan_schema' => KanSchema::LP,
            'scope' => 'Pengujian Kualitas Air Bersih, Air Limbah, Emisi Udara, dan Logam Berat',
            'address' => 'Kawasan Sains & Teknologi BJ Habibie, Gedung 420',
            'city' => 'Tangerang Selatan',
            'province' => 'Banten',
            'email' => 'lab.kimia@nusantara.go.id',
            'phone' => '021-7560555',
            'contact_person' => 'Ir. Hendra Wijaya',
            'status' => LpkStatus::ACTIVE,
            'certificate_date' => Carbon::now()->subMonths(14)->toDateString(),
            'expired_at' => Carbon::now()->addMonths(46)->toDateString(),
            'pic_user_id' => $pic1->id,
            'notes' => 'Telah menyelesaikan asesmen surveilen 1. 8 dari 10 temuan telah tertutup, masa perpanjangan aktif.',
            'pic_notes' => 'Perlu asistensi teknis verifikasi lampiran ruang lingkup pengujian emisi cerobong.',
            'drive_url' => 'https://drive.google.com/drive/folders/kan-lp001-idn',
        ]);

        $ass1 = Assessment::create([
            'lpk_id' => $lpk1->id,
            'created_by' => $pic1->id,
            'title' => 'Surveilen 1 (S1) Laboratorium Pengujian Kimia',
            'assessment_type' => AssessmentType::SURVEILLANCE,
            'start_at' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(10)->toDateString(),
            'end_at' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(12)->toDateString(),
            'location' => 'Laboratorium Terpadu Serpong',
            'lead_assessor' => 'Prof. Dr. Ir. Anwar Fauzi',
            'assessor_team' => 'Dr. Anwar Fauzi (Lead), Dra. Maria Ulfa (Teknis Kimia)',
            'status' => 'IN_PROGRESS',
            'tp_deadline_months' => 2,
            'tp_due_date' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(12)->addMonths(2)->toDateString(),
            'tp_findings_total' => 10,
            'tp_findings_closed' => 8,
            'tp_has_extension' => true,
            'tp_extension_months' => 1,
            'tp_extension_letter_no' => 'KAN-EXT/089/S1/2026',
            'tp_extended_due_date' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(12)->addMonths(3)->toDateString(),
            'assessment_report_date' => Carbon::now()->subMonth()->toDateString(),
        ]);

        // LP 2: LP-021-IDN (Pengujian Fisika & Mekanik)
        $lpk2 = Lpk::create([
            'no_reg' => 'LP-021-IDN',
            'registration_number' => 'LP-021-IDN',
            'name' => 'PT Laboratorium Pengujian Material & Fisika Logam',
            'kan_schema' => KanSchema::LP,
            'scope' => 'Pengujian Kuat Tarik, Kekerasan, Ketahanan Korosi Baja, dan Struktur Konstruksi',
            'address' => 'Jl. Industri Rungkut Megah No. 12',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'email' => 'pengujian@matfisika.co.id',
            'phone' => '031-8439900',
            'contact_person' => 'Dwi Cahyono, M.T.',
            'status' => LpkStatus::SURVEILLANCE_DUE,
            'certificate_date' => Carbon::now()->subMonths(35)->toDateString(),
            'expired_at' => Carbon::now()->addMonths(25)->toDateString(),
            'pic_user_id' => $pic1->id,
            'notes' => 'Memasuki bulan ke-35 sejak akreditasi awal, pelaksanaan Surveilen 2 (S2) harus segera dikonfirmasi.',
            'pic_notes' => 'Kandidat Lead Assessor telah dihubungi, konfirmasi jadwal tanggal 15 bulan depan.',
            'drive_url' => 'https://drive.google.com/drive/folders/kan-lp021-idn',
        ]);

        // LP 3: LP-045-IDN (Pengujian Mikrobiologi & Pangan)
        $lpk3 = Lpk::create([
            'no_reg' => 'LP-045-IDN',
            'registration_number' => 'LP-045-IDN',
            'name' => 'Balai Besar Pengujian Mutu Pangan & Mikrobiologi',
            'kan_schema' => KanSchema::LP,
            'scope' => 'Pengujian Bakteri Patogen, Salmonella, E. Coli, dan Cemaran Logam pada Makanan Olahan',
            'address' => 'Jl. Gatot Subroto Kav. 52',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'email' => 'lab.mikro@pangansehat.id',
            'phone' => '021-5251122',
            'contact_person' => 'Rina Kusumastuti, S.Si.',
            'status' => LpkStatus::ACTIVE,
            'certificate_date' => Carbon::now()->subMonths(6)->toDateString(),
            'expired_at' => Carbon::now()->addMonths(54)->toDateString(),
            'pic_user_id' => $pic2->id,
            'notes' => 'Akreditasi Awal diselesaikan memuaskan dengan SK resmi KAN nomor SK-KAN.045/LP/2026.',
            'pic_notes' => 'Sertifikat fisik telah diserahkan ke manajemen lab.',
            'drive_url' => 'https://drive.google.com/drive/folders/kan-lp045-idn',
        ]);

        $ass3 = Assessment::create([
            'lpk_id' => $lpk3->id,
            'created_by' => $pic2->id,
            'title' => 'Akreditasi Awal (AA) Laboratorium Pengujian Mikrobiologi',
            'assessment_type' => AssessmentType::INITIAL,
            'start_at' => Carbon::now()->subMonths(6)->toDateString(),
            'end_at' => Carbon::now()->subMonths(6)->addDays(3)->toDateString(),
            'location' => 'Laboratorium Pusat Pangan, Jakarta Selatan',
            'lead_assessor' => 'Drs. H. Bambang Soeprapto',
            'assessor_team' => 'Bambang Soeprapto (Lead), Ir. Yuliani (Teknis Mikrobiologi)',
            'status' => 'COMPLETED',
            'tp_deadline_months' => 3,
            'tp_due_date' => Carbon::now()->subMonths(6)->addDays(3)->addMonths(3)->toDateString(),
            'tp_findings_total' => 6,
            'tp_findings_closed' => 6,
            'tp_has_extension' => false,
            'tp_satisfied_at' => Carbon::now()->subMonths(4)->toDateString(),
            'assessment_report_date' => Carbon::now()->subMonths(4)->toDateString(),
            'eha_scheduled_date' => Carbon::now()->subMonths(3)->toDateString(),
            'eha_completed_at' => Carbon::now()->subMonths(3)->toDateString(),
            'eha_notes' => 'Rekomendasi panitia teknis: Diberikan Akreditasi Penuh SNI ISO/IEC 17025:2017 untuk 6 ruang lingkup uji.',
            'sk_issued_at' => Carbon::now()->subMonths(2)->toDateString(),
            'sk_number' => 'SK-KAN.045/LP/2026',
            'sk_lead_time_days' => 28,
        ]);

        // LP 4: LP-1450-IDN (Pengujian Elektrik & Elektronika)
        $lpk4 = Lpk::create([
            'no_reg' => 'LP-1450-IDN',
            'registration_number' => 'LP-1450-IDN',
            'name' => 'Laboratorium Pengujian Kelistrikan & EMC Nusantara',
            'kan_schema' => KanSchema::LP,
            'scope' => 'Pengujian Keselamatan Produk Elektronik, Kompatibilitas Elektromagnetik (EMC), dan Efisiensi Energi',
            'address' => 'Jl. Cisitu No. 21',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'email' => 'emc.lab@kelistrikan.go.id',
            'phone' => '022-2503055',
            'contact_person' => 'Agus Priyanto, M.Eng.',
            'status' => LpkStatus::ACTIVE,
            'certificate_date' => Carbon::now()->subMonths(20)->toDateString(),
            'expired_at' => Carbon::now()->addMonths(40)->toDateString(),
            'pic_user_id' => $pic2->id,
            'notes' => 'Surveilen 1 selesai dengan baik, seluruh temuan diverifikasi memenuhi.',
            'pic_notes' => 'Lab berencana mengajukan perluasan ruang lingkup (PRL) inverter surya pada siklus S2.',
            'drive_url' => 'https://drive.google.com/drive/folders/kan-lp1450-idn',
        ]);

        // Calendar Events
        CalendarEvent::create([
            'lpk_id' => $lpk1->id,
            'assessment_id' => $ass1->id,
            'user_id' => $pic1->id,
            'event_type' => CalendarEventType::TP_DEADLINE,
            'title' => 'Batas Waktu Perpanjangan TP: LP-001-IDN',
            'description' => 'Batas akhir pemenuhan 2 sisa temuan tindakan perbaikan S1',
            'start_at' => $ass1->tp_extended_due_date ? Carbon::parse($ass1->tp_extended_due_date)->startOfDay() : Carbon::now()->addDays(15),
            'end_at' => $ass1->tp_extended_due_date ? Carbon::parse($ass1->tp_extended_due_date)->endOfDay() : Carbon::now()->addDays(15),
            'is_all_day' => true,
            'color' => 'red',
        ]);

        CalendarEvent::create([
            'lpk_id' => $lpk2->id,
            'user_id' => $pic1->id,
            'event_type' => CalendarEventType::SURVEILLANCE_DUE,
            'title' => 'Jatuh Tempo Surveilen 2: LP-021-IDN',
            'description' => 'Memasuki bulan ke-35 siklus pengawasan KAN (Surveilen 2)',
            'start_at' => Carbon::now()->addDays(14)->startOfDay(),
            'end_at' => Carbon::now()->addDays(14)->endOfDay(),
            'is_all_day' => true,
            'color' => 'amber',
        ]);

        CalendarEvent::create([
            'lpk_id' => $lpk4->id,
            'user_id' => $pic2->id,
            'event_type' => CalendarEventType::ASSESSMENT,
            'title' => 'Agenda Asesmen Berkala: LP-1450-IDN',
            'description' => 'Kunjungan asesmen lapangan verifikasi metode uji efisiensi energi',
            'start_at' => Carbon::now()->addDays(7)->startOfDay(),
            'end_at' => Carbon::now()->addDays(8)->endOfDay(),
            'is_all_day' => false,
            'color' => 'blue',
        ]);
    }
}
