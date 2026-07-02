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
            // Add new resolution tracking columns
            if (!Schema::hasColumn('disputes', 'buyer_payout')) {
                $table->decimal('buyer_payout', 10, 2)->nullable()->after('refund_amount');
            }
            if (!Schema::hasColumn('disputes', 'seller_payout')) {
                $table->decimal('seller_payout', 10, 2)->nullable()->after('buyer_payout');
            }
            if (!Schema::hasColumn('disputes', 'resolution_notes')) {
                $table->longText('resolution_notes')->nullable()->after('admin_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropColumn(['buyer_payout', 'seller_payout', 'resolution_notes']);
        });
    }
};
