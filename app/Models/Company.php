<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'balance',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    /** Company patients whose bills deduct from this shared company pool. */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /** Money loaded into the company's shared high-cost account. */
    public function deposits(): HasMany
    {
        return $this->hasMany(CompanyDeposit::class);
    }

    /** Bills paid from this company pool. */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
