<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'frequency' => $this->frequency,
            'interval_days' => $this->interval_days,
            'due_day' => $this->due_day,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'assignee_ids' => $this->assignee_ids,
            'notify_days_before_due' => $this->notify_days_before_due,
            'next_generation_at' => $this->next_generation_at?->toISOString(),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'generations_count' => $this->whenCounted('generations'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
