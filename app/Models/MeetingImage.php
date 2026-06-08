<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MeetingImage extends Model
{
    use HasUuids;

    protected $fillable = [
        'meeting_id',
        'path',
        'original_name',
        'size',
        'mime_type',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** URL to serve this image through the private file controller. */
    public function url(): string
    {
        return route('private.file', ['path' => $this->path]);
    }

    /** Delete the stored file from disk. */
    public function deleteFile(): void
    {
        Storage::disk('local')->delete($this->path);
    }
}
