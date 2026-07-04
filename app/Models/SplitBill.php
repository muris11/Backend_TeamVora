<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SplitBill extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['team_id', 'title', 'description', 'total_amount', 'due_date', 'status', 'creator_id', 'parent_recurring_bill_id'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function recurringBill(): BelongsTo
    {
        return $this->belongsTo(RecurringBill::class, 'parent_recurring_bill_id');
    }
}
