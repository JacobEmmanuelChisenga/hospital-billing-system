<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\UserStatus;
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

        return [
            'kpis' => [
                'todaysPatients' => Visit::query()->whereDate('visit_date', $today)->distinct('patient_id')->count('patient_id'),
                'pendingVisits' => Visit::query()->open()->whereDate('visit_date', $today)->count(),
                'pendingCharges' => Visit::query()->where('status', VisitStatus::AwaitingBilling)->count(),
                'completedToday' => Visit::query()->where('status', VisitStatus::Completed)->whereDate('visit_date', $today)->count(),
            ],
            'charts' => [
                'patientFlow' => $this->lineChart(
                    'Daily Patient Flow',
                    'Visits opened today by hour — spot bottlenecks in the patient journey.',
                    $this->hourlyCounts(
                        Visit::query()->whereDate('created_at', $today)->get(),
                        fn (Visit $visit) => $visit->created_at,
                    ),
                    'Visits opened',
                ),
                'patientTypes' => $this->pieChart(
                    'Patient Type Mix',
                    'Today\'s visits by patient category.',
                    $this->countByLabel(
                        Visit::query()
                            ->with('patient')
                            ->whereDate('visit_date', $today)
                            ->get()
                            ->groupBy(fn (Visit $visit) => $visit->patient->type->label()),
                    ),
                ),
                'pendingQueue' => $this->barChart(
                    'Pending Actions Queue',
                    'Open work waiting on the Registry Clerk.',
                    $this->pendingRegistryActions(),
                ),
            ],
        ];
    }

    public function nurse(): array
    {
        $today = today();

        $notesToday = ClinicalNote::query()
            ->whereDate('created_at', $today)
            ->get();

        $todayVisits = Visit::query()
            ->whereDate('visit_date', $today)
            ->get();

        $waiting = $todayVisits->where('status', VisitStatus::ReadyForConsultation)->count();

        return [
            'kpis' => [
                'patientsWaiting' => $waiting,
                'patientsSeen' => $notesToday->count(),
                'pendingConsultations' => $waiting,
            ],
            'charts' => [
                'patientsSeen' => $this->lineChart(
                    'Patients Seen Today',
                    'Consultations recorded by hour.',
                    $this->hourlyCounts($notesToday, fn (ClinicalNote $note) => $note->created_at),
                    'Consultations',
                    '#059669',
                ),
                'diagnoses' => $this->barChart(
                    'Common Diagnoses',
                    'Top conditions documented this week.',
                    $this->topDiagnoses(),
                    '#7c3aed',
                ),
                'caseStatus' => $this->pieChart(
                    'Case Status',
                    'Today\'s visits across the consultation workflow.',
                    $this->nurseCaseStatus($todayVisits),
                ),
            ],
        ];
    }

    public function accounts(): array
    {
        $today = today();

        $depositsToday = (float) Deposit::query()->active()->whereDate('deposit_date', $today)->sum('amount');
        $companyDepositsToday = (float) CompanyDeposit::query()->active()->whereDate('deposit_date', $today)->sum('amount');
        $membershipToday = (float) MembershipFee::query()->whereDate('payment_date', $today)->sum('amount');
        $billsToday = (float) Bill::query()->posted()->whereDate('visit_date', $today)->sum('total_amount');

        return [
            'kpis' => [
                'moneyInToday' => $depositsToday + $companyDepositsToday + $membershipToday,
                'spendingToday' => $billsToday,
                'depositsToday' => $depositsToday + $companyDepositsToday,
                'membershipToday' => $membershipToday,
            ],
            'charts' => [
                'revenueBreakdown' => $this->barChart(
                    'Daily Revenue Breakdown',
                    'Money received today by source.',
                    [
                        'labels' => ['Member Deposits', 'Company Deposits', 'Membership Fees'],
                        'values' => [
                            round($depositsToday, 2),
                            round($companyDepositsToday, 2),
                            round($membershipToday, 2),
                        ],
                    ],
                    '#0f766e',
                ),
                'depositVsSpending' => $this->multiLineChart(
                    'Deposits vs Spending',
                    'Last 7 days — money in versus bills posted.',
                    $this->depositVsSpendingTrend(),
                ),
                'paymentMethods' => $this->pieChart(
                    'Payment Methods',
                    'How payments were received this week.',
                    $this->paymentMethodMix(),
                ),
            ],
        ];
    }

    public function admin(): array
    {
        return [
            'kpis' => [
                'activeUsers' => User::query()->where('status', UserStatus::Active)->count(),
                'auditEventsToday' => AuditLog::query()->whereDate('created_at', today())->count(),
                'visitsToday' => Visit::query()->whereDate('visit_date', today())->count(),
                'billsToday' => Bill::query()->posted()->whereDate('visit_date', today())->count(),
            ],
            'charts' => [
                'systemActivity' => $this->lineChart(
                    'System Activity',
                    'Audit events over the last 7 days.',
                    $this->dailyAuditCounts(),
                    'Events',
                    '#1d4ed8',
                ),
                'userActivity' => $this->barChart(
                    'User Activity',
                    'Staff actions recorded today.',
                    $this->userActivityToday(),
                    '#0f766e',
                ),
                'auditEvents' => $this->pieChart(
                    'Audit Events',
                    'Today\'s activity by event type.',
                    $this->auditEventMix(),
                ),
            ],
        ];
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @param  callable(mixed): ?Carbon  $timestamp
     * @return array{labels: list<string>, values: list<int>}
     */
    private function hourlyCounts(Collection $items, callable $timestamp): array
    {
        $hours = collect(range(6, 18))->mapWithKeys(fn (int $hour) => [sprintf('%02d:00', $hour) => 0]);

        foreach ($items as $item) {
            $time = $timestamp($item);

            if (! $time instanceof Carbon) {
                continue;
            }

            $hour = (int) $time->format('G');

            if ($hour < 6 || $hour > 18) {
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
        return [
            'labels' => $groups->keys()->values()->all(),
            'values' => $groups->map->count()->values()->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function pendingRegistryActions(): array
    {
        return [
            'labels' => [
                'Awaiting Payment',
                'Ready for Nurse',
                'Awaiting Charges',
                'Ready to Post',
            ],
            'values' => [
                Visit::query()->where('status', VisitStatus::AwaitingPayment)->count(),
                Visit::query()->where('status', VisitStatus::ReadyForConsultation)->count(),
                Visit::query()
                    ->where('status', VisitStatus::AwaitingBilling)
                    ->whereDoesntHave('chargeLines')
                    ->count(),
                Visit::query()
                    ->where('status', VisitStatus::AwaitingBilling)
                    ->whereHas('chargeLines')
                    ->count(),
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function topDiagnoses(): array
    {
        $diagnoses = ClinicalNote::query()
            ->whereDate('created_at', '>=', today()->subDays(6))
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->get()
            ->groupBy(fn (ClinicalNote $note) => trim($note->diagnosis))
            ->map->count()
            ->sortDesc()
            ->take(5);

        if ($diagnoses->isEmpty()) {
            return ['labels' => ['No data yet'], 'values' => [0]];
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
            'Awaiting Billing' => $visits->where('status', VisitStatus::AwaitingBilling)->count(),
            'Completed' => $visits->where('status', VisitStatus::Completed)->count(),
            'Other' => $visits->whereNotIn('status', [
                VisitStatus::ReadyForConsultation,
                VisitStatus::AwaitingBilling,
                VisitStatus::Completed,
            ])->count(),
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
    private function depositVsSpendingTrend(): array
    {
        $labels = [];
        $deposits = [];
        $spending = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('d M');

            $memberDeposits = (float) Deposit::query()->active()->whereDate('deposit_date', $date)->sum('amount');
            $companyDeposits = (float) CompanyDeposit::query()->active()->whereDate('deposit_date', $date)->sum('amount');
            $membership = (float) MembershipFee::query()->whereDate('payment_date', $date)->sum('amount');

            $deposits[] = round($memberDeposits + $companyDeposits + $membership, 2);
            $spending[] = round((float) Bill::query()->posted()->whereDate('visit_date', $date)->sum('total_amount'), 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Money In',
                    'data' => $deposits,
                    'borderColor' => '#0f766e',
                    'backgroundColor' => 'rgba(15, 118, 110, 0.1)',
                ],
                [
                    'label' => 'Bills Posted',
                    'data' => $spending,
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.08)',
                ],
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function paymentMethodMix(): array
    {
        $counts = [];

        foreach (PaymentMethod::cases() as $method) {
            $depositCount = Deposit::query()
                ->active()
                ->whereDate('deposit_date', '>=', today()->subDays(6))
                ->where('payment_method', $method->value)
                ->count();

            $membershipCount = MembershipFee::query()
                ->whereDate('payment_date', '>=', today()->subDays(6))
                ->where('payment_method', $method->value)
                ->count();

            $total = $depositCount + $membershipCount;

            if ($total > 0) {
                $counts[$method->label()] = $total;
            }
        }

        if ($counts === []) {
            return ['labels' => ['No payments yet'], 'values' => [0]];
        }

        return [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
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
            $labels[] = $date->format('d M');
            $values[] = AuditLog::query()->whereDate('created_at', $date)->count();
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function userActivityToday(): array
    {
        $activity = AuditLog::query()
            ->with('user')
            ->whereDate('created_at', today())
            ->get()
            ->groupBy(fn (AuditLog $log) => $log->user?->name ?? 'System')
            ->map->count()
            ->sortDesc()
            ->take(6);

        if ($activity->isEmpty()) {
            return ['labels' => ['No activity'], 'values' => [0]];
        }

        return [
            'labels' => $activity->keys()->values()->all(),
            'values' => $activity->values()->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function auditEventMix(): array
    {
        $events = AuditLog::query()
            ->whereDate('created_at', today())
            ->get()
            ->groupBy(fn (AuditLog $log) => $log->action_type->label())
            ->map->count()
            ->sortDesc()
            ->take(6);

        if ($events->isEmpty()) {
            return ['labels' => ['No events'], 'values' => [0]];
        }

        return [
            'labels' => $events->keys()->values()->all(),
            'values' => $events->values()->all(),
        ];
    }

    /**
     * @param  array{labels: list<string>, values: list<int>}  $series
     * @return array<string, mixed>
     */
    private function lineChart(string $title, string $description, array $series, string $datasetLabel, string $color = '#0f766e'): array
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
    private function barChart(string $title, string $description, array $series, string $color = '#0f766e'): array
    {
        return [
            'type' => 'bar',
            'title' => $title,
            'description' => $description,
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
    private function multiLineChart(string $title, string $description, array $series): array
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
     * @param  array{labels: list<string>, values: list<int>}  $series
     * @return array<string, mixed>
     */
    private function pieChart(string $title, string $description, array $series): array
    {
        return [
            'type' => 'pie',
            'title' => $title,
            'description' => $description,
            'data' => [
                'labels' => $series['labels'],
                'datasets' => [[
                    'data' => $series['values'],
                    'backgroundColor' => [
                        '#0f766e',
                        '#14b8a6',
                        '#5eead4',
                        '#f59e0b',
                        '#8b5cf6',
                        '#64748b',
                    ],
                ]],
            ],
        ];
    }

    private function fade(string $hex): string
    {
        return match ($hex) {
            '#059669' => 'rgba(5, 150, 105, 0.12)',
            '#1d4ed8' => 'rgba(29, 78, 216, 0.1)',
            '#7c3aed' => 'rgba(124, 58, 237, 0.1)',
            default => 'rgba(15, 118, 110, 0.1)',
        };
    }
}
