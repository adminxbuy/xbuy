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
        Schema::create('seller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('shop_name');
            $table->string('shop_slug')->unique();
            $table->text('shop_description')->nullable();
            $table->string('shop_city');
            $table->string('shop_state');
            $table->string('shop_pincode', 10);
            $table->text('aadhaar_number')->nullable();
            $table->text('pan_number')->nullable();
            $table->text('bank_account_number')->nullable();
            $table->text('bank_ifsc')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->text('upi_id')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('aadhaar_front_image')->nullable();
            $table->string('aadhaar_back_image')->nullable();
            $table->string('pan_image')->nullable();
            $table->enum('kyc_status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->string('kyc_rejection_reason')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'banned'])->default('pending');
            $table->enum('badge_level', ['basic', 'verified', 'fulfilled'])->default('basic');
            $table->boolean('shop_visit_verified')->default(false);
            $table->timestamp('shop_visit_date')->nullable();
            $table->string('razorpay_linked_account_id')->nullable();
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('pending_payout', 12, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status', 'badge_level', 'kyc_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_profiles');
    }
};
