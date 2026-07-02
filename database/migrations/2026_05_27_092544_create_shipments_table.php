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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->string('shiprocket_order_id')->nullable()->index();
            $table->string('shiprocket_shipment_id')->nullable();
            $table->string('awb_number')->nullable()->index();
            $table->string('courier_name')->nullable();
            $table->string('courier_company_id')->nullable();
            $table->string('label_url')->nullable();
            $table->string('manifest_url')->nullable();
            $table->enum('status', [
                'pending', 'label_generated', 'pickup_scheduled', 'picked_up',
                'in_transit', 'out_for_delivery', 'delivered', 'delivery_failed', 'returned'
            ])->default('pending')->index();
            $table->timestamp('pickup_scheduled_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('tracking_events')->nullable();
            $table->timestamp('estimated_delivery_date')->nullable();
            $table->boolean('is_reverse_pickup')->default(false);
            $table->foreignId('parent_shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
