<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learning_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // telegram, discord, web, mobile
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->integer('current_segment_id')->nullable();
            $table->integer('total_segments')->default(0);
            $table->integer('completed_segments')->default(0);
            $table->integer('total_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('total_points')->default(0);
            $table->integer('earned_points')->default(0);
            $table->integer('duration_seconds')->default(0); // Total time spent
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('score_percentage', 5, 2)->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['lesson_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_sessions');
    }
};
