<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'company_name' => 'Studio Freelance',
            'company_address' => 'Jl. Merdeka No. 10, Jakarta Pusat',
            'company_phone' => '081234567890',
            'company_email' => 'admin@dashboard.local',
            'company_npwp' => '',
            'company_logo' => '',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Alfin',
            'signee_name' => 'Alfin',
            'invoice_due_days' => '14',
            'revision_days' => '14',
            'invoice_number_format' => 'INV/{YYYY}/{MM}/{NUMBER}',
            'agreement_number_format' => 'PKS/{YYYY}/{NUMBER}',
            'app_timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
