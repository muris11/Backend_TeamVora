<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
