<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notif_new_messages')->default(true);
            $table->boolean('notif_new_likes')->default(true);
            $table->boolean('notif_profile_visits')->default(false);
            $table->boolean('notif_weekly_summaries')->default(false);
            $table->boolean('notif_campaigns')->default(true);
            $table->boolean('notif_in_app_sounds')->default(true);
            $table->boolean('notif_vibration')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notif_new_messages',
                'notif_new_likes',
                'notif_profile_visits',
                'notif_weekly_summaries',
                'notif_campaigns',
                'notif_in_app_sounds',
                'notif_vibration'
            ]);
        });
    }
};
