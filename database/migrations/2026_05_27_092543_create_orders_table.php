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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('listing_id')->constrained('listings')->onDelete('restrict');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('seller_id')->constrained('seller_profiles')->onDelete('restrict');
            $table->decimal('product_amount', 10, 2);
            $table->decimal('shipping_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('commission_percent', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('seller_payout_amount', 10, 2);
            $table->string('razorpay_order_id')->nullable()->index();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->enum('order_status', [
                'pending_payment', 'payment_received', 'confirmed',
                'label_generated', 'picked_up', 'in_transit',
                'out_for_delivery', 'delivered', 'testing_period',
                'completed', 'disputed', 'refunded', 'cancelled'
            ])->default('pending_payment')->index();
            $table->json('delivery_address');
            $table->timestamp('warranty_accepted_at')->nullable();
            $table->json('warranty_terms_snapshot')->nullable();
            $table->integer('testing_window_days')->default(2);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('testing_window_ends_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['order_number', 'order_status', 'buyer_id', 'seller_id', 'created_at'], 'orders_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
