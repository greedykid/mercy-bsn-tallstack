<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CalendarEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpk_id',
        'assessment_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'start_at',
        'end_at',
        'is_all_day',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => CalendarEventType::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_all_day' => 'boolean',
        ];
    }

    public function lpk(): BelongsTo
    {
        return $this->belongsTo(Lpk::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toPopoverPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'event_type_value' => $this->event_type->value,
            'event_type_label' => $this->event_type->label(),
            'event_type_category' => $this->event_type->category(),
            'event_type_color' => $this->color ?: $this->event_type->color(),
            'start_at_time' => $this->start_at->format('H:i'),
            'end_at_time' => $this->end_at->format('H:i'),
            'date_formatted' => $this->start_at->translatedFormat('l, d F Y'),
            'time_formatted' => $this->start_at->format('H:i') . ' - ' . $this->end_at->format('H:i') . ' WIB',
            'lpk_id' => $this->lpk_id,
            'lpk_name' => $this->lpk?->name,
            'lpk_no_reg' => $this->lpk ? ($this->lpk->registration_number ?: $this->lpk->no_reg) : null,
            'lpk_url' => $this->lpk_id ? route('lpks.show', $this->lpk_id) : null,
            'assessment_id' => $this->assessment_id,
            'assessment_url' => $this->assessment_id ? route('assessments.show', $this->assessment_id) : null,
        ];
    }
}
