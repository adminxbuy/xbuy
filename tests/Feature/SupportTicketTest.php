<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed site settings for tickets
        SiteSetting::create([
            'key' => 'ticket_admin_email',
            'value' => 'contact@x-buy.in',
            'type' => 'string',
            'group' => 'tickets'
        ]);

        SiteSetting::create([
            'key' => 'ticket_email_notifications_enabled',
            'value' => '1',
            'type' => 'boolean',
            'group' => 'tickets'
        ]);

        SiteSetting::create([
            'key' => 'ticket_whatsapp_notifications_enabled',
            'value' => '1',
            'type' => 'boolean',
            'group' => 'tickets'
        ]);

        SiteSetting::create([
            'key' => 'ticket_whatsapp_number',
            'value' => '+919999999999',
            'type' => 'string',
            'group' => 'tickets'
        ]);
    }

    public function test_buyer_can_create_support_ticket()
    {
        Mail::fake();
        Log::shouldReceive('info')->once();

        $buyer = User::factory()->create(['role' => 'buyer', 'phone' => '9999999999']);

        $response = $this->actingAs($buyer, 'sanctum')->postJson('/api/buyer/tickets', [
            'subject' => 'Payment Failed',
            'message' => 'My payment went through but the order is still pending.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.subject', 'Payment Failed');

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $buyer->id,
            'subject' => 'Payment Failed',
            'status' => 'open'
        ]);

        Mail::assertSent(\App\Mail\SupportTicketRaised::class, function ($mail) {
            return $mail->hasTo('contact@x-buy.in') && 
                   $mail->ticket->subject === 'Payment Failed';
        });
    }

    public function test_admin_can_resolve_support_ticket()
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'phone' => '9999999999']);
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '8888888888']);

        $ticket = SupportTicket::create([
            'user_id' => $buyer->id,
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'status' => 'open'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tickets.status', $ticket->id), [
            'status' => 'resolved'
        ]);

        $response->assertStatus(302);
        $this->assertEquals('resolved', $ticket->fresh()->status);
    }

    public function test_admin_can_view_support_tickets_list()
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'phone' => '9999999999']);
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '8888888888']);

        $ticket = SupportTicket::create([
            'user_id' => $buyer->id,
            'subject' => 'Payment Issue',
            'message' => 'Cannot pay for order.',
            'status' => 'open'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.tickets'));

        $response->assertStatus(200);
        $response->assertSee('Payment Issue');
        $response->assertSee($buyer->name);
    }
}
