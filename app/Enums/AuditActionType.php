<?php

namespace App\Enums;

/**
 * High-level action names stored in the audit log.
 */
enum AuditActionType: string
{
    case PatientCreated = 'patient_created';
    case PatientUpdated = 'patient_updated';
    case DepositCreated = 'deposit_created';
    case DepositReversed = 'deposit_reversed';
    case CompanyCreated = 'company_created';
    case CompanyUpdated = 'company_updated';
    case CompanySuspended = 'company_suspended';
    case CompanyDepositCreated = 'company_deposit_created';
    case CompanyDepositReversed = 'company_deposit_reversed';
    case BillCreated = 'bill_created';
    case BillVoided = 'bill_voided';
    case MembershipFeeRecorded = 'membership_fee_recorded';
    case MembershipRegistered = 'membership_registered';
    case VisitOpened = 'visit_opened';
    case VisitCompleted = 'visit_completed';
    case VisitCancelled = 'visit_cancelled';
    case ClinicalNoteRecorded = 'clinical_note_recorded';
    case ChargeLineAdded = 'charge_line_added';
    case ChargeLineRemoved = 'charge_line_removed';
    case UserCreated = 'user_created';
    case UserUpdated = 'user_updated';
    case SystemSettingsUpdated = 'system_settings_updated';
    case BillableServiceCreated = 'billable_service_created';
    case BillableServiceUpdated = 'billable_service_updated';

    public function label(): string
    {
        return match ($this) {
            self::PatientCreated => 'Patient registered',
            self::PatientUpdated => 'Patient updated',
            self::DepositCreated => 'Member deposit',
            self::DepositReversed => 'Deposit reversed',
            self::CompanyCreated => 'Company created',
            self::CompanyUpdated => 'Company updated',
            self::CompanySuspended => 'Company suspended',
            self::CompanyDepositCreated => 'Company deposit',
            self::CompanyDepositReversed => 'Company deposit reversed',
            self::BillCreated => 'Bill posted',
            self::BillVoided => 'Bill voided',
            self::MembershipFeeRecorded => 'Membership payment',
            self::MembershipRegistered => 'Membership registered (pending payment)',
            self::VisitOpened => 'Visit opened',
            self::VisitCompleted => 'Visit completed',
            self::VisitCancelled => 'Visit cancelled',
            self::ClinicalNoteRecorded => 'Clinical note recorded',
            self::ChargeLineAdded => 'Charge added',
            self::ChargeLineRemoved => 'Charge removed',
            self::UserCreated => 'Staff user created',
            self::UserUpdated => 'Staff user updated',
            self::SystemSettingsUpdated => 'System settings updated',
            self::BillableServiceCreated => 'Service catalogue item created',
            self::BillableServiceUpdated => 'Service catalogue item updated',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PatientCreated, self::PatientUpdated, self::MembershipRegistered => 'bg-blue-100 text-blue-800',
            self::DepositCreated, self::CompanyCreated, self::CompanyUpdated, self::CompanyDepositCreated => 'bg-green-100 text-green-800',
            self::DepositReversed, self::CompanySuspended, self::CompanyDepositReversed, self::BillVoided, self::VisitCancelled => 'bg-red-100 text-red-800',
            self::BillCreated, self::VisitCompleted, self::ChargeLineAdded => 'bg-amber-100 text-amber-800',
            self::MembershipFeeRecorded => 'bg-purple-100 text-purple-800',
            self::VisitOpened, self::ClinicalNoteRecorded => 'bg-cyan-100 text-cyan-800',
            self::ChargeLineRemoved => 'bg-orange-100 text-orange-800',
            self::UserCreated, self::UserUpdated, self::SystemSettingsUpdated, self::BillableServiceCreated, self::BillableServiceUpdated => 'bg-gray-100 text-gray-800',
        };
    }
}
