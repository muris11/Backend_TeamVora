<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'title' => $this->title,
            'log_date' => $this->log_date,
            'content' => $this->content,
            'attachment_url' => $this->attachment_path,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
