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
        Schema::create('lesson_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->string('title');
            $table->longText('content'); // Text content for this segment
            $table->longText('ai_explanation')->nullable(); // AI-generated explanation
            $table->string('audio_url')->nullable(); // TTS audio file URL (S3)
            $table->integer('audio_duration')->nullable(); // seconds
            $table->json('metadata')->nullable(); // Additional info
            $table->timestamps();

            $table->index(['lesson_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_segments');
    }
};
