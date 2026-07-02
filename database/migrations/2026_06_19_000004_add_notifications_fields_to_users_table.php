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
            $table->boolean('notify_updates')->default(true);
            $table->boolean('notify_marketing')->default(true);
            $table->boolean('notify_messages')->default(true);
            $table->boolean('notify_feedback')->default(true);
            $table->boolean('notify_discounts')->default(true);
            $table->boolean('notify_favorites')->default(true);
            $table->boolean('notify_new_items')->default(true);
            $table->string('notify_daily_limit')->default('Up to 2 notifications');
            $table->boolean('notify_email')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_updates',
                'notify_marketing',
                'notify_messages',
                'notify_feedback',
                'notify_discounts',
                'notify_favorites',
                'notify_new_items',
                'notify_daily_limit',
                'notify_email'
            ]);
        });
    }
};
