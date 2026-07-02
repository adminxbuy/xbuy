<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\SellerProfile;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Dispute;
use App\Models\Escrow;
use App\Models\SupportTicket;

class CleanupTrashCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete records that have been in the trash for more than 30 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = Carbon::now()->subDays(30);

        $this->info("Starting cleanup of trash older than: {$threshold}");

        $models = [
            SellerProfile::class,
            Listing::class,
            Order::class,
            Dispute::class,
            Escrow::class,
            SupportTicket::class,
        ];

        foreach ($models as $modelClass) {
            $deletedCount = $modelClass::onlyTrashed()
                ->where('deleted_at', '<', $threshold)
                ->forceDelete();

            $this->info("Cleaned up {$deletedCount} records from {$modelClass}.");
        }

        $this->info('Trash cleanup completed successfully.');
    }
}
