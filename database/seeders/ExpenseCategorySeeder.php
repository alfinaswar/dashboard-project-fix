<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Server dan Hosting',
            'Domain',
            'Lisensi dan Software',
            'Operasional',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            ExpenseCategory::updateOrCreate(['name' => $name]);
        }
    }
}
