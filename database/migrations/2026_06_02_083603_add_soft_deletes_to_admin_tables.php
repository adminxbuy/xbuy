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
        Schema::table('disputes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('escrow', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('escrow', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
