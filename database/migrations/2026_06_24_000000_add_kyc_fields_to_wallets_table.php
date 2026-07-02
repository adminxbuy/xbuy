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
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('dob_day')->nullable();
            $table->string('dob_month')->nullable();
            $table->integer('dob_year')->nullable();
            $table->string('ssn_last_four')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'dob_day', 'dob_month', 'dob_year', 'ssn_last_four']);
        });
    }
};
