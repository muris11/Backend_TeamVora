<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'leader_id',
        'settings',
        'logo_url',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }


    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'team_id');
    }

    public function cashBooks(): HasMany
    {
        return $this->hasMany(CashBook::class, 'team_id');
    }

    public function splitBills(): HasMany
    {
        return $this->hasMany(SplitBill::class, 'team_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'team_id');
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class, 'team_id');
    }

    public function recurringBills(): HasMany
    {
        return $this->hasMany(RecurringBill::class, 'team_id');
    }

    public function teamMedia(): HasMany
    {
        return $this->hasMany(TeamMedia::class, 'team_id');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'team_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'team_id');
    }
}
