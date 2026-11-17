<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_fee_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['platform_id', 'effective_from']);
        });

        Schema::create('platform_fee_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_fee_rule_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('value', 14, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('platform_fee_rule_id')->nullable()->after('platform_fee')->constrained()->nullOnDelete();
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->string('billing_type')->default('one_time')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            $table->dropColumn('billing_type');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('platform_fee_rule_id');
        });

        Schema::dropIfExists('platform_fee_components');
        Schema::dropIfExists('platform_fee_rules');
    }
};
