<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\PatientType;
use App\Enums\PaymentMethod;
use App\Enums\VisitType;
use App\Models\Bill;
use App\Models\Company;
use App\Models\CompanyDeposit;
use App\Models\Deposit;
use App\Models\MembershipFee;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Central place for report queries so screen totals and CSV exports always match.
 */
class ReportService
{
    public function __construct(
        private LedgerService $ledgerService,
    ) {}

    /**
     * Resolve a date range from quick presets or custom from/to fields.
     *
     * @return array{from: Carbon, to: Carbon, preset: string}
     */
    public function resolveDateRange(Request $request): array
    {
        $preset = $request->string('preset')->toString() ?: 'month';

        if ($preset === 'today') {
            return ['from' => today()->startOfDay(), 'to' => today()->endOfDay(), 'preset' => 'today'];
        }

        if ($preset === 'week') {
            return [
                'from' => now()->startOfWeek()->startOfDay(),
                'to' => now()->endOfWeek()->endOfDay(),
                'preset' => 'week',
            ];
        }

        if ($preset === 'custom' && $request->filled('from_date') && $request->filled('to_date')) {
            return [
                'from' => Carbon::parse($request->input('from_date'))->startOfDay(),
                'to' => Carbon::parse($request->input('to_date'))->endOfDay(),
                'preset' => 'custom',
            ];
        }

        // Default: current calendar month.
        return [
            'from' => now()->startOfMonth()->startOfDay(),
            'to' => now()->endOfMonth()->endOfDay(),
            'preset' => $preset === 'custom' ? 'month' : $preset,
        ];
    }

    /** High-level totals for the reports dashboard. */
    public function summary(Carbon $from, Carbon $to): array
    {
        $memberDeposits = (float) Deposit::query()
            ->active()
            ->whereDate('deposit_date', '>=', $from)
            ->whereDate('deposit_date', '<=', $to)
            ->sum('amount');

        $companyDeposits = (float) CompanyDeposit::query()
            ->active()
            ->whereDate('deposit_date', '>=', $from)
            ->whereDate('deposit_date', '<=', $to)
            ->sum('amount');

        $billsTotal = (float) Bill::query()
            ->posted()
            ->whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to)
            ->sum('total_amount');

        $voidedBills = Bill::query()
            ->where('status', BillStatus::Voided)
            ->whereBetween('voided_at', [$from, $to])
            ->get();

        $reversedDeposits = Deposit::query()
            ->whereNotNull('reversed_at')
            ->whereBetween('reversed_at', [$from, $to])
            ->get();

        $reversedCompanyDeposits = CompanyDeposit::query()
            ->whereNotNull('reversed_at')
            ->whereBetween('reversed_at', [$from, $to])
            ->get();

