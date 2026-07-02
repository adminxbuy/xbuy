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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('restrict');
            $table->foreignId('seller_id')->constrained('seller_profiles')->onDelete('restrict');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('restrict');
            $table->enum('rating_type', ['manual', 'auto'])->default('manual');
            $table->decimal('item_accuracy', 3, 2);
            $table->decimal('packaging', 3, 2);
            $table->decimal('shipping_speed', 3, 2);
            $table->decimal('communication', 3, 2);
            $table->decimal('weighted_total', 3, 2);
            $table->text('review_text')->nullable();
            $table->string('auto_reason')->nullable();
            $table->timestamp('override_allowed_until')->nullable();
            $table->timestamp('overridden_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['seller_id', 'buyer_id', 'rating_type', 'weighted_total']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
