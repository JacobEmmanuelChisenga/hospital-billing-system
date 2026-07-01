<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isAdministrator(): bool
    {
        return $this->role === UserRole::Administrator;
    }

    public function isAccountsStaff(): bool
    {
        return $this->role === UserRole::Accounts;
    }

    public function isRegistryClerk(): bool
    {
        return $this->role === UserRole::Registry || $this->role === UserRole::Nursing;
    }

    public function isNurse(): bool
    {
        return $this->role === UserRole::Nurse;
    }

    /** @deprecated Use isRegistryClerk() */
    public function isNursingStaff(): bool
    {
        return $this->isRegistryClerk();
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** Can create deposits, membership payments, and company accounts. */
    public function canPerformFinancialOperations(): bool
    {
        return $this->hasAnyRole(UserRole::financialOperations());
    }

    /** Can view deposits, receipts, statements, and financial reports. */
    public function canViewFinancialRecords(): bool
    {
        return $this->hasAnyRole(UserRole::financialViewAccess());
    }

    /** @deprecated Use canPerformFinancialOperations() or canViewFinancialRecords() */
    public function canAccessAccountsModules(): bool
    {
        return $this->canPerformFinancialOperations() || $this->canViewFinancialRecords();
    }

    /** Can search and view patient profiles. */
    public function canAccessPatientModules(): bool
    {
        return $this->hasAnyRole(UserRole::patientViewAccess());
    }

    /** Can register and edit patient demographics. */
    public function canManagePatientDemographics(): bool
    {
        return $this->hasAnyRole(UserRole::patientManageAccess());
    }

    /** Can open visits, add charges, and post bills. */
    public function canManageVisits(): bool
    {
        return $this->hasAnyRole(UserRole::visitManageAccess());
    }

    /** Can record clinical notes on active visits. */
    public function canRecordClinicalNotes(): bool
    {
        return $this->hasAnyRole(UserRole::clinicalAccess());
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'created_by');
    }

    public function companyDeposits(): HasMany
    {
        return $this->hasMany(CompanyDeposit::class, 'created_by');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'created_by');
    }

    public function membershipFees(): HasMany
    {
        return $this->hasMany(MembershipFee::class, 'created_by');
    }

    public function openedVisits(): HasMany
    {
        return $this->hasMany(Visit::class, 'opened_by');
    }

    public function clinicalNotes(): HasMany
    {
        return $this->hasMany(ClinicalNote::class, 'recorded_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
