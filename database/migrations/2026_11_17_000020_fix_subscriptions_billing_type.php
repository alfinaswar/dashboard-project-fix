<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Langganan kini memakai jenis langganan (billing_type), bukan master paket layanan.
 * Kolom service_package_id dilepas, kolom billing_type ditambahkan.
 * Aman dijalankan berulang: setiap langkah dicek dulu keberadaan kolomnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('subscriptions', 'service_package_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropForeign(['service_package_id']);
                $table->dropColumn('service_package_id');
            });
        }

        if (! Schema::hasColumn('subscriptions', 'billing_type')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->enum('billing_type', ['one_time', 'annual', 'monthly'])->default('annual')->after('client_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subscriptions', 'billing_type')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('billing_type');
            });
        }

        if (! Schema::hasColumn('subscriptions', 'service_package_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->foreignId('service_package_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            });
        }
    }
};
