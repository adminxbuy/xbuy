<?php

namespace App\Console\Commands;

use App\Models\SellerProfile;
use App\Mail\SellerOnboardingMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendSellerOnboardingEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-seller-onboarding-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send automated onboarding email sequences to newly approved sellers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting seller onboarding email checks...");

        // 1. Day 1 Onboarding: How to create first listing (sent >= 24 hours after approval)
        $day1Sellers = SellerProfile::where('status', 'active')
            ->where('onboarding_step', 1)
            ->where('kyc_approved_at', '<=', now()->subDay())
            ->get();

        foreach ($day1Sellers as $seller) {
            try {
                Mail::to($seller->user->email)->queue(new SellerOnboardingMail(1, $seller->shop_name, $seller->user->id));
                $seller->update(['onboarding_step' => 2]);
                $this->info("Day 1 onboarding queued for seller: {$seller->shop_name}");
            } catch (\Exception $e) {
                Log::error("Failed to send Day 1 onboarding to {$seller->shop_name}: " . $e->getMessage());
            }
        }

        // 2. Day 3 Onboarding: Photos and grading tips (sent >= 3 days after approval)
        $day3Sellers = SellerProfile::where('status', 'active')
            ->where('onboarding_step', 2)
            ->where('kyc_approved_at', '<=', now()->subDays(3))
            ->get();

        foreach ($day3Sellers as $seller) {
            try {
                Mail::to($seller->user->email)->queue(new SellerOnboardingMail(3, $seller->shop_name, $seller->user->id));
                $seller->update(['onboarding_step' => 3]);
                $this->info("Day 3 onboarding queued for seller: {$seller->shop_name}");
            } catch (\Exception $e) {
                Log::error("Failed to send Day 3 onboarding to {$seller->shop_name}: " . $e->getMessage());
            }
        }

        // 3. Day 7 Onboarding: Reminder if no listing created yet (sent >= 7 days after approval)
        $day7Sellers = SellerProfile::where('status', 'active')
            ->where('onboarding_step', 3)
            ->where('kyc_approved_at', '<=', now()->subDays(7))
            ->get();

        foreach ($day7Sellers as $seller) {
            try {
                // Only send Day 7 reminder if they haven't uploaded any listings yet
                $hasListings = $seller->listings()->exists();
                
                if (!$hasListings) {
                    Mail::to($seller->user->email)->queue(new SellerOnboardingMail(7, $seller->shop_name, $seller->user->id));
                    $this->info("Day 7 reminder onboarding queued for seller: {$seller->shop_name}");
                } else {
                    $this->info("Seller {$seller->shop_name} already has listings. Skipping Day 7 reminder.");
                }
                
                $seller->update(['onboarding_step' => 4]); // Complete onboarding
            } catch (\Exception $e) {
                Log::error("Failed to process Day 7 onboarding for {$seller->shop_name}: " . $e->getMessage());
            }
        }

        $this->info("Seller onboarding email checks completed.");
    }
}
