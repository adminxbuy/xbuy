<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ListingNumberService
{
    /**
     * Category slug → code mapping.
     */
    private static array $categoryMap = [
        'gpu'          => 'GPU',
        'cpu'          => 'CPU',
        'motherboard'  => 'MOB',
        'ram'          => 'RAM',
        'storage'      => 'STG',
        'psu'          => 'PSU',
        'cabinet'      => 'CAB',
        'peripheral'   => 'PER',
        'full_build'   => 'BLD',
        'cooling'      => 'CLG',
        'networking'   => 'NET',
        'cables'       => 'CBL',
        'other'        => 'OTH',
    ];

    /**
     * Generate a unique listing number.
     *
     * Format: XB-{CODE}-{YEAR}-{5_DIGIT_SERIAL}
     * Example: XB-GPU-2026-00001
     *
     * Must be called inside a DB transaction with a lock to avoid race conditions.
     */
    public static function generate(string $categorySlug): string
    {
        $code = self::$categoryMap[strtolower($categorySlug)] ?? 'OTH';
        $year = date('Y');
        $prefix = "XB-{$code}-{$year}-";

        // Find the highest serial for this category + year (lock row for update)
        $last = DB::table('listings')
            ->where('listing_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(listing_number, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
            ->value('listing_number');

        if ($last) {
            $lastSerial = (int) substr($last, strlen($prefix));
            $nextSerial = $lastSerial + 1;
        } else {
            $nextSerial = 1;
        }

        $serial = str_pad($nextSerial, 5, '0', STR_PAD_LEFT);

        return "{$prefix}{$serial}";
    }

    /**
     * Get the category code for a given slug.
     */
    public static function getCategoryCode(string $categorySlug): string
    {
        return self::$categoryMap[strtolower($categorySlug)] ?? 'OTH';
    }
}
