@extends('layouts.admin')

@section('title', 'Analytics')
@section('page_title', 'Analytics Overview')

@section('content')

{{-- Date Range Picker and Filter Controls --}}
<div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm mb-8">
    <form action="{{ route('admin.analytics') }}" method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <input type="hidden" name="days" value="custom">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-zinc-50 text-zinc-650 rounded-xl">
                <i data-lucide="calendar-range" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-zinc-950 text-sm">Select Analytics Period</h4>
                <p class="text-xs text-zinc-500">Current range: {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</p>
            </div>
        </div>

            <div class="date-range-picker-container flex flex-wrap items-center gap-4" x-data="dateRangePicker({
                start: '{{ request('start_date', $start->format('Y-m-d')) }}',
                end: '{{ request('end_date', $end->format('Y-m-d')) }}',
                startName: 'start_date',
                endName: 'end_date'
            })">
                <input type="hidden" name="start_date" x-model="dateStart">
                <input type="hidden" name="end_date" x-model="dateEnd">

                <div class="space-y-1">
                    <div class="relative">
                        <select x-model="currentPreset" @change="applyPreset($event.target.value)"
                                class="p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700 appearance-none">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="7days">Last 7 Days</option>
                            <option value="30days">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-2" x-show="currentPreset === 'custom'">
                    <div class="space-y-1">
                        <div class="relative">
                            <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400"></i>
                            <input type="text" x-ref="startInput" placeholder="Start Date" readonly
                                   class="pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none cursor-pointer font-semibold text-zinc-750 w-32">
                        </div>
                    </div>
                    <span class="text-zinc-400 text-xs mb-3">to</span>
                    <div class="space-y-1">
                        <div class="relative">
                            <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400"></i>
                            <input type="text" x-ref="endInput" placeholder="End Date" readonly
                                   class="pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none cursor-pointer font-semibold text-zinc-750 w-32">
                        </div>
                    </div>
                </div>
            </div>
    </form>
</div>

