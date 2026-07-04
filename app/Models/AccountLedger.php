<?php

namespace App\Models;

use App\Enums\LedgerAccountType;
use App\Enums\LedgerTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountLedger extends Model
{
    protected $fillable = [
        'account_type',
        'account_id',
        'transaction_type',
        'reference',
        'related_type',
        'related_id',
        'description',
        'debit',
        'credit',
        'running_balance',
        'transaction_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'account_type' => LedgerAccountType::class,
            'transaction_type' => LedgerTransactionType::class,
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
