<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Enums\VisitStatus;
use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\ClinicalNote;
use App\Models\CompanyDeposit;
use App\Models\Deposit;
use App\Models\MembershipFee;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Role-specific dashboard metrics and chart datasets.
 * Each role only receives data they can act on.
 */
class DashboardService
{
    public function registry(): array
    {
        $today = today();
        $yesterday = today()->subDay();

        $registeredToday = Visit::query()->whereDate('created_at', $today)->count();
        $registeredYesterday = Visit::query()->whereDate('created_at', $yesterday)->count();

        $waitingForNurse = Visit::query()
            ->whereDate('visit_date', $today)
            ->where('status', VisitStatus::ReadyForConsultation)
            ->count();
        $waitingYesterday = Visit::query()
            ->whereDate('visit_date', $yesterday)
            ->where('status', VisitStatus::ReadyForConsultation)
            ->count();

        $awaitingBilling = Visit::query()
            ->whereDate('visit_date', $today)
            ->where('status', VisitStatus::AwaitingBilling)
            ->count();
        $awaitingBillingYesterday = Visit::query()
            ->whereDate('visit_date', $yesterday)
            ->where('status', VisitStatus::AwaitingBilling)
            ->count();

        $completedToday = Visit::query()
            ->where('status', VisitStatus::Completed)
            ->whereDate('visit_date', $today)
            ->count();
        $completedYesterday = Visit::query()
            ->where('status', VisitStatus::Completed)
            ->whereDate('visit_date', $yesterday)
            ->count();

        return [
            'theme' => 'registry',
            'kpis' => [
                [
                    'label' => 'Registered Today',
                    'value' => number_format($registeredToday),
                    'tone' => 'blue',
                    'trend' => $this->trendDirection($registeredToday, $registeredYesterday),
                    'trendLabel' => $this->trendLabel($registeredToday, $registeredYesterday),
                    'href' => route('visits.index'),
                ],
                [
                    'label' => 'Waiting for Nurse',
                    'value' => number_format($waitingForNurse),
                    'tone' => 'amber',
                    'trend' => $this->trendDirection($waitingForNurse, $waitingYesterday),
                    'trendLabel' => $this->trendLabel($waitingForNurse, $waitingYesterday),
                    'href' => route('visits.index'),
                ],
                [
                    'label' => 'Awaiting Billing',
                    'value' => number_format($awaitingBilling),
                    'tone' => 'orange',
                    'trend' => $this->trendDirection($awaitingBilling, $awaitingBillingYesterday),
                    'trendLabel' => $this->trendLabel($awaitingBilling, $awaitingBillingYesterday),
                    'href' => route('charges.pending'),
                ],
                [
                    'label' => 'Completed Today',
                    'value' => number_format($completedToday),
                    'tone' => 'green',
                    'trend' => $this->trendDirection($completedToday, $completedYesterday),
                    'trendLabel' => $this->trendLabel($completedToday, $completedYesterday),
                    'href' => route('charges.history'),
                ],
            ],
            'charts' => [
                'patientFlow' => $this->registryPatientFlowChart($today),
                'patientTypes' => $this->pieChart(
                    'Patient Types Today',
                    null,
                    $this->countByLabel(
                        Visit::query()
                            ->with('patient')
                            ->whereDate('visit_date', $today)
                            ->get()
                            ->groupBy(fn (Visit $visit) => $visit->patient->type->label()),
                    ),
                    ['#2563eb', '#14b8a6', '#8b5cf6'],
                ),
                'pendingWorkload' => $this->barChart(
                    'Pending Workload',
                    null,
                    $this->registryPendingWorkload(),
                    '#2563eb',
                ),
            ],
            'recent' => $this->recentRegistrations(),
        ];
    }

