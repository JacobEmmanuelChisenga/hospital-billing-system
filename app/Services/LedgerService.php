<?php

namespace App\Services;

use App\Enums\LedgerAccountType;
use App\Enums\LedgerTransactionType;
use App\Models\AccountLedger;
use App\Models\Bill;
use App\Models\Company;
use App\Models\CompanyDeposit;
use App\Models\Deposit;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Bank-style account ledger for member and company prepaid balances.
 *
 * Every deposit, bill, refund, and reversal writes a chronological entry with
 * a stored running balance so statements read from one source.
 */
class LedgerService
{
    public function recordMemberDeposit(Deposit $deposit, User $user): AccountLedger
    {
        $method = $deposit->payment_method?->label() ?? 'Payment';

        return $this->post(
            LedgerAccountType::Member,
            $deposit->patient_id,
            LedgerTransactionType::Deposit,
            $this->reference('DEP', $deposit->id),
            $deposit,
            'Deposit - '.$method,
            debit: 0,
            credit: (float) $deposit->amount,
            transactionDate: Carbon::parse($deposit->deposit_date),
            user: $user,
        );
    }

    public function recordMemberDepositReversal(Deposit $deposit, User $user, string $reason): AccountLedger
    {
        return $this->post(
            LedgerAccountType::Member,
            $deposit->patient_id,
            LedgerTransactionType::Reversal,
            $this->reference('DREV', $deposit->id),
            $deposit,
            'Deposit reversal — '.$reason,
            debit: (float) $deposit->amount,
            credit: 0,
            transactionDate: now(),
            user: $user,
        );
    }

    public function recordCompanyDeposit(CompanyDeposit $deposit, User $user): AccountLedger
    {
        return $this->post(
            LedgerAccountType::Company,
            $deposit->company_id,
            LedgerTransactionType::Deposit,
            $this->reference('CDEP', $deposit->id),
            $deposit,
            'Company deposit'.($deposit->reference ? ' ('.$deposit->reference.')' : ''),
            debit: 0,
            credit: (float) $deposit->amount,
            transactionDate: Carbon::parse($deposit->deposit_date),
            user: $user,
        );
    }

    public function recordCompanyDepositReversal(CompanyDeposit $deposit, User $user, string $reason): AccountLedger
    {
        return $this->post(
            LedgerAccountType::Company,
            $deposit->company_id,
            LedgerTransactionType::Reversal,
            $this->reference('CREV', $deposit->id),
            $deposit,
            'Company deposit reversal — '.$reason,
            debit: (float) $deposit->amount,
            credit: 0,
            transactionDate: now(),
            user: $user,
        );
    }

    /**
     * Post bill charge lines as individual debit entries (bank-statement style).
     *
     * @return Collection<int, AccountLedger>
     */
    public function recordBill(Bill $bill, User $user): Collection
    {
        $bill->loadMissing(['patient', 'accountPatient', 'company']);

        [$accountType, $accountId] = $this->accountForBill($bill);
        $reference = $this->reference('BILL', $bill->id);
        $patientLabel = $bill->patient?->name;

        $entries = collect();

        foreach ($this->billLineItems($bill) as $description => $amount) {
            $lineDescription = $description;
            if ($patientLabel && ($bill->company_id || $bill->account_patient_id !== $bill->patient_id)) {
                $lineDescription .= ' — '.$patientLabel;
            }

            $entries->push($this->post(
                $accountType,
                $accountId,
                LedgerTransactionType::Bill,
                $reference,
                $bill,
                $lineDescription,
                debit: $amount,
                credit: 0,
                transactionDate: Carbon::parse($bill->visit_date),
                user: $user,
            ));
        }

        return $entries;
    }

