<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

use App\Models\SiteSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Staff Earning Auto-Calculation on Order Completion ────────
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderCompletedEvent::class,
            \App\Listeners\StaffEarningCalculationListener::class
        );

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('uploads', function (Request $request) {
            // Limit to 20 uploads per minute per user/IP
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Dynamic SMTP loader
        try {
            if (!app()->runningInConsole() || \Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
                $host = SiteSetting::getVal('smtp_host');
                if (!empty($host)) {
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $host,
                        'mail.mailers.smtp.port' => SiteSetting::getVal('smtp_port', 587),
                        'mail.mailers.smtp.username' => SiteSetting::getVal('smtp_username'),
                        'mail.mailers.smtp.password' => SiteSetting::getVal('smtp_password'),
                        'mail.mailers.smtp.encryption' => SiteSetting::getVal('smtp_encryption') === 'None' ? null : strtolower(SiteSetting::getVal('smtp_encryption')),
                        'mail.from.address' => SiteSetting::getVal('smtp_from_email', 'no-reply@xbuy.in'),
                        'mail.from.name' => SiteSetting::getVal('smtp_from_name', 'X-Buy Escrow Marketplace'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Avoid failing during setup/installation
        }

        // Register Mail Log listeners
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, function ($event) {
            $message = $event->message;
            $toEmail = implode(', ', array_map(fn($addr) => $addr->getAddress(), $message->getTo() ?? []));
            $subject = $message->getSubject();

            $templateName = 'General';
            $orderId = null;
            $userId = null;

            $headers = $message->getHeaders();
            if ($headers->has('X-Template-Name')) {
                $templateName = $headers->get('X-Template-Name')->getBodyAsString();
            }
            if ($headers->has('X-Order-Id')) {
                $orderId = (int) $headers->get('X-Order-Id')->getBodyAsString();
            }
            if ($headers->has('X-User-Id')) {
                $userId = (int) $headers->get('X-User-Id')->getBodyAsString();
            }

            $log = \App\Models\EmailLog::create([
                'to_email' => $toEmail,
                'subject' => $subject,
                'template_name' => $templateName,
                'status' => 'pending',
                'order_id' => $orderId,
                'user_id' => $userId,
            ]);

            $message->getHeaders()->addTextHeader('X-Email-Log-Id', $log->id);
            app()->instance('latest_email_log_id', $log->id);
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSent::class, function ($event) {
            $message = $event->message;
            $headers = $message->getHeaders();
            if ($headers->has('X-Email-Log-Id')) {
                $logId = (int) $headers->get('X-Email-Log-Id')->getBodyAsString();
                $log = \App\Models\EmailLog::find($logId);
                if ($log) {
                    $log->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                }
            }
        });

        app()->terminating(function () {
            if (app()->bound('latest_email_log_id')) {
                $logId = app('latest_email_log_id');
                $log = \App\Models\EmailLog::find($logId);
                if ($log && $log->status === 'pending') {
                    $log->update([
                        'status' => 'failed',
                        'error_message' => 'Connection failed or exception occurred during delivery.',
                    ]);
                }
            }
        });

        // Register Queue failing listener
        \Illuminate\Support\Facades\Queue::failing(function (\Illuminate\Queue\Events\JobFailed $event) {
            $attempts = $event->job->attempts();
            if ($attempts >= 3) {
                \App\Models\AdminAlert::create([
                    'type' => 'system_error',
                    'title' => 'Queue Job Failed Multiple Times',
                    'message' => "Queue job {$event->job->resolveName()} failed after {$attempts} attempts. Error: " . substr($event->exception->getMessage(), 0, 500),
                    'severity' => 'critical',
                ]);
            }
        });
    }
}
