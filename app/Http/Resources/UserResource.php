<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_path,
            'role' => $this->role,
            'team_id' => $this->team_id,
            'team' => new TeamResource($this->whenLoaded('team')),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions' => $this->whenLoaded('roles', fn () => $this->roles->flatMap->permissions->pluck('name')->unique()->values()->all()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
