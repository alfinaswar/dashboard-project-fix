<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_fee_items', function (Blueprint $table) {
            // amount = nominal rupiah, percent = persen dari nilai kotor order.
            $table->string('amount_type')->default('amount')->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('order_fee_items', function (Blueprint $table) {
            $table->dropColumn('amount_type');
        });
    }
};
