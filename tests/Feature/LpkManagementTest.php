<?php

declare(strict_types=1);

use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Models\Lpk;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Mercy',
        'email' => 'admin@test.local',
        'role' => 'admin',
    ]);

    $this->pic1 = User::factory()->create([
        'name' => 'PIC 1',
        'email' => 'pic1@test.local',
        'role' => 'pic',
    ]);

    $this->pic2 = User::factory()->create([
        'name' => 'PIC 2',
        'email' => 'pic2@test.local',
        'role' => 'pic',
    ]);

    $this->lpk1 = Lpk::create([
        'no_reg' => 'LP-FEATURE-001',
        'name' => 'Lab Pengujian Banten',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
        'pic_user_id' => $this->pic1->id,
        'notes' => 'Catatan kalkulasi sistem otomatis',
        'pic_notes' => 'Catatan internal rahasia untuk PIC 1',
    ]);

    $this->lpk2 = Lpk::create([
        'no_reg' => 'LP-FEATURE-002',
        'name' => 'Lab Pengujian Jakarta',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
        'pic_user_id' => $this->pic2->id,
        'notes' => 'Catatan kalkulasi sistem otomatis 2',
        'pic_notes' => 'Catatan internal rahasia untuk PIC 2',
    ]);
});

test('admin can access LPK index and see all LP records', function () {
    $this->actingAs($this->admin)
        ->get(route('lpks.index'))
        ->assertStatus(200)
        ->assertSee('LP-FEATURE-001')
        ->assertSee('LP-FEATURE-002');
});

test('PIC can access LPK index and sees their assigned LP', function () {
    $this->actingAs($this->pic1)
        ->get(route('lpks.index'))
        ->assertStatus(200)
        ->assertSee('LP-FEATURE-001')
        ->assertDontSee('LP-FEATURE-002');
});

test('LP detail displays dual notes stack with administrative notes and PIC technical notes', function () {
    $this->actingAs($this->admin)
        ->get(route('lpks.show', $this->lpk1->id))
        ->assertStatus(200)
        ->assertSee('LP-FEATURE-001')
        ->assertSee('Lab Pengujian Banten')
        ->assertSee('Catatan internal rahasia untuk PIC 1');
});

