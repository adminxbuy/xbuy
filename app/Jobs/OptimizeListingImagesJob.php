<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Services\ImageOptimizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OptimizeListingImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $listingId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $listing = Listing::with('images')->find($this->listingId);

        if (!$listing) {
            Log::warning("OptimizeListingImagesJob: Listing {$this->listingId} not found.");
            return;
        }

        Log::info("OptimizeListingImagesJob: Starting optimization for listing {$this->listingId} containing " . count($listing->images) . " images.");

        foreach ($listing->images as $image) {
            ImageOptimizer::optimize($image->image_url);
        }

        Log::info("OptimizeListingImagesJob: Finished optimization for listing {$this->listingId}.");
    }
}
