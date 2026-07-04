<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringBillGenerationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recurring_bill_id' => $this->recurring_bill_id,
            'split_bill' => new SplitBillResource($this->whenLoaded('splitBill')),
            'generated_at' => $this->generated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
