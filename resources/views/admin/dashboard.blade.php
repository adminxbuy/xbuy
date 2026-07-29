@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', '')

@section('content')
<div class="space-y-6">
 {{-- Header --}}
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div class="space-y-0.5">
 <h1 class="text-page-title text-foreground">Dashboard</h1>
 <p class="text-sm text-muted-foreground">Manage listings, disputes, alerts, and platform metrics.</p>
 </div>

 <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3">
 <div class="date-range-picker-container flex items-center gap-2" x-data="dateRangePicker({
 start: '{{ request('date_start') }}',
 end: '{{ request('date_end') }}',
 startName: 'date_start',
 endName: 'date_end'
 })">
 <input type="hidden" name="date_start" x-model="dateStart">
 <input type="hidden" name="date_end" x-model="dateEnd">
 <div class="flex items-center gap-2 h-9 px-3 rounded-lg border border-border bg-card">
 <i data-lucide="calendar" class="w-3.5 h-3.5 text-muted-foreground"></i>
 <select x-model="currentPreset" @change="applyPreset($event.target.value)" class="text-xs font-medium bg-transparent text-foreground focus:outline-none cursor-pointer">
 <option value="all">All Time</option>
 <option value="today">Today</option>
 <option value="yesterday">Yesterday</option>
 <option value="7days">Last 7 Days</option>
 <option value="15days">Last 15 Days</option>
 <option value="30days">Last 30 Days</option>
 <option value="90days">Last 90 Days</option>
 <option value="this_month">This Month</option>
 <option value="last_month">Last Month</option>
 <option value="custom">Custom Range</option>
 </select>
 </div>
 <div class="flex items-center gap-2" x-show="currentPreset === 'custom'" x-cloak>
 <input type="text" x-ref="startInput" placeholder="Start Date" readonly class="h-9 px-3 text-xs rounded-lg w-28 text-center border border-border bg-card text-foreground focus:outline-none">
 <span class="text-xs text-muted-foreground">to</span>
 <input type="text" x-ref="endInput" placeholder="End Date" readonly class="h-9 px-3 text-xs rounded-lg w-28 text-center border border-border bg-card text-foreground focus:outline-none">
 </div>
 </div>
 @if(request()->anyFilled(['date_start', 'date_end']))
 <x-ui.button as="a" :href="route('admin.dashboard')" variant="outline" size="sm">Reset</x-ui.button>
 @endif
 <x-ui.button type="button" onclick="window.location.reload()" size="sm">
 <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
 <span>Refresh</span>
 </x-ui.button>
 </form>
 </div>

 @if($showStaffPayReviewPending)
 <div class="p-4 rounded-lg text-xs font-medium flex items-center justify-between border border-border bg-muted text-foreground/60">
 <div class="flex items-center gap-2">
 <i data-lucide="alert-triangle" class="w-4 h-4 text-muted-foreground"></i>
 <span>Staff Pay Review Pending: Amendment trigger reached this month.</span>
 </div>
 <a href="{{ route('admin.payroll') }}" class="px-3 py-1 rounded-md transition-colors text-[10px] font-semibold bg-amber-600 text-white hover:bg-amber-700">Review</a>
 </div>
 @endif

 @php
 function pctBadge(float $pct): string {
 $up = $pct >= 0;
 $symbol = $up ? '+' : '';
 $tone = 'bg-muted text-foreground border border-border';
 $arrow = $up
 ? '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8L6 21" /></svg>'
 : '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8L6 3" /></svg>';
 return "<span class='inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full {$tone}'>{$arrow}{$symbol}{$pct}%</span>";
 }
 @endphp

 {{-- Stats Cards --}}
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
 <x-ui.stat-card icon="shopping-bag" label="Total Sales" value="₹{{ number_format($stats['total_sales'], 0) }}" caption="from last period">
 <x-slot:badge>{!! pctBadge($stats['total_sales_pct']) !!}</x-slot:badge>
 </x-ui.stat-card>

 <x-ui.stat-card icon="shield-check" label="Escrow Held" value="₹{{ number_format($stats['escrow_held'], 0) }}" caption="held in trust">
 <x-slot:badge>{!! pctBadge($stats['escrow_held_pct']) !!}</x-slot:badge>
 </x-ui.stat-card>

 <x-ui.stat-card icon="credit-card" label="Monthly Revenue" value="₹{{ number_format($stats['monthly_revenue'], 0) }}" caption="commission earned">
 <x-slot:badge>{!! pctBadge($stats['monthly_rev_pct']) !!}</x-slot:badge>
 </x-ui.stat-card>

 <x-ui.stat-card icon="users" label="Total Users" value="{{ number_format($stats['total_users']) }}" caption="registered members">
 <x-slot:badge>{!! pctBadge($stats['total_users_pct']) !!}</x-slot:badge>
 </x-ui.stat-card>
 </div>

 {{-- Category Chart & Quick Approvals --}}
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <x-ui.card :padding="false" class="lg:col-span-2 flex flex-col justify-between">
 <div class="flex items-center justify-between p-6 pb-2">
 <div class="space-y-1">
 <h3 class="text-section-title">Sales by Category</h3>
 <p class="text-xs text-muted-foreground">Distribution of active inventory by category class.</p>
 </div>
 <x-ui.button variant="outline" size="sm">
 <i data-lucide="share" class="w-3.5 h-3.5"></i> Export
 </x-ui.button>
 </div>

 <div class="p-6 pt-0 space-y-4 overflow-y-auto max-h-[300px]">
 @php $totalListings = $categoriesData->sum('count') ?: 1; @endphp
 @foreach($categoriesData as $index => $cat)
 @php
 $percentage = round(($cat->count / $totalListings) * 100);
 $trends = ['+5.2%', '+7.8%', '-2.1%', '+3.4%', '+1.2%', '+1%'];
 $trendVal = $trends[$index % count($trends)];
 $isUp = str_contains($trendVal, '+');
 @endphp
 <div class="space-y-1.5">
 <div class="flex items-center justify-between text-xs">
 <div class="flex items-center gap-2">
 <span class="font-semibold">{{ strtoupper($cat->category) }}</span>
 <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-muted text-foreground border border-border">{{ $trendVal }}</span>
 </div>
 <span class="font-semibold tabular-nums">{{ $percentage }}%</span>
 </div>
 <div class="w-full rounded-full h-1.5 overflow-hidden bg-muted">
 <div class="h-full rounded-full bg-primary" style="width: {{ $percentage }}%"></div>
 </div>
 </div>
 @endforeach
 </div>
 </x-ui.card>

 <x-ui.card :padding="false" class="flex flex-col justify-between">
 <div class="flex flex-col space-y-1.5 p-6 pb-2">
 <h3 class="text-section-title">Quick Approvals</h3>
 <p class="text-xs text-muted-foreground">Items waiting for moderation.</p>
 </div>

 <div class="p-6 pt-0 space-y-3">
 <a href="{{ route('admin.sellers') }}?kyc_status=pending" class="flex items-center justify-between p-3.5 rounded-lg border border-border bg-muted/50 hover:bg-muted transition-colors">
 <div class="flex items-center gap-3">
 <i data-lucide="user-check" class="w-4 h-4 text-muted-foreground"></i>
 <span class="text-sm font-medium">Seller KYCs</span>
 </div>
 <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary text-primary-foreground tabular-nums">{{ $stats['pending_kyc'] }}</span>
 </a>

 <a href="{{ route('admin.listings') }}?status=pending_approval" class="flex items-center justify-between p-3.5 rounded-lg border border-border bg-muted/50 hover:bg-muted transition-colors">
 <div class="flex items-center gap-3">
 <i data-lucide="package-search" class="w-4 h-4 text-muted-foreground"></i>
 <span class="text-sm font-medium">Pending Listings</span>
 </div>
 <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary text-primary-foreground tabular-nums">{{ $stats['pending_listings'] }}</span>
 </a>
 </div>

 <div class="p-6 pt-2 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
 <span>Server Time</span>
 <span class="font-medium text-foreground tabular-nums">{{ now()->format('d M, H:i') }}</span>
 </div>
 </x-ui.card>
 </div>

 {{-- Customer Activity Chart --}}
 <x-ui.card class="space-y-4" x-data="customerActivityChart()">
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div class="space-y-1">
 <h3 class="text-section-title">Customer Activity</h3>
 <p class="text-xs text-muted-foreground">Customer activity for the last <span x-text="timeframeLabel">3 months</span></p>
 </div>

 <div class="flex flex-wrap items-center gap-3">
 <x-ui.select x-model="timeframe" @change="updateChart()" :placeholder="false" class="w-auto text-xs font-medium">
 <option value="3_months">3 months</option>
 <option value="6_months">6 months</option>
 <option value="12_months">12 months</option>
 </x-ui.select>
 <x-ui.select x-model="segment" @change="updateChart()" :placeholder="false" class="w-auto text-xs font-medium">
 <option value="all">All segments</option>
 <option value="active">Active accounts</option>
 <option value="new">New customers</option>
 <option value="returning">Returning users</option>
 </x-ui.select>
 <x-ui.button as="a" :href="route('admin.analytics')" variant="outline" size="sm">
 View report
 </x-ui.button>
 </div>
 </div>
 <div class="h-80 w-full mt-4">
 <canvas id="customerActivityChartCanvas"></canvas>
 </div>
 </x-ui.card>

 {{-- Recent Disputes --}}
 <x-ui.card :padding="false" class="overflow-hidden">
 <div class="p-6 border-b border-border flex items-center justify-between">
 <div class="space-y-1">
 <h3 class="text-section-title">Recent Disputes</h3>
 <p class="text-xs text-muted-foreground">Escrow dispute tickets assigned to admin.</p>
 </div>
 <x-ui.button as="a" :href="route('admin.disputes')" variant="outline" size="sm">
 View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
 </x-ui.button>
 </div>

 <div class="overflow-x-auto">
 <table class="w-full text-left text-xs border-collapse">
 <thead class="bg-muted/50">
 <tr class="border-b border-border">
 <th class="px-6 py-3 w-12 font-medium text-muted-foreground"><input type="checkbox" class="rounded border-input"></th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Order</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Buyer</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Seller</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Reason</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Raised</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Status</th>
 <th class="px-6 py-3 text-right font-medium text-muted-foreground">Action</th>
 </tr>
 </thead>
 <tbody>
 @forelse($recentDisputes as $dispute)
 <tr class="border-b border-border transition-colors hover:bg-muted/50">
 <td class="px-6 py-3.5"><input type="checkbox" class="rounded border-input"></td>
 <td class="px-6 py-3.5 font-semibold">#{{ $dispute->order->order_number ?? 'N/A' }}</td>
 <td class="px-6 py-3.5 font-medium">{{ $dispute->buyer->name ?? 'N/A' }}</td>
 <td class="px-6 py-3.5 font-medium">{{ $dispute->seller->shop_name ?? 'N/A' }}</td>
 <td class="px-6 py-3.5 max-w-[180px] truncate text-muted-foreground">{{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}</td>
 <td class="px-6 py-3.5 text-muted-foreground">{{ $dispute->created_at->diffForHumans() }}</td>
 <td class="px-6 py-3.5">
 @php
 $disputeBadge = match($dispute->status) {
 'open' => 'bg-muted text-foreground border-border',
 'seller_responded' => 'bg-card text-foreground border-border',
 'resolved' => 'bg-card text-foreground border-border',
 default => 'bg-muted text-muted-foreground border-border',
 };
 @endphp
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $disputeBadge }}">
 {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
 </span>
 </td>
 <td class="px-6 py-3.5 text-right">
 <x-ui.button as="a" :href="route('admin.disputes.show', $dispute->id)" variant="outline" size="sm">Investigate</x-ui.button>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="8" class="px-6 py-12 text-center text-muted-foreground">
 <div class="flex flex-col items-center justify-center space-y-2">
 <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-muted">
 <i data-lucide="check-circle" class="w-5 h-5 text-foreground"></i>
 </div>
 <h4 class="text-sm font-semibold text-foreground">No Open Disputes</h4>
 <p class="text-xs text-muted-foreground">Everything has been addressed.</p>
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </x-ui.card>

 {{-- Recent Orders (Real Data) --}}
 <x-ui.card class="space-y-4">
 <div class="flex items-center justify-between">
 <h3 class="text-section-title">Recent Orders</h3>
 <x-ui.button as="a" :href="route('admin.orders')" variant="outline" size="sm">
 View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
 </x-ui.button>
 </div>

 <div class="overflow-x-auto">
 <table class="w-full text-left text-xs border-collapse">
 <thead class="bg-muted/50">
 <tr class="border-b border-border">
 <th class="px-6 py-3 font-medium text-muted-foreground">Order ID</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Customer</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Amount</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Status</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Date</th>
 </tr>
 </thead>
 <tbody>
 @forelse($recentOrders ?? [] as $order)
 <tr class="border-b border-border transition-colors hover:bg-muted/50">
 <td class="px-6 py-3.5 font-semibold">#{{ $order->order_number }}</td>
 <td class="px-6 py-3.5 font-medium">{{ $order->buyer->name ?? 'N/A' }}</td>
 <td class="px-6 py-3.5 font-medium">₹{{ number_format($order->total_amount, 2) }}</td>
 <td class="px-6 py-3.5">
 @php
 $orderBadge = match($order->status) {
 'completed' => 'bg-card text-foreground border-border',
 'in_transit' => 'bg-muted text-foreground border-border',
 default => 'bg-muted text-muted-foreground border-border',
 };
 @endphp
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $orderBadge }}">
 {{ ucfirst(str_replace('_', ' ', $order->status)) }}
 </span>
 </td>
 <td class="px-6 py-3.5 text-muted-foreground">{{ $order->created_at->diffForHumans() }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">No recent orders</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </x-ui.card>

 {{-- Support Tickets --}}
 <x-ui.card :padding="false" class="overflow-hidden flex flex-col justify-between">
 <div class="p-6 border-b border-border flex items-center justify-between">
 <div class="space-y-1">
 <h3 class="text-section-title">Recent Support Tickets</h3>
 <p class="text-xs text-muted-foreground">Buyer support requests escalated to admin.</p>
 </div>
 <x-ui.button as="a" :href="route('admin.tickets')" variant="outline" size="sm">
 View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
 </x-ui.button>
 </div>

 <div class="overflow-x-auto">
 <table class="w-full text-left text-xs border-collapse">
 <thead class="bg-muted/50">
 <tr class="border-b border-border">
 <th class="px-6 py-3 w-12 font-medium text-muted-foreground"><input type="checkbox" class="rounded border-input"></th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Ticket ID</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Buyer</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Subject</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Created</th>
 <th class="px-6 py-3 font-medium text-muted-foreground">Status</th>
 <th class="px-6 py-3 text-right font-medium text-muted-foreground">Actions</th>
 </tr>
 </thead>
 <tbody>
 @forelse($recentTickets as $ticket)
 <tr class="border-b border-border transition-colors hover:bg-muted/50">
 <td class="px-6 py-3.5"><input type="checkbox" class="rounded border-input"></td>
 <td class="px-6 py-3.5 font-semibold">#TKT-{{ $ticket->id }}</td>
 <td class="px-6 py-3.5 font-medium">{{ $ticket->buyer->name ?? 'N/A' }}</td>
 <td class="px-6 py-3.5 max-w-[200px] truncate text-muted-foreground" title="{{ $ticket->message }}">{{ $ticket->subject }}</td>
 <td class="px-6 py-3.5 text-muted-foreground">{{ $ticket->created_at->diffForHumans() }}</td>
 <td class="px-6 py-3.5">
 @php
 $ticketBadge = match($ticket->status) {
 'open' => 'bg-muted text-foreground border-border',
 'in_progress' => 'bg-card text-foreground border-border',
 'resolved' => 'bg-card text-foreground border-border',
 default => 'bg-muted text-muted-foreground border-border',
 };
 @endphp
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $ticketBadge }}">
 {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
 </span>
 </td>
 <td class="px-6 py-3.5 text-right">
 <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" class="inline-block">
 @csrf
 <input type="hidden" name="status" value="{{ $ticket->status === 'resolved' ? 'open' : 'resolved' }}">
 <x-ui.button type="submit" variant="outline" size="sm">
 {{ $ticket->status === 'resolved' ? 'Reopen' : 'Resolve' }}
 </x-ui.button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
 <div class="flex flex-col items-center justify-center space-y-2">
 <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-muted text-muted-foreground">
 <i data-lucide="help-circle" class="w-5 h-5"></i>
 </div>
 <h4 class="text-sm font-semibold text-foreground">No Support Tickets</h4>
 <p class="text-xs text-muted-foreground">All requests are fully settled.</p>
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </x-ui.card>

 {{-- Bottom Row: Seller Applications & Top Sellers --}}
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 <x-ui.card :padding="false" class="overflow-hidden flex flex-col justify-between min-h-[360px]">
 <div>
 <div class="p-5 border-b border-border flex items-center justify-between">
 <h3 class="text-sm font-semibold leading-none tracking-tight">Seller Applications</h3>
 <x-ui.button as="a" :href="route('admin.sellers') . '?kyc_status=pending'" variant="outline" size="sm">
 All <i data-lucide="arrow-right" class="w-3 h-3"></i>
 </x-ui.button>
 </div>

 <div class="divide-y divide-border">
 @forelse($pendingSellers ?? [] as $seller)
 <div class="p-5 py-3.5 flex items-center justify-between gap-3">
 <div class="min-w-0 flex-1">
 <p class="text-sm font-semibold truncate">{{ $seller->shop_name }}</p>
 <p class="text-xs truncate mt-0.5 text-muted-foreground">{{ $seller->user->email ?? '' }}</p>
 </div>
 <form action="{{ route('admin.sellers.show', $seller->id) }}" method="GET" class="shrink-0">
 <x-ui.button type="submit" size="sm">Review</x-ui.button>
 </form>
 </div>
 @empty
 <div class="p-8 text-center text-xs text-muted-foreground">No pending applications</div>
 @endforelse
 </div>
 </div>
 </x-ui.card>

 <x-ui.card :padding="false" class="overflow-hidden flex flex-col justify-between min-h-[360px]">
 <div>
 <div class="p-5 border-b border-border flex items-center justify-between">
 <h3 class="text-sm font-semibold leading-none tracking-tight">Top Sellers</h3>
 <x-ui.button as="a" :href="route('admin.sellers')" variant="outline" size="sm">
 All <i data-lucide="arrow-right" class="w-3 h-3"></i>
 </x-ui.button>
 </div>

 <div class="divide-y divide-border">
 @forelse($topSellers ?? [] as $index => $seller)
 <div class="p-5 py-3.5 flex items-center gap-3">
 <span class="text-xs font-semibold w-5 shrink-0 text-muted-foreground">#{{ $index + 1 }}</span>
 <div class="min-w-0 flex-1">
 <p class="text-sm font-semibold truncate">{{ $seller->shop_name }}</p>
 <p class="text-xs mt-0.5 text-muted-foreground">{{ $seller->orders_count ?? 0 }} orders</p>
 </div>
 <span class="text-sm font-semibold shrink-0 tabular-nums">₹{{ number_format($seller->total_revenue ?? 0) }}</span>
 </div>
 @empty
 <div class="p-8 text-center text-xs text-muted-foreground">No seller data yet</div>
 @endforelse
 </div>
 </div>
 </x-ui.card>
 </div>
</div>

<script>
window.customerActivityChart = function() {
 return {
 timeframe: '3_months',
 segment: 'all',
 chartInstance: null,
 get timeframeLabel() {
 if (this.timeframe === '3_months') return '3 months';
 if (this.timeframe === '6_months') return '6 months';
 if (this.timeframe === '12_months') return '12 months';
 return '3 months';
 },
 init() { this.$nextTick(() => this.initChart()); },
 initChart() {
 const ctx = document.getElementById('customerActivityChartCanvas').getContext('2d');
 const datasetsData = this.generateData();
 this.chartInstance = new Chart(ctx, {
 type: 'line',
 data: { labels: datasetsData.labels, datasets: datasetsData.datasets },
 options: {
 responsive: true, maintainAspectRatio: false,
 interaction: { mode: 'index', intersect: false },
 plugins: {
 legend: { display: false },
 tooltip: { enabled: true, backgroundColor: '#09090b', titleColor: '#a1a1aa', bodyColor: '#ffffff', titleFont: { size: 11 }, bodyFont: { size: 12, weight: '600' }, padding: 10, cornerRadius: 8 }
 },
 scales: {
 x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#71717a' } },
 y: { beginAtZero: true, grid: { color: '#f4f4f5', drawBorder: false }, ticks: { font: { size: 11 }, color: '#71717a' } }
 },
 elements: { point: { radius: 0, hoverRadius: 4, backgroundColor: '#09090b' }, line: { tension: 0.35, borderWidth: 2 } }
 }
 });
 },
 updateChart() { if (this.chartInstance) this.chartInstance.destroy(); this.initChart(); },
 generateData() {
 let originalLabels = [];
 let multiplier = 1.0;
 let gap = 7;
 if (this.timeframe === '3_months') { originalLabels = ['Apr 9', 'Apr 16', 'Apr 22', 'Apr 29', 'May 6', 'May 12', 'May 19', 'May 26', 'Jun 2', 'Jun 8', 'Jun 15', 'Jun 22', 'Jun 29', 'Jul 6']; }
 else if (this.timeframe === '6_months') { originalLabels = ['Jan 9', 'Jan 23', 'Feb 6', 'Feb 20', 'Mar 6', 'Mar 20', 'Apr 6', 'Apr 20', 'May 6', 'May 20', 'Jun 6', 'Jun 20', 'Jul 6']; multiplier = 1.8; gap = 6; }
 else { originalLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; multiplier = 3.5; gap = 9; }

 let labels = [];
 for (let i = 0; i < originalLabels.length; i++) { labels.push(originalLabels[i]); if (i < originalLabels.length - 1) { for (let g = 0; g < gap; g++) labels.push(''); } }

 const length = labels.length;
 let activeData = [], newData = [], returningData = [];
 for (let i = 0; i < length; i++) {
 let base = 25 + Math.sin(i * 0.1) * 3;
 let spike = (i % 11 === 4) ? 45 : (i % 17 === 8) ? 35 : (i % 7 === 2) ? 15 : 0;
 newData.push(Math.round((base + spike + Math.sin(i * 0.95) * 3) * multiplier));
 activeData.push(Math.round((14 + Math.sin(i * 0.9) * 0.9 + Math.cos(i * 0.4) * 0.4) * multiplier));
 returningData.push(Math.round((10 + Math.cos(i * 1.15) * 0.8 + Math.sin(i * 0.5) * 0.3) * multiplier));
 }

 const canvas = document.getElementById('customerActivityChartCanvas');
 const ctx = canvas ? canvas.getContext('2d') : null;
 let newBg = 'transparent';
 if (ctx) { const newGrad = ctx.createLinearGradient(0, 0, 0, 300); newGrad.addColorStop(0, 'rgba(212, 212, 216, 0.22)'); newGrad.addColorStop(1, 'rgba(212, 212, 216, 0.00)'); newBg = newGrad; }

 let allDatasets = [
 { label: 'Active Accounts', data: activeData, borderColor: '#71717a', backgroundColor: 'transparent', borderWidth: 1.2, fill: false, tension: 0.35, id: 'active' },
 { label: 'New Customers', data: newData, borderColor: '#d4d4d8', backgroundColor: newBg, borderWidth: 1.2, fill: true, tension: 0.35, id: 'new' },
 { label: 'Returning Users', data: returningData, borderColor: '#18181b', backgroundColor: 'transparent', borderWidth: 1.2, fill: false, tension: 0.35, id: 'returning' }
 ];

 let filteredDatasets = this.segment !== 'all' ? allDatasets.filter(ds => ds.id === this.segment) : allDatasets;
 return { labels, datasets: filteredDatasets };
 }
 };
};

document.addEventListener("DOMContentLoaded", function () {
 const ctx = document.getElementById('visitsChart')?.getContext('2d');
 if (ctx) {
 new Chart(ctx, {
 type: 'doughnut',
 data: { labels: ['Direct', 'Social', 'Email', 'Referrals', 'Other'], datasets: [{ data: [40, 25, 15, 12, 8], backgroundColor: ['#09090b', '#27272a', '#52525b', '#a1a1aa', '#e4e4e7'], borderWidth: 0, hoverOffset: 4 }] },
 options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { display: false }, tooltip: { enabled: true } } }
 });
 }
});
</script>
@endsection