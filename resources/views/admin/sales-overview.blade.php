@extends('layouts.admin')

@section('title', 'Sales Overview')

@section('content')
<div class="space-y-6 p-6 overflow-y-auto max-h-[calc(100vh-3rem)]">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-0.5">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950">Sales Overview</h1>
            <p class="text-xs text-zinc-500">Track and monitor your marketplace transaction volume, escrows, and platform commissions.</p>
        </div>

        {{-- Filters & Actions Form --}}
        <form action="{{ route('admin.sales-overview') }}" method="GET" class="flex flex-wrap items-center gap-3">
            {{-- Preset selector --}}
            <div class="flex items-center gap-2 bg-white border border-zinc-200 h-9 px-3 rounded-lg">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-zinc-500"></i>
                <select name="preset" onchange="this.form.submit()" class="text-xs font-semibold bg-transparent text-zinc-700 focus:outline-none cursor-pointer">
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
                    <input type="text" x-ref="startInput" placeholder="Start Date" readonly class="h-9 px-3 text-xs border border-zinc-200 rounded-lg bg-white w-28 text-center focus:outline-none">
                    <span class="text-zinc-400 text-xs">to</span>
                    <input type="text" x-ref="endInput" placeholder="End Date" readonly class="h-9 px-3 text-xs border border-zinc-200 rounded-lg bg-white w-28 text-center focus:outline-none">
                </div>
            </div>

            @if(request()->anyFilled(['date_start', 'date_end']) || $preset !== '30days')
                <a href="{{ route('admin.sales-overview') }}" class="inline-flex items-center justify-center h-9 px-4 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-xs font-semibold transition-all">
                    Reset
                </a>
            @endif

            <button type="submit" name="export" value="csv" class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-xs font-semibold transition-all shadow-sm">
                <i data-lucide="share" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export</span>
            </button>
        </form>
    </div>

    {{-- Main Financial Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left: 4 Metric Cards Grid --}}
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            {{-- Gross Merchandise Value (GMV) --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Gross Merchandise Value</span>
                        <h3 class="text-2xl font-bold tracking-tight text-zinc-950 mt-1">₹{{ number_format($metrics['gmv'], 2) }}</h3>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['gmv_change'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $metrics['gmv_change'] >= 0 ? '+' : '' }}{{ $metrics['gmv_change'] }}%
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-50 flex items-center justify-between text-[11px] text-zinc-400">
                    <span>vs previous period</span>
                    <span class="font-medium text-zinc-500">
                        {{ $metrics['gmv_change'] >= 0 ? '+' : '' }}₹{{ number_format($metrics['gmv'] * (abs($metrics['gmv_change'])/100), 0) }}
                    </span>
                </div>
            </div>

            {{-- Escrow Balance --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Funds In Escrow</span>
                        <h3 class="text-2xl font-bold tracking-tight text-zinc-950 mt-1">₹{{ number_format($metrics['escrow'], 2) }}</h3>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['escrow_change'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $metrics['escrow_change'] >= 0 ? '+' : '' }}{{ $metrics['escrow_change'] }}%
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-50 flex items-center justify-between text-[11px] text-zinc-400">
                    <span>held securely in trust</span>
                    <span class="font-medium text-zinc-500">Active protection</span>
                </div>
            </div>

            {{-- Platform Commission / Monthly Spend equivalent --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Platform Revenue</span>
                        <h3 class="text-2xl font-bold tracking-tight text-zinc-950 mt-1">₹{{ number_format($metrics['revenue'], 2) }}</h3>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['revenue_change'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $metrics['revenue_change'] >= 0 ? '+' : '' }}{{ $metrics['revenue_change'] }}%
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-50 flex items-center justify-between text-[11px] text-zinc-400">
                    <span>net commissions earned</span>
                    <span class="font-medium text-zinc-500">Platform share</span>
                </div>
            </div>

            {{-- Order Fulfillment Rate / Savings rate equivalent --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Fulfillment Rate</span>
                        <h3 class="text-2xl font-bold tracking-tight text-zinc-950 mt-1">{{ number_format($metrics['fulfillment'], 1) }}%</h3>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $metrics['fulfillment_change'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $metrics['fulfillment_change'] >= 0 ? '+' : '' }}{{ $metrics['fulfillment_change'] }}%
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-50 flex items-center justify-between text-[11px] text-zinc-400">
                    <span>completed vs total orders</span>
                    <span class="font-medium text-zinc-500">Conversion efficiency</span>
                </div>
            </div>

        </div>

        {{-- Right: Income Sources (Sales by Category) & Alert --}}
        <div class="space-y-4">
            
            {{-- Sales By Category Card --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold tracking-tight text-zinc-900 mb-4">Sales Distribution</h3>
                <div class="space-y-4">
                    @php
                        $totalCatGmv = $categoriesData->sum('total_sales') ?: 1;
                        // Define different zinc shade colors for styling
                        $shades = ['bg-zinc-800', 'bg-zinc-600', 'bg-zinc-400', 'bg-zinc-300', 'bg-zinc-200'];
                    @endphp
                    @forelse($categoriesData->take(3) as $idx => $cat)
                        @php
                            $percentage = round(($cat->total_sales / $totalCatGmv) * 100);
                            $shade = $shades[$idx % count($shades)];
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <div>
                                    <span class="font-bold text-zinc-800">{{ ucfirst(str_replace('_', ' ', $cat->category)) }}</span>
                                    <span class="mx-1 text-zinc-300">•</span>
                                    <span>{{ $percentage }}%</span>
                                </div>
                                <span class="font-semibold text-zinc-850">₹{{ number_format($cat->total_sales, 0) }}</span>
                            </div>
                            <div class="w-full bg-zinc-100 rounded-full h-2 overflow-hidden">
                                <div class="{{ $shade }} h-full rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-zinc-400 font-medium">No sales recorded.</div>
                    @endforelse
                </div>
            </div>

            {{-- Credit Score / API Gateway health notification widget --}}
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 shadow-sm flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-150 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <div class="space-y-1 min-w-0 flex-1">
                    <h4 class="text-xs font-bold text-zinc-850">Razorpay Payout Connection status</h4>
                    <p class="text-[10px] text-zinc-450 leading-normal">API gateway integration is optimal. Automatic escrow splits active.</p>
                </div>
                <a href="{{ route('admin.settings') }}" class="text-[10px] font-bold text-zinc-900 border border-zinc-200 bg-white hover:bg-zinc-55 px-2 py-1 rounded shadow-sm shrink-0">
                    Configure
                </a>
            </div>

        </div>

    </div>

    {{-- Line Chart and Doughnut Allocation row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- GMV and Revenue Trend Line Chart (Full Width left) --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between pb-2">
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold tracking-tight text-zinc-900">Spending Overview (GMV Trend)</h3>
                    <p class="text-[10px] text-zinc-400">Total transacted volume and commission timeline.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-zinc-500">
                        <span class="w-2.5 h-1.5 bg-zinc-900 inline-block rounded-sm"></span> GMV
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-zinc-400">
                        <span class="w-2.5 h-1.5 bg-zinc-300 inline-block rounded-sm"></span> Commission
                    </span>
                </div>
            </div>
            <div class="h-80 w-full mt-4">
                <canvas id="salesOverviewChart"></canvas>
            </div>
        </div>

        {{-- Account Allocation (Doughnut Escrow Allocation) --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm flex flex-col justify-between">
            <div class="pb-2">
                <h3 class="text-sm font-semibold tracking-tight text-zinc-900">Account Allocation (Escrow status)</h3>
                <p class="text-[10px] text-zinc-400">Total active funds allocation in trust escrow.</p>
            </div>

            <div class="relative flex items-center justify-center h-48 my-3">
                <canvas id="escrowAllocationChart" class="max-h-full"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-2">
                    <span class="text-lg font-bold tracking-tight text-zinc-950">₹{{ number_format($metrics['escrow'], 0) }}</span>
                    <span class="text-[9px] font-medium text-zinc-400">Held in Trust</span>
                </div>
            </div>

            <div class="text-[10px] font-semibold text-zinc-500 flex flex-col gap-2 pt-3 border-t border-zinc-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-zinc-900 inline-block"></span>
                        <span>Held (Secure)</span>
                    </div>
                    <span class="font-bold text-zinc-800">₹{{ number_format($escrowAllocation['held'], 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-zinc-500 inline-block"></span>
                        <span>Released</span>
                    </div>
                    <span class="font-bold text-zinc-800">₹{{ number_format($escrowAllocation['released'], 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-zinc-300 inline-block"></span>
                        <span>Refunded</span>
                    </div>
                    <span class="font-bold text-zinc-800">₹{{ number_format($escrowAllocation['refunded'], 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-rose-500 inline-block"></span>
                        <span>Disputed</span>
                    </div>
                    <span class="font-bold text-zinc-800">₹{{ number_format($escrowAllocation['disputed'], 0) }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Bottom Section Grid: Wallet, Upcoming Releases, Quick Transfer --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Card 1: Wallet --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm flex flex-col justify-between min-h-[300px]">
            <div>
                <h3 class="text-sm font-semibold tracking-tight text-zinc-900 mb-4">Wallet</h3>
                <div class="space-y-4">
                    {{-- Item 1 --}}
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-zinc-800">Razorpay Route settlement</p>
                            <p class="text-[10px] text-zinc-400">**** 4182</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-zinc-900">₹{{ number_format($metrics['revenue'] * 1.5, 2) }}</span>
                            <div class="w-8 h-8 rounded-lg bg-zinc-50 border border-zinc-150 flex items-center justify-center font-bold text-sm text-zinc-700 shrink-0">R</div>
                        </div>
                    </div>
                    {{-- Item 2 --}}
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-zinc-800">Escrow Trust Pool</p>
                            <p class="text-[10px] text-zinc-400">**** 1004</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-zinc-900">₹{{ number_format($metrics['escrow'], 2) }}</span>
                            <div class="w-8 h-8 rounded-lg bg-zinc-50 border border-zinc-150 flex items-center justify-center font-bold text-sm text-zinc-700 shrink-0">E</div>
                        </div>
                    </div>
                    {{-- Item 3 --}}
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-zinc-800">Platform commission Reserves</p>
                            <p class="text-[10px] text-zinc-400">**** 9912</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-zinc-900">₹{{ number_format($metrics['revenue'], 2) }}</span>
                            <div class="w-8 h-8 rounded-lg bg-zinc-50 border border-zinc-150 flex items-center justify-center font-bold text-sm text-zinc-700 shrink-0">P</div>
                        </div>
                    </div>
                    {{-- Item 4 --}}
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-zinc-800">Refund reserve Pool</p>
                            <p class="text-[10px] text-zinc-400">**** 8832</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-zinc-900">₹{{ number_format($escrowAllocation['disputed'], 2) }}</span>
                            <div class="w-8 h-8 rounded-lg bg-zinc-50 border border-zinc-150 flex items-center justify-center font-bold text-sm text-zinc-700 shrink-0">D</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between border-t border-zinc-100 pt-3 text-[10px] text-zinc-450 mt-4">
                <span>Physical Vault: Ledger Nano X</span>
                <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> AIR-GAPPED
                </span>
            </div>
        </div>

        {{-- Card 2: Upcoming Bills & Payments --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm flex flex-col justify-between min-h-[300px]">
            <div>
                <h3 class="text-sm font-semibold tracking-tight text-zinc-900">Upcoming Bills & Payments</h3>
                
                <div class="mt-4">
                    <h2 class="text-3xl font-extrabold tracking-tight text-zinc-950">₹{{ number_format($metrics['escrow'], 2) }}</h2>
                    <p class="text-xs text-zinc-400 mt-1">You have {{ count($upcomingReleases) }} bills due this month</p>
                </div>
                
                @if(count($upcomingReleases) > 0)
                <div class="mt-3 bg-zinc-50 border border-zinc-150 rounded-lg p-2.5 flex items-center gap-2 text-xs font-semibold text-zinc-800">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-zinc-950 shrink-0"></i>
                    <span>Autopay will process ₹{{ number_format($upcomingReleases->take(1)->first()->amount_held, 0) }} today</span>
                </div>
                @endif
                
                <div class="mt-4 space-y-2">
                    @forelse($upcomingReleases->take(3) as $escrow)
                        <a href="{{ route('admin.escrow') }}" class="flex items-center justify-between p-2.5 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-all border border-zinc-150 text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-white border border-zinc-200 flex items-center justify-center font-bold text-zinc-700">
                                    {{ substr($escrow->order->seller->shop_name ?? 'S', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-zinc-800 truncate">{{ $escrow->order->seller->shop_name ?? 'Seller' }}</p>
                                    <p class="text-[10px] text-zinc-400 truncate">{{ $escrow->release_scheduled_at ? $escrow->release_scheduled_at->format('H.i A • F d, Y') : 'Scheduled' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="font-semibold text-zinc-900">₹{{ number_format($escrow->amount_held, 0) }}</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-400"></i>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-6 text-xs text-zinc-400 font-medium">No upcoming payouts.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Column 3: Quick Transfer & Shortcuts --}}
        <div class="space-y-4">
            
            {{-- Quick Transfer --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold tracking-tight text-zinc-900">Quick Transfer</h3>
                    <div class="flex items-center -space-x-2">
                        <img src="https://ui-avatars.com/api/?name=AR&size=24&background=f4f4f5" class="w-6 h-6 rounded-full border border-white" alt="avatar">
                        <img src="https://ui-avatars.com/api/?name=SC&size=24&background=f4f4f5" class="w-6 h-6 rounded-full border border-white" alt="avatar">
                        <img src="https://ui-avatars.com/api/?name=MJ&size=24&background=f4f4f5" class="w-6 h-6 rounded-full border border-white" alt="avatar">
                        <img src="https://ui-avatars.com/api/?name=ED&size=24&background=f4f4f5" class="w-6 h-6 rounded-full border border-white" alt="avatar">
                        <div class="w-6 h-6 rounded-full border border-white bg-zinc-50 flex items-center justify-center text-[8px] font-bold text-zinc-400 cursor-pointer">+</div>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-zinc-400">₹</span>
                        <input type="number" placeholder="0.00" class="w-full pl-7 pr-12 h-9 border border-zinc-200 bg-white rounded-lg text-xs font-semibold focus:outline-none">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-zinc-400">INR</span>
                    </div>
                    <button type="button" onclick="alert('Payout Initiated (Simulation)')" class="bg-zinc-950 hover:bg-zinc-800 text-white font-semibold text-xs px-4 h-9 rounded-lg transition-all shadow-sm shrink-0">
                        Send
                    </button>
                </div>
            </div>

            {{-- Shortcuts --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold tracking-tight text-zinc-900 mb-4">Shortcuts</h3>
                <div class="grid grid-cols-4 gap-y-4 gap-x-2 text-center">
                    
                    <a href="{{ route('admin.sellers') }}?kyc_status=pending" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="qr-code" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">Scan QR</span>
                    </a>

                    <a href="{{ route('admin.payouts') }}" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="send" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">Transfer</span>
                    </a>

                    <a href="{{ route('admin.escrow') }}" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="banknote" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">Pay Bills</span>
                    </a>

                    <a href="{{ route('admin.audit-logs') }}" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="history" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">History</span>
                    </a>

                    <a href="#" onclick="event.preventDefault(); alert('Feature coming soon!')" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="smartphone" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">Mobile</span>
                    </a>

                    <a href="{{ route('admin.settings') }}" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="lightbulb" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">Electricity</span>
                    </a>

                    <a href="{{ route('admin.disputes') }}" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="droplet" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">Water</span>
                    </a>

                    <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-10 h-10 rounded-full border border-zinc-200 group-hover:bg-zinc-50 flex items-center justify-center transition-all shrink-0">
                            <i data-lucide="more-horizontal" class="w-4 h-4 text-zinc-800"></i>
                        </div>
                        <span class="text-[10px] font-medium text-zinc-500 group-hover:text-zinc-900 transition-colors">More</span>
                    </a>

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
                    '#ef4444'  // Disputed (red-500)
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
