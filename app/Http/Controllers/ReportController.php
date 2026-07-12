<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Company;
use App\Models\Patient;
use App\Services\ReportService;
use App\Support\CsvExporter;
use App\Support\PdfExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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
     * Casual caller collections — pay-as-you-go bills and payments.
     */
    public function casualCallers(ReportFilterRequest $request): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $report = $this->reportService->casualCallers($range['from'], $range['to']);

        return view('reports.casual-callers', array_merge($range, [
            'report' => $report,
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
    public function patientStatement(ReportFilterRequest $request, Patient $patient): View|RedirectResponse
    {
        if ($patient->isCashPatient()) {
            return redirect()
                ->route('patients.show', $patient)
                ->with('info', 'Casual callers do not have account statements. View visit history on the patient profile.');
        }

        $range = $this->reportService->resolveDateRange($request);
        $statement = $this->reportService->patientStatement($patient, $range['from'], $range['to']);

        return view('reports.patient-statement', array_merge($range, [
            'statement' => $statement,
            'filters' => $request->validated(),
        ]));
    }

    /** CSV export for the summary report. */
    public function exportSummary(ReportFilterRequest $request): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $summary = $this->reportService->summary($range['from'], $range['to']);

        $rows = collect([
            ['Member Deposits (K)', '', $summary['member_deposits_total']],
            ['Company Deposits (K)', '', $summary['company_deposits_total']],
            ['Bills Posted (K)', '', $summary['bills_total']],
            ['Voided Bills', $summary['voided_bills_count'], $summary['voided_bills_total']],
            ['Active Members', $summary['active_members'], ''],
            ['Total Member Balances (K)', '', $summary['total_member_balance']],
            ['Company Patients', $summary['active_company_patients'], ''],
            ['Total Company Pools (K)', '', $summary['total_company_balance']],
            ['Casual Callers', $summary['active_casual_callers'], ''],
            ['Casual Caller Bills (K)', '', $summary['casual_billed_total']],
            ['Casual Caller Collections (K)', '', $summary['casual_collected_total']],
            ['Casual Caller Outstanding (K)', '', $summary['casual_outstanding_total']],
        ]);

        foreach ($summary['visit_summary'] as $row) {
            $rows->push([
                $row['type']->label(),
                $row['count'],
                $row['total'],
            ]);
        }

        $filename = 'summary-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Metric / Type', 'Count', 'Amount (K)',
        ], $rows);
    }

    /** PDF export for the summary report. */
    public function exportSummaryPdf(ReportFilterRequest $request): Response
    {
        $range = $this->reportService->resolveDateRange($request);
        $summary = $this->reportService->summary($range['from'], $range['to']);

        $filename = 'summary-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.pdf';

        return PdfExporter::download($filename, 'reports.pdf.summary', array_merge($range, [
            'summary' => $summary,
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

    /** PDF export for the transaction report. */
    public function exportTransactionsPdf(ReportFilterRequest $request): Response
    {
        $range = $this->reportService->resolveDateRange($request);
        $transactions = $this->reportService->transactions(
            $range['from'],
            $range['to'],
            $request->input('visit_type'),
        );

        $filename = 'transactions-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.pdf';

        return PdfExporter::download($filename, 'reports.pdf.transactions', array_merge($range, [
            'transactions' => $transactions,
        ]));
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

    /** PDF export for member accounts report. */
    public function exportMemberAccountsPdf(ReportFilterRequest $request): Response
    {
        $range = $this->reportService->resolveDateRange($request);
        $accounts = $this->reportService->memberAccounts($range['from'], $range['to']);

        $filename = 'member-accounts-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.pdf';

        return PdfExporter::download($filename, 'reports.pdf.member-accounts', array_merge($range, [
            'accounts' => $accounts,
        ]));
    }

    /** CSV export for the companies list report. */
    public function exportCompanies(ReportFilterRequest $request): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $companies = $this->reportService->companies($range['from'], $range['to']);

        $rows = $companies->map(fn (array $row) => [
            $row['company']->name,
            $row['company']->patients_count,
            $row['current_balance'],
            $row['deposits_in_period'],
            $row['bills_in_period'],
        ]);

        $filename = 'companies-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Company', 'Patients', 'Pool Balance (K)', 'Deposits in Period (K)', 'Bills in Period (K)',
        ], $rows);
    }

    /** PDF export for the companies list report. */
    public function exportCompaniesPdf(ReportFilterRequest $request): Response
    {
        $range = $this->reportService->resolveDateRange($request);
        $companies = $this->reportService->companies($range['from'], $range['to']);

        $filename = 'companies-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.pdf';

        return PdfExporter::download($filename, 'reports.pdf.companies', array_merge($range, [
            'companies' => $companies,
        ]));
    }

    /** CSV export for casual caller collections report. */
    public function exportCasualCallers(ReportFilterRequest $request): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $report = $this->reportService->casualCallers($range['from'], $range['to']);

        $rows = $report['bills']->map(fn (array $row) => [
            $row['bill']->visit_date->format('Y-m-d'),
            $row['patient']->name,
            $row['patient']->file_number ?? '',
            $row['visit_label'],
            $row['amount'],
            $row['status'],
            $row['payment_method'] ?? '',
            $row['paid_at']?->format('Y-m-d H:i') ?? '',
        ]);

        $filename = 'casual-callers-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Date', 'Patient', 'File Number', 'Visit', 'Amount (K)', 'Status', 'Payment Method', 'Paid At',
        ], $rows);
    }

    /** PDF export for casual caller collections report. */
    public function exportCasualCallersPdf(ReportFilterRequest $request): Response
    {
        $range = $this->reportService->resolveDateRange($request);
        $report = $this->reportService->casualCallers($range['from'], $range['to']);

        $filename = 'casual-callers-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.pdf';

        return PdfExporter::download($filename, 'reports.pdf.casual-callers', array_merge($range, [
            'report' => $report,
        ]));
    }

    /** CSV export for a company report. */
    public function exportCompany(ReportFilterRequest $request, Company $company): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $report = $this->reportService->companySummary($company, $range['from'], $range['to']);

        $rows = $report['lines']->map(fn (array $line) => [
            $line['date']->format('Y-m-d'),
            $line['reference'],
            $line['description'],
            $line['debit'] ?? '',
            $line['credit'] ?? '',
            $line['balance'],
        ]);

        $filename = 'company-statement-'.str($company->name)->slug().'-'.$range['from']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Date', 'Reference', 'Description', 'Debit (K)', 'Credit (K)', 'Balance (K)',
        ], $rows);
    }

    /** PDF export for a company report. */
    public function exportCompanyPdf(ReportFilterRequest $request, Company $company): Response
    {
        $range = $this->reportService->resolveDateRange($request);
        $report = $this->reportService->companySummary($company, $range['from'], $range['to']);

        $filename = 'company-'.str($company->name)->slug().'-'.$range['from']->format('Y-m-d').'.pdf';

        return PdfExporter::download($filename, 'reports.pdf.company-show', array_merge($range, [
            'report' => $report,
        ]));
    }

    /** CSV export for a patient statement. */
    public function exportPatientStatement(ReportFilterRequest $request, Patient $patient): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $statement = $this->reportService->patientStatement($patient, $range['from'], $range['to']);

        $rows = $statement['lines']->map(fn (array $line) => [
            $line['date']->format('Y-m-d'),
            $line['reference'],
            $line['description'],
            $line['debit'] ?? '',
            $line['credit'] ?? '',
            $line['balance'],
        ]);

        $filename = 'statement-'.str($patient->name)->slug().'-'.$range['from']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Date', 'Reference', 'Description', 'Debit (K)', 'Credit (K)', 'Balance (K)',
        ], $rows);
    }

    /** PDF export for a patient statement. */
    public function exportPatientStatementPdf(ReportFilterRequest $request, Patient $patient): Response
    {
        $range = $this->reportService->resolveDateRange($request);
        $statement = $this->reportService->patientStatement($patient, $range['from'], $range['to']);

        $filename = 'statement-'.str($patient->name)->slug().'-'.$range['from']->format('Y-m-d').'.pdf';

        return PdfExporter::download($filename, 'reports.pdf.patient-statement', array_merge($range, [
            'statement' => $statement,
        ]));
    }
}
