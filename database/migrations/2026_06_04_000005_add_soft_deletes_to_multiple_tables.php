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
        $tables = [
            'seller_profiles',
            'listings',
            'orders',
            'subscribers',
            'ratings',
            'fraud_flags',
            'admin_alerts',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $tableGroup) {
                    $tableGroup->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'seller_profiles',
            'listings',
            'orders',
            'subscribers',
            'ratings',
            'fraud_flags',
            'admin_alerts',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $tableGroup) {
                    $tableGroup->dropSoftDeletes();
                });
            }
        }
    }
};
