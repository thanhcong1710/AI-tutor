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
            $table->enum('role', ['student', 'teacher', 'admin'])->default('student')->after('email');
            $table->string('platform')->nullable()->after('role'); // telegram, discord, web, mobile
            $table->string('platform_id')->nullable()->after('platform'); // telegram_user_id, discord_user_id...
            $table->string('phone')->nullable()->after('platform_id');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('subscription_type', ['free', 'premium'])->default('free')->after('date_of_birth');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_type');
            $table->integer('free_minutes_used_today')->default(0)->after('subscription_expires_at');
            $table->date('free_minutes_reset_date')->nullable()->after('free_minutes_used_today');
            $table->json('preferences')->nullable()->after('free_minutes_reset_date'); // language, voice, speed...
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'platform',
                'platform_id',
                'phone',
                'date_of_birth',
                'subscription_type',
                'subscription_expires_at',
                'free_minutes_used_today',
                'free_minutes_reset_date',
                'preferences',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
