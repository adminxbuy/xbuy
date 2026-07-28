@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', '')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-0.5">
            <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
            <p class="text-sm" style="color: var(--muted-foreground);">Manage listings, disputes, alerts, and platform metrics.</p>
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
                <div class="flex items-center gap-2 h-9 px-3 rounded-lg" style="border: 1px solid var(--border); background: var(--card);">
                    <i data-lucide="calendar" class="w-3.5 h-3.5" style="color: var(--muted-foreground);"></i>
                    <select x-model="currentPreset" @change="applyPreset($event.target.value)" class="text-xs font-medium bg-transparent focus:outline-none cursor-pointer" style="color: var(--foreground);">
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
                    <input type="text" x-ref="startInput" placeholder="Start Date" readonly class="h-9 px-3 text-xs rounded-lg w-28 text-center focus:outline-none" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                    <span class="text-xs" style="color: var(--muted-foreground);">to</span>
                    <input type="text" x-ref="endInput" placeholder="End Date" readonly class="h-9 px-3 text-xs rounded-lg w-28 text-center focus:outline-none" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                </div>
            </div>
            @if(request()->anyFilled(['date_start', 'date_end']))
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center h-9 px-4 rounded-lg text-xs font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                    Reset
                </a>
            @endif
            <button type="button" onclick="window.location.reload()" class="inline-flex items-center justify-center h-9 px-4 rounded-lg text-xs font-semibold transition-all gap-1.5" style="background: var(--primary); color: var(--primary-foreground);">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Refresh</span>
            </button>
        </form>
    </div>

    @if($showStaffPayReviewPending)
    <div class="p-4 rounded-lg text-xs font-medium flex items-center justify-between" style="background: #fef3c7; border: 1px solid #fde68a; color: #92400e;">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4" style="color: #d97706;"></i>
            <span>Staff Pay Review Pending: Amendment trigger reached this month.</span>
        </div>
        <a href="{{ route('admin.payroll') }}" class="px-3 py-1 rounded-md transition-all text-[10px] font-bold" style="background: #d97706; color: white;">Review</a>
    </div>
    @endif

    @php
        function pctBadge(float $pct): string {
            $up = $pct >= 0;
            $symbol = $up ? '+' : '';
            $arrow = $up 
                ? '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8L6 21" /></svg>'
                : '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8L6 3" /></svg>';
            return "<span class='inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full' style='background: var(--primary); color: var(--primary-foreground);'>{$arrow}{$symbol}{$pct}%</span>";
        }
    @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl p-6" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: var(--muted); color: var(--muted-foreground);">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium" style="color: var(--muted-foreground);">Total Sales</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight">₹{{ number_format($stats['total_sales'], 0) }}</span>
                        {!! pctBadge($stats['total_sales_pct']) !!}
                    </div>
                    <p class="text-xs" style="color: var(--muted-foreground);">from last period</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl p-6" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: var(--muted); color: var(--muted-foreground);">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium" style="color: var(--muted-foreground);">Escrow Held</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight">₹{{ number_format($stats['escrow_held'], 0) }}</span>
                        {!! pctBadge($stats['escrow_held_pct']) !!}
                    </div>
                    <p class="text-xs" style="color: var(--muted-foreground);">held in trust</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl p-6" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: var(--muted); color: var(--muted-foreground);">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium" style="color: var(--muted-foreground);">Monthly Revenue</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight">₹{{ number_format($stats['monthly_revenue'], 0) }}</span>
                        {!! pctBadge($stats['monthly_rev_pct']) !!}
                    </div>
                    <p class="text-xs" style="color: var(--muted-foreground);">commission earned</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl p-6" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: var(--muted); color: var(--muted-foreground);">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium" style="color: var(--muted-foreground);">Total Users</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight">{{ number_format($stats['total_users']) }}</span>
                        {!! pctBadge($stats['total_users_pct']) !!}
                    </div>
                    <p class="text-xs" style="color: var(--muted-foreground);">registered members</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Chart & Quick Approvals --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-xl lg:col-span-2 flex flex-col justify-between" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div class="flex items-center justify-between p-6 pb-2">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Sales by Category</h3>
                    <p class="text-xs" style="color: var(--muted-foreground);">Distribution of active inventory by category class.</p>
                </div>
                <button class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                    <i data-lucide="share" class="w-3.5 h-3.5 mr-1"></i> Export
                </button>
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
                            <div class="flex items-center space-x-2">
                                <span class="font-bold">{{ strtoupper($cat->category) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" style="{{ $isUp ? 'background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;' : 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;' }}">{{ $trendVal }}</span>
                            </div>
                            <span class="font-bold">{{ $percentage }}%</span>
                        </div>
                        <div class="w-full rounded-full h-1.5 overflow-hidden" style="background: var(--muted);">
                            <div class="h-full rounded-full" style="width: {{ $percentage }}%; background: var(--primary);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl flex flex-col justify-between" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div class="flex flex-col space-y-1.5 p-6 pb-2">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Quick Approvals</h3>
                <p class="text-xs" style="color: var(--muted-foreground);">Items waiting for moderation.</p>
            </div>
            
            <div class="p-6 pt-0 space-y-3">
                <a href="{{ route('admin.sellers') }}?kyc_status=pending" class="flex items-center justify-between p-3.5 rounded-lg transition-all" style="background: var(--muted); border: 1px solid var(--border);">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="user-check" class="w-4 h-4" style="color: var(--muted-foreground);"></i>
                        <span class="text-xs font-semibold">Seller KYCs</span>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full" style="background: var(--primary); color: var(--primary-foreground);">{{ $stats['pending_kyc'] }}</span>
                </a>
                
                <a href="{{ route('admin.listings') }}?status=pending_approval" class="flex items-center justify-between p-3.5 rounded-lg transition-all" style="background: var(--muted); border: 1px solid var(--border);">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="package-search" class="w-4 h-4" style="color: var(--muted-foreground);"></i>
                        <span class="text-xs font-semibold">Pending Listings</span>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full" style="background: var(--primary); color: var(--primary-foreground);">{{ $stats['pending_listings'] }}</span>
                </a>
            </div>

            <div class="p-6 pt-2 border-t flex items-center justify-between text-[11px]" style="border-color: var(--border); color: var(--muted-foreground);">
                <span>Server Time</span>
                <span class="font-bold">{{ now()->format('d M, H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- Customer Activity Chart --}}
    <div class="rounded-xl p-6 space-y-4" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);" x-data="customerActivityChart()">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-lg font-bold tracking-tight">Customer Activity</h3>
                <p class="text-xs" style="color: var(--muted-foreground);">Customer activity for the last <span x-text="timeframeLabel">3 months</span></p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 h-9 px-3 rounded-lg" style="border: 1px solid var(--border); background: var(--card);">
                    <select x-model="timeframe" @change="updateChart()" class="text-xs font-semibold bg-transparent focus:outline-none cursor-pointer" style="color: var(--foreground);">
                        <option value="3_months">3 months</option>
                        <option value="6_months">6 months</option>
                        <option value="12_months">12 months</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 h-9 px-3 rounded-lg" style="border: 1px solid var(--border); background: var(--card);">
                    <select x-model="segment" @change="updateChart()" class="text-xs font-semibold bg-transparent focus:outline-none cursor-pointer" style="color: var(--foreground);">
                        <option value="all">All segments</option>
                        <option value="active">Active accounts</option>
                        <option value="new">New customers</option>
                        <option value="returning">Returning users</option>
                    </select>
                </div>
                <a href="{{ route('admin.analytics') }}" class="inline-flex items-center justify-center h-9 font-semibold px-4 rounded-lg text-xs transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                    View report
                </a>
            </div>
        </div>
        <div class="h-80 w-full mt-4">
            <canvas id="customerActivityChartCanvas"></canvas>
        </div>
    </div>

    {{-- Recent Disputes --}}
    <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
        <div class="p-6 border-b flex items-center justify-between" style="border-color: var(--border);">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Recent Disputes</h3>
                <p class="text-xs" style="color: var(--muted-foreground);">Escrow dispute tickets assigned to admin.</p>
            </div>
            <a href="{{ route('admin.disputes') }}" class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                View All <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead style="background: var(--muted);">
                    <tr class="border-b" style="border-color: var(--border);">
                        <th class="px-6 py-3 w-12 font-semibold" style="color: var(--muted-foreground);"><input type="checkbox" class="rounded"></th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Order</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Buyer</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Seller</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Reason</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Raised</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Status</th>
                        <th class="px-6 py-3 text-right font-semibold" style="color: var(--muted-foreground);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDisputes as $dispute)
                    <tr class="border-b transition-colors" style="border-color: var(--border);">
                        <td class="px-6 py-3.5"><input type="checkbox" class="rounded"></td>
                        <td class="px-6 py-3.5 font-bold">#{{ $dispute->order->order_number ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 font-semibold">{{ $dispute->buyer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 font-semibold">{{ $dispute->seller->shop_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 max-w-[180px] truncate" style="color: var(--muted-foreground);">{{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}</td>
                        <td class="px-6 py-3.5" style="color: var(--muted-foreground);">{{ $dispute->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                @if($dispute->status === 'open') style="background: var(--muted); border-color: var(--border); color: var(--foreground);"
                                @elseif($dispute->status === 'seller_responded') style="background: #eff6ff; border-color: #bfdbfe; color: #1e40af;"
                                @elseif($dispute->status === 'resolved') style="background: #f0fdf4; border-color: #bbf7d0; color: #166534;"
                                @else style="background: var(--muted); border-color: var(--border); color: var(--muted-foreground);" @endif">
                                {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right font-medium">
                            <a href="{{ route('admin.disputes.show', $dispute->id) }}" class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">Investigate</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center font-medium" style="color: var(--muted-foreground);">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--muted);">
                                    <i data-lucide="check-circle" class="w-5 h-5" style="color: #22c55e;"></i>
                                </div>
                                <h4 class="text-sm font-semibold">No Open Disputes</h4>
                                <p class="text-xs" style="color: var(--muted-foreground);">Everything has been addressed.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Orders (Real Data) --}}
    <div class="rounded-xl p-6 space-y-4" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold tracking-tight">Recent Orders</h3>
            <a href="{{ route('admin.orders') }}" class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                View All <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead style="background: var(--muted);">
                    <tr class="border-b" style="border-color: var(--border);">
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Order ID</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Customer</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Amount</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Status</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders ?? [] as $order)
                    <tr class="border-b transition-colors" style="border-color: var(--border);">
                        <td class="px-6 py-3.5 font-bold">#{{ $order->order_number }}</td>
                        <td class="px-6 py-3.5 font-semibold">{{ $order->buyer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 font-semibold">₹{{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                @if($order->status === 'completed') style="background: #f0fdf4; border-color: #bbf7d0; color: #166534;"
                                @elseif($order->status === 'in_transit') style="background: #eff6ff; border-color: #bfdbfe; color: #1e40af;"
                                @else style="background: var(--muted); border-color: var(--border); color: var(--muted-foreground);" @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5" style="color: var(--muted-foreground);">{{ $order->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center" style="color: var(--muted-foreground);">No recent orders</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Support Tickets --}}
    <div class="rounded-xl overflow-hidden flex flex-col justify-between" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
        <div class="p-6 border-b flex items-center justify-between" style="border-color: var(--border);">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Recent Support Tickets</h3>
                <p class="text-xs" style="color: var(--muted-foreground);">Buyer support requests escalated to admin.</p>
            </div>
            <a href="{{ route('admin.tickets') }}" class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                View All <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead style="background: var(--muted);">
                    <tr class="border-b" style="border-color: var(--border);">
                        <th class="px-6 py-3 w-12 font-semibold" style="color: var(--muted-foreground);"><input type="checkbox" class="rounded"></th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Ticket ID</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Buyer</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Subject</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Created</th>
                        <th class="px-6 py-3 font-semibold" style="color: var(--muted-foreground);">Status</th>
                        <th class="px-6 py-3 text-right font-semibold" style="color: var(--muted-foreground);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                    <tr class="border-b transition-colors" style="border-color: var(--border);">
                        <td class="px-6 py-3.5"><input type="checkbox" class="rounded"></td>
                        <td class="px-6 py-3.5 font-bold">#TKT-{{ $ticket->id }}</td>
                        <td class="px-6 py-3.5 font-semibold">{{ $ticket->buyer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 max-w-[200px] truncate" style="color: var(--muted-foreground);" title="{{ $ticket->message }}">{{ $ticket->subject }}</td>
                        <td class="px-6 py-3.5" style="color: var(--muted-foreground);">{{ $ticket->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                @if($ticket->status === 'open') style="background: var(--muted); border-color: var(--border); color: var(--foreground);"
                                @elseif($ticket->status === 'in_progress') style="background: #eff6ff; border-color: #bfdbfe; color: #1e40af;"
                                @elseif($ticket->status === 'resolved') style="background: #f0fdf4; border-color: #bbf7d0; color: #166534;"
                                @else style="background: var(--muted); border-color: var(--border); color: var(--muted-foreground);" @endif">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" class="inline-block">
                                @csrf
                                <input type="hidden" name="status" value="{{ $ticket->status === 'resolved' ? 'open' : 'resolved' }}">
                                <button type="submit" class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                                    {{ $ticket->status === 'resolved' ? 'Reopen' : 'Resolve' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center font-medium" style="color: var(--muted-foreground);">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--muted);">
                                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                                </div>
                                <h4 class="text-sm font-semibold">No Support Tickets</h4>
                                <p class="text-xs" style="color: var(--muted-foreground);">All requests are fully settled.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bottom Row: Seller Applications & Top Sellers --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-xl overflow-hidden flex flex-col justify-between min-h-[360px]" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div>
                <div class="p-5 border-b flex items-center justify-between" style="border-color: var(--border);">
                    <h3 class="text-sm font-semibold leading-none tracking-tight">Seller Applications</h3>
                    <a href="{{ route('admin.sellers') }}?kyc_status=pending" class="inline-flex items-center justify-center h-7 px-2 rounded-lg text-[10px] font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                        All <i data-lucide="arrow-right" class="w-3 h-3 ml-0.5"></i>
                    </a>
                </div>
                
                <div class="divide-y" style="border-color: var(--border);">
                    @forelse($pendingSellers ?? [] as $seller)
                    <div class="p-5 py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold truncate">{{ $seller->shop_name }}</p>
                            <p class="text-[10px] truncate mt-0.5" style="color: var(--muted-foreground);">{{ $seller->user->email ?? '' }}</p>
                        </div>
                        <form action="{{ route('admin.sellers.show', $seller->id) }}" method="GET" class="shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-[10px] font-bold transition-all" style="background: var(--primary); color: var(--primary-foreground);">Review</button>
                        </form>
                    </div>
                    @empty
                    <div class="p-8 text-center text-xs" style="color: var(--muted-foreground);">No pending applications</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden flex flex-col justify-between min-h-[360px]" style="border: 1px solid var(--border); background: var(--card); color: var(--card-foreground);">
            <div>
                <div class="p-5 border-b flex items-center justify-between" style="border-color: var(--border);">
                    <h3 class="text-sm font-semibold leading-none tracking-tight">Top Sellers</h3>
                    <a href="{{ route('admin.sellers') }}" class="inline-flex items-center justify-center h-7 px-2 rounded-lg text-[10px] font-semibold transition-all" style="border: 1px solid var(--border); color: var(--foreground);">
                        All <i data-lucide="arrow-right" class="w-3 h-3 ml-0.5"></i>
                    </a>
                </div>
                
                <div class="divide-y" style="border-color: var(--border);">
                    @forelse($topSellers ?? [] as $index => $seller)
                    <div class="p-5 py-3.5 flex items-center gap-3">
                        <span class="text-xs font-bold w-5 shrink-0" style="color: var(--muted-foreground);">#{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold truncate">{{ $seller->shop_name }}</p>
                            <p class="text-[10px] mt-0.5" style="color: var(--muted-foreground);">{{ $seller->orders_count ?? 0 }} orders</p>
                        </div>
                        <span class="text-xs font-bold shrink-0">₹{{ number_format($seller->total_revenue ?? 0) }}</span>
                    </div>
                    @empty
                    <div class="p-8 text-center text-xs" style="color: var(--muted-foreground);">No seller data yet</div>
                    @endforelse
                </div>
            </div>
        </div>
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