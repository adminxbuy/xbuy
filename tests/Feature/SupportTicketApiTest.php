<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SupportTicketApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_user_can_create_list_and_view_ticket()
    {
        $user = \App\Models\User::factory()->create(['phone' => '8888888888']);

        // 1. Create ticket
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/support/tickets', [
            'subject' => 'Cannot checkout',
            'message' => 'The payment button is not working.',
            'priority' => 'high'
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $ticketId = $response->json('data.id');

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticketId,
            'user_id' => $user->id,
            'subject' => 'Cannot checkout',
            'priority' => 'high'
        ]);

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticketId,
            'sender_id' => $user->id,
            'message' => 'The payment button is not working.'
        ]);

        // 2. List tickets
        $listResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/support/tickets');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonCount(1, 'data.data');

        // 3. Show ticket
        $showResponse = $this->actingAs($user, 'sanctum')->getJson("/api/v1/support/tickets/{$ticketId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.subject', 'Cannot checkout');
    }

    public function test_user_can_reply_to_ticket()
    {
        $user = \App\Models\User::factory()->create(['phone' => '8888888888']);
        $ticket = \App\Models\SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Issue',
            'message' => 'First message',
            'status' => 'closed',
            'priority' => 'low'
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/support/tickets/{$ticket->id}/reply", [
            'message' => 'Still have issues',
            'attachments' => ['https://link.to/image.png']
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'sender_id' => $user->id,
            'message' => 'Still have issues'
        ]);

        $ticket->refresh();
        $this->assertEquals('open', $ticket->status); // status should revert to open
    }

    public function test_admin_can_assign_and_close_ticket()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'phone' => '8888888888']);
        $user = \App\Models\User::factory()->create(['phone' => '7777777777']);
        $ticket = \App\Models\SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Issue',
            'message' => 'First message',
            'status' => 'open',
            'priority' => 'low'
        ]);

        // 1. Assign ticket
        $assignResponse = $this->actingAs($admin, 'sanctum')->putJson("/api/admin/support/tickets/{$ticket->id}/assign", [
            'assigned_to' => $admin->id
        ]);
        $assignResponse->assertStatus(200);
        $ticket->refresh();
        $this->assertEquals($admin->id, $ticket->assigned_to);
        $this->assertEquals('in_progress', $ticket->status);

        // 2. Close ticket
        $closeResponse = $this->actingAs($admin, 'sanctum')->putJson("/api/admin/support/tickets/{$ticket->id}/close");
        $closeResponse->assertStatus(200);
        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
    }
}
