<?php

namespace Database\Seeders;

use App\Models\ServicePackage;
use Illuminate\Database\Seeder;

class ServicePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Maintenance Web Tahunan',
                'description' => 'Layanan perawatan website, backup bulanan, update plugin dan keamanan selama 1 tahun.',
                'default_price' => 3600000,
                'default_duration_months' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Hosting & Domain Tahunan',
                'description' => 'Paket sewa server cloud dan domain .com/.id selama 1 tahun.',
                'default_price' => 1800000,
                'default_duration_months' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($packages as $pkg) {
            ServicePackage::updateOrCreate(
                ['name' => $pkg['name']],
                $pkg
            );
        }
    }
}