    public function nurse(): array
    {
        $today = today();

        $notesToday = ClinicalNote::query()
            ->whereDate('created_at', $today)
            ->get();

        $todayVisits = Visit::query()
            ->with('patient')
            ->whereDate('visit_date', $today)
            ->get();

        $waiting = $todayVisits->where('status', VisitStatus::ReadyForConsultation)->count();
        $inConsultation = $todayVisits->where('status', VisitStatus::SeenByNurse)->count();

        return [
            'theme' => 'nurse',
            'kpis' => [
                [
                    'label' => 'Patients Waiting',
                    'value' => number_format($waiting),
                    'tone' => 'amber',
                    'href' => route('nurse.queue'),
                ],
                [
                    'label' => 'Patients Seen',
                    'value' => number_format($notesToday->count()),
                    'tone' => 'green',
                    'href' => route('nurse.consultations', ['period' => 'today']),
                ],
                [
                    'label' => 'Pending Consultations',
                    'value' => number_format($waiting + $inConsultation),
                    'tone' => 'orange',
                    'href' => route('nurse.active'),
                ],
            ],
            'charts' => [
                'patientsSeen' => $this->lineChart(
                    'Patients Seen Today',
                    null,
                    $this->hourlyCounts($notesToday, fn (ClinicalNote $note) => $note->created_at, 8, 17),
                    'Patients seen',
                    '#059669',
                ),
                'diagnoses' => $this->barChart(
                    'Top Diagnoses Today',
                    null,
                    $this->topDiagnosesToday(),
                    '#059669',
                    horizontal: true,
                ),
                'caseStatus' => $this->pieChart(
                    'Patient Case Status',
                    null,
                    $this->nurseCaseStatus($todayVisits),
                    ['#f59e0b', '#059669', '#6366f1', '#94a3b8'],
                ),
            ],
            'recent' => $this->nurseQueue($todayVisits),
        ];
    }

    public function accounts(): array
    {
        $today = today();
        $yesterday = today()->subDay();

        $depositsToday = (float) Deposit::query()->active()->whereDate('deposit_date', $today)->sum('amount');
        $companyDepositsToday = (float) CompanyDeposit::query()->active()->whereDate('deposit_date', $today)->sum('amount');
        $membershipToday = (float) MembershipFee::query()->whereDate('payment_date', $today)->sum('amount');
        $billsToday = (float) Bill::query()->posted()->whereDate('visit_date', $today)->sum('total_amount');
        $totalDepositsToday = $depositsToday + $companyDepositsToday;
        $totalRevenueToday = $totalDepositsToday + $membershipToday + $billsToday;

        $depositsYesterday = (float) Deposit::query()->active()->whereDate('deposit_date', $yesterday)->sum('amount')
            + (float) CompanyDeposit::query()->active()->whereDate('deposit_date', $yesterday)->sum('amount');
        $membershipYesterday = (float) MembershipFee::query()->whereDate('payment_date', $yesterday)->sum('amount');
        $billsYesterday = (float) Bill::query()->posted()->whereDate('visit_date', $yesterday)->sum('total_amount');
        $totalRevenueYesterday = $depositsYesterday + $membershipYesterday + $billsYesterday;

        $receiptsToday = Bill::query()->posted()->whereDate('visit_date', $today)->count()
            + Deposit::query()->active()->whereDate('deposit_date', $today)->count()
            + CompanyDeposit::query()->active()->whereDate('deposit_date', $today)->count()
            + MembershipFee::query()->whereDate('payment_date', $today)->count();
        $receiptsYesterday = Bill::query()->posted()->whereDate('visit_date', $yesterday)->count()
            + Deposit::query()->active()->whereDate('deposit_date', $yesterday)->count()
            + CompanyDeposit::query()->active()->whereDate('deposit_date', $yesterday)->count()
            + MembershipFee::query()->whereDate('payment_date', $yesterday)->count();

        return [
            'theme' => 'accounts',
            'kpis' => [
                [
                    'label' => 'Total Revenue Today',
                    'value' => 'K '.number_format($totalRevenueToday, 0),
                    'tone' => 'purple',
                    'trend' => $this->trendDirection($totalRevenueToday, $totalRevenueYesterday),
                    'trendLabel' => $this->trendLabel($totalRevenueToday, $totalRevenueYesterday),
                    'href' => route('reports.index'),
                ],
                [
                    'label' => 'Total Deposits Today',
                    'value' => 'K '.number_format($totalDepositsToday, 0),
                    'tone' => 'green',
                    'trend' => $this->trendDirection($totalDepositsToday, $depositsYesterday),
                    'trendLabel' => $this->trendLabel($totalDepositsToday, $depositsYesterday),
                    'href' => route('deposits.index'),
                ],
                [
                    'label' => 'Total Billing Today',
                    'value' => 'K '.number_format($billsToday, 0),
                    'tone' => 'blue',
                    'trend' => $this->trendDirection($billsToday, $billsYesterday),
                    'trendLabel' => $this->trendLabel($billsToday, $billsYesterday),
                    'href' => route('billing.index'),
                ],
                [
                    'label' => 'Receipts Issued Today',
                    'value' => number_format($receiptsToday),
                    'tone' => 'orange',
                    'trend' => $this->trendDirection($receiptsToday, $receiptsYesterday),
                    'trendLabel' => $this->trendLabel($receiptsToday, $receiptsYesterday),
                    'href' => route('billing.index'),
                ],
            ],
            'charts' => [
                'revenueBreakdown' => $this->barChart(
                    'Daily Revenue Breakdown',
                    null,
                    [
                        'labels' => ['Membership Fees', 'Deposits', 'Billing'],
                        'values' => [
                            round($membershipToday, 2),
                            round($totalDepositsToday, 2),
                            round($billsToday, 2),
                        ],
                    ],
                    '#7c3aed',
                ),
                'depositVsBilling' => $this->multiLineChart(
                    'Deposits vs Billing Trend',
                    'Last 7 days',
                    $this->depositsVsBillingTrend(),
                ),
                'paymentMethods' => $this->pieChart(
                    'Payment Methods',
                    'This week by amount received',
                    $this->paymentMethodAmounts(),
                    ['#7c3aed', '#a78bfa', '#c4b5fd', '#ddd6fe'],
                ),
            ],
            'recent' => $this->recentReceipts($today),
        ];
    }

