<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Enums\PatientStatus;
use App\Enums\PatientType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'patient_number',
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'gender',
        'date_of_birth',
        'hc_number',
        'man_number',
        'department',
        'employment_status',
        'company_id',
        'principal_patient_id',
        'relationship',
        'file_number',
        'nrc_number',
        'nationality',
        'marital_status',
        'phone_number',
        'alternative_phone',
        'email',
        'contact_address',
        'town_city',
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relationship',
        'balance',
        'status',
        'membership_valid_until',
        'membership_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => PatientType::class,
            'status' => PatientStatus::class,
            'date_of_birth' => 'date',
            'balance' => 'decimal:2',
            'membership_valid_until' => 'date',
            'membership_status' => MembershipStatus::class,
        ];
    }

    /** Company account used when this is a company patient. */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Principal member used when this patient is a dependant. */
    public function principalMember(): BelongsTo
    {
        return $this->belongsTo(self::class, 'principal_patient_id');
    }

    /** Dependants linked to this member account. */
    public function dependants(): HasMany
    {
        return $this->hasMany(self::class, 'principal_patient_id');
    }

    public function membership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Membership::class);
    }

    /** Deposits loaded directly into this member account. */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /** Bills raised for this patient, regardless of who paid them. */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /** Bills paid from this patient's member account. */
    public function paidBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'account_patient_id');
    }

    public function membershipFeesAsPrincipal(): HasMany
    {
        return $this->hasMany(MembershipFee::class, 'principal_patient_id');
    }

    public function membershipFeesAsDependant(): HasMany
    {
        return $this->hasMany(MembershipFee::class, 'dependant_patient_id');
    }

    /** Membership payments where this patient is the membership holder. */
    public function membershipPayments(): HasMany
    {
        return $this->hasMany(MembershipFee::class, 'patient_id');
    }

    /** Patient visits recorded by the Registry Clerk. */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function openVisit(): ?Visit
    {
        return $this->visits()->open()->latest('id')->first();
    }

    /** Whether scheme membership is active (paid and not expired). */
    public function membershipIsActive(): bool
    {
        if ($this->isCashPatient() || $this->isCompanyPatient()) {
            return false;
        }

        $membership = $this->membership;

        if ($membership) {
            return $membership->status === MembershipStatus::Active
                && $membership->expiry_date !== null
                && ! Carbon::parse($membership->expiry_date)->isPast();
        }

        return $this->membership_status === MembershipStatus::Active
            && $this->membership_valid_until !== null
            && ! Carbon::parse($this->membership_valid_until)->isPast();
    }

    public function membershipStatusLabel(): string
    {
        if ($this->isCompanyPatient() || $this->isCashPatient()) {
            return MembershipStatus::NotApplicable->label();
        }

        if ($this->membershipStanding() === MembershipStatus::PendingPayment) {
            return 'Pending Payment — ask Accounts to receive membership fee';
        }

        if ($this->membershipExpiryDate() === null) {
            return 'No membership on record';
        }

        return $this->membershipIsActive()
            ? 'Active until '.Carbon::parse($this->membershipExpiryDate())->format('d M Y')
            : 'Expired';
    }

    public function membershipStanding(): MembershipStatus
    {
        return $this->membership?->status ?? $this->membership_status;
    }

    public function membershipExpiryDate(): mixed
    {
        return $this->membership?->expiry_date ?? $this->membership_valid_until;
    }

    public function isMember(): bool
    {
        return $this->type === PatientType::Member;
    }

    public function isDependant(): bool
    {
        return $this->type === PatientType::Dependant;
    }

    public function isCompanyPatient(): bool
    {
        return $this->type === PatientType::Company;
    }

    public function isCashPatient(): bool
    {
        return $this->type === PatientType::CashPatient;
    }

    /** Whether this patient pays from a prepaid account rather than at the desk. */
    public function maintainsPrepaidAccount(): bool
    {
        return $this->isMember() || $this->isDependant() || $this->isCompanyPatient();
    }

    /**
     * Member account that should be charged for this patient.
     *
     * Members pay from their own balance. Dependants pay from the principal
     * member's balance. Company patients return null because they use a company.
     */
    public function billableAccountPatient(): ?self
    {
        if ($this->isMember()) {
            return $this;
        }

        if ($this->isDependant()) {
            return $this->principalMember;
        }

        return null;
    }

    /** Company pool that should be charged for this patient, if applicable. */
    public function billableCompany(): ?Company
    {
        return $this->isCompanyPatient() ? $this->company : null;
    }

    /**
     * Search patients by common identifiers used at the front desk.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.strtolower($term).'%';

        return $query->where(function (Builder $searchQuery) use ($like): void {
            $searchQuery
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(patient_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(hc_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(man_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(file_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(nrc_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(phone_number) LIKE ?', [$like])
                ->orWhereHas('membership', fn (Builder $membershipQuery) => $membershipQuery
                    ->whereRaw('LOWER(membership_number) LIKE ?', [$like]))
                ->orWhereHas('principalMember.membership', fn (Builder $membershipQuery) => $membershipQuery
                    ->whereRaw('LOWER(membership_number) LIKE ?', [$like]))
                ->orWhereHas('principalMember', fn (Builder $principalQuery) => $principalQuery
                    ->whereRaw('LOWER(hc_number) LIKE ?', [$like]));
        });
    }

    /** Balance available for billing this patient (member, principal, or company pool). */
    public function effectiveBalance(): string
    {
        if ($this->isCashPatient()) {
            return '0';
        }

        if ($this->isCompanyPatient()) {
            return (string) ($this->company?->balance ?? 0);
        }

        return (string) ($this->billableAccountPatient()?->balance ?? 0);
    }

    /**
     * Membership number shown on records and receipts.
     *
     * Members use their own membership. Dependants inherit the principal
     * member's membership number.
     */
    public function effectiveMembershipNumber(): ?string
    {
        if ($this->isCompanyPatient()) {
            return null;
        }

        if ($this->isCashPatient()) {
            return null;
        }

        if ($this->isDependant()) {
            $this->loadMissing('principalMember.membership');

            return $this->principalMember?->effectiveMembershipNumber();
        }

        return $this->membership?->membership_number
            ?? $this->hc_number;
    }

    /** Label explaining whose account balance is shown on the profile page. */
    public function effectiveBalanceOwnerLabel(): string
    {
        if ($this->isCashPatient()) {
            return 'Pay as you go';
        }

        if ($this->isCompanyPatient()) {
            return $this->company?->name ?? 'Company account';
        }

        if ($this->isDependant()) {
            return $this->principalMember?->name ?? 'Principal member';
        }

        return 'Own account';
    }

    /** Sum of unpaid cash bills for this casual caller. */
    public function outstandingBillTotal(): float
    {
        if (! $this->isCashPatient()) {
            return 0.0;
        }

        return (float) $this->bills()
            ->posted()
            ->outstandingCash()
            ->sum('total_amount');
    }
}
