<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\User;
use App\Models\Notification;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_mail_creates_log_automatically()
    {
        $user = User::factory()->create(['email' => 'buyer@example.com', 'phone' => '8888888888']);

        // Set up template in DB
        SiteSetting::create([
            'key' => 'mail_template_auth_otp_subject',
            'value' => 'Your code is {otp}',
        ]);
        SiteSetting::create([
            'key' => 'mail_template_auth_otp_body',
            'value' => '<p>OTP code is {otp}</p>',
        ]);

        // Send a mail
        Notification::sendSystemMail($user, 'auth_otp', ['otp' => '123456']);

        // Assert log entry was created
        $this->assertDatabaseHas('email_logs', [
            'to_email' => 'buyer@example.com',
            'subject' => 'Your code is 123456',
            'template_name' => 'system_notification_auth_otp',
            'status' => 'sent',
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_interact_with_logs_and_templates()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);
        $user = User::factory()->create(['email' => 'test@example.com', 'phone' => '7777777777']);

        // 1. Create a log
        $log = EmailLog::create([
            'to_email' => 'test@example.com',
            'subject' => 'Test Subject',
            'template_name' => 'system_notification_auth_otp',
            'status' => 'sent',
            'user_id' => $user->id,
            'sent_at' => now(),
        ]);

        // 2. Admin lists logs
        $response = $this->actingAs($admin)->getJson('/admin/mail/logs');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data.data');

        // 3. Admin shows log detail
        $detailResponse = $this->actingAs($admin)->getJson("/admin/mail/logs/{$log->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertJsonPath('data.subject', 'Test Subject');

        // 4. Admin lists templates
        $templatesResponse = $this->actingAs($admin)->getJson('/admin/mail/templates');
        $templatesResponse->assertStatus(200);
        $templatesResponse->assertJsonPath('success', true);
        $this->assertNotEmpty($templatesResponse->json('data'));

        // 5. Admin previews template
        $previewResponse = $this->actingAs($admin)->get("/admin/mail/templates?preview_id=auth_otp");
        $previewResponse->assertStatus(200);
        $this->assertStringContainsString('OTP', $previewResponse->content());

        // 6. Admin resends email
        $resendResponse = $this->actingAs($admin)->postJson("/admin/mail/resend/{$log->id}");
        $resendResponse->assertStatus(200);
        $resendResponse->assertJsonPath('success', true);
    }
}