        $visitSummary = Bill::query()
            ->posted()
            ->whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to)
            ->selectRaw('visit_type, COUNT(*) as bill_count, SUM(total_amount) as total_amount')
            ->groupBy('visit_type')
            ->get()
            ->keyBy(fn ($row) => $row->visit_type);

        $visitTypes = collect(VisitType::cases())->map(function (VisitType $type) use ($visitSummary) {
            $row = $visitSummary->get($type->value);

            return [
                'type' => $type,
                'count' => (int) ($row->bill_count ?? 0),
                'total' => (float) ($row->total_amount ?? 0),
            ];
        });

        $expiringMemberships = MembershipFee::query()
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('expiry_date', '<=', today()->addDays(30))
            ->count();

        $expiredMemberships = MembershipFee::query()
            ->whereDate('expiry_date', '<', today())
            ->count();

        return [
            'member_deposits_total' => $memberDeposits,
            'company_deposits_total' => $companyDeposits,
            'bills_total' => $billsTotal,
            'voided_bills_count' => $voidedBills->count(),
            'voided_bills_total' => (float) $voidedBills->sum('total_amount'),
            'reversed_deposits_count' => $reversedDeposits->count() + $reversedCompanyDeposits->count(),
            'reversed_deposits_total' => (float) $reversedDeposits->sum('amount') + (float) $reversedCompanyDeposits->sum('amount'),
            'visit_summary' => $visitTypes,
            'active_members' => Patient::query()->where('type', PatientType::Member)->where('status', 'active')->count(),
            'active_company_patients' => Patient::query()->where('type', PatientType::Company)->where('status', 'active')->count(),
            'total_member_balance' => (float) Patient::query()->where('type', PatientType::Member)->sum('balance'),
            'total_company_balance' => (float) Company::query()->sum('balance'),
            'expiring_memberships' => $expiringMemberships,
            'expired_memberships' => $expiredMemberships,
            'active_casual_callers' => Patient::query()
                ->where('type', PatientType::CashPatient)
                ->where('status', 'active')
                ->count(),
            'casual_billed_total' => $this->casualBilledTotal($from, $to),
            'casual_collected_total' => $this->casualCollectedTotal($from, $to),
            'casual_outstanding_total' => (float) Bill::query()->outstandingCash()->sum('total_amount'),
        ];
    }

    /**
     * Casual caller collections — pay-as-you-go bills and payments for the period.
     *
     * @return array{
     *     summary: array<string, mixed>,
     *     bills: Collection<int, array<string, mixed>>,
     *     payment_methods: Collection<int, array{method: string, count: int, total: float}>
     * }
     */
    public function casualCallers(Carbon $from, Carbon $to): array
    {
        $bills = Bill::query()
            ->with(['patient', 'paidBy'])
            ->whereNull('account_patient_id')
            ->whereNull('company_id')
            ->whereHas('patient', fn ($query) => $query->where('type', PatientType::CashPatient))
            ->whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to)
            ->where('status', BillStatus::Posted)
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->get();

        $collectedInPeriod = Bill::query()
            ->whereNull('account_patient_id')
            ->whereNull('company_id')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->where('status', BillStatus::Posted)
            ->whereHas('patient', fn ($query) => $query->where('type', PatientType::CashPatient))
            ->get();

        $paymentMethods = $collectedInPeriod
            ->groupBy(fn (Bill $bill) => $bill->payment_method?->value ?? 'unknown')
            ->map(function (Collection $group, string $method) {
                $enum = PaymentMethod::tryFrom($method);

                return [
                    'method' => $enum?->label() ?? 'Unknown',
                    'count' => $group->count(),
                    'total' => (float) $group->sum('total_amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $paidFromPeriodBills = $bills->filter(fn (Bill $bill) => $bill->isPaid());
        $outstandingFromPeriodBills = $bills->filter(fn (Bill $bill) => $bill->isOutstanding());

        return [
            'summary' => [
                'active_patients' => Patient::query()
                    ->where('type', PatientType::CashPatient)
                    ->where('status', 'active')
                    ->count(),
                'bills_count' => $bills->count(),
                'billed_total' => (float) $bills->sum('total_amount'),
                'paid_count' => $paidFromPeriodBills->count(),
                'paid_total' => (float) $paidFromPeriodBills->sum('total_amount'),
                'outstanding_count' => $outstandingFromPeriodBills->count(),
                'outstanding_total' => (float) $outstandingFromPeriodBills->sum('total_amount'),
                'collected_in_period' => (float) $collectedInPeriod->sum('total_amount'),
                'collected_count' => $collectedInPeriod->count(),
                'current_outstanding' => (float) Bill::query()->outstandingCash()->sum('total_amount'),
            ],
            'bills' => $bills->map(fn (Bill $bill) => [
                'bill' => $bill,
                'patient' => $bill->patient,
                'visit_label' => $bill->visit_type->label(),
                'amount' => (float) $bill->total_amount,
                'status' => $bill->isPaid() ? 'Paid' : 'Awaiting Payment',
                'payment_method' => $bill->payment_method?->label(),
                'paid_at' => $bill->paid_at,
            ]),
            'payment_methods' => $paymentMethods,
        ];
    }

    private function casualBilledTotal(Carbon $from, Carbon $to): float
    {
        return (float) Bill::query()
            ->posted()
            ->whereNull('account_patient_id')
            ->whereNull('company_id')
            ->whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to)
            ->whereHas('patient', fn ($query) => $query->where('type', PatientType::CashPatient))
            ->sum('total_amount');
    }

    private function casualCollectedTotal(Carbon $from, Carbon $to): float
    {
        return (float) Bill::query()
            ->posted()
            ->whereNull('account_patient_id')
            ->whereNull('company_id')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->whereHas('patient', fn ($query) => $query->where('type', PatientType::CashPatient))
            ->sum('total_amount');
    }

    /**
     * Chronological list of deposits, bills, voids, and reversals in the period.
     */
    public function transactions(Carbon $from, Carbon $to, ?string $visitType = null): Collection
    {
        $rows = collect();

        Deposit::query()
            ->with(['patient', 'createdBy'])
            ->whereDate('deposit_date', '>=', $from)
            ->whereDate('deposit_date', '<=', $to)
            ->orderBy('deposit_date')
            ->each(function (Deposit $deposit) use ($rows): void {
                $rows->push([
                    'date' => $deposit->deposit_date,
                    'sort_at' => $deposit->created_at,
                    'type' => 'Member Deposit',
                    'party' => $deposit->patient->name,
                    'reference' => $deposit->reference ?? 'DEP-'.$deposit->id,
                    'amount' => (float) $deposit->amount,
                    'direction' => 'in',
                    'status' => $deposit->isReversed() ? 'Reversed' : 'Active',
                    'notes' => $deposit->notes,
                ]);
            });

        Deposit::query()
            ->with(['patient'])
            ->whereNotNull('reversed_at')
            ->whereBetween('reversed_at', [$from, $to])
            ->orderBy('reversed_at')
            ->each(function (Deposit $deposit) use ($rows): void {
                $rows->push([
                    'date' => $deposit->reversed_at,
                    'sort_at' => $deposit->reversed_at,
                    'type' => 'Deposit Reversal',
                    'party' => $deposit->patient->name,
                    'reference' => $deposit->reference ?? 'DEP-'.$deposit->id,
                    'amount' => (float) $deposit->amount,
                    'direction' => 'out',
                    'status' => 'Reversed',
                    'notes' => $deposit->reversal_reason,
                ]);
            });

        CompanyDeposit::query()
            ->with(['company'])
            ->whereNotNull('reversed_at')
            ->whereBetween('reversed_at', [$from, $to])
            ->orderBy('reversed_at')
            ->each(function (CompanyDeposit $deposit) use ($rows): void {
                $rows->push([
                    'date' => $deposit->reversed_at,
                    'sort_at' => $deposit->reversed_at,
                    'type' => 'Company Deposit Reversal',
                    'party' => $deposit->company->name,
                    'reference' => $deposit->reference ?? 'CDEP-'.$deposit->id,
                    'amount' => (float) $deposit->amount,
                    'direction' => 'out',
                    'status' => 'Reversed',
                    'notes' => $deposit->reversal_reason,
                ]);
            });

        CompanyDeposit::query()
            ->with(['company', 'createdBy'])
            ->whereDate('deposit_date', '>=', $from)
            ->whereDate('deposit_date', '<=', $to)
            ->orderBy('deposit_date')
            ->each(function (CompanyDeposit $deposit) use ($rows): void {
                $rows->push([
                    'date' => $deposit->deposit_date,
                    'sort_at' => $deposit->created_at,
                    'type' => 'Company Deposit',
                    'party' => $deposit->company->name,
                    'reference' => $deposit->reference ?? 'CDEP-'.$deposit->id,
                    'amount' => (float) $deposit->amount,
                    'direction' => 'in',
                    'status' => $deposit->isReversed() ? 'Reversed' : 'Active',
                    'notes' => $deposit->notes,
                ]);
            });

        $billsQuery = Bill::query()
            ->with(['patient', 'accountPatient', 'company', 'createdBy'])
            ->whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to);

        if ($visitType) {
            $billsQuery->where('visit_type', $visitType);
        }

        $billsQuery->orderBy('visit_date')->each(function (Bill $bill) use ($rows): void {
            $rows->push([
                'date' => $bill->visit_date,
                'sort_at' => $bill->created_at,
                'type' => 'Bill ('.$bill->visit_type->label().')',
                'party' => $bill->patient->name,
                'reference' => 'BILL-'.$bill->id,
                'amount' => (float) $bill->total_amount,
                'direction' => 'out',
                'status' => $bill->status->label(),
                'notes' => $bill->payerName(),
            ]);
        });

        Bill::query()
            ->with(['patient'])
            ->where('status', BillStatus::Voided)
            ->whereBetween('voided_at', [$from, $to])
            ->orderBy('voided_at')
            ->each(function (Bill $bill) use ($rows): void {
                $rows->push([
                    'date' => $bill->voided_at,
                    'sort_at' => $bill->voided_at,
                    'type' => 'Bill Void',
                    'party' => $bill->patient->name,
                    'reference' => 'BILL-'.$bill->id,
                    'amount' => (float) $bill->total_amount,
                    'direction' => 'in',
                    'status' => 'Voided',
                    'notes' => $bill->void_reason,
                ]);
            });

        return $rows->sortBy([['date', 'asc'], ['sort_at', 'asc']])->values();
    }

    /** Member accounts with activity in the selected period. */
    public function memberAccounts(Carbon $from, Carbon $to): Collection
    {
        return Patient::query()
            ->where('type', PatientType::Member)
            ->orderBy('name')
            ->get()
            ->map(function (Patient $member) use ($from, $to) {
                $depositsInPeriod = (float) Deposit::query()
                    ->active()
                    ->where('patient_id', $member->id)
                    ->whereDate('deposit_date', '>=', $from)
                    ->whereDate('deposit_date', '<=', $to)
                    ->sum('amount');

                $billsInPeriod = (float) Bill::query()
                    ->posted()
                    ->where('account_patient_id', $member->id)
                    ->whereDate('visit_date', '>=', $from)
                    ->whereDate('visit_date', '<=', $to)
                    ->sum('total_amount');

                return [
                    'member' => $member,
                    'current_balance' => (float) $member->balance,
                    'deposits_in_period' => $depositsInPeriod,
                    'bills_in_period' => $billsInPeriod,
                    'dependants_count' => $member->dependants()->count(),
                ];
            });
    }

    /** All companies with pool usage in the selected period. */
    public function companies(Carbon $from, Carbon $to): Collection
    {
        return Company::query()
            ->withCount('patients')
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company) => $this->companySummary($company, $from, $to));
    }

    /** Single company report — bank-style ledger statement. */
    public function companySummary(Company $company, Carbon $from, Carbon $to): array
    {
        $statement = $this->ledgerService->companyStatement($company, $from, $to);

        return [
            'company' => $company,
            'current_balance' => (float) $company->balance,
            'opening_balance' => $statement['opening_balance'],
            'closing_balance' => $statement['closing_balance'],
            'deposits_in_period' => $statement['deposits_total'],
            'bills_in_period' => $statement['bills_total'],
            'lines' => $statement['lines'],
            'membership_number' => null,
        ];
    }

    /**
     * Patient / member statement — bank-style ledger for the billable account.
     * Dependants use the principal member ledger.
     */
    public function patientStatement(Patient $patient, Carbon $from, Carbon $to): array
    {
        $patient->load(['company', 'principalMember.membership', 'membership']);

        if ($patient->isCompanyPatient()) {
            $company = $patient->company;
            $statement = $company
                ? $this->ledgerService->companyStatement($company, $from, $to)
                : [
                    'opening_balance' => 0.0,
                    'closing_balance' => 0.0,
                    'deposits_total' => 0.0,
                    'bills_total' => 0.0,
                    'lines' => collect(),
                    'membership_number' => null,
                    'account_name' => $patient->name,
                ];

            // Company patient statements show only this patient's bill lines on the pool.
            $lines = $statement['lines']->filter(function (array $line) use ($patient): bool {
                if ($line['is_opening'] ?? false) {
                    return true;
                }

                return str_contains((string) $line['description'], $patient->name);
            })->values();

            return [
                'patient' => $patient,
                'payer_label' => $patient->effectiveBalanceOwnerLabel(),
                'membership_number' => null,
                'opening_balance' => $statement['opening_balance'],
                'closing_balance' => $statement['closing_balance'],
                'deposits_total' => $statement['deposits_total'],
                'bills_total' => $statement['bills_total'],
                'lines' => $lines,
            ];
        }

        $statement = $this->ledgerService->memberStatement($patient, $from, $to);

        return [
            'patient' => $patient,
            'payer_label' => $statement['payer_label'] ?? $patient->effectiveBalanceOwnerLabel(),
            'membership_number' => $patient->effectiveMembershipNumber(),
            'opening_balance' => $statement['opening_balance'],
            'closing_balance' => $statement['closing_balance'],
            'deposits_total' => $statement['deposits_total'],
            'bills_total' => $statement['bills_total'],
            'lines' => $statement['lines'],
        ];
    }
}
