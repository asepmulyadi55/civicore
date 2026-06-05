<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasUuids;

    protected $fillable = [
        'topic',
        'meeting_date',
        'meeting_time',
        'location',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function attendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class)->orderBy('created_at');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MeetingImage::class)->orderBy('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Count of attendees marked as present. */
    public function presentCount(): int
    {
        return $this->relationLoaded('attendances')
            ? $this->attendances->where('present', true)->count()
            : $this->attendances()->where('present', true)->count();
    }

    /** Formatted date + time for display. */
    public function formattedDateTime(): string
    {
        return $this->meeting_date->format('d M Y') . ' · ' . substr($this->meeting_time, 0, 5);
    }
}
