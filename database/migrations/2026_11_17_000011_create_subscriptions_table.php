<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('subscriptions', function(Blueprint $table){$table->id();$table->foreignId('client_id')->constrained()->restrictOnDelete();$table->foreignId('service_package_id')->constrained()->restrictOnDelete();$table->foreignId('previous_subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();$table->date('start_date');$table->date('end_date');$table->unsignedBigInteger('contract_value');$table->string('status')->default('active');$table->string('freeze_reason')->nullable();$table->timestamp('reminder_30_sent_at')->nullable();$table->timestamp('reminder_14_sent_at')->nullable();$table->timestamp('reminder_7_sent_at')->nullable();$table->timestamp('expired_notified_at')->nullable();$table->text('notes')->nullable();$table->softDeletes();$table->timestamps();$table->index('end_date');}); }
    public function down(): void {Schema::dropIfExists('subscriptions');}
};
