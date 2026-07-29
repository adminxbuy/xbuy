@extends('layouts.admin')

@section('title', 'Escrow Management')
@section('page_title', 'Escrow Management')

@section('header_actions')
 <a href="{{ route('admin.trash.index', 'escrows') }}" class="flex items-center space-x-1.5 px-4 py-2.5 rounded-lg border text-xs font-medium transition-all bg-muted hover:bg-muted text-foreground border-border/60 dark:hover:bg-rose-900">
 <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
 <span>Trash ({{ $trashedEscrows->count() }})</span>
 </a>
@endsection

@section('content')
<div class="space-y-6 w-full" x-data="escrowPage()">
 <!-- Top 4 Summary Cards -->
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
 <!-- Card 1: Total Held -->
 <div class="rounded-xl p-5 flex flex-col gap-2 border border-border bg-card">
 <div class="flex items-center justify-between">
 <div class="p-2 bg-muted text-foreground rounded-xl"><i data-lucide="lock" class="w-5 h-5"></i></div>
 <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border bg-muted text-foreground border-border/60">{{ $summary['count_held'] }} held</span>
 </div>
 <h3 class="text-2xl font-bold text-foreground">₹{{ number_format($summary['total_held'], 2) }}</h3>
 <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Total Held Escrow</p>
 </div>

 <!-- Card 2: Due Today -->
 <div class="rounded-xl p-5 flex flex-col gap-2 border border-border bg-card">
 <div class="p-2 rounded-xl w-fit bg-muted text-foreground/60"><i data-lucide="clock" class="w-5 h-5"></i></div>
 <h3 class="text-2xl font-bold text-foreground">₹{{ number_format($summary['due_today'], 2) }}</h3>
 <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Due Today</p>
 </div>

 <!-- Card 3: Overdue -->
 <div class="rounded-xl p-5 flex flex-col gap-2 border border-border bg-card">
 <div class="p-2 rounded-xl w-fit bg-muted text-foreground/60"><i data-lucide="alert-circle" class="w-5 h-5"></i></div>
 <h3 class="text-2xl font-bold text-foreground">₹{{ number_format($summary['overdue'], 2) }}</h3>
 <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Overdue Release</p>
 </div>

 <!-- Card 4: Disputed Amount -->
 <div class="rounded-xl p-5 flex flex-col gap-2 border border-border bg-card">
 <div class="p-2 rounded-xl w-fit bg-muted text-foreground/60"><i data-lucide="help-circle" class="w-5 h-5"></i></div>
 <h3 class="text-2xl font-bold text-foreground">₹{{ number_format($summary['total_disputed'], 2) }}</h3>
 <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Disputed Amount</p>
 </div>
 </div>

 <!-- Filters Panel -->
 <div class="rounded-xl p-5 border border-border bg-card">
 <form method="GET" action="{{ route('admin.escrow') }}" class="flex flex-wrap items-center justify-between gap-4 text-sm">
 <div class="flex flex-wrap items-center gap-4 flex-1">
 <div class="w-full sm:w-48 space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">Status</label>
 <select name="status" onchange="this.form.submit()" class="w-full p-2.5 rounded-lg border border-input bg-muted text-foreground focus:bg-card focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none font-semibold">
 <option value="">All Statuses</option>
 <option value="held" {{ request('status') === 'held' ? 'selected' : '' }}>Held</option>
 <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
 <option value="partially_released" {{ request('status') === 'partially_released' ? 'selected' : '' }}>Partially Released</option>
 <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
 <option value="disputed" {{ request('status') === 'disputed' ? 'selected' : '' }}>Disputed</option>
 </select>
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
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">Escrow Date Range</label>
 <div class="relative">
 <select x-model="currentPreset" @change="applyPreset($event.target.value)"
 class="p-2.5 pl-3 pr-8 text-xs rounded-lg border border-input bg-muted text-foreground focus:bg-card focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none transition-all cursor-pointer font-semibold appearance-none">
 <option value="all">All Time</option>
 <option value="today">Today</option>
 <option value="yesterday">Yesterday</option>
 <option value="7days">Last 7 Days</option>
 <option value="30days">Last 30 Days</option>
 <option value="this_month">This Month</option>
 <option value="last_month">Last Month</option>
 <option value="custom">Custom Range</option>
 </select>
 <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
 <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
 </div>
 </div>
 </div>

 <div class="flex items-end gap-2" x-show="currentPreset === 'custom'">
 <div class="space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">From</label>
 <div class="relative">
 <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
 <input type="text" x-ref="startInput" placeholder="Start Date" readonly
 class="pl-9 pr-4 py-2.5 text-xs rounded-lg border border-input bg-muted text-foreground focus:bg-card focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none cursor-pointer font-semibold w-32">
 </div>
 </div>
 <span class="text-xs mb-3 text-muted-foreground">to</span>
 <div class="space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">To</label>
 <div class="relative">
 <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
 <input type="text" x-ref="endInput" placeholder="End Date" readonly
 class="pl-9 pr-4 py-2.5 text-xs rounded-lg border border-input bg-muted text-foreground focus:bg-card focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none cursor-pointer font-semibold w-32">
 </div>
 </div>
 </div>
 </div>

 <div class="w-full sm:w-60 space-y-1">
 <label class="text-xs font-bold uppercase tracking-wider block text-muted-foreground">Seller Shop Name</label>
 <input type="text" name="seller" value="{{ request('seller') }}" placeholder="Search seller..."
 class="w-full p-2.5 rounded-lg border border-input bg-muted text-foreground focus:bg-card focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none font-semibold">
 </div>
 </div>

 <div class="flex items-center gap-2 pt-5">
 @if(request()->anyFilled(['status', 'seller', 'date_start', 'date_end']))
 <a href="{{ route('admin.escrow') }}" class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center transition-all border border-border text-muted-foreground">
 Clear Filters
 </a>
 @endif
 </div>
 </form>
 </div>

 <!-- Escrow List Table -->
 <div class="rounded-xl ring-0 overflow-hidden border border-border bg-card">
 <!-- Bulk Action Toolbar -->
 <div x-show="selectedIds.length > 0" x-transition.opacity style="display: none;" class="p-3 flex items-center justify-between border-b bg-muted border-border/60">
 <div class="flex items-center space-x-3">
 <span class="font-bold text-xs uppercase tracking-wider px-3 py-1 rounded-lg bg-muted text-foreground"><span x-text="selectedIds.length"></span> Selected</span>
 <form method="POST" action="{{ route('admin.escrow.bulk-action') }}" class="flex items-center space-x-2" x-ref="bulkForm">
 @csrf
 <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
 <select name="action" x-model="bulkAction" class="text-xs rounded-lg py-1.5 px-3 font-semibold border border-input bg-card text-foreground focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none">
 <option value="">Bulk Actions...</option>
 <option value="status_held">Set Held</option>
 <option value="status_released">Set Released</option>
 <option value="status_refunded">Set Refunded</option>
 <option value="delete">Move to Trash</option>
 </select>
 <button type="button" @click="if(bulkAction && confirm('Are you sure you want to apply this action to ' + selectedIds.length + ' escrow records?')) $refs.bulkForm.submit()" class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors bg-primary text-primary-foreground" :disabled="!bulkAction">
 Apply
 </button>
 </form>
 </div>
 <button type="button" @click="selectedIds = []; selectAll = false" class="text-xs font-bold px-2 py-1 uppercase tracking-wider text-foreground hover:text-amber-900 dark:hover:text-amber-100">Clear</button>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse text-sm">
 <thead class="bg-muted/50">
 <tr class="border-b text-[11px] font-bold uppercase tracking-wider border-border text-muted-foreground">
 <th class="p-4 pl-6 w-12 text-center">
 <input type="checkbox" x-model="selectAll" @change="if(selectAll) { selectedIds = {{ $escrows->pluck('id')->toJson() }} } else { selectedIds = [] }" class="w-4 h-4 rounded border-input text-foreground focus:ring-ring">
 </th>
 <th class="p-4">Order #</th>
 <th class="p-4">Seller & Buyer</th>
 <th class="p-4 text-right">Amount Held</th>
 <th class="p-4 text-right">Commission</th>
 <th class="p-4 text-right">Seller Payout</th>
 <th class="p-4">Held Since</th>
 <th class="p-4">Release Scheduled</th>
 <th class="p-4 text-center">Status</th>
 <th class="p-4 pr-6 text-right">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-border">
 @forelse($escrows as $escrow)
 @php
 $isOverdue = $escrow->status === 'held' && $escrow->release_scheduled_at && $escrow->release_scheduled_at->isPast();
 @endphp
 <tr class="transition-colors hover:bg-muted {{ $isOverdue ? 'bg-muted/40 hover:bg-muted/60/30 dark:hover:bg-amber-950/50' : '' }}">
 <td class="p-4 pl-6 text-center">
 <input type="checkbox" value="{{ $escrow->id }}" x-model="selectedIds" class="w-4 h-4 rounded border-input text-foreground focus:ring-ring">
 </td>
 <!-- Order # -->
 <td class="p-4 pl-6 font-semibold text-xs select-none text-foreground">
 <div class="flex items-center space-x-2">
 <div class="flex items-center space-x-1 cursor-pointer" @click="toggleExpand({{ $escrow->id }})">
 <i data-lucide="chevron-right" class="w-3.5 h-3.5 transition-transform duration-200 text-muted-foreground" :class="isExpanded({{ $escrow->id }}) ? 'rotate-90 font-bold' : ''"></i>
 <span class="whitespace-nowrap hover:underline">#{{ $escrow->order->order_number ?? 'N/A' }}</span>
 </div>
 @if($escrow->order_id)
 <a href="{{ route('admin.orders.show', $escrow->order_id) }}" target="_blank" @click.stop
 class="inline-flex items-center justify-center p-1 rounded-lg transition-all shrink-0 border border-border bg-muted text-muted-foreground hover:bg-muted"
 title="View Full Order Details">
 <i data-lucide="eye" class="w-3.5 h-3.5"></i>
 </a>
 @endif
 </div>
 </td>
 <!-- Seller & Buyer -->
 <td class="p-4 text-xs">
 <span class="font-semibold text-foreground">S: {{ $escrow->order->seller->shop_name ?? 'N/A' }}</span>
 <p class="mt-0.5 text-muted-foreground">B: {{ $escrow->order->buyer->name ?? 'N/A' }}</p>
 </td>
 <!-- Amount Held -->
 <td class="p-4 text-right font-semibold text-foreground">
 ₹{{ number_format($escrow->amount_held, 2) }}
 </td>
 <!-- Commission -->
 <td class="p-4 text-right text-muted-foreground">
 ₹{{ number_format($escrow->commission_amount, 2) }}
 </td>
 <!-- Seller Payout -->
 <td class="p-4 text-right font-semibold text-muted-foreground">
 ₹{{ number_format($escrow->seller_amount, 2) }}
 </td>
 <!-- Held Since -->
 <td class="p-4 text-xs text-muted-foreground">
 {{ $escrow->created_at->format('d M Y') }}
 </td>
 <!-- Release Scheduled -->
 <td class="p-4 text-xs font-semibold {{ $isOverdue ? 'text-foreground' : 'text-muted-foreground' }}">
 {{ $escrow->release_scheduled_at ? $escrow->release_scheduled_at->format('d M Y') : '—' }}
 </td>
 <!-- Status -->
 <td class="p-4 text-center">
 <span class="px-2 py-0.5 text-[10px] rounded-full font-bold uppercase tracking-wider border
 @if($escrow->status === 'held') bg-muted text-foreground border-border/60
 @elseif($escrow->status === 'released') bg-muted text-foreground border-border/60
 @elseif($escrow->status === 'partially_released') bg-muted text-foreground border-border/60
 @elseif($escrow->status === 'refunded') bg-muted text-foreground border-border/60
 @elseif($escrow->status === 'disputed') bg-muted text-foreground border-border/60
 @else bg-muted text-muted-foreground border-border @endif">
 {{ $escrow->status === 'partially_released' ? 'Partial' : $escrow->status }}
 </span>
 </td>
 <!-- Actions -->
 <td class="p-4 pr-6 text-right space-x-1 whitespace-nowrap">
 @if($escrow->status === 'held' || $escrow->status === 'disputed')
 <form action="{{ route('admin.escrow.release', $escrow->id) }}" method="POST" class="inline" onsubmit="return confirm('Release this escrow fully to the seller?');">
 @csrf
 <button type="submit" class="text-[10px] font-medium px-2 py-1.5 rounded-lg transition-all border bg-muted text-foreground border-border hover:bg-muted/60 dark:hover:bg-emerald-900" title="Release to Seller">
 Release
 </button>
 </form>

 <form action="{{ route('admin.escrow.refund', $escrow->id) }}" method="POST" class="inline" onsubmit="return confirm('Refund this escrow fully to the buyer?');">
 @csrf
 <button type="submit" class="text-[10px] font-medium px-2 py-1.5 rounded-lg transition-all border bg-muted text-foreground border-border hover:bg-muted/60 dark:hover:bg-rose-900" title="Refund to Buyer">
 Refund
 </button>
 </form>

 <button @click="openPartialModal({{ json_encode($escrow) }})" class="text-[10px] font-bold px-2 py-1.5 rounded-lg transition-all border bg-muted text-foreground border-border hover:bg-muted/60 dark:hover:bg-purple-900" title="Partial Escrow Release">
 Partial
 </button>
 @else
 <span class="text-xs font-medium text-muted-foreground">No actions</span>
 @endif
 </td>
 </tr>

 <!-- Expandable Details Row -->
 <tr x-show="isExpanded({{ $escrow->id }})" x-cloak class="bg-muted">
 <td colspan="9" class="p-5 pl-8 pr-6 border-b border-border">
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs p-5 animate-fade-in rounded-xl text-muted-foreground bg-card border border-border">
 <!-- Col 1: Product & Order Details -->
 <div class="space-y-3">
 <h5 class="font-bold text-xs uppercase tracking-wider flex items-center gap-1.5 text-foreground">
 <i data-lucide="package" class="w-4 h-4 text-muted-foreground"></i> Product & Order Details
 </h5>
 <div class="space-y-2">
 <p><span class="font-semibold text-muted-foreground">Item:</span> <span class="font-bold text-foreground">{{ $escrow->order->listing->title ?? 'N/A' }}</span></p>
 <p><span class="font-semibold text-muted-foreground">Category:</span> <span class="font-medium uppercase text-foreground">{{ str_replace('_', ' ', $escrow->order->listing->category ?? 'N/A') }}</span></p>
 <p><span class="font-semibold text-muted-foreground">Order Status:</span> <span class="font-medium uppercase text-foreground">{{ $escrow->order->order_status }}</span></p>
 <p><span class="font-semibold text-muted-foreground">Product Amount:</span> <span class="font-bold text-foreground">₹{{ number_format($escrow->order->product_amount, 2) }}</span></p>
 <p><span class="font-semibold text-muted-foreground">Shipping Amount:</span> <span class="font-bold text-foreground">₹{{ number_format($escrow->order->shipping_amount, 2) }}</span></p>
 </div>
 </div>

 <!-- Col 2: Parties & Contact -->
 <div class="space-y-3">
 <h5 class="font-bold text-xs uppercase tracking-wider flex items-center gap-1.5 text-foreground">
 <i data-lucide="users" class="w-4 h-4 text-muted-foreground"></i> Parties Contact Info
 </h5>
 <div class="space-y-2.5">
 <div class="p-2.5 rounded-xl border bg-muted border-border/60">
 <p class="font-bold mb-0.5 text-foreground">Buyer: {{ $escrow->order->buyer->name ?? 'N/A' }}</p>
 <p class="text-[10px] text-muted-foreground">Email: {{ $escrow->order->buyer->email ?? 'N/A' }}</p>
 <p class="text-[10px] text-muted-foreground">Phone: {{ $escrow->order->buyer->phone ?? 'N/A' }}</p>
 </div>
 <div class="p-2.5 rounded-xl border bg-muted border-border/60">
 <p class="font-bold mb-0.5 text-foreground">Seller Shop: {{ $escrow->order->seller->shop_name ?? 'N/A' }}</p>
 <p class="text-[10px] text-muted-foreground">Email: {{ $escrow->order->seller->user->email ?? 'N/A' }}</p>
 <p class="text-[10px] text-muted-foreground">Phone: {{ $escrow->order->seller->user->phone ?? 'N/A' }}</p>
 </div>
 </div>
 </div>

 <!-- Col 3: Transaction & Warranty IDs -->
 <div class="space-y-3">
 <h5 class="font-bold text-xs uppercase tracking-wider flex items-center gap-1.5 text-foreground">
 <i data-lucide="key" class="w-4 h-4 text-muted-foreground"></i> Escrow & Payment Details
 </h5>
 <div class="space-y-2">
 <p><span class="font-semibold text-muted-foreground">Razorpay Order:</span> <span class="font-mono font-medium text-foreground">{{ $escrow->order->razorpay_order_id ?? 'N/A' }}</span></p>
 <p><span class="font-semibold text-muted-foreground">Payment ID:</span> <span class="font-mono font-medium text-foreground">{{ $escrow->order->razorpay_payment_id ?? 'N/A' }}</span></p>
 <p><span class="font-semibold text-muted-foreground">Transfer ID:</span> <span class="font-mono font-medium text-foreground">{{ $escrow->razorpay_transfer_id ?? 'N/A' }}</span></p>
 <p><span class="font-semibold text-muted-foreground">Testing Period:</span> <span class="font-bold text-foreground">{{ $escrow->warranty_days }} Days</span></p>
 @if($escrow->release_scheduled_at)
 <p><span class="font-semibold text-muted-foreground">Release Deadline:</span> <span class="font-bold text-foreground">{{ $escrow->release_scheduled_at->format('d M Y H:i') }}</span></p>
 @endif
 <p><span class="font-semibold text-muted-foreground">Payout Status:</span>
 <span class="font-bold @if($escrow->payout_status === 'success') text-muted-foreground @elseif($escrow->payout_status === 'failed') text-rose-600 @else text-muted-foreground @endif">{{ ucfirst($escrow->payout_status ?? 'pending') }}</span>
 </p>
 @if($escrow->payout_status === 'failed')
 <p class="leading-tight mt-0.5 text-rose-600"><span class="font-bold">Error:</span> <span>{{ $escrow->payout_error_message }}</span></p>
 <form action="{{ route('admin.escrow.retry-payout', $escrow->id) }}" method="POST" class="mt-1">
 @csrf
 <button type="submit" class="text-[9px] font-bold px-2 py-1 rounded-md transition-all border bg-muted text-foreground border-border hover:bg-muted/60 dark:hover:bg-amber-900">
 Retry Route Transfer
 </button>
 </form>
 @endif

 <!-- Seller linked account customizer form -->
 <form action="{{ route('admin.sellers.update-razorpay-account', $escrow->order->seller->id) }}" method="POST" class="mt-3 pt-2.5 space-y-1 border-t border-border">
 @csrf
 <label class="block text-[9px] font-bold uppercase tracking-wide text-muted-foreground">Seller Linked Account ID</label>
 <div class="flex gap-1.5">
 <input type="text" name="razorpay_account_id" value="{{ $escrow->order->seller->razorpay_account_id }}" placeholder="e.g. acc_123456" class="px-2 py-1 rounded-lg text-[10px] w-28 border border-input bg-muted text-foreground focus:outline-none focus:border-ring focus:ring-2 focus:ring-ring/50">
 <button type="submit" class="px-2 rounded-lg text-[9px] uppercase transition-all bg-primary text-primary-foreground">Save</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="10" class="p-12 text-center italic text-muted-foreground">
 No escrow records found.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <div class="p-4 border-t border-border">
 {{ $escrows->links() }}
 </div>
 </div>

 <!-- Partial Release Modal -->
 <div x-show="partialModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto outline-none" x-cloak>
 <!-- Backdrop -->
 <div class="fixed inset-0 bg-background/65 backdrop-blur-md transition-opacity" @click="closePartialModal()"></div>

 <!-- Modal Card -->
 <div class="relative w-full max-w-md mx-auto rounded-[24px] shadow-2xl z-10 overflow-hidden bg-card border border-border"
 x-show="partialModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

 <div class="px-6 py-5 border-b flex items-center justify-between border-border bg-muted">
 <h4 class="font-bold text-sm text-foreground">Partial Escrow Release</h4>
 <button @click="closePartialModal()" class="hover:text-foreground focus:outline-none text-muted-foreground">
 <i data-lucide="x" class="w-4 h-4"></i>
 </button>
 </div>

 <form :action="'/admin/escrow/' + selectedEscrow.id + '/partial-release'" method="POST" class="p-6 space-y-4 text-sm">
 @csrf
 <div class="p-3 rounded-xl space-y-1 bg-muted border border-border">
 <div class="flex justify-between text-xs font-medium text-muted-foreground">
 <span>Total Escrow Amount:</span>
 <span class="font-bold text-foreground" x-text="'₹' + selectedEscrow.amount_held"></span>
 </div>
 </div>

 <div class="space-y-1.5">
 <label for="seller_amount" class="block text-xs font-bold uppercase tracking-wider text-muted-foreground">Amount to release to Seller</label>
 <div class="relative">
 <span class="absolute left-3 top-1/2 -translate-y-1/2 font-semibold text-xs text-muted-foreground">₹</span>
 <input type="number" step="0.01" min="0" :max="selectedEscrow.amount_held" id="seller_amount" name="seller_amount" required x-model="sellerReleaseAmount"
 class="w-full pl-7 pr-3 p-3 rounded-lg border border-input bg-muted text-foreground focus:bg-card focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none transition-all">
 </div>
 <p class="text-[10px] leading-relaxed">The remaining amount (<span class="font-semibold text-muted-foreground" x-text="'₹' + (selectedEscrow.amount_held - sellerReleaseAmount)"></span>) will be refunded to the buyer.</p>
 </div>

 <div class="pt-4 border-t flex justify-end space-x-2 border-border">
 <button type="button" @click="closePartialModal()" class="px-4 py-2 font-semibold rounded-lg text-xs transition-all border border-border text-muted-foreground">
 Cancel
 </button>
 <button type="submit" class="px-5 py-2 font-semibold rounded-lg text-xs transition-all bg-primary text-primary-foreground">
 Release Partially
 </button>
 </div>
 </form>
 </div>
 </div>
</div>


<script>
function escrowPage() {
 return {
 partialModalOpen: false,
 selectedEscrow: { id: '', amount_held: 0 },
 sellerReleaseAmount: 0,
 expandedEscrows: [],
 selectedIds: [],
 selectAll: false,
 bulkAction: '',

 toggleExpand(id) {
 if (this.expandedEscrows.includes(id)) {
 this.expandedEscrows = this.expandedEscrows.filter(i => i !== id);
 } else {
 this.expandedEscrows.push(id);
 }
 setTimeout(() => lucide.createIcons(), 50);
 },

 isExpanded(id) {
 return this.expandedEscrows.includes(id);
 },

 openPartialModal(escrow) {
 this.selectedEscrow = escrow;
 this.sellerReleaseAmount = escrow.seller_amount || escrow.amount_held;
 this.partialModalOpen = true;
 setTimeout(() => lucide.createIcons(), 50);
 },

 closePartialModal() {
 this.partialModalOpen = false;
 }
 };
}
</script>
@endsection
