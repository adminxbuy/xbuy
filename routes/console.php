<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;
use App\Jobs\EscrowAutoReleaseJob;
use App\Jobs\AutoRatingJob;
use App\Jobs\SellerMetricsJob;
use App\Jobs\DisputeEscalationJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Jobs
Schedule::command('trash:cleanup')->daily();
Schedule::command('app:send-seller-onboarding-emails')->daily();
Schedule::job(new EscrowAutoReleaseJob)->hourly();
Schedule::job(new DisputeEscalationJob)->hourly();
Schedule::job(new AutoRatingJob)->dailyAt('02:00');
Schedule::job(new SellerMetricsJob)->dailyAt('03:00');

// Scheduled Broadcast Mail & Sales Alerts campaigns
Schedule::call(function () {
    $campaigns = \App\Models\MailCampaign::where('status', 'scheduled')
        ->where('scheduled_at', '<=', now())
        ->get();

    foreach ($campaigns as $camp) {
        $camp->update(['status' => 'sending']);
        \App\Http\Controllers\Admin\MailManagementController::executeCampaign($camp);
    }
})->everyMinute();


