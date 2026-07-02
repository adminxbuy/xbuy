<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\ListingNumberService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add the listing_number column (nullable first, so existing rows don't break)
        Schema::table('listings', function (Blueprint $table) {
            $table->string('listing_number', 30)->nullable()->unique()->after('id');
        });

        // 2. Backfill existing rows — assign listing numbers per category+year order
        $listings = DB::table('listings')
            ->whereNull('listing_number')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'category', 'created_at']);

        $categoryMap = [
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

        // Track serial counters per category+year key
        $counters = [];

        foreach ($listings as $listing) {
            $year     = date('Y', strtotime($listing->created_at));
            $code     = $categoryMap[strtolower($listing->category)] ?? 'OTH';
            $key      = "{$code}-{$year}";

            if (!isset($counters[$key])) {
                $counters[$key] = 1;
            } else {
                $counters[$key]++;
            }

            $serial        = str_pad($counters[$key], 5, '0', STR_PAD_LEFT);
            $listingNumber = "XB-{$code}-{$year}-{$serial}";

            DB::table('listings')
                ->where('id', $listing->id)
                ->update(['listing_number' => $listingNumber]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropUnique(['listing_number']);
            $table->dropColumn('listing_number');
        });
    }
};