    public function admin(): array
    {
        $today = today();
        $yesterday = today()->subDay();
        $monthStart = today()->startOfMonth();

        $totalUsers = User::query()->count();
        $newUsersThisMonth = User::query()->where('created_at', '>=', $monthStart)->count();

        $activeStaffToday = AuditLog::query()
            ->whereDate('created_at', $today)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
        $activeStaffYesterday = AuditLog::query()
            ->whereDate('created_at', $yesterday)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $auditToday = AuditLog::query()->whereDate('created_at', $today)->count();
        $auditYesterday = AuditLog::query()->whereDate('created_at', $yesterday)->count();

        $activeDays = collect(range(6, 0))
            ->filter(fn (int $offset) => AuditLog::query()->whereDate('created_at', today()->subDays($offset))->exists())
            ->count();
        $uptimePercent = $activeDays === 7 ? 99.9 : round(($activeDays / 7) * 100, 1);

        $auditBreakdown = $this->auditEventBreakdown();

        return [
            'theme' => 'admin',
            'kpis' => [
                [
                    'label' => 'Total Users',
                    'value' => number_format($totalUsers),
                    'tone' => 'slate',
                    'trend' => $newUsersThisMonth > 0 ? 'up' : 'neutral',
                    'trendLabel' => $newUsersThisMonth > 0
                        ? '+'.number_format($newUsersThisMonth).' this month'
                        : 'No new users this month',
                ],
                [
                    'label' => 'Active Users Today',
                    'value' => number_format($activeStaffToday),
                    'tone' => 'blue',
                    'trend' => $this->trendDirection($activeStaffToday, $activeStaffYesterday),
                    'trendLabel' => $this->signedTrendLabel($activeStaffToday, $activeStaffYesterday, 'vs yesterday'),
                ],
                [
                    'label' => 'Total Audit Events',
                    'value' => number_format($auditToday),
                    'tone' => 'green',
                    'trend' => $this->trendDirection($auditToday, $auditYesterday),
                    'trendLabel' => $this->trendLabel($auditToday, $auditYesterday),
                ],
                [
                    'label' => 'System Uptime',
                    'value' => $uptimePercent.'%',
                    'tone' => 'green',
                    'status' => $uptimePercent >= 85 ? 'Excellent' : 'Monitoring',
                ],
            ],
            'charts' => [
                'systemActivity' => $this->lineChart(
                    'System Activity (This Week)',
                    null,
                    $this->dailyAuditCounts(),
                    'Events',
                    '#3b82f6',
                ),
                'userActivity' => $this->barChart(
                    'User Activity by Role',
                    null,
                    $this->userActivityByRole(),
                    '#3b82f6',
                ),
                'auditEvents' => $this->pieChart(
                    'Audit Events Breakdown',
                    null,
                    $auditBreakdown['series'],
                    $auditBreakdown['colors'],
                    $auditBreakdown['footer'],
                ),
            ],
            'recent' => $this->recentAuditLogs(),
        ];
    }

