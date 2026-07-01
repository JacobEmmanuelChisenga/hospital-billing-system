<?php

namespace App\Models;

use App\Enums\ChargeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillableService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => ChargeCategory::class,
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
