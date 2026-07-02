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
        Schema::create('escrow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->decimal('amount_held', 10, 2);
            $table->decimal('seller_amount', 10, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->string('razorpay_transfer_id')->nullable()->index();
            $table->string('razorpay_transfer_status')->nullable();
            $table->enum('status', [
                'held', 'released', 'refunded', 'partially_released', 'disputed', 'failed'
            ])->default('held')->index();
            $table->integer('warranty_days');
            $table->timestamp('delivery_confirmed_at')->nullable();
            $table->timestamp('release_scheduled_at')->nullable()->index();
            $table->timestamp('released_at')->nullable();
            $table->string('released_by')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escrow');
    }
};
