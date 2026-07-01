<?php

namespace App\Http\Requests;

use App\Enums\AuditActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates filters for the audit log index and CSV export.
 */
class AuditLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    public function rules(): array
    {
        return [
            'preset' => ['nullable', 'in:today,week,month,custom'],
            'from_date' => ['nullable', 'date', 'required_if:preset,custom'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date', 'required_if:preset,custom'],
            'action_type' => ['nullable', Rule::enum(AuditActionType::class)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'from_date' => 'from date',
            'to_date' => 'to date',
            'action_type' => 'action type',
            'user_id' => 'staff user',
            'search' => 'search',
        ];
    }
}
