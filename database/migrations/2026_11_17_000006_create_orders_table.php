<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('platform_id')->constrained()->restrictOnDelete();
            $table->enum('billing_type', ['one_time' => 'Sekali beli', 'annual' => 'Tahunan', 'monthly' => 'Bulanan'])->default('one_time');
            $table->string('title');
            $table->string('external_order_ref')->nullable();
            $table->date('ordered_at');
            $table->date('deadline')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('revision_count')->default(0);
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->unsignedBigInteger('platform_fee')->default(0);
            $table->string('platform_fee_override_reason')->nullable();
            $table->unsignedBigInteger('other_fee')->default(0);
            $table->string('other_fee_note')->nullable();
            $table->bigInteger('net_amount')->default(0);
            $table->string('payout_status')->default('pending');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('status');
            $table->index('payout_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};