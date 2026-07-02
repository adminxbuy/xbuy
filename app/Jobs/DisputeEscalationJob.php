<?php

namespace App\Jobs;

use App\Models\Dispute;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DisputeEscalationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running DisputeEscalationJob...');

        // Find open disputes where seller response deadline has passed
        $escalatedDisputes = Dispute::where('status', 'open')
            ->where('seller_response_deadline', '<=', Carbon::now())
            ->get();

        foreach ($escalatedDisputes as $dispute) {
            try {
                $dispute->update([
                    'status' => 'under_review',
                    'auto_escalated_at' => Carbon::now(),
                ]);
                Log::info("Dispute #{$dispute->id} auto-escalated to under_review due to seller deadline.");
            } catch (\Exception $e) {
                Log::error("Failed to auto-escalate dispute #{$dispute->id}: " . $e->getMessage());
            }
        }
    }
}
