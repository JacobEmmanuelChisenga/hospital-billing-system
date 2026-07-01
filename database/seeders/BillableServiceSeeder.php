<?php

namespace Database\Seeders;

use App\Models\BillableService;
use Illuminate\Database\Seeder;

class BillableServiceSeeder extends Seeder
{
    /**
     * Default High Cost Section service catalogue.
     * Administrators can adjust names, categories, and prices in the admin UI.
     *
     * @return list<array{name: string, category: string, price: float}>
     */
    public static function catalogue(): array
    {
        return [
            ['name' => 'Consultation', 'category' => 'consultation', 'price' => 150],
            ['name' => 'Dressing', 'category' => 'procedure', 'price' => 100],
            ['name' => 'Injection', 'category' => 'procedure', 'price' => 80],
            ['name' => 'Laboratory', 'category' => 'lab', 'price' => 250],
            ['name' => 'X-Ray', 'category' => 'procedure', 'price' => 300],
            ['name' => 'Pharmacy', 'category' => 'pharmacy', 'price' => 120],
            ['name' => 'Ward', 'category' => 'ward', 'price' => 500],
            ['name' => 'Theatre', 'category' => 'procedure', 'price' => 1000],
        ];
    }

    public function run(): void
    {
        foreach (self::catalogue() as $service) {
            BillableService::query()->updateOrCreate(
                ['name' => $service['name']],
                [
                    'category' => $service['category'],
                    'price' => $service['price'],
                    'is_active' => true,
                ],
            );
        }
    }
}
