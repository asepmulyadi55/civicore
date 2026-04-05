<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasUuids;
    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Returns the URL to access this file. Private (local disk) files go through the auth-protected route. */
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'local') {
            return route('private.file', ['path' => $this->path]);
        }
        return Storage::disk($this->disk)->url($this->path);
    }

    /** Returns a human-readable file size (KB / MB). */
    public function getHumanSizeAttribute(): string
    {
        if ($this->size < 1024) {
            return $this->size . ' B';
        }
        if ($this->size < 1048576) {
            return round($this->size / 1024, 1) . ' KB';
        }
        return round($this->size / 1048576, 2) . ' MB';
    }

    /** Returns true if this file is an image based on mime type. */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}
