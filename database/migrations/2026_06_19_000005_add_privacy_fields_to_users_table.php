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
            $table->boolean('privacy_feature_marketing')->default(true);
            $table->boolean('privacy_notify_favorites')->default(true);
            $table->boolean('privacy_personalize_feed')->default(true);
            $table->boolean('privacy_recently_viewed')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'privacy_feature_marketing',
                'privacy_notify_favorites',
                'privacy_personalize_feed',
                'privacy_recently_viewed'
            ]);
        });
    }
};
