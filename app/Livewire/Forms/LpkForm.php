<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\DTOs\LpkData;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use App\Models\Lpk;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LpkForm extends Form
{
    public ?Lpk $lpk = null;

    #[Validate('required|string|max:50')]
    public string $no_reg = '';

    #[Validate('nullable|string|max:50')]
    public ?string $registration_number = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required')]
    public string $kan_schema = 'LP';

    #[Validate('nullable|string')]
    public ?string $scope = null;

    #[Validate('nullable|string|max:255')]
    public ?string $address = null;

    #[Validate('nullable|string|max:100')]
    public ?string $city = null;

    #[Validate('nullable|string|max:100')]
    public ?string $province = null;

    #[Validate('nullable|email|max:150')]
    public ?string $email = null;

    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;

    #[Validate('nullable|string|max:100')]
    public ?string $contact_person = null;

    #[Validate('nullable|date')]
    public ?string $certificate_date = null;

    #[Validate('nullable|date')]
    public ?string $expired_at = null;

    #[Validate('nullable|exists:users,id')]
    public ?int $pic_user_id = null;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    #[Validate('nullable|url|max:255')]
    public ?string $drive_url = null;

    #[Validate('required')]
    public string $status = 'ACTIVE';

    public function setLpk(Lpk $lpk): void
    {
        $this->lpk = $lpk;
        $this->no_reg = $lpk->no_reg;
        $this->registration_number = $lpk->registration_number;
        $this->name = $lpk->name;
        $this->kan_schema = $lpk->kan_schema->value;
        $this->scope = $lpk->scope;
        $this->address = $lpk->address;
        $this->city = $lpk->city;
        $this->province = $lpk->province;
        $this->email = $lpk->email;
        $this->phone = $lpk->phone;
        $this->contact_person = $lpk->contact_person;
        $this->certificate_date = $lpk->certificate_date?->format('Y-m-d');
        $this->expired_at = $lpk->expired_at?->format('Y-m-d');
        $this->pic_user_id = $lpk->pic_user_id;
        $this->notes = $lpk->notes;
        $this->drive_url = $lpk->drive_url;
        $this->status = $lpk->status->value;
    }

    public function toDto(): LpkData
    {
        return new LpkData(
            no_reg: $this->no_reg,
            name: $this->name,
            kan_schema: KanSchema::from($this->kan_schema),
            registration_number: $this->registration_number ?: $this->no_reg,
            scope: $this->scope,
            address: $this->address,
            city: $this->city,
            province: $this->province,
            email: $this->email,
            phone: $this->phone,
            contact_person: $this->contact_person,
            certificate_date: $this->certificate_date,
            expired_at: $this->expired_at,
            pic_user_id: $this->pic_user_id,
            notes: $this->notes,
            drive_url: $this->drive_url,
            status: LpkStatus::from($this->status),
        );
    }
}
