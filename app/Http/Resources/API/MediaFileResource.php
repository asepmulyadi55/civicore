<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'filename'    => $this->filename,
            'original_name' => $this->original_name,
            'mime_type'   => $this->mime_type,
            'size'        => $this->size,
            'disk'        => $this->disk,
            'path'        => $this->path,
            'url'         => $this->disk === 'public'
                ? asset('storage/' . $this->path)
                : route('api.media.file', $this->id),
            'created_at'  => $this->created_at->toISOString(),
            'uploaded_by' => $this->whenLoaded('uploadedBy', fn() => $this->uploadedBy ? [
                'id'   => $this->uploadedBy->id,
                'name' => $this->uploadedBy->name,
            ] : null),
        ];
    }
}
