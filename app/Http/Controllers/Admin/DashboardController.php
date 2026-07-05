<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\AuditLog;
use App\Models\Dispute;
use App\Models\DisputeResponse;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Order;
use App\Models\SellerProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Helper to log admin actions.
     */
    private function logAction(string $action, string $details)
    {
        AdminActivityLog::create([
            'admin_id' => Auth::id(),
            'action' => $action,
            'description' => $details,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Admin Dashboard Home with statistics and charts data.
     */
    public function index(Request $request)
    {
        $now          = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $dateStart    = null;
        $dateEnd      = null;

        if ($request->filled('date_start') && $request->filled('date_end')) {
            $dateStart = Carbon::parse($request->input('date_start'))->startOfDay();
            $dateEnd   = Carbon::parse($request->input('date_end'))->endOfDay();
        }

        // Determine comparison period for percentages
        if ($dateStart && $dateEnd) {
            $diffInDays = $dateStart->diffInDays($dateEnd) ?: 1;
            $prevDateStart = $dateStart->copy()->subDays($diffInDays)->startOfDay();
            $prevDateEnd   = $dateStart->copy()->subSecond();
        } else {
            $yesterday     = $now->copy()->subDay();
            $prevDateStart = $yesterday->copy()->startOfDay();
            $prevDateEnd   = $yesterday->copy()->endOfDay();
        }

        // Base queries
        $salesQuery       = Order::where('order_status', 'completed');
        $escrowQuery      = Escrow::where('status', 'held');
        $listingsQuery    = Listing::where('listing_status', 'pending_approval');
        $disputesQuery    = Dispute::whereIn('status', ['open', 'seller_responded', 'under_review']);
        $revenueQuery     = Order::where('order_status', 'completed');
        $usersQuery       = User::query();

        // Comparison queries
        $salesQueryPrev   = Order::where('order_status', 'completed');
        $escrowQueryPrev  = Escrow::where('status', 'held');
        $listingsQueryPrev = Listing::where('listing_status', 'pending_approval');
        $disputesQueryPrev = Dispute::whereIn('status', ['open', 'seller_responded', 'under_review']);
        $revenueQueryPrev = Order::where('order_status', 'completed');
        $usersQueryPrev   = User::query();

        if ($dateStart && $dateEnd) {
            $salesQuery->whereBetween('completed_at', [$dateStart, $dateEnd]);
            $escrowQuery->whereBetween('created_at', [$dateStart, $dateEnd]);
            $listingsQuery->whereBetween('created_at', [$dateStart, $dateEnd]);
            $disputesQuery->whereBetween('created_at', [$dateStart, $dateEnd]);
            $revenueQuery->whereBetween('completed_at', [$dateStart, $dateEnd]);
            $usersQuery->whereBetween('created_at', [$dateStart, $dateEnd]);

            $salesQueryPrev->whereBetween('completed_at', [$prevDateStart, $prevDateEnd]);
            $escrowQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
            $listingsQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
            $disputesQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
            $revenueQueryPrev->whereBetween('completed_at', [$prevDateStart, $prevDateEnd]);
            $usersQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
        } else {
            $salesQueryPrev->whereBetween('completed_at', [$prevDateStart, $prevDateEnd]);
            $escrowQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
            $listingsQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
            $disputesQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
            $revenueQueryPrev->whereBetween('completed_at', [$prevDateStart, $prevDateEnd]);
            $usersQueryPrev->whereBetween('created_at', [$prevDateStart, $prevDateEnd]);
        }

        $totalSales       = $salesQuery->sum('total_amount');
        $totalSalesPrev   = $salesQueryPrev->sum('total_amount');

        $escrowHeld       = $escrowQuery->sum('amount_held');
        $escrowHeldPrev   = $escrowQueryPrev->sum('amount_held');

        $pendingListings  = $listingsQuery->count();
        $pendingListPrev  = $listingsQueryPrev->count();

        $openDisputes     = $disputesQuery->count();
        $openDispPrev     = $disputesQueryPrev->count();

        // Revenue (comission amount)
        $revenueVal       = $revenueQuery->sum('commission_amount');
        $revenueValPrev   = $revenueQueryPrev->sum('commission_amount');

        $totalUsers       = $usersQuery->count();
        $totalUsersPrev   = $usersQueryPrev->count();

        // helper: pct change
        $pct = fn($curr, $prev) => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : ($curr > 0 ? 100 : 0);

        $stats = [
            'total_sales'       => $totalSales,
            'total_sales_pct'   => $pct($totalSales, $totalSalesPrev),
            'escrow_held'       => $escrowHeld,
            'escrow_held_pct'   => $pct($escrowHeld, $escrowHeldPrev),
            'pending_listings'  => $pendingListings,
            'pending_list_pct'  => $pct($pendingListings, $pendingListPrev),
            'open_disputes'     => $openDisputes,
            'open_disp_pct'     => $pct($openDisputes, $openDispPrev),
            'monthly_revenue'   => $revenueVal,
            'monthly_rev_pct'   => $pct($revenueVal, $revenueValPrev),
            'total_users'       => $totalUsers,
            'total_users_pct'   => $pct($totalUsers, $totalUsersPrev),
            'pending_kyc'       => SellerProfile::where('kyc_status', 'pending')->count(),
            'total_sellers'     => SellerProfile::count(),
        ];

        // ── Listings by category chart ──
        $catQuery = Listing::selectRaw('category, count(*) as count');
        if ($dateStart && $dateEnd) {
            $catQuery->whereBetween('created_at', [$dateStart, $dateEnd]);
        }
        $categoriesData = $catQuery->groupBy('category')->get();

        // ── Revenue trend chart ──
        $dbDriver = DB::getDriverName();
        $dateSql  = $dbDriver === 'sqlite'
            ? "strftime('%Y-%m-%d', completed_at) as date"
            : "DATE(completed_at) as date";

        $chartDays = 29; // default 30 days
        if ($dateStart && $dateEnd) {
            $chartDays = $dateStart->diffInDays($dateEnd) ?: 1;
        }

        $trendQuery = Order::where('order_status', 'completed');
        if ($dateStart && $dateEnd) {
            $trendQuery->whereBetween('completed_at', [$dateStart, $dateEnd]);
        } else {
            $trendQuery->where('completed_at', '>=', $now->copy()->subDays($chartDays)->startOfDay());
        }

        $revenueTrend = $trendQuery
            ->selectRaw("{$dateSql}, SUM(commission_amount) as amount")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill dates
        $revenueTrendFilled = [];
        $trendStart = $dateStart ? $dateStart->copy() : $now->copy()->subDays($chartDays);
        for ($i = 0; $i <= $chartDays; $i++) {
            $day = $trendStart->copy()->addDays($i)->format('Y-m-d');
            $revenueTrendFilled[] = [
                'date'   => $day,
                'amount' => isset($revenueTrend[$day]) ? (float) $revenueTrend[$day]->amount : 0,
            ];
        }

        // ── Recent Disputes ─────────────────────────────────────────────────────
        $recentDisputes = Dispute::with(['order', 'buyer', 'seller'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ── Recent Orders (last 5) ──────────────────────────────────────────────
        $recentOrders = Order::with(['listing', 'buyer'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ── Recent Seller Applications (pending KYC, last 5) ───────────────────
        $recentSellerApplications = SellerProfile::with('user')
            ->where('kyc_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ── Top Sellers by GMV this month ───────────────────────────────────────
        $topSellersQuery = Order::where('order_status', 'completed');
        if ($dateStart && $dateEnd) {
            $topSellersQuery->whereBetween('completed_at', [$dateStart, $dateEnd]);
        } else {
            $topSellersQuery->where('completed_at', '>=', $startOfMonth);
        }
        $topSellers = $topSellersQuery
            ->selectRaw('seller_id, SUM(total_amount) as gmv, COUNT(*) as order_count')
            ->groupBy('seller_id')
            ->orderByDesc('gmv')
            ->take(5)
            ->get()
            ->map(function ($row) {
                $row->seller = SellerProfile::with('user')->find($row->seller_id);
                return $row;
            });

        $recentTickets = \App\Models\SupportTicket::with('buyer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $ordersThisMonthVal = Order::where('order_status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();
        $amendmentTriggerVal = (int) SiteSetting::getVal('staff_amendment_trigger_orders', 100);
        $showStaffPayReviewPending = $ordersThisMonthVal > $amendmentTriggerVal;

        return view('admin.dashboard', compact(
            'stats', 'categoriesData', 'revenueTrendFilled',
            'recentDisputes', 'recentOrders', 'recentSellerApplications', 'topSellers', 'recentTickets',
            'showStaffPayReviewPending'
        ));
    }

    /**
     * Escrow Management page.
     */
    /**
     * Escrow Management page.
     */
    public function escrow(Request $request)
    {
        $this->pruneTrashedData();
        $query = \App\Models\Escrow::with(['order.listing', 'order.buyer', 'order.seller']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('seller')) {
            $s = $request->input('seller');
            $query->whereHas('order.seller', fn($q) => $q->where('shop_name', 'like', "%{$s}%"));
        }
        if ($request->filled('date_start')) {
            $query->where('created_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->where('created_at', '<=', $request->input('date_end') . ' 23:59:59');
        }

        $escrows = $query->latest()->paginate(20);

        // Summaries
        $now = \Carbon\Carbon::now();
        $summary = [
            'total_held'     => \App\Models\Escrow::where('status', 'held')->sum('amount_held'),
            'due_today'      => \App\Models\Escrow::where('status', 'held')->whereDate('release_scheduled_at', \Carbon\Carbon::today())->sum('amount_held'),
            'overdue'        => \App\Models\Escrow::where('status', 'held')->where('release_scheduled_at', '<', $now)->sum('amount_held'),
            'total_disputed' => \App\Models\Escrow::where('status', 'disputed')->sum('amount_held'),
            'count_held'     => \App\Models\Escrow::where('status', 'held')->count(),
        ];

        $trashedEscrows = \App\Models\Escrow::onlyTrashed()->with(['order.listing', 'order.buyer', 'order.seller'])->get();

        return view('admin.escrow', compact('escrows', 'summary', 'trashedEscrows'));
    }

    /**
     * Release Escrow manually to seller.
     */
    /**
     * Release Escrow manually to seller.
     */
    public function escrowRelease($id)
    {
        $escrow = \App\Models\Escrow::findOrFail($id);
        if ($escrow->status !== 'held' && $escrow->status !== 'disputed') {
            return back()->with('error', 'Escrow cannot be released in its current status.');
        }

        $routeEnabled = \App\Models\SiteSetting::getVal('razorpay_route_enabled', false);
        if ($routeEnabled) {
            $success = \App\Console\Commands\AutoReleaseEscrow::processRelease($escrow);
            if ($success) {
                return back()->with('success', 'Escrow released and payout transfer executed successfully.');
            } else {
                return back()->with('error', 'Escrow release failed: ' . ($escrow->payout_error_message ?? 'Razorpay Route transfer failed.'));
            }
        }

        DB::transaction(function () use ($escrow) {
            $escrow->update([
                'status' => 'released',
                'payout_status' => 'success',
                'released_at' => now(),
                'released_by' => auth()->user()->name ?? 'Admin',
            ]);

            if ($escrow->order) {
                $escrow->order->update([
                    'order_status' => 'completed',
                    'completed_at' => now()
                ]);
            }
        });

        // Notify seller via mail
        \App\Models\Notification::sendSystemMail($escrow->order->seller->user, 'escrow_released', [
            'seller_name' => $escrow->order->seller->user->name,
            'order_number' => $escrow->order->order_number,
            'seller_payout_amount' => $escrow->seller_amount,
        ]);

        $this->logAction('escrow_released', "Escrow ID #{$escrow->id} manually released to seller by admin.");
        return back()->with('success', 'Escrow released successfully.');
    }

    /**
     * Refund Escrow manually to buyer.
     */
    public function escrowRefund($id)
    {
        $escrow = \App\Models\Escrow::findOrFail($id);
        if ($escrow->status !== 'held' && $escrow->status !== 'disputed') {
            return back()->with('error', 'Escrow cannot be refunded in its current status.');
        }

        $paymentId = $escrow->order->razorpay_payment_id;
        $amount = $escrow->amount_held;

        // Trigger Razorpay Refund
        if (!empty($paymentId)) {
            $response = \App\Services\RazorpayService::refundToBuyer($paymentId, $amount, "Admin Escrow Refund for Order #{$escrow->order->order_number}");
            if (!$response['success']) {
                return back()->with('error', 'Razorpay Refund failed: ' . $response['message']);
            }
        }

        DB::transaction(function () use ($escrow) {
            $escrow->update([
                'status' => 'refunded',
                'payout_status' => 'none',
                'released_at' => now(),
                'released_by' => auth()->user()->name ?? 'Admin',
                'seller_amount' => 0,
                'commission_amount' => 0
            ]);

            if ($escrow->order) {
                $escrow->order->update([
                    'order_status' => 'refunded',
                    'cancellation_reason' => 'Manually refunded by admin'
                ]);
            }
        });

        // Send Dispute Resolved Mail (100% Refund to Buyer)
        \App\Models\Notification::sendSystemMail($escrow->order->buyer, 'dispute_resolved', [
            'buyer_name' => $escrow->order->buyer->name,
            'order_number' => $escrow->order->order_number,
            'resolution_notes' => 'Full refund issued to buyer by administrator.',
            'buyer_payout' => $amount,
            'seller_payout' => 0,
        ]);

        \App\Models\Notification::sendSystemMail($escrow->order->seller->user, 'dispute_resolved', [
            'seller_name' => $escrow->order->seller->user->name,
            'order_number' => $escrow->order->order_number,
            'resolution_notes' => 'Full refund issued to buyer by administrator.',
            'buyer_payout' => $amount,
            'seller_payout' => 0,
        ]);

        $this->logAction('escrow_refunded', "Escrow ID #{$escrow->id} manually refunded to buyer by admin.");
        return back()->with('success', 'Escrow refunded successfully.');
    }

    /**
     * Retry failed payout.
     */
    public function escrowRetryPayout($id)
    {
        $escrow = \App\Models\Escrow::findOrFail($id);
        
        $success = \App\Console\Commands\AutoReleaseEscrow::processRelease($escrow);
        
        if ($success) {
            return back()->with('success', 'Payout retried and completed successfully.');
        } else {
            return back()->with('error', 'Payout retry failed: ' . ($escrow->payout_error_message ?? 'Unknown error'));
        }
    }

    /**
     * Partially release escrow.
     */
    public function escrowPartialRelease(Request $request, $id)
    {
        $escrow = \App\Models\Escrow::findOrFail($id);
        if ($escrow->status !== 'held' && $escrow->status !== 'disputed') {
            return back()->with('error', 'Escrow cannot be partially released in its current status.');
        }

        $request->validate([
            'seller_amount' => 'required|numeric|min:0|max:' . $escrow->amount_held
        ]);

        $sellerAmount = (float) $request->input('seller_amount');
        $buyerRefundAmount = $escrow->amount_held - $sellerAmount;

        // Process Refund to Buyer
        if ($buyerRefundAmount > 0) {
            $paymentId = $escrow->order->razorpay_payment_id;
            if (!empty($paymentId)) {
                $refundRes = \App\Services\RazorpayService::refundToBuyer($paymentId, $buyerRefundAmount, "Partial Refund for Order #{$escrow->order->order_number}");
                if (!$refundRes['success']) {
                    return back()->with('error', 'Razorpay Refund failed: ' . $refundRes['message']);
                }
            }
        }

        // Process Transfer to Seller
        $transferId = null;
        $payoutStatus = 'success';
        $payoutError = null;

        if ($sellerAmount > 0) {
            $accountId = $escrow->order->seller->razorpay_account_id;
            if (!empty($accountId)) {
                $transferRes = \App\Services\RazorpayService::transferToSeller($accountId, $sellerAmount, $escrow->order->order_number);
                if ($transferRes['success']) {
                    $transferId = $transferRes['transfer_id'];
                } else {
                    $payoutStatus = 'failed';
                    $payoutError = $transferRes['message'];
                }
            } else {
                $payoutStatus = 'failed';
                $payoutError = "Seller Razorpay account ID is not configured.";
            }
        }

        DB::transaction(function () use ($escrow, $sellerAmount, $transferId, $payoutStatus, $payoutError) {
            $escrow->update([
                'status' => $payoutStatus === 'failed' ? 'held' : 'partially_released',
                'payout_status' => $payoutStatus,
                'payout_error_message' => $payoutError,
                'razorpay_payout_id' => $transferId,
                'seller_amount' => $sellerAmount,
                'released_at' => $payoutStatus === 'failed' ? null : now(),
                'released_by' => auth()->user()->name ?? 'Admin',
                'commission_amount' => 0
            ]);

            if ($payoutStatus !== 'failed' && $escrow->order) {
                $escrow->order->update([
                    'order_status' => 'completed',
                    'completed_at' => now()
                ]);
            }
        });

        if ($payoutStatus === 'failed') {
            // Notify seller of failed payout
            \App\Models\Notification::sendSystemMail($escrow->order->seller->user, 'payout_failed', [
                'seller_name' => $escrow->order->seller->user->name,
                'order_number' => $escrow->order->order_number,
                'seller_payout_amount' => $sellerAmount,
                'payout_error_message' => $payoutError,
            ]);
            return back()->with('error', 'Seller payout failed: ' . $payoutError . ' (Buyer portion refunded successfully)');
        }

        // Send dynamic emails
        \App\Models\Notification::sendSystemMail($escrow->order->buyer, 'dispute_resolved', [
            'buyer_name' => $escrow->order->buyer->name,
            'order_number' => $escrow->order->order_number,
            'resolution_notes' => "Partial split resolution: Seller payout ₹{$sellerAmount}, Buyer refund ₹{$buyerRefundAmount}.",
            'buyer_payout' => $buyerRefundAmount,
            'seller_payout' => $sellerAmount,
        ]);

        \App\Models\Notification::sendSystemMail($escrow->order->seller->user, 'dispute_resolved', [
            'seller_name' => $escrow->order->seller->user->name,
            'order_number' => $escrow->order->order_number,
            'resolution_notes' => "Partial split resolution: Seller payout ₹{$sellerAmount}, Buyer refund ₹{$buyerRefundAmount}.",
            'buyer_payout' => $buyerRefundAmount,
            'seller_payout' => $sellerAmount,
        ]);

        $this->logAction('escrow_partially_released', "Escrow ID #{$escrow->id} partially released by admin: Seller gets ₹{$sellerAmount}, Buyer gets ₹{$buyerRefundAmount}");
        return back()->with('success', 'Escrow split executed successfully.');
    }

    /**
     * Payouts History page.
     */
    public function payouts(Request $request)
    {
        $this->pruneTrashedData();
        $query = \App\Models\Escrow::with(['order.listing', 'order.seller.user'])
            ->whereIn('status', ['released', 'partially_released']);

        // Filters
        if ($request->filled('seller')) {
            $s = $request->input('seller');
            $query->whereHas('order.seller', fn($q) => $q->where('shop_name', 'like', "%{$s}%"));
        }
        if ($request->filled('date_start')) {
            $query->where('released_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->where('released_at', '<=', $request->input('date_end') . ' 23:59:59');
        }

        $payouts = $query->latest('released_at')->paginate(20);

        // Summaries
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $releasedThisMonth = \App\Models\Escrow::whereIn('status', ['released', 'partially_released'])
            ->where('released_at', '>=', $startOfMonth)
            ->sum('seller_amount');

        $totalCommission = \App\Models\Escrow::whereIn('status', ['released', 'partially_released'])
            ->sum('commission_amount');

        $avgPayout = \App\Models\Escrow::whereIn('status', ['released', 'partially_released'])
            ->avg('seller_amount') ?? 0;

        $trashedPayouts = \App\Models\Escrow::onlyTrashed()
            ->with(['order.listing', 'order.seller.user'])
            ->whereIn('status', ['released', 'partially_released'])
            ->get();

        return view('admin.payouts', compact('payouts', 'releasedThisMonth', 'totalCommission', 'avgPayout', 'trashedPayouts'));
    }

    /**
     * Export Payouts to CSV.
     */
    public function payoutsExport(Request $request)
    {
        $query = \App\Models\Escrow::with(['order.seller'])
            ->whereIn('status', ['released', 'partially_released']);

        if ($request->filled('seller')) {
            $s = $request->input('seller');
            $query->whereHas('order.seller', fn($q) => $q->where('shop_name', 'like', "%{$s}%"));
        }
        if ($request->filled('date_start')) {
            $query->where('released_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->where('released_at', '<=', $request->input('date_end') . ' 23:59:59');
        }

        $payouts = $query->latest('released_at')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=payouts_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($payouts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Payout ID', 'Order #', 'Seller Name', 'Gross Amount', 'Commission Amount', 'Net Payout', 'Razorpay Transfer ID', 'Released At', 'Status']);

            foreach ($payouts as $payout) {
                fputcsv($file, [
                    'PAY-' . $payout->id,
                    $payout->order->order_number ?? 'N/A',
                    $payout->order->seller->shop_name ?? 'N/A',
                    $payout->amount_held,
                    $payout->commission_amount,
                    $payout->seller_amount,
                    $payout->razorpay_transfer_id ?? 'N/A',
                    $payout->released_at ? $payout->released_at->format('Y-m-d H:i:s') : 'N/A',
                    $payout->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Full Analytics page.
     */
    public function analyticsPage(Request $request)
    {
        $days = $request->input('days', '30');
        $now = Carbon::now();

        if ($days === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $end = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            $daysInt = in_array($days, ['7', '30', '90']) ? (int)$days : 30;
            $start = $now->copy()->subDays($daysInt - 1)->startOfDay();
            $end = $now->copy()->endOfDay();
        }

        $diffDays = max(1, $start->diffInDays($end));
        $prevStart = $start->copy()->subDays($diffDays);
        $prevEnd = $start->copy()->subSecond();

        // Helper function for percentage change
        $pctChange = fn($curr, $prev) => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : ($curr > 0 ? 100 : 0);

        // --- Current Period Stats ---
        $currentGmv = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->sum('total_amount');

        $currentOrders = Order::whereBetween('created_at', [$start, $end])
            ->count();

        $currentCommission = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->sum('commission_amount');

        $currentAov = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->avg('total_amount') ?? 0;

        // --- Previous Period Stats ---
        $prevGmv = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$prevStart, $prevEnd])
            ->sum('total_amount');

        $prevOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevCommission = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$prevStart, $prevEnd])
            ->sum('commission_amount');

        $prevAov = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$prevStart, $prevEnd])
            ->avg('total_amount') ?? 0;

        // --- Percentage Changes ---
        $gmvChange = $pctChange($currentGmv, $prevGmv);
        $ordersChange = $pctChange($currentOrders, $prevOrders);
        $commissionChange = $pctChange($currentCommission, $prevCommission);
        $aovChange = $pctChange($currentAov, $prevAov);

        // --- Day-by-Day GMV Trend ---
        $dbDriver = DB::getDriverName();
        $dateSql = $dbDriver === 'sqlite'
            ? "strftime('%Y-%m-%d', completed_at)"
            : "DATE(completed_at)";

        $gmvTrendRaw = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->selectRaw("{$dateSql} as date, SUM(total_amount) as amount")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('amount', 'date')
            ->toArray();

        $gmvLabels = [];
        $gmvData = [];
        $tempDate = $start->copy();
        while ($tempDate->lte($end)) {
            $dateStr = $tempDate->format('Y-m-d');
            $gmvLabels[] = $tempDate->format('d M');
            $gmvData[] = (float)($gmvTrendRaw[$dateStr] ?? 0);
            $tempDate->addDay();
        }

        // --- Day-by-Day Dispute Rate Trend ---
        $dateCreatedSql = $dbDriver === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at)"
            : "DATE(created_at)";

        $ordersDaily = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw("{$dateCreatedSql} as date, COUNT(*) as count")
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $disputesDaily = Order::whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->where('order_status', 'disputed')->orWhereHas('dispute');
            })
            ->selectRaw("{$dateCreatedSql} as date, COUNT(*) as count")
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $disputeLabels = [];
        $disputeData = [];
        $tempDate = $start->copy();
        while ($tempDate->lte($end)) {
            $dateStr = $tempDate->format('Y-m-d');
            $oCount = $ordersDaily[$dateStr] ?? 0;
            $dCount = $disputesDaily[$dateStr] ?? 0;
            $rate = $oCount > 0 ? round(($dCount / $oCount) * 100, 1) : 0;
            $disputeLabels[] = $tempDate->format('d M');
            $disputeData[] = $rate;
            $tempDate->addDay();
        }

        // --- Category Breakdown Bar Chart ---
        $categoryBreakdown = Order::where('orders.order_status', 'completed')
            ->whereBetween('orders.completed_at', [$start, $end])
            ->join('listings', 'orders.listing_id', '=', 'listings.id')
            ->selectRaw('listings.category, SUM(orders.total_amount) as gmv')
            ->groupBy('listings.category')
            ->orderByDesc('gmv')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => ucfirst(str_replace('_', ' ', $item->category)),
                    'value' => (float)$item->gmv,
                ];
            })
            ->toArray();

        // --- Order Status Pie Chart ---
        $statusBreakdown = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('order_status, COUNT(*) as count')
            ->groupBy('order_status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => ucfirst(str_replace('_', ' ', $item->order_status)),
                    'value' => (int)$item->count,
                ];
            })
            ->toArray();

        // --- Top 10 Sellers by GMV ---
        $topSellersGmv = Order::where('order_status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->selectRaw('seller_id, SUM(total_amount) as gmv, COUNT(*) as order_count')
            ->groupBy('seller_id')
            ->orderByDesc('gmv')
            ->take(10)
            ->get()
            ->map(function ($row) {
                $row->seller = SellerProfile::with('user')->find($row->seller_id);
                return $row;
            });

        // --- Top 10 Sellers by Rating ---
        $topSellersRating = SellerProfile::with('user')
            ->withAvg('ratings', 'weighted_total')
            ->withCount('ratings')
            ->orderByDesc('ratings_avg_weighted_total')
            ->take(10)
            ->get();

        return view('admin.analytics', compact(
            'days', 'start', 'end',
            'currentGmv', 'currentOrders', 'currentCommission', 'currentAov',
            'gmvChange', 'ordersChange', 'commissionChange', 'aovChange',
            'gmvLabels', 'gmvData',
            'disputeLabels', 'disputeData',
            'categoryBreakdown', 'statusBreakdown',
            'topSellersGmv', 'topSellersRating'
        ));
    }

    public function users(Request $request)
    {
        $tab = $request->input('tab');

        if ($tab === 'deleted') {
            $query = User::onlyTrashed()->whereIn('role', ['buyer', 'hybrid']);
        } else {
            $query = User::whereIn('role', ['buyer', 'hybrid']);
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")
                                      ->orWhere('email', 'like', "%{$s}%")
                                      ->orWhere('phone', 'like', "%{$s}%"));
        }

        if ($request->filled('badge')) {
            $query->where('buyer_badge', $request->input('badge'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_start')) {
            $query->where('created_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->where('created_at', '<=', $request->input('date_end') . ' 23:59:59');
        }

        $users = $query->withCount(['orders as total_orders'])
            ->with('wallet')
            ->latest()->paginate(20);

        // Map active login session status
        $users->getCollection()->transform(function($user) {
            $user->is_logged_in = \DB::table('sessions')->where('user_id', $user->id)->exists();
            return $user;
        });

        $badgeCounts = [
            'new_buyer'      => User::whereIn('role', ['buyer', 'hybrid'])->where('buyer_badge','new_buyer')->count(),
            'verified_buyer' => User::whereIn('role', ['buyer', 'hybrid'])->where('buyer_badge','verified_buyer')->count(),
            'trusted_buyer'  => User::whereIn('role', ['buyer', 'hybrid'])->where('buyer_badge','trusted_buyer')->count(),
            'deleted_buyer'  => User::onlyTrashed()->whereIn('role', ['buyer', 'hybrid'])->count(),
        ];

        return view('admin.users', compact('users', 'badgeCounts'));
    }

    /**
     * Suspend user.
     */
    public function userSuspend(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'status' => 'suspended'
        ]);

        $this->logAction('user_suspended', "User #{$user->id} ({$user->name}) suspended by admin. Reason: " . $request->input('reason'));
        return back()->with('success', 'User suspended successfully.');
    }

    /**
     * Ban user.
     */
    public function userBan(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'status' => 'banned'
        ]);

        $this->logAction('user_banned', "User #{$user->id} ({$user->name}) banned by admin. Reason: " . $request->input('reason'));
        return back()->with('success', 'User banned successfully.');
    }

    /**
     * Sellers list.
     */
    public function sellers(Request $request)
    {
        $this->pruneTrashedData();
        $query = SellerProfile::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('shop_name', 'like', "%{$search}%");
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->input('kyc_status'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('badge_level')) {
            $query->where('badge_level', $request->input('badge_level'));
        }

        $sellers = $query->paginate(20);
        $trashedSellers = SellerProfile::onlyTrashed()->with('user')->get();
        return view('admin.sellers.index', compact('sellers', 'trashedSellers'));
    }

    /**
     * Seller detail and KYC approval.
     */
    public function sellerShow(int $id)
    {
        $seller = SellerProfile::with(['user', 'listings', 'orders.listing', 'metrics'])->findOrFail($id);
        $messages = \App\Models\AdminSellerMessage::with('sender')->where('seller_profile_id', $id)->orderBy('created_at', 'asc')->get();
        
        $subRatings = \App\Models\Rating::where('seller_id', $id)
            ->selectRaw('AVG(item_accuracy) as item_accuracy, AVG(packaging) as packaging, AVG(shipping_speed) as shipping_speed, AVG(communication) as communication')
            ->first();

        return view('admin.sellers.show', compact('seller', 'messages', 'subRatings'));
    }

    /**
     * Store a chat message between admin and seller.
     */
    public function storeChatMessage(int $id, Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $seller = SellerProfile::findOrFail($id);

        $msg = \App\Models\AdminSellerMessage::create([
            'seller_profile_id' => $id,
            'sender_id' => auth()->id(),
            'message' => $request->input('message'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'message' => $msg->message,
                'is_admin' => $msg->sender->role === 'admin',
                'time' => $msg->created_at->format('M d, H:i'),
            ]
        ]);
    }

    /**
     * Retrieve chat messages.
     */
    public function getChatMessages(int $id)
    {
        $messages = \App\Models\AdminSellerMessage::with('sender')->where('seller_profile_id', $id)->orderBy('created_at', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'message' => $msg->message,
                    'is_admin' => $msg->sender->role === 'admin',
                    'time' => $msg->created_at->format('M d, H:i'),
                ];
            })
        ]);
    }

    /**
     * Approve or reject KYC.
     */
    public function sellerKyc(int $id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $seller = SellerProfile::findOrFail($id);
        $status = $request->input('status');

        $updateData = [
            'kyc_status' => $status,
            'kyc_rejection_reason' => $status === 'rejected' ? $request->input('reason') : null,
            'status' => $status === 'approved' ? 'active' : 'pending',
        ];

        if ($status === 'approved') {
            $updateData['kyc_approved_at'] = now();
            $updateData['onboarding_step'] = 1;
        }

        $seller->update($updateData);

        // Automatically upgrade role to seller and send welcome onboarding email on KYC approval
        if ($status === 'approved') {
            $seller->user->update(['role' => 'seller']);
            
            try {
                \Illuminate\Support\Facades\Mail::to($seller->user->email)
                    ->queue(new \App\Mail\SellerOnboardingMail(0, $seller->shop_name, $seller->user->id));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send seller welcome email: " . $e->getMessage());
            }
        }

        $this->logAction("kyc_{$status}", "Seller ID #{$seller->id} KYC status updated to {$status}");

        return back()->with('success', "KYC request has been " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully.");
    }

    /**
     * Update seller account status manually (active, suspended, banned).
     */
    public function sellerStatusUpdate(int $id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,banned',
        ]);

        $seller = SellerProfile::findOrFail($id);
        $status = $request->input('status');

        $seller->update([
            'status' => $status,
        ]);

        // Hide/Reject listings if suspended or banned
        if ($status === 'banned') {
            $seller->listings()->whereIn('listing_status', ['active', 'pending_approval'])->update(['listing_status' => 'rejected']);
        } elseif ($status === 'suspended') {
            $seller->listings()->whereIn('listing_status', ['active', 'pending_approval'])->update(['listing_status' => 'paused']);
        }

        $this->logAction("seller_status_updated", "Seller ID #{$seller->id} account status manually changed to {$status}");

        return back()->with('success', "Seller account status has been updated to " . ucfirst($status) . " successfully.");
    }

    /**
     * Update seller Razorpay Account ID manually.
     */
    public function updateRazorpayAccount(int $id, Request $request)
    {
        $request->validate([
            'razorpay_account_id' => 'required|string|max:100',
        ]);

        $seller = SellerProfile::findOrFail($id);
        $seller->update([
            'razorpay_account_id' => $request->input('razorpay_account_id'),
        ]);

        $this->logAction("seller_razorpay_updated", "Seller ID #{$seller->id} Razorpay Linked Account updated to " . $request->input('razorpay_account_id'));

        return back()->with('success', "Seller Razorpay Account ID has been updated successfully.");
    }

    /**
     * Update seller badge level manually (basic, verified, fulfilled).
     */
    public function sellerBadgeUpdate(int $id, Request $request)
    {
        $request->validate([
            'badge_level' => 'required|in:basic,verified,fulfilled',
        ]);

        $seller = SellerProfile::findOrFail($id);
        $badge = $request->input('badge_level');

        $seller->update([
            'badge_level' => $badge,
        ]);

        $this->logAction("seller_badge_updated", "Seller ID #{$seller->id} badge level manually updated to {$badge}");

        return back()->with('success', "Seller badge level has been updated to " . ucfirst($badge) . " successfully.");
    }

    /**
     * Listings list.
     */
    public function listings(Request $request)
    {
        $this->pruneTrashedData();
        $query = Listing::with(['seller.user', 'images']);

        if ($request->filled('status')) {
            $query->where('listing_status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->input('date_start'));
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->input('date_end'));
        }

        $listings = $query->orderBy('created_at', 'desc')->paginate(20);

        $tabCounts = [
            'all'              => Listing::count(),
            'pending_approval' => Listing::where('listing_status', 'pending_approval')->count(),
            'active'           => Listing::where('listing_status', 'active')->count(),
            'paused'           => Listing::where('listing_status', 'paused')->count(),
            'rejected'         => Listing::where('listing_status', 'rejected')->count(),
            'sold'             => Listing::where('listing_status', 'sold')->count(),
        ];

        $trashedListings = Listing::onlyTrashed()->with(['seller.user'])->get();

        return view('admin.listings.index', compact('listings', 'tabCounts', 'trashedListings'));
    }

    /**
     * Listing detail.
     */
    public function listingShow(int $id)
    {
        $listing = Listing::with(['seller.user', 'images'])->findOrFail($id);
        return view('admin.listings.show', compact('listing'));
    }

    /**
     * Update listing status (approve, reject, mark pending, mark sold, etc.).
     */
    public function listingApprove(int $id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:active,rejected,pending_approval,paused,sold',
            'reason' => 'required_if:status,rejected|nullable|string|max:500',
        ]);

        $listing = Listing::findOrFail($id);
        $status  = $request->input('status');

        $listing->update([
            'listing_status'   => $status,
            'rejection_reason' => $status === 'rejected' ? $request->input('reason') : null,
        ]);

        $statusLabel = ucfirst(str_replace('_', ' ', $status));
        $this->logAction("listing_{$status}", "Listing #{$listing->id} ({$listing->title}) status changed to {$status}");

        return back()->with('success', "Listing status updated to '{$statusLabel}' successfully.");
    }

    /**
     * Pause or unpause a listing.
     */
    public function listingPause(int $id)
    {
        $listing = Listing::findOrFail($id);
        $newStatus = $listing->listing_status === 'paused' ? 'active' : 'paused';

        $listing->update(['listing_status' => $newStatus]);

        $this->logAction("listing_{$newStatus}", "Listing #{$listing->id} ({$listing->title}) status changed to {$newStatus}");

        return back()->with('success', "Listing has been " . ($newStatus === 'paused' ? 'paused' : 'reactivated') . " successfully.");
    }

    /**
     * Orders list.
     */
    public function orders(Request $request)
    {
        $this->pruneTrashedData();
        $query = Order::with(['listing', 'buyer', 'seller.user', 'escrow', 'shipment', 'dispute']);

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('buyer', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->input('date_start'));
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->input('date_end'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        $allStatuses = [
            'payment_received', 'confirmed', 'label_generated', 'picked_up',
            'in_transit', 'out_for_delivery', 'delivered',
            'testing_period', 'completed', 'disputed', 'refunded', 'cancelled',
        ];

        $tabCounts = ['all' => Order::count()];
        foreach ($allStatuses as $s) {
            $tabCounts[$s] = Order::where('order_status', $s)->count();
        }

        $trashedOrders = Order::onlyTrashed()->with(['listing', 'buyer', 'seller.user'])->get();

        return view('admin.orders.index', compact('orders', 'tabCounts', 'trashedOrders'));
    }

    /**
     * Order Show.
     */
    public function orderShow(int $id)
    {
        $order = Order::with(['listing.images', 'buyer', 'seller.user', 'escrow', 'shipment', 'dispute', 'rating'])->findOrFail($id);
        $messages = \App\Models\AdminBuyerMessage::with('sender')->where('buyer_id', $order->buyer_id)->orderBy('created_at', 'asc')->get();
        return view('admin.orders.show', compact('order', 'messages'));
    }

    /**
     * Download Invoice as HTML file.
     */
    public function downloadInvoice(int $id)
    {
        $order = Order::with(['listing', 'buyer', 'seller.user'])->findOrFail($id);
        $html = view('admin.orders.invoice', compact('order'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="invoice_' . $order->order_number . '.html"');
    }

    /**
     * Resend Invoice via Email to the Buyer.
     */
    public function resendInvoice(int $id)
    {
        $order = Order::with(['listing', 'buyer', 'seller.user'])->findOrFail($id);
        $buyerEmail = $order->buyer->email ?? null;

        if (!$buyerEmail) {
            return back()->with('error', 'Buyer has no email address.');
        }

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Hello {$order->buyer->name},\n\n" .
                "Here is the invoice for your order #{$order->order_number} on X-Buy Escrow Marketplace.\n\n" .
                "Order Details:\n" .
                "- Item: {$order->listing->title}\n" .
                "- Product Price: ₹" . number_format($order->product_amount, 2) . "\n" .
                "- Shipping Cost: ₹" . number_format($order->shipping_amount, 2) . "\n" .
                "- Total Amount Paid: ₹" . number_format($order->total_amount, 2) . "\n" .
                "- Date Placed: " . $order->created_at->format('M d, Y H:i') . "\n\n" .
                "Thank you for shopping on X-Buy!\n\n" .
                "Best regards,\nX-Buy Support Team",
                function ($message) use ($buyerEmail, $order) {
                    $message->to($buyerEmail)->subject("X-Buy Order Invoice - #{$order->order_number}");
                }
            );

            return back()->with('success', 'Invoice has been resent to ' . $buyerEmail . ' successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to resend invoice email: " . $e->getMessage());
            return back()->with('error', 'Failed to resend invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get buyer chat messages list.
     */
    public function getBuyerChatMessages(int $id)
    {
        $order = Order::findOrFail($id);
        $messages = \App\Models\AdminBuyerMessage::with('sender')
            ->where('buyer_id', $order->buyer_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'message' => $msg->message,
                    'is_admin' => $msg->sender->role === 'admin',
                    'time' => $msg->created_at->format('M d, H:i'),
                ];
            })
        ]);
    }

    /**
     * Store chat message between admin and buyer.
     */
    public function storeBuyerChatMessage(int $id, Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $order = Order::findOrFail($id);

        $msg = \App\Models\AdminBuyerMessage::create([
            'buyer_id' => $order->buyer_id,
            'sender_id' => auth()->id(),
            'message' => $request->input('message'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'message' => $msg->message,
                'is_admin' => $msg->sender->role === 'admin',
                'time' => $msg->created_at->format('M d, H:i'),
            ]
        ]);
    }

    /**
     * Admin manual order status update.
     */
    public function orderUpdateStatus(int $id, Request $request)
    {
        $request->validate([
            'order_status' => 'required|in:pending_payment,payment_received,confirmed,label_generated,picked_up,in_transit,out_for_delivery,delivered,testing_period,completed,disputed,refunded,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $old   = $order->order_status;
        $new   = $request->input('order_status');

        $order->update(['order_status' => $new]);
        $this->logAction('order_status_update', "Order #{$order->order_number} status changed from {$old} to {$new} by admin");

        return back()->with('success', "Order #{$order->order_number} status updated to '" . ucwords(str_replace('_', ' ', $new)) . "'.");
    }

    /**
     * Disputes List.
     */
    public function disputes(Request $request)
    {
        $this->pruneTrashedData();
        $query = Dispute::with(['order', 'buyer', 'seller.user']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->input('date_start'));
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->input('date_end'));
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(20);
        $trashedDisputes = Dispute::onlyTrashed()->with(['order', 'buyer', 'seller.user'])->get();
        return view('admin.disputes.index', compact('disputes', 'trashedDisputes'));
    }

    /**
     * Disputes Chat Workspace.
     */
    public function disputeShow(int $id)
    {
        $dispute = Dispute::with(['order.listing.images', 'buyer', 'seller.user', 'responses.user'])->findOrFail($id);
        return view('admin.disputes.show', compact('dispute'));
    }

    /**
     * Send dispute response message.
     */
    public function disputeResponse(int $id, Request $request)
    {
        $request->validate(['response_text' => 'required|string']);
        $dispute = Dispute::findOrFail($id);

        DisputeResponse::create([
            'dispute_id' => $dispute->id,
            'responder_id' => Auth::id(),
            'responder_type' => 'admin',
            'response_text' => $request->input('response_text'),
            'evidence_images' => [],
        ]);

        if ($dispute->status === 'open' || $dispute->status === 'seller_responded') {
            $dispute->update(['status' => 'under_review']);
        }

        return back()->with('success', 'Message sent to dispute workspace.');
    }

    /**
     * Resolve dispute with detailed decision and fault assignment.
     */
    public function disputeResolve(int $id, Request $request)
    {
        $request->validate([
            'decision' => 'required|in:seller_favor,buyer_favor,partial',
            'refund_amount' => 'required_if:decision,partial|nullable|numeric|min:0',
            'fault' => 'required|in:seller,buyer,shipping,none',
            'admin_notes' => 'required|string|max:2000',
        ]);

        $dispute = Dispute::with(['order.escrow', 'buyer', 'seller.user'])->findOrFail($id);
        $decision = $request->input('decision');
        $fault = $request->input('fault');
        $notes = $request->input('admin_notes');
        $refundAmount = $decision === 'partial' ? (float)$request->input('refund_amount') : null;

        $order = $dispute->order;
        $escrowAmount = $order->escrow->amount_held ?? $order->total_amount;
        $paymentId = $order->razorpay_payment_id;
        $accountId = $order->seller->razorpay_account_id;

        // Calculate payouts
        $buyerPayout = 0;
        $sellerPayout = 0;

        if ($decision === 'buyer_favor') {
            $buyerPayout = $escrowAmount;
            $sellerPayout = 0;
        } elseif ($decision === 'seller_favor') {
            $buyerPayout = 0;
            $sellerPayout = $escrowAmount;
        } else {
            $buyerPayout = $refundAmount;
            $sellerPayout = $escrowAmount - $refundAmount;
        }

        // 1. Process Refund to Buyer (if any refund is due)
        if ($buyerPayout > 0 && !empty($paymentId)) {
            $refundRes = \App\Services\RazorpayService::refundToBuyer($paymentId, $buyerPayout, "Dispute Resolution Refund for Order #{$order->order_number}");
            if (!$refundRes['success']) {
                return back()->with('error', 'Razorpay Refund failed: ' . $refundRes['message']);
            }
        }

        // 2. Process Transfer to Seller (if any payout is due)
        $transferId = null;
        $payoutStatus = 'success';
        $payoutError = null;

        if ($sellerPayout > 0) {
            if (!empty($accountId)) {
                $transferRes = \App\Services\RazorpayService::transferToSeller($accountId, $sellerPayout, $order->order_number);
                if ($transferRes['success']) {
                    $transferId = $transferRes['transfer_id'];
                } else {
                    $payoutStatus = 'failed';
                    $payoutError = $transferRes['message'];
                }
            } else {
                $payoutStatus = 'failed';
                $payoutError = "Seller Razorpay account ID is not configured.";
            }
        }

        DB::transaction(function () use ($dispute, $decision, $fault, $notes, $buyerPayout, $sellerPayout, $transferId, $payoutStatus, $payoutError) {
            $order = $dispute->order;

            if ($decision === 'buyer_favor') {
                $order->update([
                    'order_status' => 'refunded',
                    'cancellation_reason' => 'Dispute resolved in favor of buyer: ' . $notes,
                ]);

                if ($order->escrow) {
                    $order->escrow->update([
                        'status' => 'refunded',
                        'payout_status' => 'none',
                        'seller_amount' => 0,
                        'commission_amount' => 0,
                        'released_at' => Carbon::now(),
                        'released_by' => auth()->user()->name ?? 'Admin',
                    ]);
                }
            } elseif ($decision === 'seller_favor') {
                $order->update([
                    'order_status' => $payoutStatus === 'failed' ? 'disputed' : 'completed',
                    'completed_at' => $payoutStatus === 'failed' ? null : Carbon::now(),
                ]);

                if ($order->escrow) {
                    $order->escrow->update([
                        'status' => $payoutStatus === 'failed' ? 'disputed' : 'released',
                        'payout_status' => $payoutStatus,
                        'payout_error_message' => $payoutError,
                        'razorpay_payout_id' => $transferId,
                        'released_at' => $payoutStatus === 'failed' ? null : Carbon::now(),
                        'released_by' => auth()->user()->name ?? 'Admin',
                    ]);
                }

                if ($payoutStatus !== 'failed') {
                    $order->buyer->increment('completed_orders_count');
                    $order->seller->user->increment('completed_orders_count');
                }
            } else {
                // Partial
                $order->update([
                    'order_status' => $payoutStatus === 'failed' ? 'disputed' : 'completed',
                    'completed_at' => $payoutStatus === 'failed' ? null : Carbon::now(),
                ]);

                if ($order->escrow) {
                    $order->escrow->update([
                        'status' => $payoutStatus === 'failed' ? 'disputed' : 'partially_released',
                        'payout_status' => $payoutStatus,
                        'payout_error_message' => $payoutError,
                        'razorpay_payout_id' => $transferId,
                        'seller_amount' => $sellerPayout,
                        'commission_amount' => 0,
                        'released_at' => $payoutStatus === 'failed' ? null : Carbon::now(),
                        'released_by' => auth()->user()->name ?? 'Admin',
                    ]);
                }

                if ($payoutStatus !== 'failed') {
                    $order->buyer->increment('completed_orders_count');
                    $order->seller->user->increment('completed_orders_count');
                }
            }

            // Update dispute with resolution details
            $dispute->update([
                'status' => 'resolved',
                'admin_decision' => $decision,
                'fault_assigned_to' => $fault,
                'resolution_notes' => $notes,
                'admin_notes' => $notes,
                'buyer_payout' => $buyerPayout,
                'seller_payout' => $sellerPayout,
                'resolved_at' => Carbon::now(),
            ]);

            // Increment fraud disputes count if fault is seller/shipping
            if (in_array($fault, ['seller', 'shipping'])) {
                $order->buyer->increment('fraud_disputes_count');
            }

            // Recalculate badges
            \App\Jobs\BuyerBadgeJob::dispatch($order->buyer_id);
            \App\Jobs\BadgeCalculationJob::dispatch($order->seller_id);

            // Create audit log entry
            AuditLog::create([
                'action' => 'dispute_resolved',
                'model_type' => Dispute::class,
                'model_id' => $dispute->id,
                'user_id' => Auth::id(),
                'old_values' => json_encode(['status' => 'under_review']),
                'new_values' => json_encode([
                    'status' => 'resolved',
                    'decision' => $decision,
                    'fault' => $fault,
                    'buyer_payout' => $buyerPayout,
                    'seller_payout' => $sellerPayout,
                    'notes' => $notes
                ]),
            ]);

            // Create a system resolution response in the dispute responses log
            DisputeResponse::create([
                'dispute_id' => $dispute->id,
                'responder_id' => Auth::id(),
                'responder_type' => 'admin',
                'response_text' => "System Notification: Dispute resolved in favor of " . ($decision === 'seller_favor' ? 'Seller' : ($decision === 'buyer_favor' ? 'Buyer' : 'both (Partial Release)')) . ".\nReason/Notes: " . $notes . "\nFault Assigned: " . ucfirst($fault) . "\nRefund Amount: ₹" . number_format($buyerPayout ?: 0, 2) . "\nPayout to Seller: ₹" . number_format($sellerPayout ?: 0, 2),
                'evidence_images' => [],
            ]);
        });

        // Send automatic template emails to both seller and buyer
        $order = $dispute->order;
        $buyerPayout = $dispute->buyer_payout;
        $sellerPayout = $dispute->seller_payout;

        $emailTokens = [
            'buyer_name' => $dispute->buyer->name,
            'seller_name' => $dispute->seller->user->name,
            'order_number' => $order->order_number,
            'resolution_notes' => "Resolution: " . ($decision === 'buyer_favor' ? 'Resolved in Buyer Favor (Full Refund)' : ($decision === 'seller_favor' ? 'Resolved in Seller Favor' : 'Partial Resolution')) . "\nNotes: " . $notes,
            'buyer_payout' => number_format($buyerPayout ?: 0, 2),
            'seller_payout' => number_format($sellerPayout ?: 0, 2),
        ];

        \App\Models\Notification::sendSystemMail($dispute->buyer, 'dispute_resolved', $emailTokens);
        \App\Models\Notification::sendSystemMail($dispute->seller->user, 'dispute_resolved', $emailTokens);

        $this->logAction("dispute_resolved", "Dispute #{$dispute->id} resolved: Decision={$decision}, Fault={$fault}");

        return redirect()->route('admin.disputes.show', $dispute->id)->with('success', 'Dispute has been resolved successfully.');
    }

    /**
     * Reopen a resolved dispute.
     */
    public function disputeReopen(int $id)
    {
        $dispute = Dispute::with(['order.escrow', 'buyer', 'seller.user'])->findOrFail($id);

        if ($dispute->status !== 'resolved') {
            return back()->with('error', 'Only resolved disputes can be reopened.');
        }

        DB::transaction(function () use ($dispute) {
            $order = $dispute->order;

            // Decrement completed order counts if they were previously incremented
            if (in_array($dispute->admin_decision, ['seller_favor', 'partial'])) {
                $dispute->buyer->decrement('completed_orders_count');
                $dispute->seller->user->decrement('completed_orders_count');
            }

            // Decrement fraud disputes count if fault was seller/shipping
            if (in_array($dispute->fault_assigned_to, ['seller', 'shipping'])) {
                $dispute->buyer->decrement('fraud_disputes_count');
            }

            // Set order status back to disputed
            $order->update([
                'order_status' => 'disputed',
            ]);

            // Set escrow status back to disputed
            if ($order->escrow) {
                $order->escrow->update([
                    'status' => 'disputed',
                    'released_at' => null,
                ]);
            }

            // Set status back to under_review, keeping old resolution fields intact to preserve history
            $dispute->update([
                'status' => 'under_review',
                'resolved_at' => null,
            ]);

            // Create a system notification response in the dispute responses log
            DisputeResponse::create([
                'dispute_id' => $dispute->id,
                'responder_id' => Auth::id(),
                'responder_type' => 'admin',
                'response_text' => "System Notification: This dispute has been reopened by the administrator for further investigation.",
                'evidence_images' => [],
            ]);

            // Create audit log entry
            AuditLog::create([
                'action' => 'dispute_reopened',
                'model_type' => Dispute::class,
                'model_id' => $dispute->id,
                'user_id' => Auth::id(),
                'old_values' => json_encode(['status' => 'resolved']),
                'new_values' => json_encode(['status' => 'under_review']),
            ]);
        });

        // Send automatic emails to both seller and buyer notifying them of reopening
        $order = $dispute->order;
        $tokens = [
            'order_number' => $order->order_number,
        ];

        if ($dispute->buyer) {
            \App\Models\Notification::sendSystemMail($dispute->buyer, 'dispute_reopened', $tokens);
        }
        if ($dispute->seller && $dispute->seller->user) {
            \App\Models\Notification::sendSystemMail($dispute->seller->user, 'dispute_reopened', $tokens);
        }

        $this->logAction("dispute_reopened", "Dispute #{$dispute->id} reopened by admin.");

        return redirect()->route('admin.disputes.show', $dispute->id)->with('success', 'Dispute has been reopened successfully.');
    }

    /**
     * Site Settings.
     */
    public function settings()
    {
        $settings = SiteSetting::all();
        
        $defaults = [
            'platform_name' => ['value' => 'X-Buy', 'type' => 'string', 'group' => 'general', 'description' => 'Name of the platform'],
            'support_email' => ['value' => 'support@xbuy.in', 'type' => 'string', 'group' => 'general', 'description' => 'Customer support email address'],
            'website_logo' => ['value' => '/website_assets/images/logo.png', 'type' => 'string', 'group' => 'general', 'description' => 'Platform logo image file'],
            'website_favicon' => ['value' => '/website_assets/images/favicon.ico', 'type' => 'string', 'group' => 'general', 'description' => 'Platform favicon file'],
            'homepage_banner_image' => ['value' => '', 'type' => 'string', 'group' => 'general', 'description' => 'Homepage promotional banner image'],
            'homepage_banner_url' => ['value' => '/listings', 'type' => 'string', 'group' => 'general', 'description' => 'Homepage promotional banner button redirect URL'],
            'currency_selector' => ['value' => 'INR', 'type' => 'string', 'group' => 'general', 'description' => 'Platform display currency (INR or USD)'],
            'timezone_selector' => ['value' => 'Asia/Kolkata', 'type' => 'string', 'group' => 'general', 'description' => 'Platform timezone setting'],
            'admin_2fa_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Require Two-Factor Authentication (2FA) for all administrative staff login'],
            'ticket_admin_email' => ['value' => 'contact@x-buy.in', 'type' => 'string', 'group' => 'tickets', 'description' => 'Admin email to receive support tickets'],
            'ticket_email_notifications_enabled' => ['value' => '1', 'type' => 'boolean', 'group' => 'tickets', 'description' => 'Send email alerts to admin when a new ticket is created'],
            'ticket_whatsapp_notifications_enabled' => ['value' => '0', 'type' => 'boolean', 'group' => 'tickets', 'description' => 'Trigger WhatsApp alert when a new ticket is created'],
            'ticket_whatsapp_number' => ['value' => '', 'type' => 'string', 'group' => 'tickets', 'description' => 'WhatsApp number (with country code) for support ticket alerts'],
        ];

        foreach ($defaults as $key => $data) {
            if (!$settings->contains('key', $key)) {
                $newSetting = SiteSetting::create(array_merge(['key' => $key], $data));
                $settings->push($newSetting);
            }
        }

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update Site Settings.
     */
    public function settingsUpdate(Request $request)
    {
        // Handle file uploads first
        if ($request->hasFile('website_logo')) {
            $file = $request->file('website_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('website_assets/images'), $filename);
            
            SiteSetting::updateOrCreate(
                ['key' => 'website_logo'],
                ['value' => '/website_assets/images/' . $filename, 'type' => 'string', 'group' => 'general']
            );
        } elseif ($request->filled('selected_logo_path')) {
            $logoPath = $request->input('selected_logo_path');
            if ($logoPath === 'remove') {
                $logoPath = '';
            }
            SiteSetting::updateOrCreate(
                ['key' => 'website_logo'],
                ['value' => $logoPath, 'type' => 'string', 'group' => 'general']
            );
        }

        if ($request->hasFile('website_favicon')) {
            $file = $request->file('website_favicon');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('website_assets/images'), $filename);
            
            SiteSetting::updateOrCreate(
                ['key' => 'website_favicon'],
                ['value' => '/website_assets/images/' . $filename, 'type' => 'string', 'group' => 'general']
            );
        } elseif ($request->filled('selected_favicon_path')) {
            $faviconPath = $request->input('selected_favicon_path');
            if ($faviconPath === 'remove') {
                $faviconPath = '';
            }
            SiteSetting::updateOrCreate(
                ['key' => 'website_favicon'],
                ['value' => $faviconPath, 'type' => 'string', 'group' => 'general']
            );
        }

        if ($request->hasFile('homepage_banner_image')) {
            $file = $request->file('homepage_banner_image');
            $filename = 'banner_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('website_assets/images'), $filename);
            
            SiteSetting::updateOrCreate(
                ['key' => 'homepage_banner_image'],
                ['value' => '/website_assets/images/' . $filename, 'type' => 'string', 'group' => 'general']
            );
        } elseif ($request->filled('selected_banner_path')) {
            $bannerPath = $request->input('selected_banner_path');
            if ($bannerPath === 'remove') {
                $bannerPath = '';
            }
            SiteSetting::updateOrCreate(
                ['key' => 'homepage_banner_image'],
                ['value' => $bannerPath, 'type' => 'string', 'group' => 'general']
            );
        }

        $group = $request->input('settings_group');
        if ($group) {
            $settingsInGroup = SiteSetting::where('group', $group)->get();
            
            foreach ($settingsInGroup as $setting) {
                if (in_array($setting->key, ['commission_rates', 'testing_windows', 'website_logo', 'website_favicon', 'homepage_banner_image'])) {
                    continue;
                }
                
                if ($setting->type === 'boolean') {
                    $val = $request->has($setting->key) ? '1' : '0';
                    $setting->update(['value' => $val]);
                } elseif ($request->has($setting->key)) {
                    $setting->update(['value' => $request->input($setting->key)]);
                }
            }
        }

        // Special handling for commission_rates and testing_windows if they are sent
        if ($request->has('commission_rates')) {
            $rates = $request->input('commission_rates');
            if (is_string($rates)) {
                $rates = json_decode($rates, true);
            }
            if (is_array($rates)) {
                $formatted = [];
                foreach ($rates as $cat => $rate) {
                    $formatted[$cat] = (float) $rate;
                }
                SiteSetting::updateOrCreate(
                    ['key' => 'commission_rates'],
                    ['value' => json_encode($formatted), 'type' => 'json', 'group' => 'commission']
                );
            }
        }

        if ($request->has('testing_windows')) {
            $windows = $request->input('testing_windows');
            if (is_string($windows)) {
                $windows = json_decode($windows, true);
            }
            if (is_array($windows)) {
                $formatted = [];
                foreach ($windows as $cat => $days) {
                    $formatted[$cat] = (int) $days;
                }
                SiteSetting::updateOrCreate(
                    ['key' => 'testing_windows'],
                    ['value' => json_encode($formatted), 'type' => 'json', 'group' => 'testing']
                );
            }
        }

        $this->logAction("settings_updated", "Admin updated site settings config group: " . ($group ?: 'all'));

        return back()->with('success', 'Site settings updated successfully.');
    }

    /**
     * Audit Logs & activity.
     */
    public function auditLogs()
    {
        $logs = AdminActivityLog::with('admin')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.audit', compact('logs'));
    }

    /**
     * Test SMTP Connection.
     */
    public function testSmtpConnection(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_from_email' => 'required|email',
            'smtp_from_name' => 'required|string',
            'smtp_encryption' => 'required|in:TLS,SSL,None',
            'test_email' => 'required|email',
        ]);

        try {
            // Temporarily override config
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $request->input('smtp_host'),
                'mail.mailers.smtp.port' => $request->input('smtp_port'),
                'mail.mailers.smtp.username' => $request->input('smtp_username'),
                'mail.mailers.smtp.password' => $request->input('smtp_password'),
                'mail.mailers.smtp.encryption' => $request->input('smtp_encryption') === 'None' ? null : strtolower($request->input('smtp_encryption')),
                'mail.from.address' => $request->input('smtp_from_email'),
                'mail.from.name' => $request->input('smtp_from_name'),
            ]);

            // Send test email
            \Illuminate\Support\Facades\Mail::raw('SMTP configuration test successful!', function ($message) use ($request) {
                $message->to($request->input('test_email'))
                        ->subject('X-Buy SMTP Connection Test');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $request->input('test_email'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /admin/analytics/gmv
     */
    public function analyticsGmv()
    {
        $totalGmv = Order::where('order_status', 'completed')->sum('total_amount');
        
        $dbDriver = \Illuminate\Support\Facades\DB::getDriverName();
        $dateSql = $dbDriver === 'sqlite' 
            ? 'strftime("%Y-%m", completed_at) as month' 
            : 'DATE_FORMAT(completed_at, "%Y-%m") as month';

        $monthly = Order::where('order_status', 'completed')
            ->selectRaw($dateSql . ', SUM(total_amount) as amount')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_gmv' => (float)$totalGmv,
                'monthly' => $monthly,
            ]
        ]);
    }

    /**
     * GET /admin/analytics/top-sellers
     */
    public function analyticsTopSellers()
    {
        $topSellers = SellerProfile::with('user')
            ->orderBy('total_earnings', 'desc')
            ->take(10)
            ->get()
            ->map(function ($s) {
                return [
                    'seller_id' => $s->id,
                    'shop_name' => $s->shop_name,
                    'owner_name' => $s->user->name ?? 'N/A',
                    'total_earnings' => (float)$s->total_earnings,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topSellers
        ]);
    }

    /**
     * GET /admin/analytics/top-categories
     */
    public function analyticsTopCategories()
    {
        $topCategories = Order::where('order_status', 'completed')
            ->join('listings', 'orders.listing_id', '=', 'listings.id')
            ->selectRaw('listings.category, count(*) as order_count, sum(orders.total_amount) as total_amount')
            ->groupBy('listings.category')
            ->orderBy('total_amount', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topCategories
        ]);
    }

    /**
     * GET /admin/analytics/dispute-rate
     */
    public function analyticsDisputeRate()
    {
        $totalOrders = Order::count();
        $disputedOrders = Order::where('order_status', 'disputed')
            ->orWhereHas('dispute')
            ->count();

        $rate = $totalOrders > 0 ? round(($disputedOrders / $totalOrders) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'disputed_orders' => $disputedOrders,
                'dispute_rate_percent' => $rate,
            ]
        ]);
    }

    /**
     * GET /admin/dashboard/charts/orders
     */
    public function chartOrders()
    {
        $orders = Order::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * GET /admin/dashboard/charts/revenue
     */
    public function chartRevenue()
    {
        $revenue = Order::where('order_status', 'completed')
            ->selectRaw('DATE(completed_at) as date, SUM(commission_amount) as amount')
            ->where('completed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $revenue
        ]);
    }

    /**
     * GET /admin/dashboard/charts/categories
     */
    public function chartCategories()
    {
        $categories = Listing::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * POST /admin/sellers/{id}/visit-verify
     */
    public function sellerVisitVerify(int $id)
    {
        $seller = SellerProfile::findOrFail($id);
        $seller->update([
            'shop_visit_verified' => true,
            'shop_visit_date' => now(),
        ]);

        $this->logAction("seller_visit_verified", "Seller ID #{$seller->id} shop visit verified");

        return back()->with('success', 'Seller shop visit has been verified successfully.');
    }

    /**
     * PUT /admin/settings/smtp
     */
    public function settingsSmtpUpdate(Request $request)
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        $this->logAction("smtp_settings_updated", "Admin updated SMTP configuration");
 
        return back()->with('success', 'SMTP settings updated successfully.');
    }
 


    /**
     * Support Tickets List.
     */
    public function tickets(Request $request)
    {
        $this->pruneTrashedData();
        $query = \App\Models\SupportTicket::with('buyer');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function($qb) use ($search) {
                      $qb->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->input('date_start'));
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->input('date_end'));
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        $trashedTickets = \App\Models\SupportTicket::onlyTrashed()->with('buyer')->get();
        return view('admin.tickets.index', compact('tickets', 'trashedTickets'));
    }

    /**
     * Update support ticket status.
     */
    public function updateTicketStatus(int $id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved',
        ]);

        $ticket = \App\Models\SupportTicket::findOrFail($id);
        $ticket->update(['status' => $request->input('status')]);

        $this->logAction("ticket_status_updated", "Support Ticket #{$ticket->id} status updated to " . $request->input('status'));

        return back()->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Bulk Actions
     */
    public function bulkActionSellers(Request $request)
    {
        return $this->handleBulkAction($request, \App\Models\SellerProfile::class, 'status');
    }

    public function bulkActionListings(Request $request)
    {
        return $this->handleBulkAction($request, \App\Models\Listing::class, 'status');
    }

    public function bulkActionOrders(Request $request)
    {
        return $this->handleBulkAction($request, \App\Models\Order::class, 'order_status');
    }

    public function bulkActionDisputes(Request $request)
    {
        return $this->handleBulkAction($request, \App\Models\Dispute::class, 'status');
    }

    public function bulkActionEscrows(Request $request)
    {
        return $this->handleBulkAction($request, \App\Models\Escrow::class, 'status');
    }

    public function bulkActionPayouts(Request $request)
    {
        // Payouts uses the Escrow model
        return $this->handleBulkAction($request, \App\Models\Escrow::class, 'status');
    }

    public function bulkActionTickets(Request $request)
    {
        return $this->handleBulkAction($request, \App\Models\SupportTicket::class, 'status');
    }

    private function handleBulkAction(Request $request, $modelClass, $statusColumn = 'status')
    {
        $action = $request->input('action');
        $ids = json_decode($request->input('selected_ids', '[]'), true);
        
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No items selected.');
        }

        if ($action === 'delete') {
            $modelClass::whereIn('id', $ids)->delete();
            $this->logAction("bulk_delete", "Deleted " . count($ids) . " records from " . class_basename($modelClass));
            return back()->with('success', 'Selected items moved to trash.');
        } elseif (str_starts_with($action, 'status_')) {
            $status = str_replace('status_', '', $action);
            $modelClass::whereIn('id', $ids)->update([$statusColumn => $status]);
            $this->logAction("bulk_status_update", "Updated status to '{$status}' for " . count($ids) . " records in " . class_basename($modelClass));
            return back()->with('success', 'Status updated for selected items.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function alerts(Request $request)
    {
        $this->pruneTrashedData();
        $query = \App\Models\AdminAlert::where(function ($q) {
            $q->whereNull('user_id')->orWhere('user_id', Auth::id());
        });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'unread') {
                $query->where('is_read', false);
            } elseif ($status === 'read') {
                $query->where('is_read', true);
            }
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->input('date_end'));
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(15);
        $trashedAlerts = \App\Models\AdminAlert::onlyTrashed()
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', Auth::id());
            })->get();

        return view('admin.alerts', compact('alerts', 'trashedAlerts'));
    }

    public function markAlertRead(int $id)
    {
        $alert = \App\Models\AdminAlert::where(function ($q) {
            $q->whereNull('user_id')->orWhere('user_id', Auth::id());
        })->findOrFail($id);
        $alert->update(['is_read' => true]);

        return back()->with('success', 'Alert marked as read.');
    }

    public function markAllAlertsRead()
    {
        \App\Models\AdminAlert::where('is_read', false)
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', Auth::id());
            })->update(['is_read' => true]);

        return back()->with('success', 'All alerts marked as read.');
    }

    public function clearAllAlerts()
    {
        \App\Models\AdminAlert::where(function ($q) {
            $q->whereNull('user_id')->orWhere('user_id', Auth::id());
        })->delete();

        return back()->with('success', 'All alerts cleared.');
    }

    /**
     * Admin manually credits a user's wallet.
     */
    public function creditWallet(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|gt:0',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = \App\Models\User::findOrFail($request->input('user_id'));
        $wallet = $user->getWalletInstance();

        $amount = (float) $request->input('amount');
        $description = $request->input('description', 'Admin manual credit');

        $transaction = $wallet->credit($amount, 'admin_credit', null, $description);

        return response()->json([
            'success' => true,
            'message' => 'Wallet credited successfully',
            'data' => [
                'user_id' => $user->id,
                'wallet_balance' => (float) $wallet->balance,
                'transaction' => $transaction
            ]
        ]);
    }

    /**
     * Admin: List fraud flags.
     */
    public function listFraudFlags(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $query = \App\Models\FraudFlag::with(['flaggedUser', 'flaggedListing', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('flag_type')) {
            $query->where('flag_type', $request->input('flag_type'));
        }

        $flags = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Fraud flags retrieved successfully',
            'data' => $flags
        ]);
    }

    /**
     * Admin: Mark fraud flag as reviewed.
     */
    public function reviewFraudFlag(int $id, Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $flag = \App\Models\FraudFlag::findOrFail($id);
        $flag->update([
            'status' => 'reviewed',
            'reviewed_by' => $request->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fraud flag marked as reviewed successfully',
            'data' => $flag->load(['reviewer'])
        ]);
    }

    /**
     * Admin: Dismiss fraud flag.
     */
    public function dismissFraudFlag(int $id, Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $flag = \App\Models\FraudFlag::findOrFail($id);
        $flag->update([
            'status' => 'dismissed',
            'reviewed_by' => $request->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fraud flag dismissed successfully',
            'data' => $flag->load(['reviewer'])
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Fraud Flags Web Panel (Blade)
    // ─────────────────────────────────────────────────────────────

    /**
     * Show the Fraud Flags admin panel.
     */
    public function fraudFlagsIndex(Request $request)
    {
        $this->pruneTrashedData();
        $query = \App\Models\FraudFlag::with(['flaggedUser', 'flaggedListing', 'reviewer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('flag_type')) {
            $query->where('flag_type', $request->input('flag_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('flaggedUser', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('flaggedListing', fn($l) => $l->where('title', 'like', "%{$search}%"));
            });
        }

        // Date range filter
        if ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('created_at', [
                \Illuminate\Support\Carbon::parse($request->input('date_start'))->startOfDay(),
                \Illuminate\Support\Carbon::parse($request->input('date_end'))->endOfDay(),
            ]);
        } elseif ($request->filled('date_start')) {
            $query->where('created_at', '>=', \Illuminate\Support\Carbon::parse($request->input('date_start'))->startOfDay());
        } elseif ($request->filled('date_end')) {
            $query->where('created_at', '<=', \Illuminate\Support\Carbon::parse($request->input('date_end'))->endOfDay());
        }

        $flags = $query->paginate(20)->withQueryString();

        $summary = [
            'total'    => \App\Models\FraudFlag::count(),
            'pending'  => \App\Models\FraudFlag::where('status', 'pending')->count(),
            'reviewed' => \App\Models\FraudFlag::where('status', 'reviewed')->count(),
            'dismissed'=> \App\Models\FraudFlag::where('status', 'dismissed')->count(),
        ];

        $trashedFlags = \App\Models\FraudFlag::onlyTrashed()->with(['flaggedUser', 'flaggedListing', 'reviewer'])->get();

        return view('admin.fraud-flags.index', compact('flags', 'summary', 'trashedFlags'));
    }

    /**
     * Manually create a fraud flag.
     */
    public function createFraudFlagManual(Request $request)
    {
        $validated = $request->validate([
            'flag_type'          => 'required|in:duplicate_serial,multiple_accounts_same_ip,same_bank_multiple_sellers,rapid_listings,suspicious_buyer_pattern',
            'flagged_user_id'    => 'nullable|exists:users,id',
            'flagged_listing_id' => 'nullable|exists:listings,id',
            'note'               => 'nullable|string|max:500',
        ]);

        \App\Models\FraudFlag::create([
            'flag_type'          => $validated['flag_type'],
            'flagged_user_id'    => $validated['flagged_user_id'] ?? null,
            'flagged_listing_id' => $validated['flagged_listing_id'] ?? null,
            'details'            => ['note' => $validated['note'] ?? 'Manually flagged by admin'],
            'status'             => 'pending',
        ]);

        $this->logAction('fraud_flag_manual_create', "Manually created fraud flag of type: {$validated['flag_type']}");

        return redirect()->route('admin.fraud-flags.index')
            ->with('success', 'Fraud flag created successfully.');
    }

    /**
     * Mark a fraud flag as reviewed (web).
     */
    public function reviewFraudFlagWeb(int $id)
    {
        $flag = \App\Models\FraudFlag::findOrFail($id);
        $flag->update(['status' => 'reviewed', 'reviewed_by' => Auth::id()]);

        $this->logAction('fraud_flag_review', "Reviewed fraud flag #{$id} (type: {$flag->flag_type})");

        return redirect()->back()->with('success', "Flag #{$id} marked as reviewed.");
    }

    /**
     * Dismiss a fraud flag (web).
     */
    public function dismissFraudFlagWeb(int $id)
    {
        $flag = \App\Models\FraudFlag::findOrFail($id);
        $flag->update(['status' => 'dismissed', 'reviewed_by' => Auth::id()]);

        $this->logAction('fraud_flag_dismiss', "Dismissed fraud flag #{$id} (type: {$flag->flag_type})");

        return redirect()->back()->with('success', "Flag #{$id} dismissed.");
    }

    public function viewTrash(string $modelName)
    {
        $class = $this->getModelClass($modelName);
        if (!$class && $modelName !== 'content') abort(404);

        $search = request('search');

        if ($modelName === 'content') {
            $types = ['images', 'pdfs', 'videos'];
            $trashedContent = [];
            foreach ($types as $type) {
                $dir = public_path('website_assets/trash/' . $type);
                $trashedContent[$type] = [];
                if (\Illuminate\Support\Facades\File::exists($dir)) {
                    $files = \Illuminate\Support\Facades\File::files($dir);
                    foreach ($files as $file) {
                        $filename = $file->getFilename();
                        $relativePath = '/website_assets/trash/' . $type . '/' . $filename;
                        
                        $originalName = $filename;
                        if (preg_match('/^(.*)_deleted_\d+\.(.*)$/i', $filename, $matches)) {
                            $originalName = $matches[1] . '.' . $matches[2];
                        }
                        
                        if ($search && !str_contains(strtolower($originalName), strtolower($search)) && !str_contains(strtolower($filename), strtolower($search))) {
                            continue;
                        }

                        $trashedContent[$type][] = [
                            'name' => $filename,
                            'original_name' => $originalName,
                            'url' => $relativePath,
                            'size' => round($file->getSize() / 1024, 2) . ' KB',
                            'deleted_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        ];
                    }
                }
            }
            return view('admin.trash', compact('modelName', 'trashedContent'));
        }

        $query = $class::onlyTrashed();
        if ($modelName === 'pages') {
            $query->with('category');
        }

        if ($search) {
            $query->where(function($q) use ($search, $modelName) {
                if ($modelName === 'articles') {
                    $q->where('title', 'like', "%{$search}%")->orWhere('body', 'like', "%{$search}%");
                } elseif ($modelName === 'sellers') {
                    $q->where('shop_name', 'like', "%{$search}%");
                } elseif ($modelName === 'listings') {
                    $q->where('title', 'like', "%{$search}%")->orWhere('serial_number', 'like', "%{$search}%");
                } elseif ($modelName === 'orders') {
                    $q->where('order_number', 'like', "%{$search}%");
                } elseif ($modelName === 'disputes') {
                    $q->where('id', $search)->orWhereHas('order', function($o) use ($search) {
                        $o->where('order_number', 'like', "%{$search}%");
                    });
                } elseif ($modelName === 'escrow' || $modelName === 'payouts') {
                    $q->where('id', $search)->orWhere('razorpay_transfer_id', 'like', "%{$search}%");
                } elseif ($modelName === 'tickets') {
                    $q->where('subject', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%");
                } elseif ($modelName === 'subscribers') {
                    $q->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
                } elseif ($modelName === 'ratings') {
                    $q->where('review_text', 'like', "%{$search}%");
                } elseif ($modelName === 'fraud-flags') {
                    $q->where('flag_type', 'like', "%{$search}%");
                } elseif ($modelName === 'alerts') {
                    $q->where('title', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%");
                } elseif ($modelName === 'pages') {
                    $q->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
                } elseif ($modelName === 'page-categories') {
                    $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
                } elseif ($modelName === 'categories') {
                    $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
                } else {
                    $q->where('id', $search);
                }
            });
        }

        $trashedItems = $query->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.trash', compact('modelName', 'trashedItems'));
    }

    public function deleteModel(string $modelName, int $id)
    {
        $class = $this->getModelClass($modelName);
        if (!$class) abort(404);
        
        $record = $class::findOrFail($id);
        $record->delete();
        
        $this->logAction("delete_{$modelName}", "Moved record #{$id} from {$modelName} to trash");
        
        return redirect()->back()->with('success', 'Item moved to trash.');
    }

    public function restoreModel(string $modelName, int $id)
    {
        $class = $this->getModelClass($modelName);
        if (!$class) abort(404);
        
        $record = $class::onlyTrashed()->findOrFail($id);
        $record->restore();
        
        $this->logAction("restore_{$modelName}", "Restored record #{$id} from {$modelName} trash");
        
        return redirect()->back()->with('success', 'Item restored successfully.');
    }

    public function forceDeleteModel(string $modelName, int $id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only the Superadmin can permanently delete items.');
        }

        $class = $this->getModelClass($modelName);
        if (!$class) abort(404);
        
        $record = $class::onlyTrashed()->findOrFail($id);
        $record->forceDelete();
        
        $this->logAction("force_delete_{$modelName}", "Permanently deleted record #{$id} from {$modelName}");
        
        return redirect()->back()->with('success', 'Item permanently deleted.');
    }

    private function getModelClass(string $model)
    {
        $map = [
            'sellers' => \App\Models\SellerProfile::class,
            'listings' => \App\Models\Listing::class,
            'orders' => \App\Models\Order::class,
            'disputes' => \App\Models\Dispute::class,
            'escrow' => \App\Models\Escrow::class,
            'payouts' => \App\Models\Escrow::class,
            'tickets' => \App\Models\SupportTicket::class,
            'articles' => \App\Models\Article::class,
            'subscribers' => \App\Models\Subscriber::class,
            'ratings' => \App\Models\Rating::class,
            'fraud-flags' => \App\Models\FraudFlag::class,
            'alerts' => \App\Models\AdminAlert::class,
            'pages' => \App\Models\Page::class,
            'page-categories' => \App\Models\PageCategory::class,
            'categories' => \App\Models\Category::class,
        ];
        return $map[$model] ?? null;
    }

    private function pruneTrashedData()
    {
        $cutoff = now()->subDays(30);
        $models = [
            \App\Models\SellerProfile::class,
            \App\Models\Listing::class,
            \App\Models\Order::class,
            \App\Models\Dispute::class,
            \App\Models\Escrow::class,
            \App\Models\SupportTicket::class,
            \App\Models\Article::class,
            \App\Models\Subscriber::class,
            \App\Models\Rating::class,
            \App\Models\FraudFlag::class,
            \App\Models\AdminAlert::class,
            \App\Models\Category::class,
        ];
        foreach ($models as $model) {
            $model::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
        }
    }

    /**
     * Show staff profile settings.
     */
    public function profile()
    {
        $user = Auth::user();
        $profile = \App\Models\StaffProfile::firstOrCreate(['user_id' => $user->id]);
        $changeRequests = \App\Models\SupportTicket::where('user_id', $user->id)
            ->where('subject', 'Profile Change Request')
            ->latest()
            ->get();
        return view('admin.profile', compact('user', 'profile', 'changeRequests'));
    }

    /**
     * Update/Submit profile change requests.
     */
    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $profile = \App\Models\StaffProfile::firstOrCreate(['user_id' => $user->id]);

        $request->validate([
            'gender' => 'nullable|string|in:male,female,other',
            'dob' => 'nullable|date',
            // fields that require approval (request type)
            'requested_name' => 'nullable|string|max:100',
            'requested_email' => 'nullable|email|max:150',
            'requested_mobile' => 'nullable|string|max:20',
            'requested_bank_account' => 'nullable|string|max:200',
            'requested_upi_id' => 'nullable|string|max:200',
        ]);

        // Direct Updates
        $user->update([
            'gender' => $request->input('gender'),
            'dob' => $request->input('dob'),
        ]);

        // Check for Change Requests
        $changes = [];
        if ($request->filled('requested_name') && $request->input('requested_name') !== $user->name) {
            $changes['Name'] = "Current: '{$user->name}' -> Proposed: '" . $request->input('requested_name') . "'";
        }
        if ($request->filled('requested_email') && $request->input('requested_email') !== $user->email) {
            $changes['Email'] = "Current: '{$user->email}' -> Proposed: '" . $request->input('requested_email') . "'";
        }
        if ($request->filled('requested_mobile') && $request->input('requested_mobile') !== $user->phone) {
            $changes['Mobile'] = "Current: '{$user->phone}' -> Proposed: '" . $request->input('requested_mobile') . "'";
        }
        if ($request->filled('requested_bank_account') && $request->input('requested_bank_account') !== $profile->bank_account) {
            $changes['Bank Account'] = "Proposed change to Bank Account Details";
        }
        if ($request->filled('requested_upi_id') && $request->input('requested_upi_id') !== $profile->upi_id) {
            $changes['UPI ID'] = "Current: '" . ($profile->upi_id ?: 'None') . "' -> Proposed: '" . $request->input('requested_upi_id') . "'";
        }

        if (!empty($changes)) {
            $changesSummary = '';
            foreach ($changes as $field => $desc) {
                $changesSummary .= "• {$field}: {$desc}\n";
            }

            // Create notification alert for Super Admin
            \App\Models\AdminAlert::create([
                'title' => 'Profile Change Request from ' . $user->name,
                'message' => "Staff admin '{$user->name}' (ID #{$user->id}) has requested profile updates:\n\n" . $changesSummary . "\nRequested on: " . now()->toDateTimeString(),
                'is_read' => false,
                'user_id' => null, // null means visible to all super admins / moderators
            ]);

            // Save change request as Support Ticket
            \App\Models\SupportTicket::create([
                'user_id' => $user->id,
                'subject' => 'Profile Change Request',
                'message' => $changesSummary,
                'status' => 'open',
            ]);

            $this->logAction("profile_change_requested", "Staff admin submitted a profile change request for: " . implode(', ', array_keys($changes)));

            return back()->with('success', 'Profile changes submitted for approval. Direct fields (Gender/DOB) updated directly.');
        }

        $this->logAction("profile_updated", "Staff admin updated profile details directly (Gender/DOB).");
        return back()->with('success', 'Profile updated successfully.');
    }
}
