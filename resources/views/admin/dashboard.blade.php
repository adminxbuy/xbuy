@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', '')

@section('content')
<div class="space-y-6 p-6">
    {{-- Header Row --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-0.5">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950">Dashboard</h1>
            <p class="text-xs text-zinc-500">Manage listings, disputes, alerts, and platform metrics.</p>
        </div>
        
        {{-- Timeline Date Range Filter Form --}}
        <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="date-range-picker-container flex items-center gap-2" x-data="dateRangePicker({
                start: '{{ request('date_start') }}',
                end: '{{ request('date_end') }}',
                startName: 'date_start',
                endName: 'date_end'
            })">
                <input type="hidden" name="date_start" x-model="dateStart">
                <input type="hidden" name="date_end" x-model="dateEnd">

                {{-- Preset Selector Styled as Shadcn Select Button --}}
                <div class="flex items-center gap-2 bg-white border border-zinc-200 h-9 px-3 rounded-lg">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-zinc-500"></i>
                    <select x-model="currentPreset" @change="applyPreset($event.target.value)" class="text-xs font-medium bg-transparent text-zinc-700 focus:outline-none cursor-pointer">
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

                {{-- Custom Date Inputs --}}
                <div class="flex items-center gap-2" x-show="currentPreset === 'custom'" x-cloak>
                    <input type="text" x-ref="startInput" placeholder="Start Date" readonly class="h-9 px-3 text-xs border border-zinc-200 rounded-lg bg-white w-28 text-center focus:outline-none">
                    <span class="text-zinc-400 text-xs">to</span>
                    <input type="text" x-ref="endInput" placeholder="End Date" readonly class="h-9 px-3 text-xs border border-zinc-200 rounded-lg bg-white w-28 text-center focus:outline-none">
                </div>
            </div>

            @if(request()->anyFilled(['date_start', 'date_end']))
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center h-9 px-4 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-xs font-semibold transition-all">
                    Reset
                </a>
            @endif

            <button type="button" onclick="window.location.reload()" class="inline-flex items-center justify-center h-9 bg-zinc-950 hover:bg-zinc-800 text-zinc-50 font-semibold px-4 rounded-lg text-xs transition-all gap-1.5">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Refresh Stats</span>
            </button>
        </form>
    </div>

    @if($showStaffPayReviewPending)
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs font-medium flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
            <span>Staff Pay Review Pending: Amendment trigger reached this month. Please review staff compensation.</span>
        </div>
        <a href="{{ route('admin.payroll') }}" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-md transition-all text-[10px] font-bold">
            Review
        </a>
    </div>
    @endif

    {{-- Helper for Percentage Trend Badges --}}
    @php
        function pctBadge(float $pct): string {
            $up = $pct >= 0;
            $symbol = $up ? '+' : '';
            $arrow = $up 
                ? '<svg class="w-3 h-3 text-zinc-50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8L6 21" /></svg>'
                : '<svg class="w-3 h-3 text-zinc-50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8L6 3" /></svg>';
            return "<span class='inline-flex items-center gap-1 bg-zinc-950 text-white text-[11px] font-semibold px-2 py-0.5 rounded-full'>{$arrow}{$symbol}{$pct}%</span>";
        }
    @endphp

    {{-- ── 1. 4-Card Stats Summary Grid (Shadcn Card layout) ──────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Sales --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 flex flex-col justify-between p-6">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-50 border border-zinc-150 flex items-center justify-center text-zinc-600">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium text-zinc-500">Total Sales</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight text-zinc-950">₹{{ number_format($stats['total_sales'], 0) }}</span>
                        {!! pctBadge($stats['total_sales_pct']) !!}
                    </div>
                    <p class="text-xs text-zinc-400">from last period</p>
                </div>
            </div>
        </div>

        {{-- Escrow Held --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 flex flex-col justify-between p-6">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-50 border border-zinc-150 flex items-center justify-center text-zinc-600">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium text-zinc-500">Escrow Held</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight text-zinc-950">₹{{ number_format($stats['escrow_held'], 0) }}</span>
                        {!! pctBadge($stats['escrow_held_pct']) !!}
                    </div>
                    <p class="text-xs text-zinc-400">held in trust</p>
                </div>
            </div>
        </div>

        {{-- Monthly Revenue --}}
        <div class="rounded-xl border border-zinc-250 bg-white text-zinc-950 flex flex-col justify-between p-6 ring-1 ring-zinc-950">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-50 border border-zinc-150 flex items-center justify-center text-zinc-600">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold text-zinc-900">Monthly Revenue</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight text-zinc-950">₹{{ number_format($stats['monthly_revenue'], 0) }}</span>
                        {!! pctBadge($stats['monthly_rev_pct']) !!}
                    </div>
                    <p class="text-xs text-zinc-500 font-semibold">commission earned</p>
                </div>
            </div>
        </div>

        {{-- Total Users --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 flex flex-col justify-between p-6">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-50 border border-zinc-150 flex items-center justify-center text-zinc-600">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium text-zinc-500">Total Users</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight text-zinc-950">{{ number_format($stats['total_users']) }}</span>
                        {!! pctBadge($stats['total_users_pct']) !!}
                    </div>
                    <p class="text-xs text-zinc-400">registered members</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Category Chart, Quick Approvals & Store Visits ──────── --}}
    {{-- ── 2. Category Chart & Quick Approvals ──────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Listings by Category --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 lg:col-span-2 flex flex-col justify-between">
            <div class="flex items-center justify-between p-6 pb-2">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Sales by Category</h3>
                    <p class="text-xs text-zinc-500">Distribution of active inventory by category class.</p>
                </div>
                <button class="inline-flex items-center justify-center h-8 px-3 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-xs font-semibold transition-all">
                    <i data-lucide="share" class="w-3.5 h-3.5 mr-1"></i>
                    <span>Export</span>
                </button>
            </div>
            
            <div class="p-6 pt-0 space-y-4 overflow-y-auto max-h-[300px]">
                @php
                    $totalListings = $categoriesData->sum('count') ?: 1;
                @endphp
                @foreach($categoriesData as $index => $cat)
                    @php
                        $percentage = round(($cat->count / $totalListings) * 100);
                        // Simulate some mock trend values matching Canada, Greenland etc.
                        $trends = ['+5.2%', '+7.8%', '-2.1%', '+3.4%', '+1.2%', '+1%'];
                        $trendVal = $trends[$index % count($trends)];
                        $isUp = str_contains($trendVal, '+');
                        $trendColor = $isUp ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100';
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-zinc-800">{{ strtoupper($cat->category) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $trendColor }}">
                                    {{ $trendVal }}
                                </span>
                            </div>
                            <span class="font-bold text-zinc-900">{{ $percentage }}%</span>
                        </div>
                        <div class="w-full bg-zinc-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-zinc-950 h-full rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Approvals Card --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 flex flex-col justify-between">
            <div class="flex flex-col space-y-1.5 p-6 pb-2">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Quick Approvals</h3>
                <p class="text-xs text-zinc-500">Items waiting for system moderation.</p>
            </div>
            
            <div class="p-6 pt-0 space-y-3">
                <a href="{{ route('admin.sellers') }}?kyc_status=pending"
                   class="flex items-center justify-between p-3.5 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-all border border-zinc-100">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="user-check" class="w-4 h-4 text-zinc-650"></i>
                        <span class="text-xs font-semibold text-zinc-800">Seller KYCs</span>
                    </div>
                    <span class="bg-zinc-950 text-zinc-50 text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">{{ $stats['pending_kyc'] }}</span>
                </a>
                
                <a href="{{ route('admin.listings') }}?status=pending_approval"
                   class="flex items-center justify-between p-3.5 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-all border border-zinc-100">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="package-search" class="w-4 h-4 text-zinc-650"></i>
                        <span class="text-xs font-semibold text-zinc-800">Pending Listings</span>
                    </div>
                    <span class="bg-zinc-950 text-zinc-50 text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">{{ $stats['pending_listings'] }}</span>
                </a>
            </div>

            <div class="p-6 pt-2 border-t border-zinc-100 flex items-center justify-between text-[11px] text-zinc-400">
                <span>System Server Time</span>
                <span class="font-bold text-zinc-700">{{ now()->format('d M, H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- ── 3. Customer Activity Chart (Full Width) ──────────────────── --}}
    <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 flex flex-col p-6 space-y-4" x-data="customerActivityChart()">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-lg font-bold tracking-tight text-zinc-900">Customer Activity</h3>
                <p class="text-xs text-zinc-500">Customer activity for the last <span x-text="timeframeLabel">3 months</span></p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Timeframe dropdown --}}
                <div class="flex items-center gap-2 bg-white border border-zinc-200 h-9 px-3 rounded-lg">
                    <select x-model="timeframe" @change="updateChart()" class="text-xs font-semibold bg-transparent text-zinc-750 focus:outline-none cursor-pointer">
                        <option value="3_months">3 months</option>
                        <option value="6_months">6 months</option>
                        <option value="12_months">12 months</option>
                    </select>
                </div>

                {{-- Segment dropdown --}}
                <div class="flex items-center gap-2 bg-white border border-zinc-200 h-9 px-3 rounded-lg">
                    <select x-model="segment" @change="updateChart()" class="text-xs font-semibold bg-transparent text-zinc-750 focus:outline-none cursor-pointer">
                        <option value="all">All segments</option>
                        <option value="active">Active accounts</option>
                        <option value="new">New customers</option>
                        <option value="returning">Returning users</option>
                    </select>
                </div>

                {{-- View Report button --}}
                <button type="button" class="inline-flex items-center justify-center h-9 border border-zinc-200 hover:bg-zinc-50 text-zinc-950 font-semibold px-4 rounded-lg text-xs transition-all">
                    <span>View report</span>
                </button>
            </div>
        </div>



        <div class="h-80 w-full mt-4">
            <canvas id="customerActivityChartCanvas"></canvas>
        </div>
    </div>

    {{-- ── 3.5 Customer Reviews & Store Visits ────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Customer Reviews Card --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 flex flex-col justify-between p-6">
            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold leading-none tracking-tight">Customer Reviews</h3>
                    <p class="text-[10px] text-zinc-500">Based on 5,500 verified purchases</p>
                </div>
                <a href="{{ route('admin.ratings') }}" class="inline-flex items-center justify-center h-7 px-2 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-[10px] font-semibold transition-all">
                    <span>View All</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 ml-0.5"></i>
                </a>
            </div>

            {{-- Breakdown Grid --}}
            <div class="flex items-center gap-4 py-3">
                {{-- Average rating left --}}
                <div class="flex flex-col items-center justify-center text-center shrink-0 w-20">
                    <div class="flex items-center text-amber-400 gap-0.5 mb-1">
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current opacity-30"></i>
                    </div>
                    <span class="text-3xl font-extrabold tracking-tight text-zinc-950">4.5</span>
                    <span class="text-[9px] text-zinc-400 font-semibold uppercase">out of 5</span>
                </div>

                {{-- Progress bars right --}}
                <div class="flex-1 space-y-1 text-[10px]">
                    {{-- 5 Star --}}
                    <div class="flex items-center gap-2">
                        <span class="w-4 font-bold text-zinc-650">5★</span>
                        <div class="flex-1 bg-zinc-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 73%"></div>
                        </div>
                        <span class="w-8 text-right text-zinc-400 font-bold">4000</span>
                    </div>
                    {{-- 4 Star --}}
                    <div class="flex items-center gap-2">
                        <span class="w-4 font-bold text-zinc-650">4★</span>
                        <div class="flex-1 bg-zinc-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-lime-500 h-full rounded-full" style="width: 38%"></div>
                        </div>
                        <span class="w-8 text-right text-zinc-400 font-bold">2100</span>
                    </div>
                    {{-- 3 Star --}}
                    <div class="flex items-center gap-2">
                        <span class="w-4 font-bold text-zinc-650">3★</span>
                        <div class="flex-1 bg-zinc-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full" style="width: 15%"></div>
                        </div>
                        <span class="w-8 text-right text-zinc-400 font-bold">800</span>
                    </div>
                    {{-- 2 Star --}}
                    <div class="flex items-center gap-2">
                        <span class="w-4 font-bold text-zinc-650">2★</span>
                        <div class="flex-1 bg-zinc-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-orange-500 h-full rounded-full" style="width: 11%"></div>
                        </div>
                        <span class="w-8 text-right text-zinc-400 font-bold">631</span>
                    </div>
                    {{-- 1 Star --}}
                    <div class="flex items-center gap-2">
                        <span class="w-4 font-bold text-zinc-650">1★</span>
                        <div class="flex-1 bg-zinc-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-rose-500 h-full rounded-full" style="width: 6%"></div>
                        </div>
                        <span class="w-8 text-right text-zinc-400 font-bold">344</span>
                    </div>
                </div>
            </div>

            {{-- Recent Review Card --}}
            <div class="bg-zinc-50 border border-zinc-150 rounded-lg p-3 space-y-2 mt-2">
                <div class="flex items-center justify-between text-[10px]">
                    <div class="flex text-amber-400 gap-0.5">
                        <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                        <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                        <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                        <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                        <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                    </div>
                    <span class="text-zinc-400">March 12, 2025</span>
                </div>
                <h4 class="text-xs font-bold text-zinc-800 leading-tight">Exceeded my expectations!</h4>
                <p class="text-[10px] text-zinc-550 leading-relaxed">I was skeptical at first, but this product has completely changed my daily routine. The quality is outstanding.</p>
                <div class="flex items-center gap-2 pt-1.5 border-t border-zinc-200/50">
                    <span class="text-[10px] font-bold text-zinc-705">Sarah J.</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[8px] font-extrabold uppercase border border-emerald-100">Verified Purchase</span>
                </div>
            </div>
        </div>

        {{-- Store Visits by Source --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 flex flex-col justify-between p-6">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Store Visits</h3>
                <p class="text-xs text-zinc-500">Traffic by channel.</p>
            </div>
            
            {{-- Doughnut Container with Center Text --}}
            <div class="relative flex items-center justify-center h-32 my-2">
                <canvas id="visitsChart" class="max-h-full"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-1.5">
                    <span class="text-xl font-bold tracking-tight text-zinc-950">10.2K</span>
                    <span class="text-[9px] font-medium text-zinc-400">Visitors</span>
                </div>
            </div>
            
            {{-- Custom Legend --}}
            <div class="text-[9px] font-bold text-zinc-705 flex flex-col items-center gap-1.5 pt-2 border-t border-zinc-100">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-[#09090b]"></span> Direct</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-[#27272a]"></span> Social</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-[#52525b]"></span> Email</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-[#a1a1aa]"></span> Referrals</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-[#e4e4e7]"></span> Other</span>
                </div>
            </div>
        </div>
    </div>


    {{-- ── 4. Recent Disputes Table ───────────────────────────────────── --}}
    <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 overflow-hidden">
        <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Recent Dispute Escalations</h3>
                <p class="text-xs text-zinc-500">Escrow dispute tickets currently assigned to admin resolution.</p>
            </div>
            <a href="{{ route('admin.disputes') }}" class="inline-flex items-center justify-center h-8 px-3 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-xs font-semibold transition-all">
                View All <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-zinc-50/75 border-b border-zinc-200">
                    <tr>
                        <th class="px-6 py-3 w-12 text-zinc-500 font-semibold"><input type="checkbox" class="rounded border-zinc-300"></th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Order</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Buyer</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Seller</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Reason</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Raised</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Status</th>
                        <th class="px-6 py-3 text-right text-zinc-500 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-150">
                    @forelse($recentDisputes as $dispute)
                    <tr class="hover:bg-zinc-50/50 transition-all">
                        <td class="px-6 py-3.5"><input type="checkbox" class="rounded border-zinc-300"></td>
                        <td class="px-6 py-3.5 font-bold text-zinc-900">#{{ $dispute->order->order_number ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 text-zinc-700 font-semibold">{{ $dispute->buyer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 text-zinc-700 font-semibold">{{ $dispute->seller->shop_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 text-zinc-500 max-w-[180px] truncate">{{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}</td>
                        <td class="px-6 py-3.5 text-zinc-400">{{ $dispute->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border
                                @if($dispute->status === 'open') bg-zinc-100 border-zinc-200 text-zinc-800
                                @elseif($dispute->status === 'seller_responded') bg-blue-100 border-blue-200 text-blue-800
                                @elseif($dispute->status === 'resolved') bg-emerald-100 border-emerald-200 text-emerald-800
                                @else bg-zinc-100 border-zinc-200 text-zinc-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right font-medium">
                            <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                               class="inline-flex items-center justify-center h-8 px-3 border border-zinc-250 hover:bg-zinc-100 hover:border-zinc-300 rounded-lg text-xs font-semibold transition-all">
                                Investigate
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500 font-medium">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="w-10 h-10 bg-zinc-150 rounded-lg flex items-center justify-center">
                                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                                </div>
                                <h4 class="text-sm font-semibold">No Open Disputes</h4>
                                <p class="text-xs text-zinc-455">Everything has been addressed.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── 5 & 6. Recent Orders & Support Tickets ────────────────────────── --}}

    {{-- ── 6. Full Width Recent Orders ─────────────────────────────────── --}}
    <div x-data="{
        search: '',
        sortBy: 'amount',
        sortDesc: true,
        page: 1,
        perPage: 8,
        orders: [
            {
                id: '#1023',
                customerName: 'Theodore Bell',
                customerEmail: 'theodore.bell@example.com',
                avatar: 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Tire Doodad',
                amount: 300.00,
                status: 'processing'
            },
            {
                id: '#2045',
                customerName: 'Amelia Grant',
                customerEmail: 'amelia.grant@example.com',
                avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Engine Kit',
                amount: 450.00,
                status: 'paid'
            },
            {
                id: '#3067',
                customerName: 'Eleanor Ward',
                customerEmail: 'eleanor.ward@example.com',
                avatar: '',
                product: 'Brake Pad',
                amount: 200.00,
                status: 'success'
            },
            {
                id: '#4089',
                customerName: 'Henry Carter',
                customerEmail: 'henry.carter@example.com',
                avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Fuel Pump',
                amount: 500.00,
                status: 'processing'
            },
            {
                id: '#5102',
                customerName: 'Olivia Harris',
                customerEmail: 'olivia.harris@example.com',
                avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Steering Wheel',
                amount: 350.00,
                status: 'failed'
            },
            {
                id: '#6123',
                customerName: 'James Robinson',
                customerEmail: 'james.robinson@example.com',
                avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Air Filter',
                amount: 180.00,
                status: 'paid'
            },
            {
                id: '#7145',
                customerName: 'Sophia Martinez',
                customerEmail: 'sophia.martinez@example.com',
                avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Oil Filter',
                amount: 220.00,
                status: 'success'
            },
            {
                id: '#8167',
                customerName: 'Liam Thompson',
                customerEmail: 'liam.thompson@example.com',
                avatar: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Radiator Cap',
                amount: 290.00,
                status: 'processing'
            },
            {
                id: '#9104',
                customerName: 'Noah Walker',
                customerEmail: 'noah.walker@example.com',
                avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Spark Plug',
                amount: 120.00,
                status: 'success'
            },
            {
                id: '#1125',
                customerName: 'Charlotte Hall',
                customerEmail: 'charlotte.hall@example.com',
                avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Brake Disc',
                amount: 380.00,
                status: 'paid'
            },
            {
                id: '#1236',
                customerName: 'Lucas Allen',
                customerEmail: 'lucas.allen@example.com',
                avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Wiper Blades',
                amount: 95.00,
                status: 'success'
            },
            {
                id: '#1347',
                customerName: 'Mia Young',
                customerEmail: 'mia.young@example.com',
                avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Car Battery',
                amount: 410.00,
                status: 'processing'
            },
            {
                id: '#1458',
                customerName: 'Ethan King',
                customerEmail: 'ethan.king@example.com',
                avatar: '',
                product: 'Alternator',
                amount: 320.00,
                status: 'failed'
            },
            {
                id: '#1569',
                customerName: 'Harper Wright',
                customerEmail: 'harper.wright@example.com',
                avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Cabin Filter',
                amount: 85.00,
                status: 'success'
            },
            {
                id: '#1670',
                customerName: 'Evelyn Scott',
                customerEmail: 'evelyn.scott@example.com',
                avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Ignition Coil',
                amount: 175.00,
                status: 'paid'
            },
            {
                id: '#1781',
                customerName: 'Mason Green',
                customerEmail: 'mason.green@example.com',
                avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&h=150&q=80',
                product: 'Shock Absorber',
                amount: 260.00,
                status: 'processing'
            }
        ],
        get filteredOrders() {
            var term = this.search.toLowerCase();
            var list = this.orders.filter(function(o) {
                return o.id.toLowerCase().includes(term) || 
                       o.customerName.toLowerCase().includes(term) || 
                       o.product.toLowerCase().includes(term) ||
                       o.status.toLowerCase().includes(term);
            });
            if (this.sortBy === 'amount') {
                var desc = this.sortDesc;
                list.sort(function(a, b) {
                    return desc ? b.amount - a.amount : a.amount - b.amount;
                });
            }
            return list;
        },
        get paginatedOrders() {
            var start = (this.page - 1) * this.perPage;
            return this.filteredOrders.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.ceil(this.filteredOrders.length / this.perPage) || 1;
        },
        toggleSort(col) {
            if (this.sortBy === col) {
                this.sortDesc = !this.sortDesc;
            } else {
                this.sortBy = col;
                this.sortDesc = false;
            }
        },
        exportCSV() {
            var csv = 'ID,Customer,Product,Amount,Status\n';
            this.filteredOrders.forEach(function(o) {
                csv += o.id + ',' + o.customerName + ',' + o.product + ',' + o.amount + ',' + o.status + '\n';
            });
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'recent_orders.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }" class="rounded-xl border border-zinc-200 bg-white text-zinc-950 p-6 space-y-4">
        
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold tracking-tight text-zinc-900">Recent Orders</h3>
            <button @click="exportCSV()" class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-xs font-semibold transition-all shadow-sm">
                <i data-lucide="share" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export</span>
            </button>
        </div>

        <div>
            <input type="text" x-model="search" @input="page = 1" placeholder="Filter orders..." 
                class="w-full max-w-sm h-10 px-3 border border-zinc-200 rounded-lg bg-white text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 placeholder-zinc-400">
        </div>

        <div class="border border-zinc-200 rounded-lg overflow-hidden">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-zinc-50/75 border-b border-zinc-200">
                    <tr>
                        <th class="px-6 py-3.5 text-zinc-500 font-semibold w-24">ID</th>
                        <th class="px-6 py-3.5 text-zinc-500 font-semibold">Customer</th>
                        <th class="px-6 py-3.5 text-zinc-500 font-semibold">Product</th>
                        <th class="px-6 py-3.5 text-zinc-500 font-semibold w-32 cursor-pointer select-none hover:text-zinc-800" @click="toggleSort('amount')">
                            <div class="flex items-center gap-1">
                                <span>Amount</span>
                                <span class="text-[10px] text-zinc-400">↑↓</span>
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-zinc-500 font-semibold w-32">Status</th>
                        <th class="px-6 py-3.5 text-right text-zinc-500 font-semibold w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    <template x-for="order in paginatedOrders" :key="order.id">
                        <tr class="hover:bg-zinc-50/50 transition-all">
                            <td class="px-6 py-4 font-medium text-zinc-400" x-text="order.id"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <template x-if="order.avatar">
                                        <img :src="order.avatar" class="w-8 h-8 rounded-full border border-zinc-100 object-cover shrink-0" alt="Avatar">
                                    </template>
                                    <template x-if="!order.avatar">
                                        <div class="w-8 h-8 shrink-0"></div>
                                    </template>
                                    <div>
                                        <div class="font-semibold text-zinc-900" x-text="order.customerName"></div>
                                        <div class="text-[10px] text-zinc-400" x-text="order.customerEmail"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-zinc-700 font-medium max-w-[200px] truncate" x-text="order.product"></td>
                            <td class="px-6 py-4 font-semibold text-zinc-900" x-text="'$' + order.amount.toFixed(2)"></td>
                            <td class="px-6 py-4">
                                <template x-if="order.status === 'completed' || order.status === 'success'">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border border-emerald-500 text-emerald-600 bg-transparent">
                                        Success
                                    </span>
                                </template>
                                <template x-if="order.status === 'pending' || order.status === 'processing'">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border border-blue-400 text-blue-500 bg-transparent">
                                        Processing
                                    </span>
                                </template>
                                <template x-if="order.status === 'paid'">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border border-amber-500 text-amber-600 bg-transparent">
                                        Paid
                                    </span>
                                </template>
                                <template x-if="order.status === 'failed' || order.status === 'disputed' || order.status === 'refunded'">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-600 text-white border border-transparent">
                                        Failed
                                    </span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-zinc-400 hover:text-zinc-650 transition-colors">
                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="paginatedOrders.length === 0">
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-zinc-400 font-medium">
                                No orders matching the criteria.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between text-xs text-zinc-500 pt-2">
            <div>
                Showing <span class="font-semibold text-zinc-700" x-text="filteredOrders.length > 0 ? (page - 1) * perPage + 1 : 0"></span> to 
                <span class="font-semibold text-zinc-700" x-text="Math.min(page * perPage, filteredOrders.length)"></span> of 
                <span class="font-semibold text-zinc-700" x-text="filteredOrders.length"></span> entries
            </div>
            <div class="flex items-center gap-2">
                <button @click="if(page > 1) page--" :disabled="page === 1" 
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-50 disabled:opacity-50 disabled:pointer-events-none transition-all shadow-sm">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <button @click="if(page < totalPages) page++" :disabled="page === totalPages" 
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-50 disabled:opacity-50 disabled:pointer-events-none transition-all shadow-sm">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ── 5. Support Tickets Table ───────────────────────────────────── --}}
    <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 overflow-hidden flex flex-col justify-between">
        <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Recent Support Tickets</h3>
                <p class="text-xs text-zinc-500">Buyer support requests escalated to admin moderation.</p>
            </div>
            <a href="{{ route('admin.tickets') }}" class="inline-flex items-center justify-center h-8 px-3 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-xs font-semibold transition-all">
                View All <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-zinc-50/75 border-b border-zinc-200">
                    <tr>
                        <th class="px-6 py-3 w-12 text-zinc-500 font-semibold"><input type="checkbox" class="rounded border-zinc-350"></th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Ticket ID</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Buyer Name</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Buyer Email</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Subject</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Created</th>
                        <th class="px-6 py-3 text-zinc-500 font-semibold">Status</th>
                        <th class="px-6 py-3 text-right text-zinc-500 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-150">
                    @forelse($recentTickets as $ticket)
                    <tr class="hover:bg-zinc-50/50 transition-all">
                        <td class="px-6 py-3.5"><input type="checkbox" class="rounded border-zinc-300"></td>
                        <td class="px-6 py-3.5 font-bold text-zinc-900">#TKT-{{ $ticket->id }}</td>
                        <td class="px-6 py-3.5 text-zinc-700 font-semibold">{{ $ticket->buyer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 text-zinc-500">{{ $ticket->buyer->email ?? 'N/A' }}</td>
                        <td class="px-6 py-3.5 text-zinc-500 max-w-[200px] truncate" title="{{ $ticket->message }}">{{ $ticket->subject }}</td>
                        <td class="px-6 py-3.5 text-zinc-400">{{ $ticket->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors border
                                @if($ticket->status === 'open') bg-zinc-100 border-zinc-200 text-zinc-800
                                @elseif($ticket->status === 'in_progress') bg-blue-100 border-blue-200 text-blue-800
                                @elseif($ticket->status === 'resolved') bg-emerald-100 border-emerald-200 text-emerald-800
                                @else bg-zinc-100 border-zinc-200 text-zinc-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" class="inline-block">
                                @csrf
                                <input type="hidden" name="status" value="{{ $ticket->status === 'resolved' ? 'open' : 'resolved' }}">
                                <button type="submit" class="inline-flex items-center justify-center h-8 px-3 border border-zinc-250 hover:bg-zinc-100 hover:border-zinc-300 rounded-lg text-xs font-semibold transition-all">
                                    {{ $ticket->status === 'resolved' ? 'Reopen' : 'Resolve' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500 font-medium">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="w-10 h-10 bg-zinc-150 rounded-lg flex items-center justify-center">
                                    <i data-lucide="help-circle" class="w-5 h-5 text-zinc-550"></i>
                                </div>
                                <h4 class="text-sm font-semibold">No Support Tickets</h4>
                                <p class="text-xs text-zinc-450">All requests are fully settled.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ── 7. Bottom Row: Seller Applications & Top Sellers ────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Seller Applications --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 overflow-hidden flex flex-col justify-between min-h-[360px]">
            <div>
                <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold leading-none tracking-tight">Seller Applications</h3>
                    <a href="{{ route('admin.sellers') }}?kyc_status=pending" class="inline-flex items-center justify-center h-7 px-2 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-[10px] font-semibold transition-all">
                        All <i data-lucide="arrow-right" class="w-3 h-3 ml-0.5"></i>
                    </a>
                </div>
                
                <div class="divide-y divide-zinc-100">
                    <div class="p-5 py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">Retro Threads Co.</p>
                            <p class="text-[10px] text-zinc-400 truncate mt-0.5">retro.threads@gmail.com</p>
                        </div>
                        <form onsubmit="event.preventDefault(); alert('Approved (Mock Data)');" class="shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center h-8 px-3 border border-zinc-250 bg-zinc-950 hover:bg-zinc-800 text-white rounded-lg text-[10px] font-bold transition-all shadow-sm">
                                Approve
                            </button>
                        </form>
                    </div>
                    <div class="p-5 py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">Pixel Hardware & Parts</p>
                            <p class="text-[10px] text-zinc-400 truncate mt-0.5">contact@pixelhardware.io</p>
                        </div>
                        <form onsubmit="event.preventDefault(); alert('Approved (Mock Data)');" class="shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center h-8 px-3 border border-zinc-250 bg-zinc-950 hover:bg-zinc-800 text-white rounded-lg text-[10px] font-bold transition-all shadow-sm">
                                Approve
                            </button>
                        </form>
                    </div>
                    <div class="p-5 py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">Organic Green Labs</p>
                            <p class="text-[10px] text-zinc-400 truncate mt-0.5">support@organicgreen.co</p>
                        </div>
                        <form onsubmit="event.preventDefault(); alert('Approved (Mock Data)');" class="shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center h-8 px-3 border border-zinc-250 bg-zinc-950 hover:bg-zinc-800 text-white rounded-lg text-[10px] font-bold transition-all shadow-sm">
                                Approve
                            </button>
                        </form>
                    </div>
                    <div class="p-5 py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">Aero Drone Tech</p>
                            <p class="text-[10px] text-zinc-400 truncate mt-0.5">sales@aerodrone.net</p>
                        </div>
                        <form onsubmit="event.preventDefault(); alert('Approved (Mock Data)');" class="shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center h-8 px-3 border border-zinc-250 bg-zinc-950 hover:bg-zinc-800 text-white rounded-lg text-[10px] font-bold transition-all shadow-sm">
                                Approve
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Sellers --}}
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-950 overflow-hidden flex flex-col justify-between min-h-[360px]">
            <div>
                <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold leading-none tracking-tight">Top Sellers</h3>
                    <a href="{{ route('admin.sellers') }}" class="inline-flex items-center justify-center h-7 px-2 border border-zinc-200 hover:bg-zinc-50 rounded-lg text-[10px] font-semibold transition-all">
                        All <i data-lucide="arrow-right" class="w-3 h-3 ml-0.5"></i>
                    </a>
                </div>
                
                <div class="divide-y divide-zinc-100">
                    <div class="p-5 py-3.5 flex items-center gap-3">
                        <span class="text-xs font-bold text-zinc-400 w-5 shrink-0">#1</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">PC Hardware Hub</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5">24 orders</p>
                        </div>
                        <span class="text-xs font-bold text-zinc-900 shrink-0">₹54,650</span>
                    </div>
                    <div class="p-5 py-3.5 flex items-center gap-3">
                        <span class="text-xs font-bold text-zinc-400 w-5 shrink-0">#2</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">Gadget Galaxy</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5">18 orders</p>
                        </div>
                        <span class="text-xs font-bold text-zinc-900 shrink-0">₹32,180</span>
                    </div>
                    <div class="p-5 py-3.5 flex items-center gap-3">
                        <span class="text-xs font-bold text-zinc-400 w-5 shrink-0">#3</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">Auto Gear</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5">12 orders</p>
                        </div>
                        <span class="text-xs font-bold text-zinc-900 shrink-0">₹19,450</span>
                    </div>
                    <div class="p-5 py-3.5 flex items-center gap-3">
                        <span class="text-xs font-bold text-zinc-400 w-5 shrink-0">#4</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-zinc-800 truncate">Aero Drone Labs</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5">9 orders</p>
                        </div>
                        <span class="text-xs font-bold text-zinc-900 shrink-0">₹15,200</span>
                    </div>
                </div>
            </div>
        </div>
</div>

<script>
// 2. Customer Activity Line Chart
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
        init() {
            this.$nextTick(() => {
                this.initChart();
            });
        },
        initChart() {
            const ctx = document.getElementById('customerActivityChartCanvas').getContext('2d');
            const datasetsData = this.generateData();

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: datasetsData.labels,
                    datasets: datasetsData.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#09090b',
                            titleColor: '#a1a1aa',
                            bodyColor: '#ffffff',
                            titleFont: { size: 11, family: 'Inter' },
                            bodyFont: { size: 12, family: 'Inter', weight: '600' },
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, family: 'Inter' }, color: '#71717a' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f4f4f5', drawBorder: false },
                            ticks: { font: { size: 11, family: 'Inter' }, color: '#71717a' }
                        }
                    },
                    elements: {
                        point: {
                            radius: 0,
                            hoverRadius: 4,
                            backgroundColor: '#09090b'
                        },
                        line: {
                            tension: 0.35,
                            borderWidth: 2
                        }
                    }
                }
            });
        },
        updateChart() {
            if (!this.chartInstance) return;
            const datasetsData = this.generateData();
            this.chartInstance.data.labels = datasetsData.labels;
            this.chartInstance.data.datasets = datasetsData.datasets;
            this.chartInstance.update();
        },
        generateData() {
            let labels = [];
            let multiplier = 1.0;
            let shift = 0.8;
            if (this.timeframe === '3_months') {
                labels = ['Apr 9', 'Apr 16', 'Apr 22', 'Apr 29', 'May 6', 'May 12', 'May 19', 'May 26', 'Jun 2', 'Jun 8', 'Jun 15', 'Jun 22', 'Jun 29', 'Jul 6'];
                multiplier = 1.0;
                shift = 0.8;
            } else if (this.timeframe === '6_months') {
                labels = ['Jan 9', 'Jan 23', 'Feb 6', 'Feb 20', 'Mar 6', 'Mar 20', 'Apr 6', 'Apr 20', 'May 6', 'May 20', 'Jun 6', 'Jun 20', 'Jul 6'];
                multiplier = 1.8;
                shift = 1.3;
            } else {
                labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                multiplier = 3.5;
                shift = 2.1;
            }

            const length = labels.length;
            let activeData = [];
            let newData = [];
            let returningData = [];

            for (let i = 0; i < length; i++) {
                let factor = (i % 3 === 0) ? 2.5 : 1.2;
                if (i % 5 === 0) factor = 4.0;
                
                activeData.push(Math.round((40 + Math.sin(i * shift) * 15 + factor * 8) * multiplier));
                newData.push(Math.round((25 + Math.sin(i * (shift * 0.7)) * 8 + (i % 2) * 4) * multiplier));
                returningData.push(Math.round((15 + Math.cos(i * (shift * 0.8)) * 5 + (i % 3) * 2) * multiplier));
            }

            const canvas = document.getElementById('customerActivityChartCanvas');
            const ctx = canvas ? canvas.getContext('2d') : null;
            let activeBg = 'transparent';
            let newBg = 'transparent';
            let returningBg = 'transparent';

            if (ctx) {
                const activeGrad = ctx.createLinearGradient(0, 0, 0, 300);
                activeGrad.addColorStop(0, 'rgba(99, 102, 241, 0.18)');
                activeGrad.addColorStop(1, 'rgba(99, 102, 241, 0.00)');
                activeBg = activeGrad;

                const newGrad = ctx.createLinearGradient(0, 0, 0, 300);
                newGrad.addColorStop(0, 'rgba(16, 185, 129, 0.18)');
                newGrad.addColorStop(1, 'rgba(16, 185, 129, 0.00)');
                newBg = newGrad;

                const returningGrad = ctx.createLinearGradient(0, 0, 0, 300);
                returningGrad.addColorStop(0, 'rgba(244, 63, 94, 0.18)');
                returningGrad.addColorStop(1, 'rgba(244, 63, 94, 0.00)');
                returningBg = returningGrad;
            }

            let allDatasets = [
                {
                    label: 'Active Accounts',
                    data: activeData,
                    borderColor: '#6366f1',
                    backgroundColor: activeBg,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    id: 'active'
                },
                {
                    label: 'New Customers',
                    data: newData,
                    borderColor: '#10b981',
                    backgroundColor: newBg,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    id: 'new'
                },
                {
                    label: 'Returning Users',
                    data: returningData,
                    borderColor: '#f43f5e',
                    backgroundColor: returningBg,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    id: 'returning'
                }
            ];

            let filteredDatasets = allDatasets;
            if (this.segment !== 'all') {
                filteredDatasets = allDatasets.filter(ds => ds.id === this.segment);
            }

            return {
                labels: labels,
                datasets: filteredDatasets
            };
        }
    };
};

document.addEventListener("DOMContentLoaded", function () {


    // 3. Store Visits Doughnut Chart
    (function () {
        const ctx = document.getElementById('visitsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Direct', 'Social', 'Email', 'Referrals', 'Other'],
                datasets: [{
                    data: [40, 25, 15, 12, 8],
                    backgroundColor: ['#09090b', '#27272a', '#52525b', '#a1a1aa', '#e4e4e7'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                }
            }
        });
    })();

});
</script>
@endsection
