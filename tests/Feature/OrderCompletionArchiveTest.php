<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Order;
use App\Models\SellerProfile;
use App\Models\SoldArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderCompletionArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_completion_triggers_listing_sold_and_sold_archive_copy()
    {
        $sellerUser = User::factory()->create(['phone' => '9876543210', 'role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'status' => 'active',
        ]);
        $sellerUser->role = 'seller';
        $sellerUser->save();

        $buyer = User::factory()->create(['phone' => '1234567890']);

        $listing = Listing::create([
            'seller_id' => $seller->id,
            'title' => 'Vintage Keyboard',
            'slug' => 'vintage-keyboard',
            'category' => 'peripheral',
            'grade' => 'A',
            'price' => 5000,
            'serial_number' => 'KB9876',
            'pickup_city' => 'Delhi',
            'pickup_state' => 'Delhi',
            'pickup_pincode' => '110001',
            'listing_status' => 'active',
        ]);

        $order = Order::create([
            'order_number' => 'ORD12345',
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_amount' => 5000,
            'shipping_amount' => 100,
            'total_amount' => 5100,
            'commission_percent' => 5,
            'commission_amount' => 250,
            'seller_payout_amount' => 4750,
            'order_status' => 'testing_period',
            'delivery_address' => ['city' => 'Mumbai'],
        ]);

        // Complete the order
        $order->update(['order_status' => 'completed']);

        // Check if listing status changed to sold
        $listing->refresh();
        $this->assertEquals('sold', $listing->listing_status);

        // Check if sold_archive record was created
        $archive = SoldArchive::where('listing_id', $listing->id)->first();
        $this->assertNotNull($archive);
        $this->assertEquals('Vintage Keyboard', $archive->product_title);
        $this->assertEquals(5000, $archive->sale_price);
        $this->assertEquals('Mumbai', $archive->buyer_city);
        $this->assertNotNull($archive->listing_snapshot);

        // Check public list API: should be removed
        $responseList = $this->getJson('/api/listings');
        $responseList->assertStatus(200);
        $this->assertEmpty(collect($responseList->json('data.data'))->where('slug', 'vintage-keyboard'));

        // Check public detail API: should return 404
        $responseDetail = $this->getJson('/api/listings/vintage-keyboard');
        $responseDetail->assertStatus(404);

        // Check seller sold-archive endpoint
        Sanctum::actingAs($sellerUser);
        $responseSold = $this->getJson('/api/seller/sold-archive');
        $responseSold->assertStatus(200);
        $responseSold->assertJsonCount(1, 'data.data');
        $this->assertEquals('Vintage Keyboard', $responseSold->json('data.data.0.product_title'));
    }
}
