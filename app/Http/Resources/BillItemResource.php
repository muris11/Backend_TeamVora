<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'split_bill_id' => $this->split_bill_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'proof_url' => $this->proof_path,
            'verified_by' => new UserResource($this->whenLoaded('verifier')),
            'verified_at' => $this->verified_at?->toISOString(),
            'reminder_sent_at' => $this->reminder_sent_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
