<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\KanSchema;
use App\Enums\LpkStatus;

readonly class LpkData
{
    public function __construct(
        public string $no_reg,
        public string $name,
        public KanSchema $kan_schema,
        public ?string $registration_number = null,
        public ?string $scope = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $province = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $contact_person = null,
        public ?string $certificate_date = null,
        public ?string $expired_at = null,
        public ?int $pic_user_id = null,
        public ?string $notes = null,
        public ?string $drive_url = null,
        public LpkStatus $status = LpkStatus::ACTIVE,
        public ?string $assessment_title = null,
        public ?string $assessment_type = null,
        public ?string $assessment_date = null,
        public ?string $assessment_end_date = null,
        public ?string $lead_assessor = null,
        public ?string $assessor_team = null,
        public ?string $assessment_status = null,
        public ?int $tp_findings_total = null,
        public ?int $tp_findings_closed = null,
        public ?string $tp_due_date = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $noReg = (string) ($data['no_reg_lpk'] ?? $data['no_reg'] ?? $data['nomor_registrasi'] ?? $data['no_registrasi'] ?? '');
        $regNumber = (string) ($data['no_akreditasi'] ?? $data['nomor_akreditasi'] ?? $data['registration_number'] ?? '');

        if (empty($noReg) && ! empty($regNumber)) {
            $noReg = $regNumber;
        }
        if (empty($regNumber) && ! empty($noReg)) {
            $regNumber = $noReg;
        }

        $address = $data['alamat'] ?? $data['address'] ?? null;
        $timAsesmen = $data['tim_asesmen'] ?? $data['tim_asesor'] ?? $data['asesor_kepala'] ?? $data['lead_assessor'] ?? $data['ketua_asesor'] ?? null;

        return new self(
            no_reg: $noReg,
            name: (string) ($data['nama_lpk'] ?? $data['name'] ?? $data['nama'] ?? ''),
            kan_schema: $data['kan_schema'] instanceof KanSchema
                ? $data['kan_schema']
                : KanSchema::from((string) ($data['kan_schema'] ?? $data['jenis_akreditasi'] ?? 'LP')),
            registration_number: $regNumber,
            scope: $data['scope'] ?? $data['lingkup'] ?? null,
            address: $address,
            city: $data['city'] ?? $data['kota'] ?? null,
            province: $data['province'] ?? $data['provinsi'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? $data['telepon'] ?? null,
            contact_person: $data['contact_person'] ?? $data['kontak'] ?? null,
            certificate_date: isset($data['certificate_date']) && $data['certificate_date'] ? (string) $data['certificate_date'] : (isset($data['tgl_sertifikat']) && $data['tgl_sertifikat'] ? (string) $data['tgl_sertifikat'] : null),
            expired_at: isset($data['expired_at']) && $data['expired_at'] ? (string) $data['expired_at'] : (isset($data['tgl_kedaluwarsa']) && $data['tgl_kedaluwarsa'] ? (string) $data['tgl_kedaluwarsa'] : null),
            pic_user_id: isset($data['pic_user_id']) && $data['pic_user_id'] ? (int) $data['pic_user_id'] : null,
            notes: $data['notes'] ?? $data['catatan'] ?? null,
            drive_url: $data['drive_url'] ?? null,
            status: $data['status'] instanceof LpkStatus
                ? $data['status']
                : (isset($data['status']) ? LpkStatus::from((string) $data['status']) : LpkStatus::ACTIVE),
            assessment_title: $data['assessment_title'] ?? $data['judul_asesmen'] ?? null,
            assessment_type: $data['assessment_type'] ?? $data['jenis_asesmen'] ?? $data['tipe_asesmen'] ?? null,
            assessment_date: $data['assessment_date'] ?? $data['tgl_asesmen'] ?? $data['tgl_kunjungan'] ?? null,
            assessment_end_date: $data['assessment_end_date'] ?? $data['tgl_selesai_asesmen'] ?? null,
            lead_assessor: $timAsesmen,
            assessor_team: $data['assessor_team'] ?? $timAsesmen,
            assessment_status: $data['assessment_status'] ?? $data['status_asesmen'] ?? null,
            tp_findings_total: isset($data['tp_findings_total']) && $data['tp_findings_total'] !== '' ? (int) $data['tp_findings_total'] : (isset($data['temuan_total']) && $data['temuan_total'] !== '' ? (int) $data['temuan_total'] : null),
            tp_findings_closed: isset($data['tp_findings_closed']) && $data['tp_findings_closed'] !== '' ? (int) $data['tp_findings_closed'] : (isset($data['temuan_selesai']) && $data['temuan_selesai'] !== '' ? (int) $data['temuan_selesai'] : null),
            tp_due_date: $data['tp_due_date'] ?? $data['batas_tp'] ?? $data['tgl_batas_tp'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'no_reg' => $this->no_reg,
            'registration_number' => $this->registration_number ?: $this->no_reg,
            'name' => $this->name,
            'kan_schema' => $this->kan_schema->value,
            'scope' => $this->scope,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'email' => $this->email,
            'phone' => $this->phone,
            'contact_person' => $this->contact_person,
            'certificate_date' => $this->certificate_date,
            'expired_at' => $this->expired_at,
            'pic_user_id' => $this->pic_user_id,
            'notes' => $this->notes,
            'drive_url' => $this->drive_url,
            'status' => $this->status->value,
        ];
    }
}
