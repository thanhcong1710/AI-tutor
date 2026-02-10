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
        // Change enum to string to support more statuses like 'published', 'hidden'
        DB::statement("ALTER TABLE lessons MODIFY COLUMN status VARCHAR(20) DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum (Warning: this might fail if data contains new statuses)
        DB::statement("ALTER TABLE lessons MODIFY COLUMN status ENUM('draft', 'processing', 'ready', 'failed') DEFAULT 'draft'");
    }
};
