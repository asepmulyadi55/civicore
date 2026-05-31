<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Lightweight DTO that mimics MediaFile Eloquent model attributes
 * for resident / family-member photos that are stored on-disk but
 * not tracked in the media_files table.
 *
 * The blade grid template accesses properties the same way it does
 * on MediaFile, via __get() which delegates to a public property or
 * a computed getter method (getXxxAttribute pattern).
 */
class VirtualMediaFile
{
    public readonly string $id;
    public readonly string $disk;
    public readonly string $path;
    public readonly string $original_name;
    public readonly string $mime_type;
    public readonly int    $size;
    public readonly ?Carbon $created_at;

    /** @param array{id:string, disk:string, path:string, original_name:string, mime_type:string, size:int, created_at:Carbon|null} $attrs */
    public function __construct(array $attrs)
    {
        $this->id            = $attrs['id'];
        $this->disk          = $attrs['disk']          ?? 'local';
        $this->path          = $attrs['path'];
        $this->original_name = $attrs['original_name'];
        $this->mime_type     = $attrs['mime_type']     ?? 'image/jpeg';
        $this->size          = $attrs['size']          ?? 0;
        $this->created_at    = $attrs['created_at']    ?? null;
    }

    /** Blade uses $file->url */
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'local') {
            return route('private.file', ['path' => $this->path]);
        }
        return Storage::disk($this->disk)->url($this->path);
    }

    /** Blade uses $file->is_image */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Blade uses $file->delete_url
     * Returns the route URL for deleting this virtual file's photo from the owning record.
     */
    public function getDeleteUrlAttribute(): string
    {
        if (str_starts_with($this->id, 'householder:')) {
            return route('media.householder-photo.destroy', substr($this->id, 9));
        }
        // member:
        return route('media.resident-photo.destroy', substr($this->id, 7));
    }

    /** Blade uses $file->human_size */
    public function getHumanSizeAttribute(): string
    {
        if ($this->size < 1024)    return $this->size . ' B';
        if ($this->size < 1048576) return round($this->size / 1024, 1) . ' KB';
        return round($this->size / 1048576, 2) . ' MB';
    }

    /** Support Eloquent-style $file->url, $file->is_image, $file->human_size, $file->delete_url */
    public function __get(string $name): mixed
    {
        // Convert snake_case to camelCase so e.g. 'is_image' → 'getIsImageAttribute'
        $camel  = str_replace('_', '', ucwords($name, '_'));
        $method = 'get' . $camel . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return null;
    }
}

