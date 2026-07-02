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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('seller_profiles')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', [
                'gpu', 'cpu', 'motherboard', 'ram', 'storage', 'psu',
                'cabinet', 'peripheral', 'full_build', 'cooling', 'networking',
                'cables', 'other'
            ])->index();
            $table->enum('grade', ['A', 'B', 'C'])->index();
            $table->string('serial_number')->index();
            $table->string('brand')->nullable();
            $table->string('model_name')->nullable();
            $table->text('condition_notes')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->enum('manufacturer_warranty_status', ['active', 'expired', 'none'])->default('none');
            $table->integer('manufacturer_warranty_months')->nullable();
            $table->integer('seller_warranty_months')->nullable()->comment('only for fulfilled badge sellers');
            $table->enum('document_status', ['full', 'partial', 'none'])->default('none');
            $table->enum('listing_status', ['draft', 'pending_approval', 'active', 'paused', 'sold', 'rejected', 'expired'])->default('draft')->index();
            $table->string('rejection_reason')->nullable();
            $table->string('shipping_type')->default('prepaid');
            $table->decimal('shipping_charges', 8, 2)->default(0);
            $table->string('pickup_city');
            $table->string('pickup_state');
            $table->string('pickup_pincode', 10);
            $table->integer('views_count')->default(0);
            $table->integer('wishlist_count')->default(0);
            $table->boolean('trusted_buyers_only')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['category', 'grade', 'listing_status', 'price', 'seller_id', 'serial_number'], 'listings_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
