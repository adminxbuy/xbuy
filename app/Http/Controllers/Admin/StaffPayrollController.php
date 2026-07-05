<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\StaffEarning;
use App\Models\StaffPayrollNotice;
use App\Models\User;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffPayrollController extends Controller
{
    // ────────────────────────────────────────────────────────────────────
    // Page A: Staff Directory — /admin/staff
    // ────────────────────────────────────────────────────────────────────
    public function staffDirectory(Request $request)
    {
        // Self-healing: Ensure all admin users have is_staff = true and a StaffProfile
        $adminsWithoutStaff = User::where('role', 'admin')
            ->where(function($q) {
                $q->where('is_staff', false)->orWhereNull('is_staff');
            })->get();

        foreach ($adminsWithoutStaff as $admin) {
            $admin->update([
                'is_staff' => true,
                'staff_role' => $admin->admin_role === 'super_admin' ? 'superadmin' : 'admin',
            ]);
        }

        // Also ensure all admins with is_staff = true have a StaffProfile
        $staffUsers = User::where('is_staff', true)->get();
        foreach ($staffUsers as $su) {
            if (!$su->staffProfile) {
                \App\Models\StaffProfile::create([
                    'user_id' => $su->id,
                    'designation' => $su->admin_role === 'super_admin' ? 'Superadmin Executive' : ($su->admin_role ? ucfirst(str_replace('_', ' ', $su->admin_role)) : 'Staff Member'),
                    'appointment_date' => now()->toDateString(),
                    'appointment_letter_ref' => 'XBUY/APT/' . now()->year . '/' . sprintf('%03d', $su->id),
                    'status' => 'active',
                ]);
            }
        }

        $query = User::where('is_staff', true)
            ->with(['staffProfile']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $month = now()->month;
        $year  = now()->year;

        $staff = $query->get()->map(function($user) use ($month, $year) {
            $user->this_month_earned = StaffEarning::where('staff_user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('final_earning');

            $user->total_earned = StaffEarning::where('staff_user_id', $user->id)
                ->sum('final_earning');

            return $user;
        });

        return view('admin.staff.index', compact('staff'));
    }

    // ────────────────────────────────────────────────────────────────────
    // Page B: Staff Profile — /admin/staff/{id}
    // ────────────────────────────────────────────────────────────────────
    public function staffProfile(Request $request, int $id)
    {
        $member = User::where('is_staff', true)->with('staffProfile')->findOrFail($id);
        $profile = $member->staffProfile;

        // Earnings History filterable by month/year
        $earningsQuery = StaffEarning::where('staff_user_id', $id)
            ->with(['order.listing'])
            ->orderByDesc('created_at');

        if ($request->filled('month_year')) {
            $parts = explode('-', $request->input('month_year'));
            if (count($parts) === 2) {
                $earningsQuery->where('year', (int)$parts[0])
                             ->where('month', (int)$parts[1]);
            }
        }

        $allEarnings = $earningsQuery->get();

        // Group by month and year
        $grouped = $allEarnings->groupBy(function($earning) {
            return $earning->year . '-' . str_pad($earning->month, 2, '0', STR_PAD_LEFT);
        });

        $monthlySummaries = [];
        foreach ($grouped as $monthKey => $items) {
            $parts = explode('-', $monthKey);
            $year = (int)$parts[0];
            $month = (int)$parts[1];
            $monthName = Carbon::create($year, $month, 1)->format('F Y');

            $monthlySummaries[$monthKey] = [
                'month' => $monthKey,
                'month_name' => $monthName,
                'orders_count' => $items->count(),
                'gross' => $items->sum('gross_commission'),
                'expenses' => $items->sum('platform_expenses'),
                'net' => $items->sum('final_earning'),
                'status' => $items->first()->status ?? 'pending',
                'orders_list' => $items,
            ];
        }

        // Notices
        $notices = StaffPayrollNotice::where('staff_user_id', $id)
            ->orderByDesc('created_at')
            ->get();

        // Activity Logs
        $logs = AdminActivityLog::where('admin_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.staff.show', compact('member', 'profile', 'monthlySummaries', 'notices', 'logs'));
    }

    // ────────────────────────────────────────────────────────────────────
    // Page C: Payroll Overview — /admin/payroll
    // ────────────────────────────────────────────────────────────────────
    public function payrollOverview()
    {
        $month = now()->month;
        $year  = now()->year;

        $totalPendingThisMonth = StaffEarning::where('status', 'pending')
            ->where('month', $month)
            ->where('year', $year)
            ->sum('final_earning');

        $staffCount = User::where('is_staff', true)->count();

        $ordersThisMonth = Order::where('order_status', 'completed')
            ->whereMonth('completed_at', $month)
            ->whereYear('completed_at', $year)
            ->count();

        $triggerOrders = (int) SiteSetting::getVal('staff_amendment_trigger_orders', 100);
        $progressPct = $triggerOrders > 0 ? min(round(($ordersThisMonth / $triggerOrders) * 100), 100) : 0;

        // Fetch pending list staff-wise
        $pendingEarnings = StaffEarning::where('status', 'pending')
            ->where('month', $month)
            ->where('year', $year)
            ->with('staff.staffProfile')
            ->get()
            ->groupBy('staff_user_id');

        $pendingStaffList = [];
        foreach ($pendingEarnings as $staffId => $items) {
            $staff = $items->first()->staff;
            if ($staff) {
                $pendingStaffList[] = [
                    'staff' => $staff,
                    'orders_count' => $items->count(),
                    'gross' => $items->sum('gross_commission'),
                    'expenses' => $items->sum('platform_expenses'),
                    'net' => $items->sum('final_earning'),
                    'status' => 'pending',
                ];
            }
        }

        return view('admin.payroll.index', compact(
            'totalPendingThisMonth', 'staffCount', 'ordersThisMonth',
            'triggerOrders', 'progressPct', 'pendingStaffList'
        ));
    }

    // ────────────────────────────────────────────────────────────────────
    // Disburse All
    // ────────────────────────────────────────────────────────────────────
    public function disburseAll(Request $request)
    {
        $month = now()->month;
        $year  = now()->year;
        $monthName = Carbon::create($year, $month, 1)->format('F Y');

        // Find staff members with pending earnings
        $staffWithPending = StaffEarning::where('status', 'pending')
            ->where('month', $month)
            ->where('year', $year)
            ->distinct()
            ->pluck('staff_user_id');

        if ($staffWithPending->isEmpty()) {
            return redirect()->back()->with('error', 'No pending disbursements found for this cycle.');
        }

        $totalDisbursed = 0;

        \Illuminate\Support\Facades\DB::transaction(function() use ($staffWithPending, $month, $year, $monthName, &$totalDisbursed) {
            foreach ($staffWithPending as $staffId) {
                $staff = User::with('staffProfile')->findOrFail($staffId);

                // Fetch earnings
                $earnings = StaffEarning::where('staff_user_id', $staffId)
                    ->where('status', 'pending')
                    ->where('month', $month)
                    ->where('year', $year)
                    ->with('order')
                    ->get();

                $totalEarned = $earnings->sum('final_earning');
                $totalDisbursed += $totalEarned;

                $paymentRef = 'PAY-' . now()->format('YmdHis') . '-' . $staffId;

                // 1. Mark earnings as paid
                StaffEarning::where('staff_user_id', $staffId)
                    ->where('status', 'pending')
                    ->where('month', $month)
                    ->where('year', $year)
                    ->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                    ]);

                // 2. Generate PDF
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.payroll.slip_pdf', [
                    'staff'       => $staff,
                    'earnings'    => $earnings,
                    'totalEarned' => $totalEarned,
                    'monthName'   => $monthName,
                    'paymentRef'  => $paymentRef,
                ]);

                // Ensure directory exists and save PDF
                $pdfDirectory = storage_path('app/public/payroll_notices');
                if (!file_exists($pdfDirectory)) {
                    mkdir($pdfDirectory, 0755, true);
                }
                $pdfFileName = "slip_{$staffId}_{$year}_{$month}.pdf";
                $pdfPath = "{$pdfDirectory}/{$pdfFileName}";
                $pdf->save($pdfPath);
                $attachmentRelativePath = "payroll_notices/{$pdfFileName}";

                // 3. Save PDF in staff_payroll_notices
                StaffPayrollNotice::create([
                    'staff_user_id'   => $staffId,
                    'sent_by'         => auth()->id(),
                    'notice_type'     => 'salary_slip',
                    'subject'         => 'Salary Slip — ' . $monthName,
                    'message'         => "Your salary slip for {$monthName} has been generated. Total earned: ₹" . number_format($totalEarned, 2) . ". Payment reference: {$paymentRef}.",
                    'pdf_attachment'  => $attachmentRelativePath,
                    'delivery_method' => 'email',
                    'sent_via_email'  => true,
                    'month'           => $month,
                    'year'            => $year,
                ]);

                // 4. Send Email with PDF attached
                try {
                    $emailData = [
                        'staff'       => $staff,
                        'earnings'    => $earnings,
                        'totalEarned' => $totalEarned,
                        'monthName'   => $monthName,
                        'paymentRef'  => $paymentRef,
                    ];

                    \Illuminate\Support\Facades\Mail::send('admin.payroll.slip_email', $emailData, function ($message) use ($staff, $monthName, $pdf) {
                        $message->from('no-reply@x-buy.in', 'X-Buy')
                                ->to($staff->email)
                                ->subject("X-Buy Salary — {$monthName}")
                                ->attachData($pdf->output(), "salary_slip_{$monthName}.pdf", [
                                    'mime' => 'application/pdf',
                                ]);
                    });
                } catch (\Exception $mailEx) {
                    \Illuminate\Support\Facades\Log::error("Failed to send salary email to {$staff->email}: " . $mailEx->getMessage());
                }
            }
        });

        return redirect()->back()->with('success', "₹" . number_format($totalDisbursed, 2) . " disbursed. Salary slips and emails generated successfully.");
    }

    // ────────────────────────────────────────────────────────────────────
    // GET /admin/staff/{id}/notice Form
    // ────────────────────────────────────────────────────────────────────
    public function sendNoticeForm(int $id)
    {
        $member = User::where('is_staff', true)->findOrFail($id);
        return view('admin.staff.notice', compact('member'));
    }

    // ────────────────────────────────────────────────────────────────────
    // POST /admin/staff/{id}/notice Send
    // ────────────────────────────────────────────────────────────────────
    public function sendNotice(Request $request, int $id)
    {
        $request->validate([
            'type'            => 'required|string',
            'subject'         => 'required|string|max:255',
            'message'         => 'required|string',
            'delivery_method' => 'required|string|max:50',
            'pdf_file'        => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('pdf_file')) {
            $attachmentPath = $request->file('pdf_file')->store('payroll_notices', 'public');
        }

        // Map type input parameter to db enum/string column
        $noticeType = match($request->type) {
            'Official Warning' => 'warning',
            'Salary Slip' => 'salary_slip',
            'Promotion Letter' => 'general',
            'Performance Review' => 'general',
            'Bonus' => 'bonus',
            'Suspension Notice' => 'suspension_notice',
            'Termination Notice' => 'termination_notice',
            default => strtolower(str_replace([' ', '/'], '_', $request->type))
        };

        $notice = StaffPayrollNotice::create([
            'staff_user_id'     => $id,
            'sent_by'           => auth()->id(),
            'notice_type'       => $noticeType,
            'subject'           => $request->subject,
            'message'           => $request->message,
            'pdf_attachment'    => $attachmentPath,
            'delivery_method'   => $request->delivery_method,
            'sent_via_email'    => in_array($request->delivery_method, ['email', 'both', 'email_alert', 'all']),
            'sent_via_whatsapp' => in_array($request->delivery_method, ['whatsapp', 'both', 'all']),
            'month'             => now()->month,
            'year'              => now()->year,
        ]);

        $hasAlert = in_array($request->delivery_method, ['alert', 'email_alert', 'all']);
        if ($hasAlert) {
            \App\Models\AdminAlert::create([
                'user_id'        => $id,
                'type'           => 'system_error',
                'title'          => $request->subject,
                'message'        => strip_tags($request->message),
                'reference_type' => 'staff_payroll_notices',
                'reference_id'   => $notice->id,
                'severity'       => ($noticeType === 'warning' || $noticeType === 'termination_notice') ? 'high' : 'medium',
                'is_read'        => false,
            ]);
        }

        return redirect()->route('admin.staff.show', $id)->with('success', 'Notice dispatched successfully.');
    }

    // ────────────────────────────────────────────────────────────────────
    // Suspend Staff
    // ────────────────────────────────────────────────────────────────────
    public function suspendStaff(int $id)
    {
        $profile = StaffProfile::where('user_id', $id)->firstOrFail();
        $profile->update(['status' => 'suspended']);

        $user = User::findOrFail($id);
        $user->update(['status' => 'suspended']);

        return redirect()->back()->with('success', 'Staff member suspended.');
    }

    // ────────────────────────────────────────────────────────────────────
    // Terminate Staff
    // ────────────────────────────────────────────────────────────────────
    public function terminateStaff(Request $request, int $id)
    {
        $request->validate([
            'termination_reason' => 'required|string',
        ]);

        $profile = StaffProfile::where('user_id', $id)->firstOrFail();
        $profile->update([
            'status'              => 'terminated',
            'termination_date'    => now(),
            'termination_reason'  => $request->termination_reason,
        ]);

        $user = User::findOrFail($id);
        $user->update(['status' => 'suspended']); // Suspend user account on termination to block logins

        return redirect()->back()->with('success', 'Staff member terminated.');
    }

    // ────────────────────────────────────────────────────────────────────
    // Reactivate Staff
    // ────────────────────────────────────────────────────────────────────
    public function reactivateStaff(int $id)
    {
        $profile = StaffProfile::where('user_id', $id)->firstOrFail();
        $profile->update([
            'status'              => 'active',
            'termination_date'    => null,
            'termination_reason'  => null,
        ]);

        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Staff member reactivated.');
    }

    // ────────────────────────────────────────────────────────────────────
    // Page F: Payroll Settings GET
    // ────────────────────────────────────────────────────────────────────
    public function payrollSettings()
    {
        $settings = [
            'commission_percent' => (float) SiteSetting::getVal('staff_commission_percent', 10.0),
            'platform_expense'   => (float) SiteSetting::getVal('staff_platform_expense_per_order', 50.0),
            'min_cap'            => (float) SiteSetting::getVal('staff_min_cap', 5.0),
            'max_cap'            => (float) SiteSetting::getVal('staff_max_cap', 50.0),
            'payment_day'        => (int) SiteSetting::getVal('staff_payment_day', 5),
            'amendment_trigger'  => (int) SiteSetting::getVal('staff_amendment_trigger_orders', 100),
        ];

        return view('admin.payroll.settings', compact('settings'));
    }

    // ────────────────────────────────────────────────────────────────────
    // Page F: Payroll Settings POST
    // ────────────────────────────────────────────────────────────────────
    public function savePayrollSettings(Request $request)
    {
        $request->validate([
            'commission_percent' => 'required|numeric|min:0|max:100',
            'platform_expense'   => 'required|numeric|min:0',
            'min_cap'            => 'required|numeric|min:0',
            'max_cap'            => 'required|numeric|min:0|gte:min_cap',
            'payment_day'        => 'required|integer|min:1|max:31',
            'amendment_trigger'  => 'required|integer|min:1',
        ]);

        SiteSetting::updateOrCreate(['key' => 'staff_commission_percent'], ['value' => $request->commission_percent, 'type' => 'decimal', 'group' => 'payroll']);
        SiteSetting::updateOrCreate(['key' => 'staff_platform_expense_per_order'], ['value' => $request->platform_expense, 'type' => 'decimal', 'group' => 'payroll']);
        SiteSetting::updateOrCreate(['key' => 'staff_min_cap'], ['value' => $request->min_cap, 'type' => 'decimal', 'group' => 'payroll']);
        SiteSetting::updateOrCreate(['key' => 'staff_max_cap'], ['value' => $request->max_cap, 'type' => 'decimal', 'group' => 'payroll']);
        SiteSetting::updateOrCreate(['key' => 'staff_payment_day'], ['value' => $request->payment_day, 'type' => 'integer', 'group' => 'payroll']);
        SiteSetting::updateOrCreate(['key' => 'staff_amendment_trigger_orders'], ['value' => $request->amendment_trigger, 'type' => 'integer', 'group' => 'payroll']);

        return redirect()->back()->with('success', 'Global payroll settings updated successfully.');
    }
}
