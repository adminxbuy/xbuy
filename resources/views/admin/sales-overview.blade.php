@extends('layouts.admin')

@section('title', 'Sales Overview')
@section('page_title', 'Sales Overview')

@section('content')
<div class="space-y-6 overflow-y-auto">
 {{-- Filters & Actions Form --}}
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <form action="{{ route('admin.sales-overview') }}" method="GET" class="flex flex-wrap items-center gap-3">
 {{-- Preset selector --}}
 <div class="flex items-center gap-2 bg-card border border-border h-9 px-3 rounded-lg">
 <i data-lucide="calendar" class="w-3.5 h-3.5 text-muted-foreground"></i>
 <select name="preset" onchange="this.form.submit()" class="text-xs font-semibold bg-transparent text-foreground focus:outline-none cursor-pointer">
 <option value="today" {{ $preset === 'today' ? 'selected' : '' }}>Today</option>
 <option value="yesterday" {{ $preset === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
 <option value="7days" {{ $preset === '7days' ? 'selected' : '' }}>Last 7 Days</option>
 <option value="15days" {{ $preset === '15days' ? 'selected' : '' }}>Last 15 Days</option>
 <option value="30days" {{ $preset === '30days' ? 'selected' : '' }}>Last 30 Days</option>
 <option value="90days" {{ $preset === '90days' ? 'selected' : '' }}>Last 90 Days</option>
 <option value="this_month" {{ $preset === 'this_month' ? 'selected' : '' }}>This Month</option>
 <option value="last_month" {{ $preset === 'last_month' ? 'selected' : '' }}>Last Month</option>
 <option value="custom" {{ $preset === 'custom' ? 'selected' : '' }}>Custom Range</option>
 </select>
 </div>

 {{-- Custom date range picker using existing Alpine component if preset === 'custom' --}}
 <div class="date-range-picker-container flex items-center gap-2" x-data="dateRangePicker({
 start: '{{ request('date_start') }}',
 end: '{{ request('date_end') }}',
 startName: 'date_start',
 endName: 'date_end'
 })">
 <input type="hidden" name="date_start" x-model="dateStart" @change="$nextTick(() => { if (dateStart && dateEnd) $el.closest('form').submit() })">
 <input type="hidden" name="date_end" x-model="dateEnd" @change="$nextTick(() => { if (dateStart && dateEnd) $el.closest('form').submit() })">

 <div class="flex items-center gap-2" x-show="'{{ $preset }}' === 'custom' || currentPreset === 'custom'" x-cloak>
 <input type="text" x-ref="startInput" placeholder="Start Date" readonly class="h-9 px-3 text-xs border border-border rounded-lg bg-card w-28 text-center focus:outline-none">
 <span class="text-muted-foreground text-xs">to</span>
 <input type="text" x-ref="endInput" placeholder="End Date" readonly class="h-9 px-3 text-xs border border-border rounded-lg bg-card w-28 text-center focus:outline-none">
 </div>
 </div>

 @if(request()->anyFilled(['date_start', 'date_end']) || $preset !== '30days')
 <a href="{{ route('admin.sales-overview') }}" class="inline-flex items-center justify-center h-9 px-4 border border-border hover:bg-muted rounded-lg text-xs font-semibold transition-all">
 Reset
 </a>
 @endif

 <button type="submit" name="export" value="csv" class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 border border-border hover:bg-muted rounded-lg text-xs font-semibold transition-all ">
 <i data-lucide="share" class="w-3.5 h-3.5 text-muted-foreground"></i>
 <span>Export</span>
 </button>
 </form>
 </div>

 {{-- Main Financial Grid Layout --}}
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  {{-- Left: 4 Metric Cards Grid --}}
 <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
  {{-- Gross Merchandise Value (GMV) --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between relative overflow-hidden">
 <div class="flex items-start justify-between">
 <div class="space-y-1">
 <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Gross Merchandise Value</span>
 <h3 class="text-2xl font-bold tracking-tight text-foreground mt-1">₹{{ number_format($metrics['gmv'], 2) }}</h3>
 </div>
 <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['gmv_change'] >= 0 ? 'bg-muted text-foreground' : 'bg-muted text-foreground' }}">
 {{ $metrics['gmv_change'] >= 0 ? '+' : '' }}{{ $metrics['gmv_change'] }}%
 </span>
 </div>
 <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-[11px] text-muted-foreground">
 <span>vs previous period</span>
 <span class="font-medium text-muted-foreground">
 {{ $metrics['gmv_change'] >= 0 ? '+' : '' }}₹{{ number_format($metrics['gmv'] * (abs($metrics['gmv_change'])/100), 0) }}
 </span>
 </div>
 </div>

 {{-- Escrow Balance --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between relative overflow-hidden">
 <div class="flex items-start justify-between">
 <div class="space-y-1">
 <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Funds In Escrow</span>
 <h3 class="text-2xl font-bold tracking-tight text-foreground mt-1">₹{{ number_format($metrics['escrow'], 2) }}</h3>
 </div>
 <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['escrow_change'] >= 0 ? 'bg-muted text-foreground' : 'bg-muted text-foreground' }}">
 {{ $metrics['escrow_change'] >= 0 ? '+' : '' }}{{ $metrics['escrow_change'] }}%
 </span>
 </div>
 <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-[11px] text-muted-foreground">
 <span>held securely in trust</span>
 <span class="font-medium text-muted-foreground">Active protection</span>
 </div>
 </div>

 {{-- Platform Commission / Monthly Spend equivalent --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between relative overflow-hidden">
 <div class="flex items-start justify-between">
 <div class="space-y-1">
 <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Platform Revenue</span>
 <h3 class="text-2xl font-bold tracking-tight text-foreground mt-1">₹{{ number_format($metrics['revenue'], 2) }}</h3>
 </div>
 <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['revenue_change'] >= 0 ? 'bg-muted text-foreground' : 'bg-muted text-foreground' }}">
 {{ $metrics['revenue_change'] >= 0 ? '+' : '' }}{{ $metrics['revenue_change'] }}%
 </span>
 </div>
 <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-[11px] text-muted-foreground">
 <span>net commissions earned</span>
 <span class="font-medium text-muted-foreground">Platform share</span>
 </div>
 </div>

 {{-- Order Fulfillment Rate / Savings rate equivalent --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between relative overflow-hidden">
 <div class="flex items-start justify-between">
 <div class="space-y-1">
 <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Fulfillment Rate</span>
 <h3 class="text-2xl font-bold tracking-tight text-foreground mt-1">{{ number_format($metrics['fulfillment'], 1) }}%</h3>
 </div>
 <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['fulfillment_change'] >= 0 ? 'bg-muted text-foreground' : 'bg-muted text-foreground' }}">
 {{ $metrics['fulfillment_change'] >= 0 ? '+' : '' }}{{ $metrics['fulfillment_change'] }}%
 </span>
 </div>
 <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-[11px] text-muted-foreground">
 <span>completed vs total orders</span>
 <span class="font-medium text-muted-foreground">Conversion efficiency</span>
 </div>
 </div>

 </div>

 {{-- Right: Income Sources (Sales by Category) & Alert --}}
 <div class="space-y-4">
  {{-- Sales By Category Card --}}
 <div class="rounded-xl border border-border bg-card p-6 ">
 <h3 class="text-sm font-semibold tracking-tight text-foreground mb-4">Sales Distribution</h3>
 <div class="space-y-4">
 @php
 $totalCatGmv = $categoriesData->sum('total_sales') ?: 1;
 // Define different zinc shade colors for styling
 $shades = ['bg-primary', 'bg-primary/70', 'bg-primary/50', 'bg-muted', 'bg-muted'];
 @endphp
 @forelse($categoriesData->take(3) as $idx => $cat)
 @php
 $percentage = round(($cat->total_sales / $totalCatGmv) * 100);
 $shade = $shades[$idx % count($shades)];
 @endphp
 <div class="space-y-1">
 <div class="flex items-center justify-between text-xs text-muted-foreground">
 <div>
 <span class="font-bold text-foreground">{{ ucfirst(str_replace('_', ' ', $cat->category)) }}</span>
 <span class="mx-1 text-muted-foreground">•</span>
 <span>{{ $percentage }}%</span>
 </div>
 <span class="font-semibold text-foreground">₹{{ number_format($cat->total_sales, 0) }}</span>
 </div>
 <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
 <div class="{{ $shade }} h-full rounded-full" style="width: {{ $percentage }}%"></div>
 </div>
 </div>
 @empty
 <div class="text-center py-6 text-xs text-muted-foreground font-medium">No sales recorded.</div>
 @endforelse
 </div>
 </div>

 {{-- Credit Score / API Gateway health notification widget --}}
 <div class="rounded-xl border border-border bg-muted p-4 flex items-start gap-3">
 <div class="w-8 h-8 rounded-lg bg-card border border-border flex items-center justify-center text-muted-foreground shrink-0">
 <i data-lucide="check-circle" class="size-4"></i>
 </div>
 <div class="space-y-1 min-w-0 flex-1">
 <h4 class="text-xs font-bold text-foreground">Razorpay Payout Connection status</h4>
 <p class="text-[10px] text-muted-foreground leading-normal">API gateway integration is optimal. Automatic escrow splits active.</p>
 </div>
 <a href="{{ route('admin.settings') }}" class="text-[10px] font-bold text-foreground border border-border bg-card hover:bg-muted px-2 py-1 rounded shrink-0">
 Configure
 </a>
 </div>

 </div>

 </div>

 {{-- Line Chart and Doughnut Allocation row --}}
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  {{-- GMV and Revenue Trend Line Chart (Full Width left) --}}
 <div class="lg:col-span-2 rounded-xl border border-border bg-card p-6 flex flex-col justify-between">
 <div class="flex items-center justify-between pb-2">
 <div class="space-y-1">
 <h3 class="text-sm font-semibold tracking-tight text-foreground">Spending Overview (GMV Trend)</h3>
 <p class="text-[10px] text-muted-foreground">Total transacted volume and commission timeline.</p>
 </div>
 <div class="flex items-center gap-2">
 <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-muted-foreground">
 <span class="w-2.5 h-1.5 bg-primary inline-block rounded-sm"></span> GMV
 </span>
 <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-muted-foreground">
 <span class="w-2.5 h-1.5 bg-muted inline-block rounded-sm"></span> Commission
 </span>
 </div>
 </div>
 <div class="h-80 w-full mt-4">
 <canvas id="salesOverviewChart"></canvas>
 </div>
 </div>

 {{-- Account Allocation (Doughnut Escrow Allocation) --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between">
 <div class="pb-2">
 <h3 class="text-sm font-semibold tracking-tight text-foreground">Account Allocation (Escrow status)</h3>
 <p class="text-[10px] text-muted-foreground">Total active funds allocation in trust escrow.</p>
 </div>

 <div class="relative flex items-center justify-center h-48 my-3">
 <canvas id="escrowAllocationChart" class="max-h-full"></canvas>
 <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-2">
 <span class="text-lg font-bold tracking-tight text-foreground">₹{{ number_format($metrics['escrow'], 0) }}</span>
 <span class="text-[9px] font-medium text-muted-foreground">Held in Trust</span>
 </div>
 </div>

 <div class="text-[10px] font-semibold text-muted-foreground flex flex-col gap-2 pt-3 border-t border-border">
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-1.5">
 <span class="w-2.5 h-2.5 rounded-sm bg-primary inline-block"></span>
 <span>Held (Secure)</span>
 </div>
 <span class="font-bold text-foreground">₹{{ number_format($escrowAllocation['held'], 0) }}</span>
 </div>
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-1.5">
 <span class="w-2.5 h-2.5 rounded-sm bg-muted inline-block"></span>
 <span>Released</span>
 </div>
 <span class="font-bold text-foreground">₹{{ number_format($escrowAllocation['released'], 0) }}</span>
 </div>
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-1.5">
 <span class="w-2.5 h-2.5 rounded-sm bg-muted inline-block"></span>
 <span>Refunded</span>
 </div>
 <span class="font-bold text-foreground">₹{{ number_format($escrowAllocation['refunded'], 0) }}</span>
 </div>
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-1.5">
 <span class="w-2.5 h-2.5 rounded-sm bg-rose-500 inline-block"></span>
 <span>Disputed</span>
 </div>
 <span class="font-bold text-foreground">₹{{ number_format($escrowAllocation['disputed'], 0) }}</span>
 </div>
 </div>
 </div>

 </div>

 {{-- Bottom Section Grid: Wallet, Upcoming Releases, Quick Transfer --}}
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  {{-- Card 1: Top Performing Shop (styled like standard dashboard card) --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between min-h-[250px] relative overflow-hidden">
  <div class="flex items-start justify-between">
 <div class="space-y-1">
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Top Selling Shop</span>
 @if($topShops->first() && $topShops->first()->seller)
 <h4 class="text-lg font-bold tracking-tight text-foreground mt-1">{{ $topShops->first()->seller->shop_name }}</h4>
 <p class="text-[10px] text-muted-foreground">Managed by {{ $topShops->first()->seller->user->name }}</p>
 @else
 <h4 class="text-lg font-bold tracking-tight text-foreground mt-1">No Top Shop</h4>
 <p class="text-[10px] text-muted-foreground">No transactions recorded yet.</p>
 @endif
 </div>
 <div class="w-8 h-8 rounded-lg bg-muted border border-border flex items-center justify-center font-bold text-sm text-foreground shrink-0">S</div>
 </div>

 @if($topShops->first())
 <div class="space-y-1.5">
 <span class="text-[9px] font-semibold text-muted-foreground uppercase tracking-wider">Total Shop GMV</span>
 <h3 class="text-3xl font-bold tracking-tight text-foreground">₹{{ number_format($topShops->first()->gmv, 2) }}</h3>
 <p class="text-[10px] text-muted-foreground">Generated over {{ $topShops->first()->order_count }} successful orders</p>
 </div>
 @else
 <div class="space-y-1.5">
 <span class="text-[9px] font-semibold text-muted-foreground uppercase tracking-wider">Total Shop GMV</span>
 <h3 class="text-3xl font-bold tracking-tight text-foreground">₹0.00</h3>
 </div>
 @endif

 <div class="flex items-center justify-between border-t border-border pt-3 text-xs text-muted-foreground">
 <span>Direct payout split active</span>
 <i data-lucide="chevron-right" class="w-4 h-4 text-muted-foreground"></i>
 </div>
 </div>

 {{-- Card 2: Upcoming Releases --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between">
 <div>
 <h3 class="text-sm font-semibold tracking-tight text-foreground mb-3">Upcoming Payments / Releases</h3>
 <div class="divide-y divide-border">
 @forelse($upcomingReleases as $escrow)
 <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
 <div class="min-w-0">
 <p class="font-bold text-foreground truncate">#{{ $escrow->order->order_number ?? 'N/A' }}</p>
 <p class="text-[10px] text-muted-foreground truncate">{{ $escrow->release_scheduled_at ? $escrow->release_scheduled_at->format('d M, H:i') : 'Scheduled' }}</p>
 </div>
 <span class="font-semibold text-foreground shrink-0">₹{{ number_format($escrow->amount_held, 0) }}</span>
 </div>
 @empty
 <div class="text-center py-8 text-xs text-muted-foreground font-medium">No pending releases found.</div>
 @endforelse
 </div>
 </div>
  <a href="{{ route('admin.escrow') }}" class="inline-flex items-center justify-center w-full h-9 border border-border hover:bg-muted text-foreground font-semibold rounded-lg text-xs transition-all mt-4">
 Manage All Escrows
 </a>
 </div>

 {{-- Card 3: Commission rate quick-calculator --}}
 <div class="rounded-xl border border-border bg-card p-6 flex flex-col justify-between" x-data="{
 amount: 5000,
 rate: 5,
 get commission() { return (this.amount * (this.rate / 100)).toFixed(2) },
 get net() { return (this.amount - this.commission).toFixed(2) }
 }">
 <div>
 <h3 class="text-sm font-semibold tracking-tight text-foreground mb-3">Quick Commission Calculator</h3>
 <div class="space-y-3.5">
 <div>
 <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Transaction amount (INR)</label>
 <input type="number" x-model.number="amount" class="w-full h-9 px-3 border border-border rounded-lg text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-ring">
 </div>
 <div>
 <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Commission Rate (%)</label>
 <select x-model.number="rate" class="w-full h-9 px-3 border border-border rounded-lg text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-ring">
 <option value="2">2.0% (Standard)</option>
 <option value="5" selected>5.0% (Partner)</option>
 <option value="10">10.0% (Premium)</option>
 <option value="15">15.0% (Special)</option>
 </select>
 </div>
 </div>
 </div>

 <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs">
 <div>
 <p class="text-[10px] text-muted-foreground">Commission Fee</p>
 <p class="font-bold text-foreground">₹<span x-text="commission"></span></p>
 </div>
 <div class="text-right">
 <p class="text-[10px] text-muted-foreground">Net Seller Payout</p>
 <p class="font-bold text-foreground">₹<span x-text="net"></span></p>
 </div>
 </div>
 </div>

 </div>

</div>

{{-- Charts Initialization Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Sleek Line Chart for Sales (GMV) and Revenue Trend
 const salesCtx = document.getElementById('salesOverviewChart').getContext('2d');
  const salesLabels = @json($chartLabels);
 const gmvData = @json($chartGmvData);
 const revData = @json($chartRevData);
  new Chart(salesCtx, {
 type: 'line',
 data: {
 labels: salesLabels,
 datasets: [
 {
 label: 'Gross Volume (GMV)',
 data: gmvData,
 borderColor: '#18181b', // sleek zinc-900 border
 borderWidth: 2,
 pointRadius: 0,
 pointHoverRadius: 4,
 tension: 0.35,
 fill: false
 },
 {
 label: 'Platform Commission',
 data: revData,
 borderColor: '#a1a1aa', // zinc-400 border
 borderWidth: 1.5,
 borderDash: [4, 4],
 pointRadius: 0,
 pointHoverRadius: 3,
 tension: 0.35,
 fill: false
 }
 ]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: {
 display: false // Using custom top legend
 },
 tooltip: {
 mode: 'index',
 intersect: false,
 backgroundColor: '#18181b',
 titleColor: '#ffffff',
 bodyColor: '#e4e4e7',
 titleFont: {
 family: 'Outfit',
 size: 11
 },
 bodyFont: {
 family: 'Plus Jakarta Sans',
 size: 10
 },
 padding: 8,
 cornerRadius: 6,
 callbacks: {
 label: function(context) {
 return ' ' + context.dataset.label + ': ₹' + context.raw.toLocaleString();
 }
 }
 }
 },
 scales: {
 x: {
 grid: {
 display: false
 },
 ticks: {
 font: {
 family: 'Plus Jakarta Sans',
 size: 9
 },
 color: '#71717a'
 }
 },
 y: {
 grid: {
 color: '#f4f4f5'
 },
 ticks: {
 font: {
 family: 'Plus Jakarta Sans',
 size: 9
 },
 color: '#71717a',
 callback: function(value) {
 if (value >= 1000) {
 return '₹' + (value/1000) + 'k';
 }
 return '₹' + value;
 }
 }
 }
 }
 }
 });

 // 2. Escrow Allocation Doughnut Chart
 const escrowCtx = document.getElementById('escrowAllocationChart').getContext('2d');
  const allocationData = @json(array_values($escrowAllocation));
  new Chart(escrowCtx, {
 type: 'doughnut',
 data: {
 labels: ['Held (Secure)', 'Released', 'Refunded', 'Disputed'],
 datasets: [{
 data: allocationData,
 backgroundColor: [
 '#18181b', // Held (zinc-900)
 '#71717a', // Released (zinc-500)
 '#d4d4d8', // Refunded (zinc-300)
 '#ef4444' // Disputed (red-500)
 ],
 borderWidth: 2,
 borderColor: '#ffffff',
 hoverOffset: 4
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 cutout: '80%',
 plugins: {
 legend: {
 display: false // Custom list legend
 },
 tooltip: {
 backgroundColor: '#18181b',
 titleColor: '#ffffff',
 bodyColor: '#e4e4e7',
 titleFont: {
 family: 'Outfit',
 size: 11
 },
 bodyFont: {
 family: 'Plus Jakarta Sans',
 size: 10
 },
 padding: 8,
 cornerRadius: 6,
 callbacks: {
 label: function(context) {
 return ' ' + context.label + ': ₹' + context.raw.toLocaleString();
 }
 }
 }
 }
 }
 });

});
</script>
@endsection
