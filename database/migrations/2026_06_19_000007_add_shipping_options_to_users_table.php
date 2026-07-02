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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ship_bluedart_pickup')->default(true)->after('address');
            $table->boolean('ship_delhivery_pickup')->default(true)->after('ship_bluedart_pickup');
            $table->boolean('ship_dtdc_pickup')->default(true)->after('ship_delhivery_pickup');
            $table->boolean('ship_delhivery_dropoff')->default(true)->after('ship_dtdc_pickup');
            $table->boolean('ship_bluedart_dropoff')->default(true)->after('ship_delhivery_dropoff');
            $table->boolean('ship_dtdc_dropoff')->default(true)->after('ship_bluedart_dropoff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ship_bluedart_pickup',
                'ship_delhivery_pickup',
                'ship_dtdc_pickup',
                'ship_delhivery_dropoff',
                'ship_bluedart_dropoff',
                'ship_dtdc_dropoff',
            ]);
        });
    }
};
