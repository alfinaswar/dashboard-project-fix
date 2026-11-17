<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('agreements', function (Blueprint $table) { $table->id(); $table->foreignId('client_id')->constrained()->restrictOnDelete(); $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('template_id')->constrained('document_templates')->restrictOnDelete(); $table->string('number')->unique(); $table->string('title'); $table->string('status')->default('draft'); $table->unsignedInteger('current_version')->default(1); $table->string('signed_file_path')->nullable(); $table->date('signed_at')->nullable(); $table->timestamps(); });
        Schema::create('agreement_versions', function (Blueprint $table) { $table->id(); $table->foreignId('agreement_id')->constrained()->cascadeOnDelete(); $table->unsignedInteger('version_number'); $table->json('variables'); $table->longText('body_rendered'); $table->string('pdf_path')->nullable(); $table->string('docx_path')->nullable(); $table->timestamp('created_at')->useCurrent(); $table->unique(['agreement_id','version_number']); });
    }
    public function down(): void { Schema::dropIfExists('agreement_versions'); Schema::dropIfExists('agreements'); }
};
