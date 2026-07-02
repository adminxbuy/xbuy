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
        Schema::create('seller_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_profiles')->onDelete('cascade');
            $table->integer('total_orders')->default(0);
            $table->integer('completed_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);
            $table->integer('disputed_orders')->default(0);
            $table->decimal('dispute_rate_percent', 5, 2)->default(0);
            $table->decimal('response_rate_percent', 5, 2)->default(100);
            $table->decimal('avg_response_time_hours', 8, 2)->default(0);
            $table->decimal('cooperation_score', 4, 2)->default(10.00);
            $table->decimal('star_rating', 3, 2)->default(0);
            $table->integer('total_ratings')->default(0);
            $table->decimal('on_time_delivery_rate', 5, 2)->default(100);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'star_rating', 'cooperation_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_metrics');
    }
};
