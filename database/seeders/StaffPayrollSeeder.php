<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StaffProfile;
use App\Models\StaffEarning;
use App\Models\Order;
use App\Models\Listing;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffPayrollSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure global payroll settings exist
        $settings = [
            'staff_commission_percent' => '10',
            'staff_min_cap' => '5',
            'staff_max_cap' => '100',
            'staff_platform_expense_per_order' => '15',
            'staff_payment_day' => '5',
            'staff_amendment_trigger_orders' => '15', // low trigger value for testing alert badge
        ];
        foreach ($settings as $k => $v) {
            SiteSetting::updateOrCreate(['key' => $k], [
                'value' => $v,
                'type' => 'integer',
                'group' => 'payroll',
            ]);
        }

        // 2. Setup Superadmin staff profile
        $mainAdmin = User::where('email', 'x-buy@admin.in')->first();
        if ($mainAdmin) {
            $mainAdmin->update([
                'is_staff' => true,
                'staff_role' => 'superadmin',
            ]);

            StaffProfile::updateOrCreate(['user_id' => $mainAdmin->id], [
                'designation' => 'Superadmin Executive',
                'appointment_date' => Carbon::now()->subMonths(12),
                'bank_account' => '1234567890',
                'upi_id' => 'x-buy@oksbi',
                'aadhaar_number' => '1234-5678-9012',
                'pan_number' => 'ABCDE1234F',
                'appointment_letter_ref' => 'REF/2025/SA-001',
                'status' => 'active',
            ]);
        }

        // 3. Create three staff members
        $staffMembers = [
            [
                'name' => 'Rajesh Kumar',
                'email' => 'rajesh@xbuy.in',
                'admin_role' => 'operations',
                'designation' => 'Operations Manager',
                'bank_account' => '98765432101',
                'upi_id' => 'rajesh@okhdfc',
                'aadhaar_number' => '1111-2222-3333',
                'pan_number' => 'FGHJK5678I',
                'appointment_letter_ref' => 'REF/2025/OPS-042',
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya@xbuy.in',
                'admin_role' => 'finance',
                'designation' => 'Finance Analyst',
                'bank_account' => '112233445566',
                'upi_id' => 'priya@okicici',
                'aadhaar_number' => '4444-5555-6666',
                'pan_number' => 'LMNOP1234E',
                'appointment_letter_ref' => 'REF/2025/FIN-089',
            ],
            [
                'name' => 'Amit Patel',
                'email' => 'amit@xbuy.in',
                'admin_role' => 'support',
                'designation' => 'Customer Operations Lead',
                'bank_account' => '998877665544',
                'upi_id' => 'amit@okaxis',
                'aadhaar_number' => '7777-8888-9999',
                'pan_number' => 'QRSTU4321A',
                'appointment_letter_ref' => 'REF/2026/SUP-012',
            ],
        ];

        $users = [];
        foreach ($staffMembers as $staff) {
            $user = User::updateOrCreate(['email' => $staff['email']], [
                'name' => $staff['name'],
                'phone' => '98765' . rand(10000, 99999),
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'admin_role' => $staff['admin_role'],
                'is_staff' => true,
                'staff_role' => 'staff',
                'status' => 'active',
                'is_email_verified' => true,
                'is_phone_verified' => true,
            ]);

            StaffProfile::updateOrCreate(['user_id' => $user->id], [
                'designation' => $staff['designation'],
                'appointment_date' => Carbon::now()->subMonths(6),
                'bank_account' => $staff['bank_account'],
                'upi_id' => $staff['upi_id'],
                'aadhaar_number' => $staff['aadhaar_number'],
                'pan_number' => $staff['pan_number'],
                'appointment_letter_ref' => $staff['appointment_letter_ref'],
                'status' => 'active',
            ]);

            $users[] = $user;
        }

        // 4. Create dummy listings and completed orders if we don't have enough completed orders
        $completedOrdersCount = Order::where('order_status', 'completed')->count();
        if ($completedOrdersCount < 10) {
            $buyer = User::firstOrCreate(['email' => 'buyer@xbuy.in'], [
                'name' => 'Test Buyer',
                'phone' => '98765' . rand(10000, 99999),
                'password' => Hash::make('password123'),
                'role' => 'buyer',
            ]);

            $listing = Listing::inRandomOrder()->first();
            if (!$listing) {
                $seller = User::firstOrCreate(['email' => 'seller@xbuy.in'], [
                    'name' => 'Test Seller',
                    'phone' => '98765' . rand(10000, 99999),
                    'password' => Hash::make('password123'),
                    'role' => 'seller',
                ]);

                $listing = Listing::create([
                    'seller_id' => $seller->id,
                    'title' => 'GeForce RTX 4090 GPU',
                    'slug' => 'geforce-rtx-4090-gpu-' . strtolower(\Illuminate\Support\Str::random(5)),
                    'description' => 'Fully tested and clean GPU in working condition.',
                    'category' => 'gpu',
                    'brand' => 'NVIDIA',
                    'model_name' => 'RTX 4090',
                    'price' => 150000.00,
                    'listing_status' => 'active',
                    'serial_number' => 'SN-' . strtoupper(\Illuminate\Support\Str::random(12)),
                    'grade' => 'A',
                    'pickup_city' => 'Bangalore',
                    'pickup_state' => 'Karnataka',
                    'pickup_pincode' => '560001',
                    'pickup_address' => 'Test pickup address',
                ]);
            }

            // Create 20 completed orders spread across past 3 months
            for ($i = 0; $i < 20; $i++) {
                $completedAt = Carbon::now()->subDays(rand(1, 75)); // spread over ~2.5 months
                $productAmount = (float) $listing->price;
                $shippingAmount = 150.00;
                $totalAmount = $productAmount + $shippingAmount;
                $commissionPercent = 5.00;
                $commissionAmount = round(($productAmount * $commissionPercent) / 100, 2);
                $sellerPayoutAmount = $productAmount - $commissionAmount;

                Order::create([
                    'order_number' => 'XBUY-ORD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                    'listing_id' => $listing->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $listing->seller_id,
                    'product_amount' => $productAmount,
                    'shipping_amount' => $shippingAmount,
                    'total_amount' => $totalAmount,
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => $commissionAmount,
                    'seller_payout_amount' => $sellerPayoutAmount,
                    'razorpay_order_id' => 'order_' . \Illuminate\Support\Str::random(14),
                    'razorpay_payment_id' => 'pay_' . \Illuminate\Support\Str::random(14),
                    'order_status' => 'completed',
                    'completed_at' => $completedAt,
                    'created_at' => $completedAt->copy()->subDays(3),
                    'delivery_address' => [
                        'name' => $buyer->name,
                        'phone' => $buyer->phone,
                        'street' => '123 Main St',
                        'city' => 'Bangalore',
                        'state' => 'Karnataka',
                        'pincode' => '560001',
                    ],
                    'testing_window_days' => 2,
                    'delivered_at' => $completedAt->copy()->subDays(2),
                ]);
            }
        }

        // Clear old staff earnings so we seed cleanly
        StaffEarning::truncate();

        // 5. Generate Staff Earnings
        $orders = Order::where('order_status', 'completed')->get();
        $commissionPct = 10.0;
        $minCap = 5.0;
        $maxCap = 100.0;
        $platformExpense = 15.0;

        foreach ($orders as $order) {
            $saleValue = (float) $order->total_amount;
            $category = $order->listing->category ?? 'other';
            $completedAt = $order->completed_at ?? now();

            // Calculate commission details
            $grossCommission = round($saleValue * ($commissionPct / 100), 2);
            $netProfit = max(0.00, round($grossCommission - $platformExpense, 2));
            $calculatedAmount = round($netProfit * ($commissionPct / 100), 2);

            $minCapApplied = false;
            $maxCapApplied = false;
            $finalEarning = $calculatedAmount;

            if ($finalEarning < $minCap) {
                $finalEarning = $minCap;
                $minCapApplied = true;
            }
            if ($finalEarning > $maxCap) {
                $finalEarning = $maxCap;
                $maxCapApplied = true;
            }

            // Determine status based on order completion date
            // Orders completed in previous months are set to 'paid', current month is 'pending'
            $isCurrentMonth = ($completedAt->month === now()->month && $completedAt->year === now()->year);
            $status = $isCurrentMonth ? 'pending' : 'paid';
            $paidAt = $isCurrentMonth ? null : $completedAt->copy()->addDays(5);

            foreach ($users as $staff) {
                StaffEarning::create([
                    'staff_user_id' => $staff->id,
                    'order_id' => $order->id,
                    'sale_value' => $saleValue,
                    'category' => $category,
                    'gross_commission' => $grossCommission,
                    'platform_expenses' => $platformExpense,
                    'net_profit' => $netProfit,
                    'commission_percentage' => $commissionPct,
                    'calculated_amount' => $calculatedAmount,
                    'min_cap_applied' => $minCapApplied,
                    'max_cap_applied' => $maxCapApplied,
                    'final_earning' => $finalEarning,
                    'month' => $completedAt->month,
                    'year' => $completedAt->year,
                    'status' => $status,
                    'paid_at' => $paidAt,
                    'created_at' => $completedAt,
                    'updated_at' => $completedAt,
                ]);
            }
        }
    }
}
