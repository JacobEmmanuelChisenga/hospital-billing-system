<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'principal_patient_id',
        'dependant_patient_id',
        'amount',
        'payment_method',
        'reference',
        'payment_date',
        'expiry_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    /** The member or dependant whose membership this payment covers. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /** Principal member, set only when the holder is a dependant. */
    public function principalPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'principal_patient_id');
    }

    /** Dependant covered by this annual membership fee. */
    public function dependantPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'dependant_patient_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return ! $this->isExpired() && $this->expiry_date->lte(now()->addDays($days));
    }
}
