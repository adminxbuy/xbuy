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
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('seller_id')->constrained('categories')->onDelete('set null');
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('categories')->onDelete('set null');
            
            $table->index(['category_id', 'subcategory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['listings_category_id_foreign']);
            $table->dropForeign(['listings_subcategory_id_foreign']);
            $table->dropIndex(['listings_category_id_subcategory_id_index']);
            $table->dropColumn(['category_id', 'subcategory_id']);
        });
    }
};
