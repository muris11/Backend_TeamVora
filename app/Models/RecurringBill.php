<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RecurringBill extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'team_id', 'title', 'description', 'creator_id', 'amount', 'frequency', 'interval_days',
        'due_day', 'status', 'start_date', 'end_date', 'last_generated_at', 'next_generation_at',
        'assignee_ids', 'notify_days_before_due', 'last_error_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_generated_at' => 'date',
            'next_generation_at' => 'date',
            'assignee_ids' => 'array',
            'last_error_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function generations(): HasMany
    {
        return $this->hasMany(RecurringBillGeneration::class);
    }

    public function generatedBills(): HasMany
    {
        return $this->hasMany(SplitBill::class, 'parent_recurring_bill_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDueForGeneration($query)
    {
        return $query->active()
            ->whereDate('next_generation_at', '<=', today())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            });
    }
}
