<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SplitBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'total_amount' => (float) $this->total_amount,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => BillItemResource::collection($this->whenLoaded('items')),
            'parent_recurring_bill_id' => $this->parent_recurring_bill_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
