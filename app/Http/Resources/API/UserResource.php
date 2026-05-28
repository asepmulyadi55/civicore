<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'username'       => $this->username,
            'email'          => $this->email,
            'is_active'      => $this->is_active,
            'language'       => $this->language,
            'avatar_url'     => $this->avatarUrl(),
            'last_login_at'  => $this->last_login_at?->toISOString(),
            'last_active_at' => $this->last_active_at?->toISOString(),
            'created_at'     => $this->created_at->toISOString(),
            'role'           => $this->whenLoaded('role', fn() => [
                'id'    => $this->role->id,
                'name'  => $this->role->name,
                'label' => $this->role->label,
            ]),
            'block'          => $this->whenLoaded('block', fn() => $this->block ? [
                'id'   => $this->block->id,
                'name' => $this->block->name,
            ] : null),
        ];
    }
}
