<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained()->restrictOnDelete();
            $table->date('withdrawn_at');
            $table->unsignedBigInteger('amount_received');
            $table->unsignedBigInteger('fee')->default(0);
            $table->string('bank_name');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('withdrawal_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->unique(['withdrawal_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_orders');
        Schema::dropIfExists('withdrawals');
    }
};
