<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill existing orders with the clean ORD-{YEAR}-{SERIAL} format.
     * The order_number column already exists; we just update its values.
     */
    public function up(): void
    {
        // Fetch all orders ordered by created_at to assign serials chronologically per year
        $orders = DB::table('orders')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'order_number', 'created_at']);

        // Track serial counters per year
        $counters = [];

        foreach ($orders as $order) {
            $year = date('Y', strtotime($order->created_at));

            if (!isset($counters[$year])) {
                $counters[$year] = 1;
            } else {
                $counters[$year]++;
            }

            $serial      = str_pad($counters[$year], 5, '0', STR_PAD_LEFT);
            $orderNumber = "ORD-{$year}-{$serial}";

            DB::table('orders')
                ->where('id', $order->id)
                ->update(['order_number' => $orderNumber]);
        }
    }

    /**
     * Reverse the migrations — nothing to revert schema-wise.
     */
    public function down(): void
    {
        // No schema changes to undo
    }
};
