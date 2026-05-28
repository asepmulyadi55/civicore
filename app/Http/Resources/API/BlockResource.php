<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'is_active'       => $this->is_active,
            'residents_count' => $this->whenCounted('residents'),
            'units_count'     => $this->whenCounted('units'),
            'created_at'      => $this->created_at->toISOString(),
        ];
    }
}
