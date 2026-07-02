<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
        \App\Models\SiteSetting::create([
            'key' => 'commission_rates',
            'value' => json_encode(['gpu' => 5.0]),
            'type' => 'json',
            'group' => 'rates',
        ]);
        
        \App\Models\SiteSetting::create([
            'key' => 'testing_windows',
            'value' => json_encode(['gpu' => 3]),
            'type' => 'json',
            'group' => 'policy',
        ]);
    }

    public function test_can_retrieve_wallet_balance()
    {
        $user = User::factory()->create(['phone' => '9999999999']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/wallet/balance');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.balance', 0);
    }

    public function test_can_retrieve_wallet_transactions()
    {
        $user = User::factory()->create(['phone' => '9999999999']);
        $wallet = $user->getWalletInstance();
        
        $wallet->credit(100.00, 'referral', null, 'Referral credit');
        $wallet->debit(40.00, 'order_payment', null, 'Order payment');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/wallet/transactions');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'data.data');
        $this->assertEquals('debit', $response->json('data.data.0.type'));
        $this->assertEquals('credit', $response->json('data.data.1.type'));
    }

    public function test_admin_can_manually_credit_wallet()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '8888888888']);
        $user = User::factory()->create(['phone' => '9999999999']);

        Sanctum::actingAs($admin);

        $response = $this->putJson('/api/admin/wallet/credit', [
            'user_id' => $user->id,
            'amount' => 500.50,
            'description' => 'Dispute settlement'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.wallet_balance', 500.50);

        $this->assertDatabaseHas('wallet_transactions', [
            'type' => 'credit',
            'amount' => 500.50,
            'source' => 'admin_credit',
            'description' => 'Dispute settlement'
        ]);

        $this->assertEquals(500.50, (float) $user->getWalletInstance()->balance);
    }

    public function test_checkout_can_fully_pay_via_wallet()
    {
        $buyer = User::factory()->create(['phone' => '9999999999']);
        $wallet = $buyer->getWalletInstance();
        $wallet->credit(10000.00, 'admin_credit', null, 'Preloading wallet');

        $sellerUser = User::factory()->create(['phone' => '8888888888', 'role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop A',
            'shop_slug' => 'shop-a',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);

        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Fast GPU',
            'slug' => 'fast-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 5000,
            'shipping_charges' => 200,
            'serial_number' => 'SN12345',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson('/api/buyer/checkout', [
            'listing_id' => $listing->id,
            'use_wallet' => true,
            'delivery_address' => [
                'name' => 'John Doe',
                'phone' => '9999999999',
                'street' => '123 Main St',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pincode' => '110001'
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        
        $order = Order::find($response->json('data.id'));
        $this->assertEquals('payment_received', $order->order_status);
        $this->assertEquals(5200.00, (float) $order->wallet_amount_applied);

        $wallet->refresh();
        $this->assertEquals(4800.00, (float) $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => 5200.00,
            'source' => 'order_payment',
            'reference_id' => $order->id
        ]);

        $this->assertEquals('sold', $listing->fresh()->listing_status);
    }

    public function test_checkout_can_partially_pay_via_wallet()
    {
        $buyer = User::factory()->create(['phone' => '9999999999']);
        $wallet = $buyer->getWalletInstance();
        $wallet->credit(2000.00, 'admin_credit', null, 'Preloading wallet');

        $sellerUser = User::factory()->create(['phone' => '8888888888', 'role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop A',
            'shop_slug' => 'shop-a',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);

        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Fast GPU',
            'slug' => 'fast-gpu',
            'category' => 'gpu',
            'grade' => 'A',
            'price' => 5000,
            'shipping_charges' => 200,
            'serial_number' => 'SN12345',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson('/api/buyer/checkout', [
            'listing_id' => $listing->id,
            'use_wallet' => true,
            'delivery_address' => [
                'name' => 'John Doe',
                'phone' => '9999999999',
                'street' => '123 Main St',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pincode' => '110001'
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        
        $order = Order::find($response->json('data.id'));
        $this->assertEquals('pending_payment', $order->order_status);
        $this->assertEquals(2000.00, (float) $order->wallet_amount_applied);

        $wallet->refresh();
        $this->assertEquals(0.00, (float) $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => 2000.00,
            'source' => 'order_payment',
            'reference_id' => $order->id
        ]);

        $this->assertEquals('active', $listing->fresh()->listing_status);
    }
}
