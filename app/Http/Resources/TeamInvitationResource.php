<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'invited_by' => $this->invited_by,
            'email' => $this->email,
            'token' => $this->when($request->routeIs('invitations.accept'), $this->token),
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'is_expired' => $this->isExpired(),
            'inviter' => new UserResource($this->whenLoaded('inviter')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
