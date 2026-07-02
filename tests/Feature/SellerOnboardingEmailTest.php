<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SellerOnboardingEmailTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_seller_approval_sends_day_0_onboarding_email()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'phone' => '8888888888']);
        $user = \App\Models\User::factory()->create(['role' => 'buyer', 'phone' => '7777777777']);
        $seller = \App\Models\SellerProfile::create([
            'user_id' => $user->id,
            'shop_name' => 'Future Store',
            'shop_slug' => 'future-store',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'pending',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)
            ->post("/admin/sellers/{$seller->id}/kyc", [
                'status' => 'approved'
            ]);

        $response->assertRedirect();
        
        $seller->refresh();
        $this->assertEquals('approved', $seller->kyc_status);
        $this->assertEquals('active', $seller->status);
        $this->assertNotNull($seller->kyc_approved_at);
        $this->assertEquals(1, $seller->onboarding_step);

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\SellerOnboardingMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->envelope()->subject === "Welcome to X-Buy! Start Selling PC Components";
        });
    }

    public function test_onboarding_sequence_progression_via_artisan_command()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $user = \App\Models\User::factory()->create(['role' => 'seller', 'phone' => '8888888888']);
        
        // 1. Day 1 test (approved 25 hours ago, step = 1)
        $seller1 = \App\Models\SellerProfile::create([
            'user_id' => $user->id,
            'shop_name' => 'Shop 1',
            'shop_slug' => 'shop-1',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'status' => 'active',
            'kyc_approved_at' => now()->subHours(25),
            'onboarding_step' => 1
        ]);

        // 2. Day 3 test (approved 73 hours ago, step = 2)
        $seller2 = \App\Models\SellerProfile::create([
            'user_id' => $user->id,
            'shop_name' => 'Shop 2',
            'shop_slug' => 'shop-2',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'status' => 'active',
            'kyc_approved_at' => now()->subHours(73),
            'onboarding_step' => 2
        ]);

        // 3. Day 7 test - No listings (approved 8 days ago, step = 3)
        $seller3 = \App\Models\SellerProfile::create([
            'user_id' => $user->id,
            'shop_name' => 'Shop 3',
            'shop_slug' => 'shop-3',
            'shop_city' => 'Delhi',
            'shop_state' => 'Delhi',
            'shop_pincode' => '110001',
            'kyc_status' => 'approved',
            'status' => 'active',
            'kyc_approved_at' => now()->subDays(8),
            'onboarding_step' => 3
        ]);

        $this->artisan('app:send-seller-onboarding-emails')->assertSuccessful();

        $seller1->refresh();
        $seller2->refresh();
        $seller3->refresh();

        $this->assertEquals(2, $seller1->onboarding_step);
        $this->assertEquals(3, $seller2->onboarding_step);
        $this->assertEquals(4, $seller3->onboarding_step);

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\SellerOnboardingMail::class, function ($mail) {
            return $mail->envelope()->subject === "How to Create Your First Listing on X-Buy";
        });

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\SellerOnboardingMail::class, function ($mail) {
            return $mail->envelope()->subject === "Pro-Tips: Take Better Photos & Grade Components Correctly";
        });

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\SellerOnboardingMail::class, function ($mail) {
            return $mail->envelope()->subject === "Need Help Listing Your PC Components on X-Buy?";
        });
    }
}
