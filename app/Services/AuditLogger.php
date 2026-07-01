<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Small helper for writing audit log entries from controllers and services.
 *
 * Every important financial or patient action should call this so we have
 * a clear record of who did what and when.
 */
class AuditLogger
{
    public static function log(
        AuditActionType $actionType,
        string $description,
        ?Model $related = null,
        ?array $metadata = null,
    ): AuditLog {
        return AuditLog::create([
            'action_type' => $actionType,
            'description' => $description,
            'user_id' => Auth::id(),
            'related_type' => $related ? $related::class : null,
            'related_id' => $related?->getKey(),
            'metadata' => $metadata,
        ]);
    }
}
