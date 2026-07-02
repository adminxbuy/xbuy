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
        Schema::create('sold_archive', function (Blueprint $table) {
            $table->id();
            $table->integer('listing_id')->index();
            $table->foreignId('seller_id')->constrained('seller_profiles')->onDelete('restrict');
            $table->string('product_title');
            $table->string('category');
            $table->string('grade');
            $table->string('serial_number');
            $table->decimal('sale_price', 10, 2);
            $table->string('buyer_city')->nullable();
            $table->timestamp('sold_at');
            $table->decimal('final_rating', 3, 2)->nullable();
            $table->enum('rating_type', ['manual', 'auto', 'none'])->default('none');
            $table->boolean('dispute_raised')->default(false);
            $table->string('dispute_outcome')->nullable();
            $table->json('listing_snapshot');
            $table->timestamps();

            $table->index(['seller_id', 'category', 'sold_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sold_archive');
    }
};