    /**
     * Reverse a voided bill as credit (refund) lines.
     *
     * @return Collection<int, AccountLedger>
     */
    public function recordBillVoid(Bill $bill, User $user, string $reason): Collection
    {
        $bill->loadMissing(['patient', 'accountPatient', 'company']);

        [$accountType, $accountId] = $this->accountForBill($bill);
        $reference = $this->reference('VOID', $bill->id);
        $patientLabel = $bill->patient?->name;

        $entries = collect();

        foreach ($this->billLineItems($bill) as $description => $amount) {
            $lineDescription = 'Refund: '.$description;
            if ($patientLabel && ($bill->company_id || $bill->account_patient_id !== $bill->patient_id)) {
                $lineDescription .= ' — '.$patientLabel;
            }
            $lineDescription .= ' ('.$reason.')';

            $entries->push($this->post(
                $accountType,
                $accountId,
                LedgerTransactionType::Refund,
                $reference,
                $bill,
                $lineDescription,
                debit: 0,
                credit: $amount,
                transactionDate: now(),
                user: $user,
            ));
        }

        return $entries;
    }

    /**
     * Bank-style statement for a member or company account.
     *
     * @return array{
     *     account_type: LedgerAccountType,
     *     account_id: int,
     *     account_name: string,
     *     membership_number: ?string,
     *     opening_balance: float,
     *     deposits_total: float,
     *     bills_total: float,
     *     closing_balance: float,
     *     lines: Collection<int, array<string, mixed>>
     * }
     */
    public function statement(LedgerAccountType $accountType, int $accountId, Carbon $from, Carbon $to): array
    {
        $openingBalance = $this->balanceBefore($accountType, $accountId, $from);

        $entries = AccountLedger::query()
            ->where('account_type', $accountType)
            ->where('account_id', $accountId)
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $lines = collect([
            [
                'date' => $from->copy(),
                'reference' => 'OPENING',
                'description' => 'Opening Balance',
                'debit' => null,
                'credit' => null,
                'balance' => $openingBalance,
                'is_opening' => true,
            ],
        ]);

        foreach ($entries as $entry) {
            $lines->push([
                'date' => $entry->transaction_date,
                'reference' => $entry->reference,
                'description' => $entry->description,
                'debit' => (float) $entry->debit > 0 ? (float) $entry->debit : null,
                'credit' => (float) $entry->credit > 0 ? (float) $entry->credit : null,
                'balance' => (float) $entry->running_balance,
                'is_opening' => false,
            ]);
        }

        $depositsTotal = (float) $entries
            ->where('transaction_type', LedgerTransactionType::Deposit)
            ->sum('credit');

        $billsTotal = (float) $entries
            ->where('transaction_type', LedgerTransactionType::Bill)
            ->sum('debit');

        $closingBalance = $entries->isNotEmpty()
            ? (float) $entries->last()->running_balance
            : $openingBalance;

        return [
            'account_type' => $accountType,
            'account_id' => $accountId,
            'account_name' => $this->accountName($accountType, $accountId),
            'membership_number' => $accountType === LedgerAccountType::Member
                ? $this->memberMembershipNumber($accountId)
                : null,
            'opening_balance' => $openingBalance,
            'deposits_total' => $depositsTotal,
            'bills_total' => $billsTotal,
            'closing_balance' => $closingBalance,
            'lines' => $lines,
        ];
    }

    public function memberStatement(Patient $member, Carbon $from, Carbon $to): array
    {
        $account = $member->isDependant() ? $member->billableAccountPatient() : $member;

        if (! $account || ! $account->isMember()) {
            return $this->emptyStatement(LedgerAccountType::Member, $member->id, $member->name, $from);
        }

        $statement = $this->statement(LedgerAccountType::Member, $account->id, $from, $to);
        $statement['patient'] = $member;
        $statement['payer_label'] = $member->isDependant()
            ? 'Principal: '.$account->name
            : 'Own account';

        return $statement;
    }

    public function companyStatement(Company $company, Carbon $from, Carbon $to): array
    {
        $statement = $this->statement(LedgerAccountType::Company, $company->id, $from, $to);
        $statement['company'] = $company;

        return $statement;
    }

