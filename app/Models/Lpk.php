<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lpk extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_reg',
        'registration_number',
        'name',
        'kan_schema',
        'schema',
        'scope',
        'address',
        'city',
        'province',
        'email',
        'phone',
        'contact_person',
        'status',
        'certificate_date',
        'expired_at',
        'pic_user_id',
        'notes',
        'pic_notes',
        'drive_url',
        'suspended_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'kan_schema' => KanSchema::class,
            'status' => LpkStatus::class,
            'certificate_date' => 'date',
            'expired_at' => 'date',
            'suspended_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(\App\Services\SurveillanceEngineService::class)->clearNotificationCache();
        });

        static::deleted(function () {
            app(\App\Services\SurveillanceEngineService::class)->clearNotificationCache();
        });
    }

    public function getSchemaAttribute(): ?KanSchema
    {
        return $this->kan_schema;
    }

    public function setSchemaAttribute(mixed $value): void
    {
        $this->attributes['kan_schema'] = $value instanceof KanSchema ? $value->value : $value;
    }


    public function isExpired(): bool
    {
        return $this->expired_at ? $this->expired_at->isPast() : false;
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class)->orderByDesc('start_at');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function scopeForPic(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('pic_user_id', $user->id);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $term = "%{$search}%";
            $q->where('no_reg', 'like', $term)
                ->orWhere('name', 'like', $term)
                ->orWhere('address', 'like', $term)
                ->orWhere('city', 'like', $term)
                ->orWhere('province', 'like', $term)
                ->orWhere('contact_person', 'like', $term)
                ->orWhere('email', 'like', $term);
        });
    }

    public function scopeSchema(Builder $query, KanSchema|string|null $schema): Builder
    {
        if (blank($schema)) {
            return $query;
        }

        $val = $schema instanceof KanSchema ? $schema->value : $schema;

        return $query->where('kan_schema', $val);
    }

    public function scopeStatus(Builder $query, LpkStatus|string|null $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        $val = $status instanceof LpkStatus ? $status->value : $status;

        return $query->where('status', $val);
    }
}
