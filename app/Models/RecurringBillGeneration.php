<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringBillGeneration extends Model
{
    use HasUuids;

    protected $fillable = ['recurring_bill_id', 'split_bill_id', 'generated_at'];

    protected function casts(): array
    {
        return [
            'generated_at' => 'date',
        ];
    }

    public function recurringBill(): BelongsTo
    {
        return $this->belongsTo(RecurringBill::class);
    }

    public function splitBill(): BelongsTo
    {
        return $this->belongsTo(SplitBill::class);
    }
}
