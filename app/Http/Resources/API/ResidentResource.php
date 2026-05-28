<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'fullname'            => $this->fullname,
            'phone'               => $this->phone,
            'email'               => $this->email,
            'is_active'           => $this->is_active,
            'notes'               => $this->notes,
            'photo_url'           => $this->photo_path
                ? route('api.residents.photo', $this->id)
                : null,
            'created_at'          => $this->created_at->toISOString(),
            'updated_at'          => $this->updated_at->toISOString(),
            'block'               => $this->whenLoaded('block', fn() => $this->block ? [
                'id'   => $this->block->id,
                'name' => $this->block->name,
            ] : null),
            'unit'                => $this->whenLoaded('unit', fn() => $this->unit ? [
                'id'           => $this->unit->id,
                'unit_number'  => $this->unit->unit_number,
                'house_status' => $this->unit->house_status,
            ] : null),
            'family_members'      => $this->whenLoaded('familyMembers', fn() =>
                $this->familyMembers->map(fn($m) => [
                    'id'       => $m->id,
                    'fullname' => $m->fullname,
                    'is_head'  => $m->is_head,
                ])
            ),
        ];
    }
}
