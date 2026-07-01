<?php

namespace App\Models;

use App\Enums\ChargeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargeLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'billable_service_id',
        'category',
        'description',
        'amount',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => ChargeCategory::class,
            'amount' => 'decimal:2',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function billableService(): BelongsTo
    {
        return $this->belongsTo(BillableService::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
