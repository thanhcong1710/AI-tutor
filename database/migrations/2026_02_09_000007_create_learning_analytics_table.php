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
        Schema::create('learning_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('subject');
            $table->string('level');
            $table->integer('total_sessions')->default(0);
            $table->integer('completed_sessions')->default(0);
            $table->integer('total_questions_answered')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->integer('total_time_spent_minutes')->default(0);
            $table->integer('current_streak_days')->default(0);
            $table->integer('longest_streak_days')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->json('strengths')->nullable(); // Topics student is good at
            $table->json('weaknesses')->nullable(); // Topics student needs improvement
            $table->json('learning_pace')->nullable(); // Fast, medium, slow for different topics
            $table->timestamps();

            $table->unique(['student_id', 'subject', 'level']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_analytics');
    }
};
