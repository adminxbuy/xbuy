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
        Schema::create('fraud_flags', function (Blueprint $table) {
            $table->id();
            $table->enum('flag_type', [
                'duplicate_serial',
                'multiple_accounts_same_ip',
                'same_bank_multiple_sellers',
                'rapid_listings',
                'suspicious_buyer_pattern'
            ])->index();
            $table->foreignId('flagged_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('flagged_listing_id')->nullable()->constrained('listings')->onDelete('cascade');
            $table->json('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'dismissed'])->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fraud_flags');
    }
};
