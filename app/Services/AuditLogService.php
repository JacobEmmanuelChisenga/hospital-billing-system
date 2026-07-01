<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Query helpers for the audit log screen and CSV export.
 */
class AuditLogService
{
    public function query(
        Carbon $from,
        Carbon $to,
        ?AuditActionType $actionType = null,
        ?int $userId = null,
        ?string $search = null,
    ): Builder {
        return AuditLog::query()
            ->with(['user', 'related'])
            ->when($actionType, fn (Builder $query) => $query->where('action_type', $actionType))
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->when($search, function (Builder $query) use ($search): void {
                $query->where('description', 'like', '%'.$search.'%');
            })
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function paginate(
        Carbon $from,
        Carbon $to,
        ?AuditActionType $actionType = null,
        ?int $userId = null,
        ?string $search = null,
        int $perPage = 25,
    ): LengthAwarePaginator {
        return $this->query($from, $to, $actionType, $userId, $search)
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Flat rows for CSV export. */
    public function exportRows(
        Carbon $from,
        Carbon $to,
        ?AuditActionType $actionType = null,
        ?int $userId = null,
        ?string $search = null,
    ): Collection {
        return $this->query($from, $to, $actionType, $userId, $search)
            ->get()
            ->map(fn (AuditLog $log) => [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->action_type->label(),
                $log->user?->name ?? 'System',
                $log->description,
                $log->relatedSummary(),
                $log->metadata ? json_encode($log->metadata) : '',
            ]);
    }
}
