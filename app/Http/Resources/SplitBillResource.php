<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SplitBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userItem = null;
        if ($this->relationLoaded('items') && $request->user()) {
            $userItem = $this->items->firstWhere('user_id', $request->user()->id);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'total_amount' => (float) $this->total_amount,
            'due_date' => $this->due_date,
            'status' => $this->status, // overall status (active, completed)
            'user_status' => $userItem ? $userItem->status : 'none', // unpaid, pending_verification, paid, or none
            'user_amount' => $userItem ? (float) $userItem->amount : 0,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => BillItemResource::collection($this->whenLoaded('items')),
            'parent_recurring_bill_id' => $this->parent_recurring_bill_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
