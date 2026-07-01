<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'amount',
        'deposit_date',
        'reference',
        'notes',
        'created_by',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'deposit_date' => 'date',
            'reversed_at' => 'datetime',
        ];
    }

    /** Company account that received this deposit. */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }

    /** Deposits that still count toward the company pool balance. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('reversed_at');
    }
}
