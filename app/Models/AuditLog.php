<?php

namespace App\Models;

use App\Enums\AuditActionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_type',
        'description',
        'user_id',
        'related_type',
        'related_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'action_type' => AuditActionType::class,
            'metadata' => 'array',
        ];
    }

    /** Staff user who performed the action. Nullable if the user was deleted. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Record affected by the action, such as a bill, deposit, or patient. */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /** Short label for the related record in lists and exports. */
    public function relatedSummary(): string
    {
        if (! $this->related_type || ! $this->related_id) {
            return '';
        }

        $related = $this->related;

        if ($related instanceof Patient) {
            return $related->name;
        }

        if ($related instanceof Deposit) {
            return 'Deposit #'.$related->id;
        }

        if ($related instanceof CompanyDeposit) {
            return 'Company deposit #'.$related->id;
        }

        if ($related instanceof Bill) {
            return 'Bill #'.$related->id;
        }

        if ($related instanceof Company) {
            return $related->name;
        }

        return class_basename($this->related_type).' #'.$this->related_id;
    }

    /** Link to the related record when one exists in the app. */
    public function relatedUrl(): ?string
    {
        if (! $this->related_type || ! $this->related_id) {
            return null;
        }

        $related = $this->related;

        return match ($this->related_type) {
            Patient::class => route('patients.show', $this->related_id),
            Deposit::class => route('deposits.show', $this->related_id),
            Bill::class => route('billing.show', $this->related_id),
            CompanyDeposit::class => $related ? route('company-accounts.show', $related->company_id) : null,
            Company::class => route('company-accounts.show', $this->related_id),
            default => null,
        };
    }
}
