<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            [
                'name' => 'Fastwork',
                'code' => 'fastwork',
                'commission_percent' => 20.00,
                'fixed_fee' => 0,
                'withdrawal_fee' => 10000,
                'notes' => 'Platform freelance Fastwork',
                'is_active' => true,
            ],
            [
                'name' => 'Langsung',
                'code' => 'direct',
                'commission_percent' => 0.00,
                'fixed_fee' => 0,
                'withdrawal_fee' => 0,
                'notes' => 'Klien langsung di luar platform',
                'is_active' => true,
            ],
        ];

        foreach ($platforms as $platform) {
            Platform::updateOrCreate(
                ['code' => $platform['code']],
                $platform
            );
        }
    }
}
