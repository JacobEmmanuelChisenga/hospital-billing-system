<?php

namespace App\Http\Controllers;

use App\Enums\AuditActionType;
use App\Enums\UserStatus;
use App\Http\Requests\AuditLogFilterRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ReportService;
use App\Support\CsvExporter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
        private ReportService $reportService,
    ) {}

    /**
     * Searchable audit trail for patient, deposit, and billing actions.
     */
    public function index(AuditLogFilterRequest $request): View
    {
        $range = $this->reportService->resolveDateRange($request);
        $actionType = AuditActionType::tryFrom((string) $request->input('action_type'));
        $userId = $request->filled('user_id') ? $request->integer('user_id') : null;
        $search = $request->string('search')->trim()->toString() ?: null;

        $logs = $this->auditLogService->paginate(
            $range['from'],
            $range['to'],
            $actionType,
            $userId,
            $search,
        );

        $staffUsers = User::query()
            ->where('status', UserStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('audit-logs.index', array_merge($range, [
            'logs' => $logs,
            'staffUsers' => $staffUsers,
            'filters' => $request->validated(),
            'selectedActionType' => $actionType?->value,
            'selectedUserId' => $userId,
            'search' => $search ?? '',
        ]));
    }

    /** Full detail for a single audit entry. */
    public function show(AuditLog $auditLog): View
    {
        $auditLog->load(['user', 'related']);

        return view('audit-logs.show', [
            'log' => $auditLog,
        ]);
    }

    /** CSV export of filtered audit entries. */
    public function export(AuditLogFilterRequest $request): StreamedResponse
    {
        $range = $this->reportService->resolveDateRange($request);
        $actionType = AuditActionType::tryFrom((string) $request->input('action_type'));
        $userId = $request->filled('user_id') ? $request->integer('user_id') : null;
        $search = $request->string('search')->trim()->toString() ?: null;

        $rows = $this->auditLogService->exportRows(
            $range['from'],
            $range['to'],
            $actionType,
            $userId,
            $search,
        );

        $filename = 'audit-log-'.$range['from']->format('Y-m-d').'-to-'.$range['to']->format('Y-m-d').'.csv';

        return CsvExporter::download($filename, [
            'Date & Time', 'Action', 'Staff User', 'Description', 'Related Record', 'Metadata',
        ], $rows);
    }
}
