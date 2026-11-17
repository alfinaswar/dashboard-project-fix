<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Kolom bisa sudah ada kalau migrasi sempat jalan dengan nama file lama.
        if (Schema::hasColumn('invoices', 'payment_id')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            // Invoice pembayaran: satu invoice per pembayaran masuk, terpisah dari invoice order.
            $table->foreignId('payment_id')->nullable()->unique()->after('order_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropUnique(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
};
