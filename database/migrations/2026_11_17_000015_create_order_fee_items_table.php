<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->bigInteger('amount')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Order lama: platform_fee tunggal jadi satu baris potongan agar tidak ada angka hilang.
        \Illuminate\Support\Facades\DB::table('orders')
            ->where('platform_fee', '>', 0)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->each(function ($order) {
                \Illuminate\Support\Facades\DB::table('order_fee_items')->insert([
                    'order_id' => $order->id,
                    'label' => 'Potongan platform',
                    'amount' => $order->platform_fee,
                    'position' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_fee_items');
    }
};
