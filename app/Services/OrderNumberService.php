<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    /**
     * Generate a unique order number.
     *
     * Format: ORD-{YEAR}-{5_DIGIT_SERIAL}
     * Example: ORD-2026-00001
     *
     * Must be called inside a DB transaction with a lock to avoid race conditions.
     */
    public static function generate(): string
    {
        $year   = date('Y');
        $prefix = "ORD-{$year}-";

        // Find the highest serial for this year (lock for update)
        $last = DB::table('orders')
            ->where('order_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(order_number, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
            ->value('order_number');

        if ($last) {
            $lastSerial = (int) substr($last, strlen($prefix));
            $nextSerial = $lastSerial + 1;
        } else {
            $nextSerial = 1;
        }

        $serial = str_pad($nextSerial, 5, '0', STR_PAD_LEFT);

        return "{$prefix}{$serial}";
    }
}
