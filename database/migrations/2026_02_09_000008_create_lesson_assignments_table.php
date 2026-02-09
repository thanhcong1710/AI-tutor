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
        Schema::create('lesson_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at');
            $table->timestamp('due_date')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'overdue'])->default('assigned');
            $table->text('teacher_notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['teacher_id', 'lesson_id']);
            $table->unique(['lesson_id', 'student_id']); // One assignment per student per lesson
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_assignments');
    }
};