test('unauthenticated user is redirected to login when trying to access admin dashboard', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('PIC can import LP records via CSV and they are automatically assigned to the PIC', function () {
    $csv = "no_reg,name,scope,city,province,certificate_date,expired_at\n" .
           "LP-999-IDN,Lab Uji Pengujian Pangan,Mikrobiologi,Surabaya,Jawa Timur,2024-01-01,2029-01-01";

    \Livewire\Livewire::actingAs($this->pic1)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('importMode', 'paste')
        ->set('csvContent', $csv)
        ->call('executeImport');

    $imported = Lpk::where('no_reg', 'LP-999-IDN')->first();
    expect($imported)->not->toBeNull();
    expect($imported->name)->toBe('Lab Uji Pengujian Pangan');
    expect($imported->pic_user_id)->toBe($this->pic1->id);
});

test('PIC can render LPK create form and submit new LPK', function () {
    $this->actingAs($this->pic1)
        ->get(route('lpks.create'))
        ->assertStatus(200)
        ->assertSee('Pendaftaran Laboratorium Penguji Baru')
        ->assertSee('PIC Pendamping');

    \Livewire\Livewire::actingAs($this->pic1)
        ->test(\App\Livewire\Lpks\LpkForm::class)
        ->set('form.no_reg', 'LP-TEST-PIC-NEW')
        ->set('form.name', 'Laboratorium Uji Mandiri PIC')
        ->set('form.kan_schema', 'LP')
        ->set('form.status', 'ACTIVE')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $created = Lpk::where('no_reg', 'LP-TEST-PIC-NEW')->first();
    expect($created)->not->toBeNull();
    expect($created->pic_user_id)->toBe($this->pic1->id);

    $dialog = session()->get('ts-ui:dialog');
    expect($dialog)->not->toBeNull();
    expect($dialog['type'])->toBe('success');
    expect($dialog['title'])->toBe('Berhasil Didaftarkan');
});

test('PIC can render LPK edit form and update their assigned LPK', function () {
    $this->actingAs($this->pic1)
        ->get(route('lpks.edit', $this->lpk1->id))
        ->assertStatus(200)
        ->assertSee('Ubah Profil Laboratorium')
        ->assertSee('LP-FEATURE-001');

    \Livewire\Livewire::actingAs($this->pic1)
        ->test(\App\Livewire\Lpks\LpkForm::class, ['id' => $this->lpk1->id])
        ->set('form.name', 'Lab Pengujian Banten Diperbarui PIC')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->lpk1->refresh();
    expect($this->lpk1->name)->toBe('Lab Pengujian Banten Diperbarui PIC');

    $dialog = session()->get('ts-ui:dialog');
    expect($dialog)->not->toBeNull();
    expect($dialog['type'])->toBe('success');
    expect($dialog['title'])->toBe('Berhasil Diperbarui');
});

test('active filter badges are displayed when search or status filter is applied', function () {
    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->assertDontSee('Filter Aktif:')
        ->set('search', 'Banten')
        ->assertSee('Filter Aktif:')
        ->assertSee('Pencarian:')
        ->assertSee('Banten')
        ->set('selectedStatus', 'ACTIVE')
        ->assertSee('Status:')
        ->assertSee('Aktif')
        ->call('clearSearch')
        ->assertDontSee('Pencarian:')
        ->assertSee('Status:')
        ->call('resetFilters')
        ->assertDontSee('Filter Aktif:');
});

test('importing LPK with assessment columns also creates and displays assessment data', function () {
    $csv = "no_reg,name,scope,city,province,certificate_date,expired_at,tgl_asesmen,jenis_asesmen,lead_assessor\n" .
           "LP-888-IDN,Lab Uji Kimia Presisi,Kimia & Lingkungan,Bandung,Jawa Barat,2024-05-01,2029-05-01,2025-05-10,SURVEILLANCE,Dr. Hendra Gunawan";

    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('importMode', 'paste')
        ->set('csvContent', $csv)
        ->call('executeImport');

    $lpk = Lpk::where('no_reg', 'LP-888-IDN')->first();
    expect($lpk)->not->toBeNull();
    expect($lpk->name)->toBe('Lab Uji Kimia Presisi');

    $assessment = \App\Models\Assessment::where('lpk_id', $lpk->id)->first();
    expect($assessment)->not->toBeNull();
    expect($assessment->lead_assessor)->toBe('Dr. Hendra Gunawan');
    expect($assessment->assessment_type)->toBe(\App\Enums\AssessmentType::SURVEILLANCE);
    expect($assessment->start_at->toDateString())->toBe('2025-05-10');

    // Verify calendar event was also synced
    $event = \App\Models\CalendarEvent::where('lpk_id', $lpk->id)->first();
    expect($event)->not->toBeNull();
});

test('importing LPK with custom headers nama lpk, jenis akreditasi, no reg lpk, no akreditasi, and alamat works perfectly', function () {
    $csv = "nama lpk,jenis akreditasi,no reg lpk,no akreditasi,alamat,tgl sertifikat,tgl asesmen,jenis asesmen,asesor kepala\n" .
           "Lab Kalibrasi Nasional,Laboratorium Penguji,LP-777-IDN,KAN-LP-777,\"Jl. Gatot Subroto No. 45, Bandung, Jawa Barat\",2024-06-01,2025-06-15,SURVEILLANCE,Dr. Joko Widodo";

    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('importMode', 'paste')
        ->set('csvContent', $csv)
        ->call('executeImport');

    $lpk = Lpk::where('no_reg', 'LP-777-IDN')->first();
    expect($lpk)->not->toBeNull();
    expect($lpk->name)->toBe('Lab Kalibrasi Nasional');
    expect($lpk->registration_number)->toBe('KAN-LP-777');
    expect($lpk->kan_schema)->toBe(\App\Enums\KanSchema::LP);
    expect($lpk->address)->toBe('Jl. Gatot Subroto No. 45, Bandung, Jawa Barat');

    $assessment = \App\Models\Assessment::where('lpk_id', $lpk->id)->first();
    expect($assessment)->not->toBeNull();
    expect($assessment->lead_assessor)->toBe('Dr. Joko Widodo');
    expect($assessment->start_at->toDateString())->toBe('2025-06-15');
});

test('importing LPK with numeric no reg lpk 344, no akreditasi LP-101-IDN, and tim_asesmen S1_PRL works accurately', function () {
    $csv = "nama lpk,jenis akreditasi,no reg lpk,no akreditasi,alamat,tgl sertifikat,tgl asesmen,jenis asesmen,tim_asesmen\n" .
           "Balai Pengujian Lingkungan Indonesia,LP,344,LP-101-IDN,\"Kawasan Puspiptek Serpong, Tangerang Selatan, Banten\",2024-01-10,2025-03-20,S1_PRL,Prof. Dr. Ir. Anwar Fauzi";

    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('importMode', 'paste')
        ->set('csvContent', $csv)
        ->call('executeImport');

    $lpk = Lpk::where('no_reg', '344')->first();
    expect($lpk)->not->toBeNull();
    expect($lpk->name)->toBe('Balai Pengujian Lingkungan Indonesia');
    expect($lpk->registration_number)->toBe('LP-101-IDN');
    expect($lpk->address)->toBe('Kawasan Puspiptek Serpong, Tangerang Selatan, Banten');

    $assessment = \App\Models\Assessment::where('lpk_id', $lpk->id)->first();
    expect($assessment)->not->toBeNull();
    expect($assessment->lead_assessor)->toBe('Prof. Dr. Ir. Anwar Fauzi');
    expect($assessment->assessment_type)->toBe(\App\Enums\AssessmentType::SURVEILLANCE_PRL);
    expect($assessment->assessment_type->label())->toBe('Survailen 1 + PRL (S1_PRL)');
});

test('importing LPK with headers containing periods like No. Reg LPK and Tgl. Asesmen works correctly', function () {
    $csv = "Nama Laboratorium,Jenis Akreditasi,No. Reg LPK,No. Akreditasi,Alamat,Tgl. Sertifikat,Tgl. Asesmen,Jenis Asesmen,Tim Asesmen\n" .
           "Laboratorium Pengujian Kimia Terpadu,LP,998,LP-998-IDN,\"Jl. Pegangsaan Timur No. 56, Jakarta Pusat\",2024-05-10,2025-08-14,S2,Dr. Ratna Sari";

    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('importMode', 'paste')
        ->set('csvContent', $csv)
        ->call('executeImport');

    $lpk = Lpk::where('no_reg', '998')->first();
    expect($lpk)->not->toBeNull();
    expect($lpk->name)->toBe('Laboratorium Pengujian Kimia Terpadu');
    expect($lpk->registration_number)->toBe('LP-998-IDN');

    $assessment = \App\Models\Assessment::where('lpk_id', $lpk->id)->first();
    expect($assessment)->not->toBeNull();
    expect($assessment->start_at->toDateString())->toBe('2025-08-14');
    expect($assessment->lead_assessor)->toBe('Dr. Ratna Sari');
});

test('importing LPK via uploaded file works properly', function () {
    $csv = "nama_lpk,jenis_akreditasi,no_reg_lpk,no_akreditasi,alamat\n" .
           "Lab Bioteknologi Tropis,LP,881,LP-881-IDN,\"Jl. Raya Bogor KM 46, Cibinong, Jawa Barat\"";

    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('lpk_data.csv', $csv);

    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('importMode', 'file')
        ->set('importFile', $file)
        ->call('executeImport');

    $lpk = Lpk::where('no_reg', '881')->first();
    expect($lpk)->not->toBeNull();
    expect($lpk->name)->toBe('Lab Bioteknologi Tropis');
    expect($lpk->registration_number)->toBe('LP-881-IDN');
});

test('LPK table supports sorting by column and direction', function () {
    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('sort', ['column' => 'name', 'direction' => 'desc'])
        ->assertSet('sort.column', 'name')
        ->assertSet('sort.direction', 'desc')
        ->assertSee('Lab Pengujian Jakarta')
        ->assertSee('Lab Pengujian Banten');
});

test('LPK table supports quantity dropdown per page', function () {
    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('quantity', 25)
        ->assertSet('quantity', 25);
});

test('LPK table supports multi-selection and bulk delete', function () {
    $tempLpk1 = Lpk::create([
        'no_reg' => 'LP-TEMP-001',
        'name' => 'Lab Hapus 1',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
    ]);

    $tempLpk2 = Lpk::create([
        'no_reg' => 'LP-TEMP-002',
        'name' => 'Lab Hapus 2',
        'schema' => KanSchema::LP,
        'status' => LpkStatus::ACTIVE,
    ]);

    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('selected', [$tempLpk1->id, $tempLpk2->id])
        ->assertSee('Data laboratorium dipilih')
        ->assertSee('Hapus Terpilih (2)')
        ->call('deleteSelected')
        ->assertSet('selected', []);

    expect(Lpk::find($tempLpk1->id))->toBeNull();
    expect(Lpk::find($tempLpk2->id))->toBeNull();
});

test('LPK table allows clearing status filter to null or empty without error', function () {
    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->set('selectedStatus', 'ACTIVE')
        ->assertSet('selectedStatus', 'ACTIVE')
        ->set('selectedStatus', null)
        ->assertSet('selectedStatus', null)
        ->call('clearStatus')
        ->assertSet('selectedStatus', null)
        ->call('resetFilters')
        ->assertSet('selectedStatus', null)
        ->assertSet('search', '');
});

test('LPK view mode can toggle between table and grid mode without error', function () {
    \Livewire\Livewire::actingAs($this->admin)
        ->test(\App\Livewire\Lpks\LpkIndex::class)
        ->assertSet('viewMode', 'table')
        ->assertSee('Tabel')
        ->assertSee('Grid')
        ->call('setViewMode', 'grid')
        ->assertSet('viewMode', 'grid')
        ->assertSee('Jadwalkan')
        ->call('setViewMode', 'table')
        ->assertSet('viewMode', 'table');
});

