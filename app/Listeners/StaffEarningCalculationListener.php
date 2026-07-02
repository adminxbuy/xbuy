<?php

namespace App\Listeners;

use App\Events\OrderCompletedEvent;
use App\Models\StaffEarning;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StaffEarningCalculationListener
{
    /**
     * Handle the OrderCompletedEvent.
     *
     * Logic:
     * 1. Order ka category check karo
     * 2. Sale value lo
     * 3. Gross commission calculate karo (site_settings se commission rate)
     * 4. Platform expense deduct karo (site_settings se)
     * 5. Net profit = gross_commission - platform_expenses
     * 6. Calculated amount = net_profit × (commission_percentage / 100)
     * 7. Min/max cap apply karo (site_settings se)
     * 8. staff_earnings table mein insert karo for ALL active staff
     * 9. Status = pending
     */
    public function handle(OrderCompletedEvent $event): void
    {
        $order = $event->order;

        // Don't create duplicate earnings for the same order
        if (StaffEarning::where('order_id', $order->id)->exists()) {
            return;
        }

        // Load settings
        $commissionPct    = (float) SiteSetting::getVal('staff_commission_percent', 10);
        $minCap           = (float) SiteSetting::getVal('staff_min_cap', 5);
        $maxCap           = (float) SiteSetting::getVal('staff_max_cap', 50);
        $platformExpense  = (float) SiteSetting::getVal('staff_platform_expense_per_order', 50);

        // Order data
        $saleValue = (float) $order->total_amount;
        $category  = $order->listing->category ?? 'other';

        // Step 3: Gross commission = sale_value × commission_percentage
        $grossCommission = round($saleValue * ($commissionPct / 100), 2);

        // Step 4: Platform expense deduction
        $platformExp = $platformExpense;

        // Step 5: Net profit
        $netProfit = round($grossCommission - $platformExp, 2);
        if ($netProfit < 0) $netProfit = 0;

        // Step 6: Calculated amount = net_profit × 10% (staff commission percent)
        $calculatedAmount = round($netProfit * ($commissionPct / 100), 2);

        // Step 7: Apply min/max cap
        $minCapApplied = false;
        $maxCapApplied = false;
        $finalEarning  = $calculatedAmount;

        if ($finalEarning < $minCap) {
            $finalEarning  = $minCap;
            $minCapApplied = true;
        }
        if ($finalEarning > $maxCap) {
            $finalEarning  = $maxCap;
            $maxCapApplied = true;
        }

        // Step 8: Create earning record for each active staff member
        $staffMembers = User::where('is_staff', true)
            ->where('status', 'active')
            ->whereHas('staffProfile', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        foreach ($staffMembers as $staff) {
            StaffEarning::create([
                'staff_user_id'         => $staff->id,
                'order_id'              => $order->id,
                'sale_value'            => $saleValue,
                'category'              => $category,
                'gross_commission'      => $grossCommission,
                'platform_expenses'     => $platformExp,
                'net_profit'            => $netProfit,
                'commission_percentage' => $commissionPct,
                'calculated_amount'     => $calculatedAmount,
                'min_cap_applied'       => $minCapApplied,
                'max_cap_applied'       => $maxCapApplied,
                'final_earning'         => $finalEarning,
                'month'                 => (int) $order->completed_at?->month ?? now()->month,
                'year'                  => (int) $order->completed_at?->year ?? now()->year,
                'status'                => 'pending',
            ]);
        }

        Log::info("Staff earnings calculated for Order #{$order->order_number} — {$staffMembers->count()} staff members.");
    }
}
