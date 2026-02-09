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
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('learning_sessions')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('lesson_questions')->onDelete('cascade');
            $table->text('student_answer');
            $table->string('answer_audio_url')->nullable(); // If student answered via voice
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->text('ai_feedback')->nullable(); // AI-generated feedback
            $table->json('ai_evaluation')->nullable(); // Detailed AI evaluation (grammar, logic, etc.)
            $table->integer('attempt_number')->default(1);
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->index(['session_id', 'question_id']);
            $table->index(['question_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
