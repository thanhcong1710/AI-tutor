<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to avoid doctrine/dbal dependency for ENUM changes
        DB::statement("ALTER TABLE learning_sessions MODIFY COLUMN status ENUM('in_progress', 'completed', 'abandoned', 'paused') NOT NULL DEFAULT 'in_progress'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            // Revert back without 'paused'
            // This might fail if there are 'paused' records, so handle with care or just leave it.
            // For safety, we can map 'paused' back to 'abandoned' before altering? 
            // DB::table('learning_sessions')->where('status', 'paused')->update(['status' => 'abandoned']);

            $table->enum('status', ['in_progress', 'completed', 'abandoned'])
                ->default('in_progress')
                ->change();
        });
    }
};
