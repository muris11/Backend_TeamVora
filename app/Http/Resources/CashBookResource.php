<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashBookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'date' => $this->date,
            'attachment_url' => $this->attachment_path,
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
