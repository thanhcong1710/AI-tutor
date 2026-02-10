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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'total_tokens_used')) {
                $table->unsignedBigInteger('total_tokens_used')->default(0)->after('language');
                $table->unsignedBigInteger('tokens_used_this_month')->default(0)->after('total_tokens_used');
                $table->date('tokens_reset_date')->nullable()->after('tokens_used_this_month');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_tokens_used', 'tokens_used_this_month', 'tokens_reset_date']);
        });
    }
};