{{-- Row 1: KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    {{-- Card 1: GMV --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex flex-col gap-2 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl w-fit"><i data-lucide="indian-rupee" class="w-5 h-5"></i></div>
            <span class="text-xs font-bold flex items-center gap-1 {{ $gmvChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                <i data-lucide="{{ $gmvChange >= 0 ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                {{ $gmvChange >= 0 ? '+' : '' }}{{ $gmvChange }}%
            </span>
        </div>
        <h3 class="text-2xl font-bold text-zinc-900 mt-2">₹{{ number_format($currentGmv, 0) }}</h3>
        <p class="text-[11px] text-zinc-400 font-semibold uppercase tracking-wide">GMV</p>
    </div>

    {{-- Card 2: Orders --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex flex-col gap-2 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl w-fit"><i data-lucide="shopping-bag" class="w-5 h-5"></i></div>
            <span class="text-xs font-bold flex items-center gap-1 {{ $ordersChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                <i data-lucide="{{ $ordersChange >= 0 ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                {{ $ordersChange >= 0 ? '+' : '' }}{{ $ordersChange }}%
            </span>
        </div>
        <h3 class="text-2xl font-bold text-zinc-900 mt-2">{{ number_format($currentOrders) }}</h3>
        <p class="text-[11px] text-zinc-400 font-semibold uppercase tracking-wide">Orders</p>
    </div>

    {{-- Card 3: Commission --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex flex-col gap-2 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <div class="p-2.5 bg-violet-50 text-violet-600 rounded-xl w-fit"><i data-lucide="percent" class="w-5 h-5"></i></div>
            <span class="text-xs font-bold flex items-center gap-1 {{ $commissionChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                <i data-lucide="{{ $commissionChange >= 0 ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                {{ $commissionChange >= 0 ? '+' : '' }}{{ $commissionChange }}%
            </span>
        </div>
        <h3 class="text-2xl font-bold text-zinc-900 mt-2">₹{{ number_format($currentCommission, 0) }}</h3>
        <p class="text-[11px] text-zinc-400 font-semibold uppercase tracking-wide">Commission</p>
    </div>

    {{-- Card 4: Avg Order Value --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex flex-col gap-2 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <div class="p-2.5 bg-zinc-50 text-zinc-650 rounded-xl w-fit"><i data-lucide="bar-chart-2" class="w-5 h-5"></i></div>
            <span class="text-xs font-bold flex items-center gap-1 {{ $aovChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                <i data-lucide="{{ $aovChange >= 0 ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                {{ $aovChange >= 0 ? '+' : '' }}{{ $aovChange }}%
            </span>
        </div>
        <h3 class="text-2xl font-bold text-zinc-900 mt-2">₹{{ number_format($currentAov, 0) }}</h3>
        <p class="text-[11px] text-zinc-400 font-semibold uppercase tracking-wide">Avg Order Value</p>
    </div>
</div>

{{-- Row 2: Full Width GMV Line Chart --}}
<div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-bold text-zinc-800 text-sm">GMV Trend</h3>
            <p class="text-xs text-zinc-400">Gross Merchandise Value trend over the selected period</p>
        </div>
    </div>
    <div class="h-80">
        <canvas id="gmvTrendChart"></canvas>
    </div>
</div>

{{-- Row 3: Category Breakdown & Order Status Side-by-Side --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Category Breakdown Bar Chart --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
        <div>
            <h3 class="font-bold text-zinc-800 text-sm">Category Breakdown</h3>
            <p class="text-xs text-zinc-400 mb-4">GMV generated per listing category</p>
        </div>
        <div class="h-64">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    {{-- Order Status Pie Chart --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
        <div>
            <h3 class="font-bold text-zinc-800 text-sm">Order Status Breakdown</h3>
            <p class="text-xs text-zinc-400 mb-4">Distribution of statuses for orders in this period</p>
        </div>
        <div class="h-64 flex justify-center items-center">
            <div class="w-full max-w-[240px]">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Row 4: Top 10 Tables Side-by-Side --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Top 10 Sellers by GMV --}}
    <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between bg-zinc-50/50">
            <div>
                <h3 class="font-bold text-zinc-800 text-sm">Top 10 Sellers by GMV</h3>
                <p class="text-[11px] text-zinc-400">Ranked by total sales volume in this period</p>
            </div>
            <i data-lucide="award" class="w-5 h-5 text-[#09090b]"></i>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-[10px] uppercase text-zinc-400 tracking-wider font-bold border-b border-zinc-250">
                    <tr>
                        <th class="px-5 py-3 text-left">Rank</th>
                        <th class="px-5 py-3 text-left">Shop Name</th>
                        <th class="px-5 py-3 text-right">Orders</th>
                        <th class="px-5 py-3 text-right">Total GMV</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($topSellersGmv as $index => $sellerGmv)
                    <tr class="hover:bg-zinc-50/50">
                        <td class="px-5 py-3.5 text-xs font-bold text-zinc-400">#{{ $index + 1 }}</td>
                        <td class="px-5 py-3.5 text-xs font-semibold text-zinc-800">
                            {{ $sellerGmv->seller->shop_name ?? 'Unknown Shop' }}
                        </td>
                        <td class="px-5 py-3.5 text-right text-xs text-zinc-500">{{ $sellerGmv->order_count }}</td>
                        <td class="px-5 py-3.5 text-right text-xs font-bold text-zinc-900">₹{{ number_format($sellerGmv->gmv, 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-zinc-400 text-xs">No seller data available for this range</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top 10 Sellers by Rating --}}
    <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between bg-zinc-50/50">
            <div>
                <h3 class="font-bold text-zinc-800 text-sm">Top 10 Sellers by Rating</h3>
                <p class="text-[11px] text-zinc-400">Overall ratings from verified transactions</p>
            </div>
            <i data-lucide="star" class="w-5 h-5 text-yellow-400 fill-current"></i>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-[10px] uppercase text-zinc-400 tracking-wider font-bold border-b border-zinc-250">
                    <tr>
                        <th class="px-5 py-3 text-left">Rank</th>
                        <th class="px-5 py-3 text-left">Shop Name</th>
                        <th class="px-5 py-3 text-right">Reviews</th>
                        <th class="px-5 py-3 text-right">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($topSellersRating as $index => $sellerRating)
                    <tr class="hover:bg-zinc-50/50">
                        <td class="px-5 py-3.5 text-xs font-bold text-zinc-400">#{{ $index + 1 }}</td>
                        <td class="px-5 py-3.5 text-xs font-semibold text-zinc-800">
                            {{ $sellerRating->shop_name }}
                        </td>
                        <td class="px-5 py-3.5 text-right text-xs text-zinc-500">{{ $sellerRating->ratings_count }}</td>
                        <td class="px-5 py-3.5 text-right text-xs">
                            @if($sellerRating->ratings_avg_weighted_total)
                            <div class="flex items-center justify-end gap-1.5 font-bold text-zinc-900">
                                <i data-lucide="star" class="w-3.5 h-3.5 text-yellow-400 fill-yellow-400"></i>
                                <span>{{ number_format($sellerRating->ratings_avg_weighted_total, 1) }}</span>
                            </div>
                            @else
                            <span class="text-zinc-450 text-[11px] font-semibold">No ratings</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-zinc-400 text-xs">No rated sellers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Row 5: Dispute Rate Trend Line Chart --}}
<div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-bold text-zinc-800 text-sm">Dispute Rate Trend</h3>
            <p class="text-xs text-zinc-400">Daily dispute percentage with a standard 5% limit threshold</p>
        </div>
    </div>
    <div class="h-80">
        <canvas id="disputeTrendChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- 1. GMV Line Chart ---
    const gmvCtx = document.getElementById('gmvTrendChart').getContext('2d');
    const gmvGrad = gmvCtx.createLinearGradient(0, 0, 0, 250);
    gmvGrad.addColorStop(0, 'rgba(253, 216, 53, 0.35)');
    gmvGrad.addColorStop(1, 'rgba(253, 216, 53, 0)');

    new Chart(gmvCtx, {
        type: 'line',
        data: {
            labels: @json($gmvLabels),
            datasets: [{
                label: 'GMV (₹)',
                data: @json($gmvData),
                borderColor: '#09090b',
                borderWidth: 2.5,
                backgroundColor: gmvGrad,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '#09090b',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '₹' + Number(v).toLocaleString('en-IN') }
                }
            },
            plugins: { legend: { display: false } }
        }
    });

    // --- 2. Category Bar Chart ---
    const catData = @json($categoryBreakdown);
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: catData.map(d => d.label),
            datasets: [{
                label: 'GMV (₹)',
                data: catData.map(d => d.value),
                backgroundColor: '#18181b',
                borderRadius: 8,
                maxBarThickness: 32,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '₹' + Number(v).toLocaleString('en-IN') }
                }
            },
            plugins: { legend: { display: false } }
        }
    });

    // --- 3. Order Status Pie Chart ---
    const statusData = @json($statusBreakdown);
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(d => d.label),
            datasets: [{
                data: statusData.map(d => d.value),
                backgroundColor: [
                    '#09090b', // Gold
                    '#18181b', // Dark Zinc
                    '#a1a1aa', // Zinc
                    '#3b82f6', // Blue
                    '#ef4444', // Red
                    '#10b981', // Emerald
                    '#8b5cf6', // Violet
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 10 } }
                }
            }
        }
    });

    // --- 4. Dispute Rate Trend Line Chart with 5% threshold line ---
    const disputeCtx = document.getElementById('disputeTrendChart').getContext('2d');
    const dispLabels = @json($disputeLabels);
    const dispData = @json($disputeData);

    new Chart(disputeCtx, {
        type: 'line',
        data: {
            labels: dispLabels,
            datasets: [{
                label: 'Dispute Rate (%)',
                data: dispData,
                borderColor: '#ef4444',
                borderWidth: 2,
                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#ef4444',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v + '%' },
                    max: Math.max(10, Math.max(...dispData) + 2)
                }
            },
            plugins: {
                legend: { display: false }
            },
            plugins: [{
                id: 'horizontalLine',
                afterDraw: function(chart) {
                    if (chart.scales.y) {
                        const ctx = chart.ctx;
                        const yValue = chart.scales.y.getPixelForValue(5);
                        ctx.save();
                        ctx.beginPath();
                        ctx.moveTo(chart.scales.x.left, yValue);
                        ctx.lineTo(chart.scales.x.right, yValue);
                        ctx.strokeStyle = '#f43f5e';
                        ctx.lineWidth = 2;
                        ctx.setLineDash([6, 6]);
                        ctx.stroke();

                        // Label
                        ctx.fillStyle = '#ef4444';
                        ctx.font = 'bold 11px Outfit, sans-serif';
                        ctx.fillText('5% Limit Threshold', chart.scales.x.left + 8, yValue - 8);
                        ctx.restore();
                    }
                }
            }]
        }
    });
});
</script>

@endsection
