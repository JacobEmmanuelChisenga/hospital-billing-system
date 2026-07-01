<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'membership_number',
        'status',
        'start_date',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'start_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
