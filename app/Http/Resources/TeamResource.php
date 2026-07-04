<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'leader' => new UserResource($this->whenLoaded('leader')),
            'members_count' => $this->whenCounted('members'),
            'settings' => $this->settings,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
