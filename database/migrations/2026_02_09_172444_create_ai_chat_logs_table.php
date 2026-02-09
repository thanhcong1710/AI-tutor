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
        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('platform')->index(); // 'telegram', 'web', 'discord'
            $table->string('platform_chat_id')->nullable()->index(); // Telegram Chat ID or Session ID
            $table->string('role'); // 'user' or 'assistant'
            $table->text('message');
            $table->json('metadata')->nullable(); // Store extra info like tokens, model used
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
    }
};
