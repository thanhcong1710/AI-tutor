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
        Schema::table('ai_chat_logs', function (Blueprint $table) {
            $table->integer('prompt_tokens')->nullable()->after('metadata');
            $table->integer('completion_tokens')->nullable()->after('prompt_tokens');
            $table->integer('total_tokens')->nullable()->after('completion_tokens');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_chat_logs', function (Blueprint $table) {
            $table->dropColumn(['prompt_tokens', 'completion_tokens', 'total_tokens']);
        });
    }
};
