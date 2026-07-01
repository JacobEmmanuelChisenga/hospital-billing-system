<?php

namespace App\Models;

use App\Enums\VisitStatus;
use App\Enums\VisitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'visit_date',
        'visit_type',
        'ward_bed',
        'status',
        'opened_by',
        'bill_id',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'visit_type' => VisitType::class,
            'status' => VisitStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function chargeLines(): HasMany
    {
        return $this->hasMany(ChargeLine::class);
    }

    public function clinicalNote(): HasOne
    {
        return $this->hasOne(ClinicalNote::class);
    }

    public function isOpen(): bool
    {
        return $this->status->isActive();
    }

    public function canRecordClinicalNotes(): bool
    {
        return in_array($this->status, [
            VisitStatus::ReadyForConsultation,
            VisitStatus::SeenByNurse,
            VisitStatus::AwaitingBilling,
        ], true);
    }

    public function canAddCharges(): bool
    {
        return $this->status === VisitStatus::AwaitingBilling;
    }

    public function chargesTotal(): float
    {
        return (float) $this->chargeLines()->sum('amount');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [VisitStatus::Completed->value, VisitStatus::Cancelled->value]);
    }
}
