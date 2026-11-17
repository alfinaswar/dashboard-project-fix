<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->string('name'); $table->text('description')->nullable(); $table->date('start_date')->nullable();
            $table->date('target_date')->nullable(); $table->string('status')->default('planning'); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('project_stages', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->string('name');
            $table->unsignedInteger('weight'); $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('status')->default('todo'); $table->date('target_date')->nullable(); $table->date('actual_date')->nullable();
            $table->unsignedInteger('position')->default(0); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type'); $table->unsignedBigInteger('subject_id'); $table->string('action'); $table->json('changes')->nullable(); $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('activity_logs'); Schema::dropIfExists('project_stages'); Schema::dropIfExists('projects'); }
};
