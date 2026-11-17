<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penarikan saldo dihapus: order kini hanya melacak lunas atau belum lunas.
        // Tabel penghubung harus dilepas dulu karena foreign key ke withdrawals.
        Schema::dropIfExists('withdrawal_orders');
        Schema::dropIfExists('withdrawals');

        // Biaya penarikan tidak dipakai lagi tanpa fitur penarikan.
        if (Schema::hasColumn('platforms', 'withdrawal_fee')) {
            Schema::table('platforms', function (Blueprint $table) {
                $table->dropColumn('withdrawal_fee');
            });
        }

        if (Schema::hasColumn('orders', 'payout_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('payout_status');
            });
        }
    }

    public function down(): void
    {
        // shortcut: data penarikan lama tidak bisa dipulihkan, perbaiki manual dari backup kalau perlu rollback.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payout_status')->default('pending');
        });

        Schema::table('platforms', function (Blueprint $table) {
            $table->unsignedBigInteger('withdrawal_fee')->default(0);
        });
    }
};