    public function balanceBefore(LedgerAccountType $accountType, int $accountId, Carbon $before): float
    {
        $entry = AccountLedger::query()
            ->where('account_type', $accountType)
            ->where('account_id', $accountId)
            ->whereDate('transaction_date', '<', $before)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->first();

        return $entry ? (float) $entry->running_balance : 0.0;
    }

    public function currentLedgerBalance(LedgerAccountType $accountType, int $accountId): float
    {
        $entry = AccountLedger::query()
            ->where('account_type', $accountType)
            ->where('account_id', $accountId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->first();

        return $entry ? (float) $entry->running_balance : 0.0;
    }

    public function totalCreditsToday(LedgerAccountType $accountType): float
    {
        return (float) AccountLedger::query()
            ->where('account_type', $accountType)
            ->whereDate('transaction_date', today())
            ->where('transaction_type', LedgerTransactionType::Deposit)
            ->sum('credit');
    }

    public function totalDebitsToday(LedgerAccountType $accountType): float
    {
        return (float) AccountLedger::query()
            ->where('account_type', $accountType)
            ->whereDate('transaction_date', today())
            ->where('transaction_type', LedgerTransactionType::Bill)
            ->sum('debit');
    }

    public function totalCurrentBalances(LedgerAccountType $accountType): float
    {
        if ($accountType === LedgerAccountType::Member) {
            return (float) Patient::query()->where('type', 'member')->sum('balance');
        }

        return (float) Company::query()->sum('balance');
    }

    /**
     * Rebuild the entire ledger from historical deposits and bills.
     * Safe to re-run — clears ledger rows first.
     */
    public function rebuild(): int
    {
        return DB::transaction(function (): int {
            AccountLedger::query()->delete();

            $events = collect();

            Deposit::query()->orderBy('deposit_date')->orderBy('id')->each(function (Deposit $deposit) use ($events): void {
                $events->push([
                    'sort' => $deposit->deposit_date->format('Y-m-d').'-'.$deposit->created_at?->format('His').'-D'.$deposit->id,
                    'type' => 'member_deposit',
                    'model' => $deposit,
                ]);

                if ($deposit->isReversed()) {
                    $events->push([
                        'sort' => ($deposit->reversed_at ?? $deposit->updated_at)->format('Y-m-d-His').'-DR'.$deposit->id,
                        'type' => 'member_deposit_reversal',
                        'model' => $deposit,
                    ]);
                }
            });

            CompanyDeposit::query()->orderBy('deposit_date')->orderBy('id')->each(function (CompanyDeposit $deposit) use ($events): void {
                $events->push([
                    'sort' => $deposit->deposit_date->format('Y-m-d').'-'.$deposit->created_at?->format('His').'-C'.$deposit->id,
                    'type' => 'company_deposit',
                    'model' => $deposit,
                ]);

                if ($deposit->isReversed()) {
                    $events->push([
                        'sort' => ($deposit->reversed_at ?? $deposit->updated_at)->format('Y-m-d-His').'-CR'.$deposit->id,
                        'type' => 'company_deposit_reversal',
                        'model' => $deposit,
                    ]);
                }
            });

            Bill::query()->orderBy('visit_date')->orderBy('id')->each(function (Bill $bill) use ($events): void {
                $events->push([
                    'sort' => $bill->visit_date->format('Y-m-d').'-'.$bill->created_at?->format('His').'-B'.$bill->id,
                    'type' => 'bill',
                    'model' => $bill,
                ]);

                if ($bill->isVoided()) {
                    $events->push([
                        'sort' => ($bill->voided_at ?? $bill->updated_at)->format('Y-m-d-His').'-V'.$bill->id,
                        'type' => 'bill_void',
                        'model' => $bill,
                    ]);
                }
            });

            $systemUser = User::query()->orderBy('id')->first();
            $count = 0;

            foreach ($events->sortBy('sort') as $event) {
                $user = $this->actorFor($event['model'], $systemUser);

                match ($event['type']) {
                    'member_deposit' => $this->recordMemberDeposit($event['model'], $user),
                    'member_deposit_reversal' => $this->recordMemberDepositReversal($event['model'], $user, $event['model']->reversal_reason ?? 'Reversed'),
                    'company_deposit' => $this->recordCompanyDeposit($event['model'], $user),
                    'company_deposit_reversal' => $this->recordCompanyDepositReversal($event['model'], $user, $event['model']->reversal_reason ?? 'Reversed'),
                    'bill' => $this->recordBill($event['model'], $user),
                    'bill_void' => $this->recordBillVoid($event['model'], $user, $event['model']->void_reason ?? 'Voided'),
                };

                $count++;
            }

            return $count;
        });
    }

    private function post(
        LedgerAccountType $accountType,
        int $accountId,
        LedgerTransactionType $transactionType,
        string $reference,
        Model $related,
        string $description,
        float $debit,
        float $credit,
        Carbon $transactionDate,
        User $user,
    ): AccountLedger {
        $previous = $this->currentLedgerBalance($accountType, $accountId);
        $running = round($previous + $credit - $debit, 2);

        return AccountLedger::query()->create([
            'account_type' => $accountType,
            'account_id' => $accountId,
            'transaction_type' => $transactionType,
            'reference' => $reference,
            'related_type' => $related::class,
            'related_id' => $related->getKey(),
            'description' => $description,
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'running_balance' => $running,
            'transaction_date' => $transactionDate->toDateString(),
            'created_by' => $user->id,
        ]);
    }

    /**
     * @return array{0: LedgerAccountType, 1: int}
     */
    private function accountForBill(Bill $bill): array
    {
        if ($bill->company_id) {
            return [LedgerAccountType::Company, $bill->company_id];
        }

        return [LedgerAccountType::Member, (int) $bill->account_patient_id];
    }

    /**
     * @return array<string, float>
     */
    private function billLineItems(Bill $bill): array
    {
        $items = [
            'Consultation' => (float) $bill->consultation_amount,
            'Pharmacy / Medicine' => (float) $bill->pharmacy_amount,
            'Laboratory' => (float) $bill->lab_amount,
            'Ward / Bed' => (float) $bill->ward_amount,
            'Other' => (float) $bill->other_amount,
        ];

        $items = array_filter($items, fn (float $amount) => $amount > 0);

        if ($items === []) {
            $items = ['Visit charges' => (float) $bill->total_amount];
        }

        return $items;
    }

    private function reference(string $prefix, int $id): string
    {
        return $prefix.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    private function accountName(LedgerAccountType $accountType, int $accountId): string
    {
        if ($accountType === LedgerAccountType::Member) {
            return Patient::query()->find($accountId)?->name ?? 'Member #'.$accountId;
        }

        return Company::query()->find($accountId)?->name ?? 'Company #'.$accountId;
    }

    private function memberMembershipNumber(int $patientId): ?string
    {
        $patient = Patient::query()->with('membership')->find($patientId);

        return $patient?->membership?->membership_number
            ?? $patient?->hc_number;
    }

    private function actorFor(Model $model, ?User $fallback): User
    {
        $userId = $model->created_by
            ?? $model->voided_by
            ?? $model->reversed_by
            ?? null;

        if ($userId) {
            $user = User::query()->find($userId);
            if ($user) {
                return $user;
            }
        }

        return $fallback ?? User::query()->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyStatement(LedgerAccountType $accountType, int $accountId, string $name, Carbon $from): array
    {
        return [
            'account_type' => $accountType,
            'account_id' => $accountId,
            'account_name' => $name,
            'membership_number' => null,
            'opening_balance' => 0.0,
            'deposits_total' => 0.0,
            'bills_total' => 0.0,
            'closing_balance' => 0.0,
            'lines' => collect([
                [
                    'date' => $from->copy(),
                    'reference' => 'OPENING',
                    'description' => 'Opening Balance',
                    'debit' => null,
                    'credit' => null,
                    'balance' => 0.0,
                    'is_opening' => true,
                ],
            ]),
        ];
    }
}
