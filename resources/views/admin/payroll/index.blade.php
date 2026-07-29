@extends('layouts.admin')

@section('title', 'Payroll Overview')
@section('page_title', 'Payroll Overview')

@section('header_actions')
 <div class="flex items-center gap-2">
 <a href="{{ route('admin.payroll.settings') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-muted hover:bg-muted text-foreground hover:text-foreground rounded-lg text-xs font-medium transition-all border border-border ">
 <i data-lucide="settings" class="w-3.5 h-3.5"></i>
 <span>Payroll Settings</span>
 </a>
 @if(count($pendingStaffList) > 0)
 <form action="{{ route('admin.payroll.disburse') }}" method="POST" onsubmit="return confirm('Disburse payrolls to all pending staff members? This will send Salary Slip emails automatically.')">
 @csrf
 <button type="submit" class="inline-flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground font-medium px-4 py-2.5 rounded-lg text-xs transition-all ">
 <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
 <span>Disburse All Pending</span>
 </button>
 </form>
 @endif
 </div>
@endsection

@section('content')
<!-- Metric Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
 <!-- Card 1: Total Pending -->
 <div class="bg-card border border-border rounded-xl p-5 flex flex-col justify-between">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Total Pending (This Month)</span>
 <div class="p-2 bg-muted text-muted-foreground rounded-lg">
 <i data-lucide="wallet" class="w-4 h-4"></i>
 </div>
 </div>
 <h3 class="text-2xl font-bold text-foreground mt-3">₹{{ number_format($totalPendingThisMonth, 2) }}</h3>
 <p class="text-[10px] text-muted-foreground mt-1">Pending payout for current cycle</p>
 </div>

 <!-- Card 2: Total Staff Count -->
 <div class="bg-card border border-border rounded-xl p-5 flex flex-col justify-between">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Total Staff Count</span>
 <div class="p-2 bg-muted text-muted-foreground rounded-lg">
 <i data-lucide="users" class="w-4 h-4"></i>
 </div>
 </div>
 <h3 class="text-2xl font-bold text-foreground mt-3">{{ $staffCount }}</h3>
 <p class="text-[10px] text-muted-foreground mt-1">Active platform operators</p>
 </div>

 <!-- Card 3: Orders count -->
 <div class="bg-card border border-border rounded-xl p-5 flex flex-col justify-between">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Orders (This Month)</span>
 <div class="p-2 bg-muted text-muted-foreground rounded-lg">
 <i data-lucide="shopping-bag" class="w-4 h-4"></i>
 </div>
 </div>
 <h3 class="text-2xl font-bold text-foreground mt-3">{{ $ordersThisMonth }}</h3>
 <p class="text-[10px] text-muted-foreground mt-1">Total orders processed</p>
 </div>

 <!-- Card 4: Amendment Trigger Progress -->
 <div class="bg-card border border-border rounded-xl p-5 flex flex-col justify-between">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Amendment Trigger</span>
 <span class="text-[10px] bg-muted text-foreground border border-border/60 font-bold px-2 py-0.5 rounded-full">{{ $ordersThisMonth }}/{{ $triggerOrders }} Orders</span>
 </div>
  <!-- Progress bar -->
 <div class="mt-4">
 <div class="w-full bg-muted rounded-full h-2">
 <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ $progressPct }}%"></div>
 </div>
 <div class="flex justify-between items-center mt-2 text-[10px]">
 <span class="text-muted-foreground">Trigger Progress</span>
 <span class="font-bold text-foreground">{{ $progressPct }}%</span>
 </div>
 </div>
 </div>
</div>

<!-- Pending Payouts Table -->
<div class="bg-card border border-border rounded-xl ring-0 overflow-hidden mb-6">
 <div class="px-6 py-4 border-b border-border flex items-center justify-between">
 <div>
 <h3 class="font-bold text-foreground text-base">Pending Monthly Earnings</h3>
 <p class="text-xs text-muted-foreground">List of staff members with unpaid commissions generated this month.</p>
 </div>
 <span class="text-xs bg-muted text-foreground font-bold px-3 py-1 rounded-full border border-border">
 Unpaid Cycle
 </span>
 </div>

 <div class="overflow-x-auto">
 <table class="w-full text-left text-sm">
 <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground font-medium border-b border-border">
 <tr>
 <th class="px-6 py-4">Name / designation</th>
 <th class="px-6 py-4">Orders Handled</th>
 <th class="px-6 py-4">Gross Earnings</th>
 <th class="px-6 py-4">Platform Expenses</th>
 <th class="px-6 py-4">Net Payout</th>
 <th class="px-6 py-4">Payment Status</th>
 <th class="px-6 py-4 text-right">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-border">
 @forelse($pendingStaffList as $item)
 <tr class="hover:bg-muted transition-all font-semibold">
 <td class="px-6 py-4">
 <div class="flex items-center space-x-3">
 <img src="https://ui-avatars.com/api/?name={{ urlencode($item['staff']->name) }}&background=e4e4e7&color=71717a&size=36" class="w-9 h-9 rounded-xl" alt="avatar">
 <div>
 <h4 class="text-xs font-bold text-foreground">{{ $item['staff']->name }}</h4>
 <p class="text-[10px] text-muted-foreground">{{ $item['staff']->staffProfile->designation ?? 'Staff Member' }}</p>
 </div>
 </div>
 </td>
 <td class="px-6 py-4 text-muted-foreground text-xs">
 {{ $item['orders_count'] }} orders
 </td>
 <td class="px-6 py-4 text-foreground text-xs">
 ₹{{ number_format($item['gross'], 2) }}
 </td>
 <td class="px-6 py-4 text-muted-foreground text-xs">
 ₹{{ number_format($item['expenses'], 2) }}
 </td>
 <td class="px-6 py-4 text-foreground font-semibold text-xs bg-muted">
 ₹{{ number_format($item['net'], 2) }}
 </td>
 <td class="px-6 py-4">
 <span class="px-2 py-0.5 text-[9px] rounded-full font-bold bg-muted text-foreground border border-border">
 {{ ucfirst($item['status']) }}
 </span>
 </td>
 <td class="px-6 py-4 text-right">
 <a href="{{ route('admin.staff.show', $item['staff']->id) }}?tab=earnings"
 class="inline-flex items-center gap-1 text-xs font-bold bg-muted hover:bg-primary text-primary-foreground hover:bg-primary/90 text-foreground px-3 py-1.5 rounded-lg transition-all border border-border">
 <span>Inspect Earnings</span>
 <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
 </a>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="px-6 py-12 text-center">
 <div class="flex flex-col items-center justify-center">
 <div class="w-10 h-10 bg-muted rounded-xl flex items-center justify-center text-muted-foreground mb-2 border border-border">
 <i data-lucide="check-circle" class="w-4 h-4 text-muted-foreground"></i>
 </div>
 <h4 class="text-xs font-bold text-foreground">No Pending Payouts</h4>
 <p class="text-[11px] text-muted-foreground mt-0.5">All staff commissions for this cycle have been disbursed! 🎉</p>
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</div>
@endsection
