<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Company;
use App\Models\Patient;
use App\Services\ReportService;
use App\Support\CsvExporter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    /**
     * Reports dashboard — summary totals and visit breakdown for a date range.
     */
    public function index(ReportFilterRequest $request): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $summary = $this->reportService->summary($range['from'], $range['to']);

        return view('reports.index', array_merge($range, [
            'summary' => $summary,
            'filters' => $request->validated(),
        ]));
    }

    /**
     * Detailed transaction list for the period.
     */
    public function transactions(ReportFilterRequest $request): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $transactions = $this->reportService->transactions(
            $range['from'],
            $range['to'],
            $request->input('visit_type'),
        );

        return view('reports.transactions', array_merge($range, [
            'transactions' => $transactions,
            'filters' => $request->validated(),
        ]));
    }

    /**
     * Member account balances and period activity (Accounts / Admin).
     */
    public function memberAccounts(ReportFilterRequest $request): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $accounts = $this->reportService->memberAccounts($range['from'], $range['to']);

        return view('reports.member-accounts', array_merge($range, [
            'accounts' => $accounts,
            'filters' => $request->validated(),
        ]));
    }

    /**
     * Company pool balances and usage summary.
     */
    public function companies(ReportFilterRequest $request): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $companies = $this->reportService->companies($range['from'], $range['to']);

        return view('reports.companies', array_merge($range, [
            'companies' => $companies,
            'filters' => $request->validated(),
        ]));
    }

    /**
     * Single company report with bill detail.
     */
    public function companyShow(ReportFilterRequest $request, Company $company): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $report = $this->reportService->companySummary($company, $range['from'], $range['to']);

        return view('reports.company-show', array_merge($range, [
            'report' => $report,
            'filters' => $request->validated(),
        ]));
    }

    /**
     * Patient statement — visits and deposits for a date range.
     */
    public function patientStatement(ReportFilterRequest $request, Patient $patient): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $statement = $this->reportService->patientStatement($patient, $range['from'], $range['to']);

        return view('reports.patient-statement', array_merge($range, [
            'statement' => $statement,
            'filters' => $request->validated(),
        ]));
    }

    /** CSV export for the transaction report. */
    public function exportTransactions(ReportFilterRequest $request): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $transactions = $this->reportService->transactions(
            $range['from'],
            $range['to'],
            $request->input('visit_type'),
        );

        $rows = $transactions->map(fn (array $row) => [
            $row['date'] instanceof \DateTimeInterface ? $row['date']->format('Y-m-d') : $row['date'],
            $row['type'],
            $row['party'],
            $row['reference'],
            $row['direction'] === 'in' ? $row['amount'] : -$row['amount'],
            $row['status'],
            $row['notes'] ?? '',
        ]);

        $filename = 'transactions-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Date', 'Type', 'Party', 'Reference', 'Amount (K)', 'Status', 'Notes',
        ], $rows);
    }

    /** CSV export for member accounts report. */
    public function exportMemberAccounts(ReportFilterRequest $request): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $accounts = $this->reportService->memberAccounts($range['from'], $range['to']);

        $rows = $accounts->map(fn (array $row) => [
            $row['member']->name,
            $row['member']->hc_number ?? '',
            $row['current_balance'],
            $row['deposits_in_period'],
            $row['bills_in_period'],
            $row['dependants_count'],
            $row['member']->status->label(),
        ]);

        $filename = 'member-accounts-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Member', 'HC Number', 'Current Balance (K)', 'Deposits in Period (K)', 'Bills in Period (K)', 'Dependants', 'Status',
        ], $rows);
    }

    /** CSV export for a company report. */
    public function exportCompany(ReportFilterRequest $request, Company $company): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $report = $this->reportService->companySummary($company, $range['from'], $range['to']);

        $rows = $report['bills']->map(fn ($bill) => [
            $bill->visit_date->format('Y-m-d'),
            $bill->patient->name,
            $bill->visit_type->label(),
            $bill->total_amount,
            $bill->status->label(),
        ]);

        $filename = 'company-'.str($company->name)->slug().'-'.$range['from']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Date', 'Patient', 'Visit Type', 'Amount (K)', 'Status',
        ], $rows);
    }

    /** CSV export for a patient statement. */
    public function exportPatientStatement(ReportFilterRequest $request, Patient $patient): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $statement = $this->reportService->patientStatement($patient, $range['from'], $range['to']);

        $rows = $statement['lines']->map(fn (array $line) => [
            $line['date']->format('Y-m-d'),
            $line['description'],
            $line['debit'] ?: '',
            $line['credit'] ?: '',
            $line['status'],
        ]);

        $filename = 'statement-'.str($patient->name)->slug().'-'.$range['from']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Date', 'Description', 'Debit (K)', 'Credit (K)', 'Status',
        ], $rows);
    }
}
