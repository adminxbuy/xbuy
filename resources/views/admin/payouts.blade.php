@extends('layouts.admin')

@section('title', 'Payouts History')
@section('page_title', 'Payouts History')

@section('header_actions')
 <a href="{{ route('admin.trash.index', 'payouts') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-muted hover:bg-muted text-foreground rounded-lg border border-border/50 text-xs font-medium transition-all ">
 <i data-lucide="trash-2" class="w-4.5 h-4.5 text-muted-foreground"></i>
 <span>Trash ({{ $trashedPayouts->count() }})</span>
 </a>
@endsection

@section('content')
 <div class="space-y-6">
 <!-- Top 3 Summary Cards -->
 <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
 <!-- Card 1: Released This Month -->
 <div class="rounded-xl p-5 flex flex-col gap-2 bg-card border border-border">
 <div class="p-2 bg-muted text-foreground rounded-xl w-fit/60"><i data-lucide="wallet"
 class="size-5"></i></div>
 <h3 class="text-2xl font-bold text-foreground">₹{{ number_format($releasedThisMonth, 2) }}</h3>
 <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Released This Month</p>
 </div>

 <!-- Card 2: Total Commission Earned -->
 <div class="rounded-xl p-5 flex flex-col gap-2 bg-card border border-border">
 <div class="p-2 rounded-xl w-fit bg-primary text-primary-foreground"><i data-lucide="percent"
 class="size-5"></i></div>
 <h3 class="text-2xl font-bold text-foreground">₹{{ number_format($totalCommission, 2) }}</h3>
 <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Total Commission Earned</p>
 </div>

 <!-- Card 3: Average Payout Amount -->
 <div class="rounded-xl p-5 flex flex-col gap-2 bg-card border border-border">
 <div class="p-2 bg-muted text-foreground rounded-xl w-fit/60"><i data-lucide="bar-chart-2" class="size-5"></i>
 </div>
 <h3 class="text-2xl font-bold text-foreground">₹{{ number_format($avgPayout, 2) }}</h3>
 <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Average Payout Amount</p>
 </div>
 </div>

 <!-- Filters & Export -->
 <div class="rounded-xl p-5 flex flex-col md:flex-row justify-between items-stretch md:items-end gap-4 bg-card border border-border">
 <form method="GET" action="{{ route('admin.payouts') }}"
 class="flex flex-wrap items-center justify-between gap-4 text-sm flex-1">
 <div class="flex flex-wrap items-center gap-4 flex-1">
 <div class="w-full sm:w-60 space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">Seller Name</label>
 <input type="text" name="seller" value="{{ request('seller') }}" placeholder="Search seller..."
 class="w-full p-2.5 rounded-lg border border-input bg-muted focus:bg-card focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none">
 </div>

 <div class="date-range-picker-container flex flex-wrap items-center gap-4" x-data="dateRangePicker({
 start: '{{ request('date_start') }}',
 end: '{{ request('date_end') }}',
 startName: 'date_start',
 endName: 'date_end'
 })">
 <input type="hidden" name="date_start" x-model="dateStart">
 <input type="hidden" name="date_end" x-model="dateEnd">

 <div class="space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">Released Date
 Range</label>
 <div class="relative">
 <select x-model="currentPreset" @change="applyPreset($event.target.value)"
 class="p-2.5 pl-3 pr-8 text-xs rounded-lg focus:bg-card focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none transition-all cursor-pointer font-semibold appearance-none border border-input bg-muted text-foreground">
 <option value="all">All Time</option>
 <option value="today">Today</option>
 <option value="yesterday">Yesterday</option>
 <option value="7days">Last 7 Days</option>
 <option value="30days">Last 30 Days</option>
 <option value="this_month">This Month</option>
 <option value="last_month">Last Month</option>
 <option value="custom">Custom Range</option>
 </select>
 <div
 class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
 <i data-lucide="chevron-down" class="size-3.5"></i>
 </div>
 </div>
 </div>

 <div class="flex items-end gap-2" x-show="currentPreset === 'custom'">
 <div class="space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">From</label>
 <div class="relative">
 <i data-lucide="calendar"
 class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
 <input type="text" x-ref="startInput" placeholder="Start Date" readonly
 class="pl-9 pr-4 py-2.5 text-xs rounded-lg focus:bg-card focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none cursor-pointer font-semibold w-32 border border-input bg-muted text-foreground">
 </div>
 </div>
 <span class="text-xs mb-3 text-muted-foreground">to</span>
 <div class="space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">To</label>
 <div class="relative">
 <i data-lucide="calendar"
 class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
 <input type="text" x-ref="endInput" placeholder="End Date" readonly
 class="pl-9 pr-4 py-2.5 text-xs rounded-lg focus:bg-card focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none cursor-pointer font-semibold w-32 border border-input bg-muted text-foreground">
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="flex items-center gap-2 pt-5">
 @if(request()->anyFilled(['seller', 'date_start', 'date_end']))
 <a href="{{ route('admin.payouts') }}"
 class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center transition-all border border-border text-muted-foreground">
 Clear Filters
 </a>
 @endif
 </div>
 </form>

 <div class="border-t md:border-t-0 md:border-l border-border pt-4 md:pt-0 md:pl-4 flex items-end">
 <a href="{{ route('admin.payouts.export', request()->query()) }}"
 class="w-full md:w-auto font-semibold py-2.5 px-5 rounded-lg ring-0 transition-all flex items-center justify-center space-x-2 text-xs bg-primary text-primary-foreground">
 <i data-lucide="download" class="size-4"></i>
 <span>Export CSV</span>
 </a>
 </div>
 </div>

 <!-- Payouts Table -->
 <div class="rounded-xl ring-0 overflow-hidden bg-card border border-border"
 x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
 <!-- Bulk Action Toolbar -->
 <div x-show="selectedIds.length > 0" x-transition.opacity x-cloak
 class="bg-muted border-b border-amber-100 p-3 flex items-center justify-between/60">
 <div class="flex items-center space-x-3">
 <span
 class="text-foreground font-bold text-xs uppercase tracking-wider bg-muted/50 px-3 py-1 rounded-lg"><span
 x-text="selectedIds.length"></span> Selected</span>
 <form method="POST" action="{{ route('admin.payouts.bulk-action') }}"
 class="flex items-center space-x-2" x-ref="bulkForm">
 @csrf
 <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
 <select name="action" x-model="bulkAction"
 class="text-xs border-border/50 rounded-lg focus:ring-amber-500 focus:border-amber-500 py-1.5 px-3 font-semibold bg-card text-foreground">
 <option value="">Bulk Actions...</option>
 <option value="status_released">Set Released</option>
 <option value="status_refunded">Set Refunded</option>
 <option value="delete">Move to Trash</option>
 </select>
 <button type="button"
 @click="if(bulkAction && confirm('Are you sure you want to apply this action to ' + selectedIds.length + ' payouts?')) $refs.bulkForm.submit()"
 class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors bg-primary text-primary-foreground"
 :disabled="!bulkAction">
 Apply
 </button>
 </form>
 </div>
 <button type="button" @click="selectedIds = []; selectAll = false"
 class="text-muted-foreground hover:text-foreground text-xs font-bold px-2 py-1 uppercase tracking-wider">Clear</button>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse text-sm">
 <thead>
 <tr
 class="border-b border-border text-[11px] font-bold uppercase tracking-wider bg-muted/50 text-muted-foreground">
 <th class="p-4 pl-6 w-12 text-center">
 <input type="checkbox" x-model="selectAll"
 @change="if(selectAll) { selectedIds = {{ $payouts->pluck('id')->toJson() }} } else { selectedIds = [] }"
 class="w-4 h-4 rounded border-border text-foreground focus:ring-black">
 </th>
 <th class="p-4">Payout ID</th>
 <th class="p-4">Order #</th>
 <th class="p-4">Seller Name</th>
 <th class="p-4 text-right">Gross Amount</th>
 <th class="p-4 text-center">Commission%</th>
 <th class="p-4 text-right">Commission Amt</th>
 <th class="p-4 text-right">Net Payout</th>
 <th class="p-4">Transfer ID</th>
 <th class="p-4">Released At</th>
 <th class="p-4 pr-6 text-center">Status</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-border">
 @forelse($payouts as $payout)
 @php
 $commPercent = $payout->amount_held > 0 ? round(($payout->commission_amount / $payout->amount_held) * 100, 1) : 0;
 @endphp
 <tr class="hover:bg-muted transition-colors text-muted-foreground">
 <td class="p-4 pl-6 text-center">
 <input type="checkbox" value="{{ $payout->id }}" x-model="selectedIds"
 class="w-4 h-4 rounded border-border text-foreground focus:ring-black">
 </td>
 <!-- Payout ID -->
 <td class="p-4 pl-6 font-mono text-xs font-bold">
 PAY-{{ $payout->id }}
 </td>
 <!-- Order # -->
 <td class="p-4 font-bold text-xs text-foreground">
 #{{ $payout->order->order_number ?? 'N/A' }}
 </td>
 <!-- Seller Name -->
 <td class="p-4 font-semibold text-xs text-foreground">
 {{ $payout->order->seller->shop_name ?? 'N/A' }}
 </td>
 <!-- Gross Amount -->
 <td class="p-4 text-right font-semibold text-foreground">
 ₹{{ number_format($payout->amount_held, 2) }}
 </td>
 <!-- Commission % -->
 <td class="p-4 text-center font-mono text-xs">
 {{ $commPercent }}%
 </td>
 <!-- Commission Amt -->
 <td class="p-4 text-right">
 ₹{{ number_format($payout->commission_amount, 2) }}
 </td>
 <!-- Net Payout -->
 <td class="p-4 text-right font-bold text-foreground">
 ₹{{ number_format($payout->seller_amount, 2) }}
 </td>
 <!-- Transfer ID -->
 <td class="p-4 font-mono text-xs">
 {{ $payout->razorpay_transfer_id ?? 'N/A' }}
 </td>
 <!-- Released At -->
 <td class="p-4 text-xs">
 {{ $payout->released_at ? $payout->released_at->format('d M Y, H:i') : '—' }}
 </td>
 <!-- Status -->
 <td class="p-4 pr-6 text-center">
 <span class="px-2 py-0.5 text-[9px] rounded-full font-bold uppercase tracking-wider border
 @if($payout->status === 'released') bg-muted text-foreground border-border/60
 @elseif($payout->status === 'partially_released') bg-muted text-foreground border-border/60
 @else bg-muted text-foreground border-border @endif">
 {{ $payout->status === 'partially_released' ? 'Partial' : $payout->status }}
 </span>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="11" class="p-12 text-center italic text-muted-foreground">
 No payouts history found.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($payouts->hasPages())
 <div class="p-4 border-t border-border">
 {{ $payouts->withQueryString()->links() }}
 </div>
 @endif
 </div>

 </div>
@endsection