    /**
     * @return list<array{patient: string, number: string, time: string, url: string}>
     */
    private function recentRegistrations(): array
    {
        return Visit::query()
            ->with('patient')
            ->whereDate('created_at', today())
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Visit $visit) => [
                'patient' => $visit->patient->name,
                'number' => $visit->patient->patient_number ?? $visit->patient->hc_number ?? '—',
                'time' => $visit->created_at?->format('H:i') ?? '—',
                'url' => route('visits.show', $visit),
            ])
            ->all();
    }

    /**
     * @param  Collection<int, Visit>  $todayVisits
     * @return list<array{patient: string, number: string, status: string, statusClass: string, url: string}>
     */
    private function nurseQueue(Collection $todayVisits): array
    {
        return $todayVisits
            ->whereIn('status', [VisitStatus::ReadyForConsultation, VisitStatus::SeenByNurse])
            ->sortBy('created_at')
            ->take(8)
            ->map(fn (Visit $visit) => [
                'patient' => $visit->patient->name,
                'number' => $visit->patient->patient_number ?? '—',
                'status' => $visit->status->label(),
                'statusClass' => $visit->status->badgeClass(),
                'url' => route('visits.show', $visit),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, payer: string, amount: string, time: string, url: ?string}>
     */
    private function recentReceipts(Carbon $today): array
    {
        $items = collect();

        Bill::query()
            ->with('patient')
            ->posted()
            ->whereDate('visit_date', $today)
            ->latest()
            ->limit(5)
            ->get()
            ->each(function (Bill $bill) use ($items): void {
                $items->push([
                    'sort' => $bill->created_at,
                    'id' => 'Bill #'.$bill->id,
                    'payer' => $bill->patient->name,
                    'amount' => 'K '.number_format((float) $bill->total_amount, 2),
                    'time' => $bill->created_at?->format('H:i') ?? '—',
                    'url' => route('billing.receipt', $bill),
                ]);
            });

        Deposit::query()
            ->with('patient')
            ->active()
            ->whereDate('deposit_date', $today)
            ->latest()
            ->limit(5)
            ->get()
            ->each(function (Deposit $deposit) use ($items): void {
                $items->push([
                    'sort' => $deposit->created_at,
                    'id' => 'Deposit #'.$deposit->id,
                    'payer' => $deposit->patient->name,
                    'amount' => 'K '.number_format((float) $deposit->amount, 2),
                    'time' => $deposit->created_at?->format('H:i') ?? '—',
                    'url' => route('deposits.show', $deposit),
                ]);
            });

        MembershipFee::query()
            ->with('patient')
            ->whereDate('payment_date', $today)
            ->latest()
            ->limit(5)
            ->get()
            ->each(function (MembershipFee $fee) use ($items): void {
                $items->push([
                    'sort' => $fee->created_at,
                    'id' => 'Membership #'.$fee->id,
                    'payer' => $fee->patient->name,
                    'amount' => 'K '.number_format((float) $fee->amount, 2),
                    'time' => $fee->created_at?->format('H:i') ?? '—',
                    'url' => route('membership-fees.show', $fee),
                ]);
            });

        return $items
            ->sortByDesc('sort')
            ->take(8)
            ->map(fn (array $item) => collect($item)->except('sort')->all())
            ->values()
            ->all();
    }

    /**
     * @return list<array{description: string, time: string, url: string}>
     */
    private function recentAuditLogs(): array
    {
        return AuditLog::query()
            ->with(['user', 'related'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (AuditLog $log) => [
                'description' => $this->formatAuditLogSummary($log),
                'time' => $log->created_at?->format('M j, Y g:i A') ?? '—',
                'url' => route('audit-logs.show', $log),
            ])
            ->all();
    }

    private function formatAuditLogSummary(AuditLog $log): string
    {
        $reference = $log->relatedSummary();

        if ($reference === '' && is_array($log->metadata)) {
            $reference = $log->metadata['patient_number']
                ?? $log->metadata['reference']
                ?? '';
        }

        if ($reference === '' && $log->user) {
            $reference = strtok($log->user->email, '@') ?: $log->user->name;
        }

        $action = match ($log->action_type) {
            AuditActionType::PatientCreated => 'Patient created',
            AuditActionType::PatientUpdated => 'Patient updated',
            AuditActionType::BillCreated => 'Bill posted',
            AuditActionType::BillVoided => 'Bill voided',
            AuditActionType::DepositCreated => 'Deposit recorded',
            AuditActionType::CompanyDepositCreated => 'Company deposit recorded',
            AuditActionType::MembershipFeeRecorded => 'Membership payment recorded',
            AuditActionType::VisitOpened => 'Visit opened',
            AuditActionType::VisitCompleted => 'Visit completed',
            AuditActionType::ClinicalNoteRecorded => 'Clinical note recorded',
            AuditActionType::UserCreated => 'Staff user created',
            AuditActionType::UserUpdated => 'Staff user updated',
            default => $log->action_type->label(),
        };

        return $reference !== ''
            ? "{$action} - {$reference}"
            : $action;
    }

    /**
     * @return array{series: array{labels: list<string>, values: list<int>}, colors: list<string>, footer: string}
     */
    private function auditEventBreakdown(): array
    {
        $logs = AuditLog::query()->whereDate('created_at', today())->get();

        $groups = [
            'Patient Created' => 0,
            'Bills Posted' => 0,
            'Deposits' => 0,
            'System Events' => 0,
        ];

        foreach ($logs as $log) {
            match ($log->action_type) {
                AuditActionType::PatientCreated => $groups['Patient Created']++,
                AuditActionType::BillCreated => $groups['Bills Posted']++,
                AuditActionType::DepositCreated,
                AuditActionType::CompanyDepositCreated,
                AuditActionType::MembershipFeeRecorded => $groups['Deposits']++,
                default => $groups['System Events']++,
            };
        }

        $groups = array_filter($groups, fn (int $count) => $count > 0);
        $total = array_sum($groups);

        if ($total === 0) {
            return [
                'series' => ['labels' => ['No events today'], 'values' => [0]],
                'colors' => ['#cbd5e1'],
                'footer' => 'Total: 0',
            ];
        }

        $labels = [];
        $values = [];

        foreach ($groups as $label => $count) {
            $percent = (int) round(($count / $total) * 100);
            $labels[] = "{$label} ({$percent}%)";
            $values[] = $count;
        }

        return [
            'series' => [
                'labels' => $labels,
                'values' => $values,
            ],
            'colors' => ['#3b82f6', '#22c55e', '#f97316', '#8b5cf6'],
            'footer' => 'Total: '.number_format($total),
        ];
    }

    private function registryPatientFlowChart(Carbon $today): array
    {
        $hours = collect(range(8, 17))->mapWithKeys(fn (int $hour) => [sprintf('%02d:00', $hour) => 0]);

        $visits = Visit::query()
            ->with('clinicalNote')
            ->whereDate('visit_date', $today)
            ->get();

        $opened = $this->hourlyCounts($visits, fn (Visit $visit) => $visit->created_at, 8, 17);
        $waiting = $this->hourlyCounts(
            $visits->where('status', VisitStatus::ReadyForConsultation),
            fn (Visit $visit) => $visit->updated_at,
            8,
            17,
        );
        $consulted = $this->hourlyCounts(
            $visits->filter(fn (Visit $visit) => $visit->clinicalNote !== null),
            fn (Visit $visit) => $visit->clinicalNote->created_at,
            8,
            17,
        );
        $awaitingBilling = $this->hourlyCounts(
            $visits->whereIn('status', [VisitStatus::AwaitingBilling, VisitStatus::Billed, VisitStatus::Completed]),
            fn (Visit $visit) => $visit->updated_at,
            8,
            17,
        );
        $completed = $this->hourlyCounts(
            $visits->where('status', VisitStatus::Completed),
            fn (Visit $visit) => $visit->completed_at ?? $visit->updated_at,
            8,
            17,
        );

        return $this->multiLineChart(
            'Daily Patient Flow',
            'Patient journey through the clinic today',
            [
                'labels' => $hours->keys()->values()->all(),
                'datasets' => [
                    ['label' => 'Registered', 'data' => $opened['values'], 'borderColor' => '#2563eb', 'backgroundColor' => 'rgba(37, 99, 235, 0.08)'],
                    ['label' => 'Waiting Nurse', 'data' => $waiting['values'], 'borderColor' => '#f59e0b', 'backgroundColor' => 'rgba(245, 158, 11, 0.08)'],
                    ['label' => 'Consulted', 'data' => $consulted['values'], 'borderColor' => '#7c3aed', 'backgroundColor' => 'rgba(124, 58, 237, 0.08)'],
                    ['label' => 'Awaiting Billing', 'data' => $awaitingBilling['values'], 'borderColor' => '#ea580c', 'backgroundColor' => 'rgba(234, 88, 12, 0.08)'],
                    ['label' => 'Completed', 'data' => $completed['values'], 'borderColor' => '#059669', 'backgroundColor' => 'rgba(5, 150, 105, 0.08)'],
                ],
            ],
        );
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function registryPendingWorkload(): array
    {
        return [
            'labels' => [
                'Awaiting Payment',
                'Awaiting Billing',
                'Missing Charges',
            ],
            'values' => [
                Visit::query()->where('status', VisitStatus::AwaitingPayment)->count(),
                Visit::query()->where('status', VisitStatus::AwaitingBilling)->count(),
                Visit::query()
                    ->where('status', VisitStatus::AwaitingBilling)
                    ->whereDoesntHave('chargeLines')
                    ->count(),
            ],
        ];
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @param  callable(mixed): ?Carbon  $timestamp
     * @return array{labels: list<string>, values: list<int>}
     */
    private function hourlyCounts(Collection $items, callable $timestamp, int $startHour = 6, int $endHour = 18): array
    {
        $hours = collect(range($startHour, $endHour))->mapWithKeys(fn (int $hour) => [sprintf('%02d:00', $hour) => 0]);

        foreach ($items as $item) {
            $time = $timestamp($item);

            if (! $time instanceof Carbon) {
                continue;
            }

            $hour = (int) $time->format('G');

            if ($hour < $startHour || $hour > $endHour) {
                continue;
            }

            $key = sprintf('%02d:00', $hour);
            $hours[$key] = ($hours[$key] ?? 0) + 1;
        }

        return [
            'labels' => $hours->keys()->values()->all(),
            'values' => $hours->values()->all(),
        ];
    }

    /**
     * @param  Collection<string, Collection<int, mixed>>  $groups
     * @return array{labels: list<string>, values: list<int>}
     */
    private function countByLabel(Collection $groups): array
    {
        if ($groups->isEmpty()) {
            return ['labels' => ['No visits today'], 'values' => [0]];
        }

        return [
            'labels' => $groups->keys()->values()->all(),
            'values' => $groups->map->count()->values()->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function topDiagnosesToday(): array
    {
        $diagnoses = ClinicalNote::query()
            ->whereDate('created_at', today())
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->get()
            ->groupBy(fn (ClinicalNote $note) => trim($note->diagnosis))
            ->map->count()
            ->sortDesc()
            ->take(5);

        if ($diagnoses->isEmpty()) {
            return ['labels' => ['No diagnoses yet'], 'values' => [0]];
        }

        return [
            'labels' => $diagnoses->keys()->values()->all(),
            'values' => $diagnoses->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, Visit>  $visits
     * @return array{labels: list<string>, values: list<int>}
     */
    private function nurseCaseStatus(Collection $visits): array
    {
        $groups = [
            'Waiting' => $visits->where('status', VisitStatus::ReadyForConsultation)->count(),
            'In Consultation' => $visits->where('status', VisitStatus::SeenByNurse)->count(),
            'Awaiting Billing' => $visits->where('status', VisitStatus::AwaitingBilling)->count(),
            'Completed' => $visits->where('status', VisitStatus::Completed)->count(),
        ];

        $groups = array_filter($groups, fn (int $count) => $count > 0);

        if ($groups === []) {
            return ['labels' => ['No visits today'], 'values' => [0]];
        }

        return [
            'labels' => array_keys($groups),
            'values' => array_values($groups),
        ];
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<float>, borderColor: string, backgroundColor: string}>}
     */
    private function depositsVsBillingTrend(): array
    {
        $labels = [];
        $deposits = [];
        $billing = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('D');

            $memberDeposits = (float) Deposit::query()->active()->whereDate('deposit_date', $date)->sum('amount');
            $companyDeposits = (float) CompanyDeposit::query()->active()->whereDate('deposit_date', $date)->sum('amount');
            $membership = (float) MembershipFee::query()->whereDate('payment_date', $date)->sum('amount');

            $deposits[] = round($memberDeposits + $companyDeposits + $membership, 2);
            $billing[] = round((float) Bill::query()->posted()->whereDate('visit_date', $date)->sum('total_amount'), 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Deposits',
                    'data' => $deposits,
                    'borderColor' => '#7c3aed',
                    'backgroundColor' => 'rgba(124, 58, 237, 0.08)',
                ],
                [
                    'label' => 'Billing',
                    'data' => $billing,
                    'borderColor' => '#ea580c',
                    'backgroundColor' => 'rgba(234, 88, 12, 0.08)',
                ],
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function paymentMethodAmounts(): array
    {
        $amounts = [];

        foreach (PaymentMethod::cases() as $method) {
            $depositAmount = (float) Deposit::query()
                ->active()
                ->whereDate('deposit_date', '>=', today()->subDays(6))
                ->where('payment_method', $method->value)
                ->sum('amount');

            $membershipAmount = (float) MembershipFee::query()
                ->whereDate('payment_date', '>=', today()->subDays(6))
                ->where('payment_method', $method->value)
                ->sum('amount');

            $total = round($depositAmount + $membershipAmount, 2);

            if ($total > 0) {
                $amounts[$method->label()] = $total;
            }
        }

        if ($amounts === []) {
            return ['labels' => ['No payments yet'], 'values' => [0]];
        }

        return [
            'labels' => array_keys($amounts),
            'values' => array_values($amounts),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function dailyAuditCounts(): array
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('D');
            $values[] = AuditLog::query()->whereDate('created_at', $date)->count();
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function userActivityByRole(): array
    {
        $activity = AuditLog::query()
            ->with('user')
            ->whereDate('created_at', today())
            ->get()
            ->groupBy(fn (AuditLog $log) => match ($log->user?->role) {
                UserRole::Registry, UserRole::Nursing => 'Registry Clerk',
                UserRole::Nurse => 'Nurse',
                UserRole::Accounts => 'Accounts',
                UserRole::Administrator => 'Admin',
                default => 'System',
            })
            ->map->count();

        $order = ['Registry Clerk', 'Nurse', 'Accounts', 'Admin', 'System'];
        $labels = [];
        $values = [];

        foreach ($order as $role) {
            if (! $activity->has($role)) {
                continue;
            }

            $labels[] = $role;
            $values[] = $activity[$role];
        }

        if ($labels === []) {
            return ['labels' => ['No activity'], 'values' => [0]];
        }

        return compact('labels', 'values');
    }

    private function trendDirection(float|int $today, float|int $yesterday): string
    {
        if ($today > $yesterday) {
            return 'up';
        }

        if ($today < $yesterday) {
            return 'down';
        }

        return 'neutral';
    }

    private function trendLabel(float|int $today, float|int $yesterday): string
    {
        if ($yesterday == 0) {
            return $today > 0 ? 'New activity today' : 'No change vs yesterday';
        }

        $change = round((($today - $yesterday) / $yesterday) * 100);

        if ($change === 0.0) {
            return 'No change vs yesterday';
        }

        $prefix = $change > 0 ? '+' : '';

        return $prefix.$change.'% vs yesterday';
    }

    private function signedTrendLabel(float|int $today, float|int $yesterday, string $suffix): string
    {
        $difference = $today - $yesterday;

        if ($difference === 0) {
            return 'No change '.$suffix;
        }

        $prefix = $difference > 0 ? '+' : '';

        return $prefix.number_format($difference).' '.$suffix;
    }

    /**
     * @param  array{labels: list<string>, values: list<int>}  $series
     * @return array<string, mixed>
     */
    private function lineChart(string $title, ?string $description, array $series, string $datasetLabel, string $color = '#0f766e'): array
    {
        return [
            'type' => 'line',
            'title' => $title,
            'description' => $description,
            'data' => [
                'labels' => $series['labels'],
                'datasets' => [[
                    'label' => $datasetLabel,
                    'data' => $series['values'],
                    'borderColor' => $color,
                    'backgroundColor' => $this->fade($color),
                    'fill' => true,
                    'tension' => 0.35,
                ]],
            ],
        ];
    }

    /**
     * @param  array{labels: list<string>, values: list<int|float>}  $series
     * @return array<string, mixed>
     */
    private function barChart(
        string $title,
        ?string $description,
        array $series,
        string $color = '#0f766e',
        bool $horizontal = false,
    ): array {
        return [
            'type' => 'bar',
            'title' => $title,
            'description' => $description,
            'horizontal' => $horizontal,
            'data' => [
                'labels' => $series['labels'],
                'datasets' => [[
                    'label' => $title,
                    'data' => $series['values'],
                    'backgroundColor' => $color,
                    'borderRadius' => 6,
                ]],
            ],
        ];
    }

    /**
     * @param  array{labels: list<string>, datasets: list<array<string, mixed>>}  $series
     * @return array<string, mixed>
     */
    private function multiLineChart(string $title, ?string $description, array $series): array
    {
        return [
            'type' => 'line',
            'title' => $title,
            'description' => $description,
            'data' => [
                'labels' => $series['labels'],
                'datasets' => array_map(function (array $dataset): array {
                    return array_merge($dataset, [
                        'fill' => false,
                        'tension' => 0.35,
                    ]);
                }, $series['datasets']),
            ],
        ];
    }

    /**
     * @param  array{labels: list<string>, values: list<int|float>}  $series
     * @param  list<string>  $colors
     * @return array<string, mixed>
     */
    private function pieChart(string $title, ?string $description, array $series, array $colors = [], ?string $footer = null): array
    {
        if ($colors === []) {
            $colors = ['#0f766e', '#14b8a6', '#5eead4', '#f59e0b', '#8b5cf6', '#64748b'];
        }

        $chart = [
            'type' => 'pie',
            'title' => $title,
            'description' => $description,
            'data' => [
                'labels' => $series['labels'],
                'datasets' => [[
                    'data' => $series['values'],
                    'backgroundColor' => $colors,
                ]],
            ],
        ];

        if ($footer !== null) {
            $chart['footer'] = $footer;
        }

        return $chart;
    }

    private function fade(string $hex): string
    {
        return match ($hex) {
            '#059669' => 'rgba(5, 150, 105, 0.12)',
            '#2563eb' => 'rgba(37, 99, 235, 0.1)',
            '#1e40af', '#1d4ed8' => 'rgba(30, 64, 175, 0.1)',
            '#7c3aed' => 'rgba(124, 58, 237, 0.1)',
            default => 'rgba(15, 118, 110, 0.1)',
        };
    }
}
