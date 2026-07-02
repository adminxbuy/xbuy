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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('seller_id')->constrained('seller_profiles')->onDelete('restrict');
            $table->enum('dispute_type', [
                'item_not_as_described', 'item_not_working', 'fake_counterfeit',
                'shipping_delay', 'packaging_damage', 'other'
            ]);
            $table->text('description');
            $table->json('evidence_images')->nullable();
            $table->enum('status', ['open', 'seller_responded', 'under_review', 'resolved', 'closed'])->default('open')->index();
            $table->enum('admin_decision', ['pending', 'seller_favor', 'buyer_favor', 'partial'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->enum('fault_assigned_to', ['seller', 'buyer', 'shipping', 'none'])->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->decimal('seller_payout_after_dispute', 10, 2)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('seller_response_deadline')->nullable();
            $table->timestamp('auto_escalated_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status', 'buyer_id', 'seller_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
