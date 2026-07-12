<?php

namespace App\Models;

use App\Enums\BillStatus;
use App\Enums\PaymentMethod;
use App\Enums\VisitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'account_patient_id',
        'company_id',
        'visit_id',
        'visit_date',
        'visit_type',
        'ward_bed',
        'consultation_amount',
        'pharmacy_amount',
        'lab_amount',
        'ward_amount',
        'other_amount',
        'total_amount',
        'notes',
        'status',
        'payment_method',
        'paid_at',
        'paid_by',
        'void_reason',
        'voided_at',
        'voided_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'visit_type' => VisitType::class,
            'consultation_amount' => 'decimal:2',
            'pharmacy_amount' => 'decimal:2',
            'lab_amount' => 'decimal:2',
            'ward_amount' => 'decimal:2',
            'other_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'status' => BillStatus::class,
            'payment_method' => PaymentMethod::class,
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    /** Patient who received the service. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** Member account that paid the bill, if this was not a company bill. */
    public function accountPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'account_patient_id');
    }

    /** Company account that paid the bill, if this was a company patient. */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /** Charge lines from the visit that produced this bill. */
    public function chargeLines(): HasMany
    {
        return $this->hasMany(ChargeLine::class, 'visit_id', 'visit_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function isVoided(): bool
    {
        return $this->status === BillStatus::Voided;
    }

    /** Casual caller bill with no prepaid account attached. */
    public function isCashBill(): bool
    {
        return $this->account_patient_id === null && $this->company_id === null;
    }

    public function isPaid(): bool
    {
        if ($this->isVoided()) {
            return false;
        }

        if ($this->isCashBill()) {
            return $this->paid_at !== null;
        }

        return true;
    }

    public function isOutstanding(): bool
    {
        return $this->isCashBill() && ! $this->isVoided() && $this->paid_at === null;
    }

    /** Human-friendly payer name for receipts and statements. */
    public function payerName(): string
    {
        if ($this->isCashBill()) {
            return $this->patient?->name ?? 'Casual caller';
        }

        if ($this->company) {
            return $this->company->name;
        }

        return $this->accountPatient?->name ?? 'Unknown account';
    }

    /** Bills that are still active (not voided). */
    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', BillStatus::Posted);
    }

    /** Casual caller bills awaiting payment at Accounts. */
    public function scopeOutstandingCash(Builder $query): Builder
    {
        return $query
            ->posted()
            ->whereNull('account_patient_id')
            ->whereNull('company_id')
            ->whereNull('paid_at');
    }

    /** Remaining balance on the payer account after this bill was posted. */
    public function payerBalanceAfter(): float
    {
        if ($this->isCashBill()) {
            return 0.0;
        }

        if ($this->company) {
            return (float) $this->company->balance;
        }

        return (float) ($this->accountPatient?->balance ?? 0);
    }
}
