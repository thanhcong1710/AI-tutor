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
        Schema::create('lesson_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_segment_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer', 'essay'])->default('multiple_choice');
            $table->text('question');
            $table->json('options')->nullable(); // For multiple choice: ["A", "B", "C", "D"]
            $table->text('correct_answer');
            $table->text('explanation')->nullable(); // Why this is the correct answer
            $table->integer('points')->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->timestamps();

            $table->index(['lesson_segment_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_questions');
    }
};
