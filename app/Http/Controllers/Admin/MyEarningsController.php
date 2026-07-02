<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffEarning;
use App\Models\StaffPayrollNotice;
use Illuminate\Http\Request;

class MyEarningsController extends Controller
{
    /**
     * Page E: My Earnings — /admin/my-earnings
     * Staff-facing view of their own earnings
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Handle filter month_year (format: YYYY-MM)
        $month = now()->month;
        $year  = now()->year;

        if ($request->filled('month_year')) {
            $parts = explode('-', $request->input('month_year'));
            if (count($parts) === 2) {
                $year = (int)$parts[0];
                $month = (int)$parts[1];
            }
        }

        // Summary cards
        $thisMonthEarned = StaffEarning::where('staff_user_id', $user->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->sum('final_earning');

        $lastMonth = now()->subMonth();
        $lastMonthEarned = StaffEarning::where('staff_user_id', $user->id)
            ->where('month', $lastMonth->month)
            ->where('year', $lastMonth->year)
            ->sum('final_earning');

        $totalEarned = StaffEarning::where('staff_user_id', $user->id)
            ->sum('final_earning');

        // Filtered earnings (what the view expects as filteredOrders)
        $filteredOrders = StaffEarning::where('staff_user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->with('order.listing')
            ->orderByDesc('created_at')
            ->get();

        // Salary slips downloadable records
        $salarySlips = StaffPayrollNotice::where('staff_user_id', $user->id)
            ->where('notice_type', 'salary_slip')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.my-earnings', compact(
            'thisMonthEarned', 'lastMonthEarned', 'totalEarned', 'filteredOrders', 'salarySlips'
        ));
    }

    /**
     * Printable statement for earnings
     */
    public function print(Request $request)
    {
        $user = auth()->user();

        $month = now()->month;
        $year  = now()->year;

        if ($request->filled('month_year')) {
            $parts = explode('-', $request->input('month_year'));
            if (count($parts) === 2) {
                $year = (int)$parts[0];
                $month = (int)$parts[1];
            }
        }

        $thisMonthEarned = StaffEarning::where('staff_user_id', $user->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->sum('final_earning');

        $lastMonth = now()->subMonth();
        $lastMonthEarned = StaffEarning::where('staff_user_id', $user->id)
            ->where('month', $lastMonth->month)
            ->where('year', $lastMonth->year)
            ->sum('final_earning');

        $totalEarned = StaffEarning::where('staff_user_id', $user->id)
            ->sum('final_earning');

        $filteredOrders = StaffEarning::where('staff_user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->with('order.listing')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.my-earnings-print', compact(
            'thisMonthEarned', 'lastMonthEarned', 'totalEarned', 'filteredOrders'
        ));
    }
}
