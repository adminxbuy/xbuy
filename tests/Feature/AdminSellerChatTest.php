<?php

namespace Tests\Feature;

use App\Models\SellerProfile;
use App\Models\AdminSellerMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSellerChatTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $sellerUser;
    private $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'phone' => '9999999999',
            'role' => 'admin',
        ]);

        $this->sellerUser = User::factory()->create([
            'phone' => '9876543210',
            'role' => 'seller',
        ]);

        $this->seller = SellerProfile::create([
            'user_id' => $this->sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
        ]);
    }

    public function test_admin_can_send_chat_message()
    {
        $this->actingAs($this->admin)
            ->postJson("/admin/sellers/{$this->seller->id}/chat", [
                'message' => 'Hello from Admin!',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Hello from Admin!');

        $this->assertDatabaseHas('admin_seller_messages', [
            'seller_profile_id' => $this->seller->id,
            'sender_id' => $this->admin->id,
            'message' => 'Hello from Admin!',
        ]);
    }

    public function test_admin_can_retrieve_chat_messages()
    {
        AdminSellerMessage::create([
            'seller_profile_id' => $this->seller->id,
            'sender_id' => $this->admin->id,
            'message' => 'Message 1',
        ]);

        $this->actingAs($this->admin)
            ->getJson("/admin/sellers/{$this->seller->id}/chat")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Message 1');
    }

    public function test_chats_are_isolated_per_seller()
    {
        $anotherSellerUser = User::factory()->create(['phone' => '9111111111', 'role' => 'seller']);
        $anotherSeller = SellerProfile::create([
            'user_id' => $anotherSellerUser->id,
            'shop_name' => 'Other Shop',
            'shop_slug' => 'other-shop',
            'shop_city' => 'Mumbai',
            'shop_state' => 'Maharashtra',
            'shop_pincode' => '400001',
        ]);

        AdminSellerMessage::create([
            'seller_profile_id' => $this->seller->id,
            'sender_id' => $this->admin->id,
            'message' => 'Secret message for Seller 1',
        ]);

        $this->actingAs($this->admin)
            ->getJson("/admin/sellers/{$anotherSeller->id}/chat")
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
