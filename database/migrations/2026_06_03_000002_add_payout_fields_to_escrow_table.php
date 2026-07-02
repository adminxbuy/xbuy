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
        Schema::table('escrow', function (Blueprint $table) {
            $table->string('payout_status')->default('pending')->after('status'); // pending, success, failed, none
            $table->string('razorpay_payout_id')->nullable()->after('payout_status');
            $table->text('payout_error_message')->nullable()->after('razorpay_payout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escrow', function (Blueprint $table) {
            $table->dropColumn(['payout_status', 'razorpay_payout_id', 'payout_error_message']);
        });
    }
};
