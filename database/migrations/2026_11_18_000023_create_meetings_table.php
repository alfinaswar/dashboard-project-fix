<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->dateTime('meeting_date')->nullable();
            $table->string('attendees')->nullable();
            $table->text('notes')->nullable();
            $table->text('action_items')->nullable();
            $table->string('color')->default('yellow'); // yellow, green, pink, blue, purple, orange
            $table->string('status')->default('scheduled'); // scheduled, in_progress, action_needed, done
            $table->unsignedInteger('position')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->index('status');
            $table->index('meeting_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
