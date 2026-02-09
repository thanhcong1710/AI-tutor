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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('subject', ['english', 'math', 'logic', 'science', 'history'])->default('english');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('estimated_duration')->default(30); // minutes
            $table->string('original_file_path')->nullable(); // PDF, DOCX, PPT uploaded by teacher
            $table->string('original_file_type')->nullable(); // pdf, docx, ppt
            $table->longText('content')->nullable(); // Extracted text content
            $table->json('metadata')->nullable(); // Additional info: page count, word count...
            $table->enum('status', ['draft', 'processing', 'ready', 'failed'])->default('draft');
            $table->text('processing_error')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['teacher_id', 'status']);
            $table->index(['subject', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
