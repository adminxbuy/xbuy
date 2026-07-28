@extends('layouts.admin')

@section('title', 'Orders Registry')
@section('page_title', 'All Orders')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'orders') }}" class="flex items-center space-x-1.5 px-4 py-2 rounded-lg text-xs font-bold transition-all" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
        <span>Trash ({{ $trashedOrders->count() }})</span>
    </a>
@endsection

@section('content')
<div x-data="{ drawerOpen: false, order: null, open(data) { this.order = data; this.drawerOpen = true; }, close() { this.drawerOpen = false; } }" @keydown.escape.window="close()">

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-2 flex-wrap mb-5 overflow-x-auto pb-1">
        @php
            $tabs = [
                '' => 'All', 'payment_received' => 'Payment Received', 'confirmed' => 'Confirmed',
                'in_transit' => 'In Transit', 'delivered' => 'Delivered', 'testing_period' => 'Testing Period',
                'completed' => 'Completed', 'disputed' => 'Disputed', 'refunded' => 'Refunded',
            ];
            $activeTab = request('status', '');
        @endphp
        @foreach($tabs as $val => $label)
            @php $cnt = $val === '' ? $tabCounts['all'] : ($tabCounts[$val] ?? 0); @endphp
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), $val ? ['status' => $val] : [])) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold border transition-all whitespace-nowrap"
               style="{{ $activeTab === $val ? 'background: var(--primary); color: var(--primary-foreground); border-color: var(--primary);' : 'color: var(--muted-foreground); border-color: var(--border); background: var(--card);' }}">
                {{ $label }}
                @if($cnt > 0 || $val === '')
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                          style="{{ $activeTab === $val ? 'background: rgba(255,255,255,0.2); color: var(--primary-foreground);' : 'background: var(--muted); color: var(--muted-foreground);' }}">{{ $cnt }}</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Search & Date Filters --}}
    <div class="rounded-xl p-4 mb-5" style="border: 1px solid var(--border); background: var(--card);">
        <form action="{{ route('admin.orders') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4 text-sm">
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <div class="flex flex-wrap items-center gap-4 flex-1">
                <div class="w-full sm:w-72 space-y-1">
                    <label class="text-xs font-bold uppercase tracking-wider block" style="color: var(--muted-foreground);">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color: var(--muted-foreground);"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order number or buyer name..."
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-xl focus:ring-1 focus:outline-none" style="border: 1px solid var(--border); background: var(--muted); color: var(--foreground);">
                    </div>
                </div>
                <div class="date-range-picker-container flex flex-wrap items-center gap-4" x-data="dateRangePicker({ start: '{{ request('date_start') }}', end: '{{ request('date_end') }}', startName: 'date_start', endName: 'date_end' })">
                    <input type="hidden" name="date_start" x-model="dateStart">
                    <input type="hidden" name="date_end" x-model="dateEnd">
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider block" style="color: var(--muted-foreground);">Date Range</label>
                        <select x-model="currentPreset" @change="applyPreset($event.target.value)" class="p-2.5 pl-3 pr-8 text-xs rounded-xl focus:ring-1 focus:outline-none transition-all cursor-pointer font-semibold appearance-none" style="border: 1px solid var(--border); background: var(--muted); color: var(--foreground);">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="7days">Last 7 Days</option>
                            <option value="30days">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2" x-show="currentPreset === 'custom'">
                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase tracking-wider block" style="color: var(--muted-foreground);">From</label>
                            <input type="text" x-ref="startInput" placeholder="Start" readonly class="pl-3 pr-4 py-2.5 text-xs rounded-xl w-32 font-semibold" style="border: 1px solid var(--border); background: var(--muted); color: var(--foreground);">
                        </div>
                        <span class="text-xs mb-3" style="color: var(--muted-foreground);">to</span>
                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase tracking-wider block" style="color: var(--muted-foreground);">To</label>
                            <input type="text" x-ref="endInput" placeholder="End" readonly class="pl-3 pr-4 py-2.5 text-xs rounded-xl w-32 font-semibold" style="border: 1px solid var(--border); background: var(--muted); color: var(--foreground);">
                        </div>
                    </div>
                </div>
            </div>
            @if(request()->anyFilled(['search', 'date_start', 'date_end']))
                <a href="{{ route('admin.orders', request()->only('status')) }}" class="px-4 py-2 rounded-xl text-xs font-semibold" style="border: 1px solid var(--border); color: var(--muted-foreground);">Clear Filters</a>
            @endif
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--border); background: var(--card);" x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
        <div x-show="selectedIds.length > 0" x-transition.opacity class="p-3 flex items-center justify-between" style="background: var(--muted); border-bottom: 1px solid var(--border);">
            <div class="flex items-center space-x-3">
                <span class="font-bold text-xs uppercase tracking-wider px-3 py-1 rounded-lg" style="background: var(--primary); color: var(--primary-foreground);"><span x-text="selectedIds.length"></span> Selected</span>
                <form method="POST" action="{{ route('admin.orders.bulk-action') }}" class="flex items-center space-x-2" x-ref="bulkForm">
                    @csrf
                    <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
                    <select name="action" x-model="bulkAction" class="text-xs rounded-lg py-1.5 px-3 font-semibold" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                        <option value="">Bulk Actions...</option>
                        <option value="status_confirmed">Set Confirmed</option>
                        <option value="status_delivered">Set Delivered</option>
                        <option value="status_completed">Set Completed</option>
                        <option value="status_cancelled">Set Cancelled</option>
                        <option value="delete">Move to Trash</option>
                    </select>
                    <button type="button" @click="if(bulkAction && confirm('Apply to ' + selectedIds.length + ' orders?')) $refs.bulkForm.submit()" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" style="background: var(--primary); color: var(--primary-foreground);" :disabled="!bulkAction">Apply</button>
                </form>
            </div>
            <button type="button" @click="selectedIds = []; selectAll = false" class="text-xs font-bold px-2 py-1" style="color: var(--muted-foreground);">Clear</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead style="background: var(--muted);">
                    <tr class="border-b" style="border-color: var(--border);">
                        <th class="px-5 py-3.5 w-12 text-center"><input type="checkbox" x-model="selectAll" @change="if(selectAll) { selectedIds = {{ collect($orders->items())->pluck('id')->toJson() }} } else { selectedIds = [] }" class="rounded"></th>
                        <th class="px-5 py-3.5 font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Order</th>
                        <th class="px-5 py-3.5 font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Product</th>
                        <th class="px-5 py-3.5 font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Buyer</th>
                        <th class="px-5 py-3.5 font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Seller</th>
                        <th class="px-5 py-3.5 text-right font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Amount</th>
                        <th class="px-5 py-3.5 font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Escrow</th>
                        <th class="px-5 py-3.5 font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Status</th>
                        <th class="px-5 py-3.5 text-right font-semibold text-[10px] uppercase tracking-wider" style="color: var(--muted-foreground);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $addr = $order->delivery_address ?? [];
                            $esc = $order->escrow;
                            $ship = $order->shipment;
                            $dispute = $order->dispute;
                            $timeline = [
                                ['status' => 'pending_payment', 'label' => 'Order Placed', 'ts' => $order->created_at?->format('d M Y, H:i')],
                                ['status' => 'payment_received', 'label' => 'Payment Received', 'ts' => null],
                                ['status' => 'confirmed', 'label' => 'Seller Confirmed', 'ts' => null],
                                ['status' => 'label_generated', 'label' => 'Label Generated', 'ts' => null],
                                ['status' => 'picked_up', 'label' => 'Picked Up', 'ts' => $ship?->picked_up_at?->format('d M Y, H:i')],
                                ['status' => 'in_transit', 'label' => 'In Transit', 'ts' => null],
                                ['status' => 'out_for_delivery', 'label' => 'Out for Delivery', 'ts' => null],
                                ['status' => 'delivered', 'label' => 'Delivered', 'ts' => $order->delivered_at?->format('d M Y, H:i')],
                                ['status' => 'testing_period', 'label' => 'Testing Period', 'ts' => $order->delivered_at?->format('d M Y, H:i')],
                                ['status' => 'completed', 'label' => 'Completed', 'ts' => $order->completed_at?->format('d M Y, H:i')],
                            ];
                            $statusOrder = array_column($timeline, 'status');
                            $currentIdx = array_search($order->order_status, $statusOrder);
                            $payload = [
                                'id' => $order->id, 'order_number' => $order->order_number, 'order_status' => $order->order_status,
                                'status_label' => $order->status_label, 'created_at' => $order->created_at->format('d M Y, H:i'),
                                'total_amount' => number_format($order->total_amount, 2),
                                'product_amount' => number_format($order->product_amount, 2),
                                'shipping_amount' => number_format($order->shipping_amount, 2),
                                'commission_pct' => $order->commission_percent,
                                'commission_amt' => number_format($order->commission_amount, 2),
                                'seller_payout' => number_format($order->seller_payout_amount, 2),
                                'buyer_name' => $order->buyer->name ?? 'N/A', 'buyer_email' => $order->buyer->email ?? '',
                                'buyer_phone' => $order->buyer->phone ?? '',
                                'buyer_url' => route('admin.users', ['search' => $order->buyer->email ?? '']),
                                'delivery_name' => $addr['name'] ?? ($order->buyer->name ?? ''),
                                'delivery_phone' => $addr['phone'] ?? ($order->buyer->phone ?? ''),
                                'delivery_address' => trim(($addr['address_line1'] ?? '') . ' ' . ($addr['address_line2'] ?? '') . ', ' . ($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '') . ' - ' . ($addr['pincode'] ?? '')),
                                'shop_name' => $order->seller->shop_name ?? 'N/A',
                                'seller_email' => $order->seller->user->email ?? '',
                                'seller_url' => route('admin.sellers.show', $order->seller_id),
                                'escrow_status' => $esc?->status ?? 'none',
                                'escrow_held' => $esc ? number_format($esc->amount_held, 2) : '0.00',
                                'testing_ends_at' => $order->testing_window_ends_at?->format('d M Y, H:i'),
                                'testing_hours_left' => $order->testing_window_remaining_hours,
                                'awb_number' => $ship?->awb_number ?? null,
                                'courier_name' => $ship?->courier_name ?? null,
                                'ship_status' => $ship?->status ?? null,
                                'pickup_at' => $ship?->picked_up_at?->format('d M Y, H:i'),
                                'delivered_at' => $ship?->delivered_at?->format('d M Y, H:i'),
                                'est_delivery' => $ship?->estimated_delivery_date?->format('d M Y'),
                                'dispute_id' => $dispute?->id,
                                'dispute_url' => $dispute ? route('admin.disputes.show', $dispute->id) : null,
                                'timeline' => $timeline, 'current_step_idx' => $currentIdx !== false ? $currentIdx : -1,
                                'status_url' => route('admin.orders.status', $order->id), 'csrf' => csrf_token(),
                                'show_url' => route('admin.orders.show', $order->id),
                                'invoice_download_url' => route('admin.orders.invoice.download', $order->id),
                                'invoice_resend_url' => route('admin.orders.invoice.resend', $order->id),
                            ];
                        @endphp
                        <tr class="border-b transition-all cursor-pointer" style="border-color: var(--border);" @click="open({{ json_encode($payload) }})">
                            <td class="px-5 py-3.5 text-center" @click.stop><input type="checkbox" value="{{ $order->id }}" x-model="selectedIds" class="rounded"></td>
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-xs">#{{ $order->order_number }}</p>
                                <p class="text-[11px] mt-0.5" style="color: var(--muted-foreground);">{{ $order->created_at->format('d M Y') }}</p>
                            </td>
                            <td class="px-5 py-3.5"><p class="text-xs font-medium truncate max-w-[160px]">{{ $order->listing->title ?? 'N/A' }}</p></td>
                            <td class="px-5 py-3.5 text-xs" style="color: var(--muted-foreground);">{{ $order->buyer->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-xs" style="color: var(--muted-foreground);">{{ $order->seller->shop_name ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-right"><p class="text-xs font-bold">₹{{ number_format($order->total_amount, 0) }}</p></td>
                            <td class="px-5 py-3.5">
                                @if($order->escrow)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border
                                        @if($order->escrow->status === 'held') style="background: #fef3c7; color: #92400e; border-color: #fde68a;"
                                        @elseif($order->escrow->status === 'released') style="background: #f0fdf4; color: #166534; border-color: #bbf7d0;"
                                        @elseif($order->escrow->status === 'disputed') style="background: #fef2f2; color: #991b1b; border-color: #fecaca;"
                                        @else style="background: var(--muted); color: var(--muted-foreground); border-color: var(--border);" @endif">{{ ucfirst($order->escrow->status) }}</span>
                                @else
                                    <span class="text-[10px]" style="color: var(--muted-foreground);">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full" style="background-color:{{ $order->status_color }}18; color:{{ $order->status_color }};">{{ $order->status_label }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.orders.invoice.download', $order->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all" style="border: 1px solid var(--border); color: var(--muted-foreground);" title="Download Invoice"><i data-lucide="download" class="w-4 h-4"></i></a>
                                    <button @click="open({{ json_encode($payload) }})" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg transition-all" style="background: var(--primary); color: var(--primary-foreground);">Track <i data-lucide="panels-right-open" class="w-3.5 h-3.5"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3" style="background: var(--muted);"><i data-lucide="shopping-bag" class="w-5 h-5" style="color: var(--muted-foreground);"></i></div>
                                    <h4 class="text-sm font-bold">No Orders Found</h4>
                                    <p class="text-xs mt-1 max-w-xs mx-auto" style="color: var(--muted-foreground);">No orders match the current filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-5 py-4 border-t" style="border-color: var(--border); background: var(--muted);">{{ $orders->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Side Drawer --}}
    <div x-show="drawerOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="close()" class="fixed inset-0 backdrop-blur-sm z-40" style="background: rgba(0,0,0,0.4); display:none;"></div>

    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed top-0 right-0 h-full w-full max-w-xl shadow-2xl z-50 flex flex-col" style="background: var(--card); display:none;">
        <div class="flex items-center justify-between px-6 py-4 border-b shrink-0" style="border-color: var(--border); background: var(--muted);">
            <div>
                <h3 class="font-bold text-base" x-text="'Order #' + (order?.order_number ?? '')"></h3>
                <p class="text-[11px] mt-0.5" style="color: var(--muted-foreground);" x-text="'Placed ' + (order?.created_at ?? '')"></p>
            </div>
            <div class="flex items-center gap-2">
                <a :href="order?.show_url" class="inline-flex items-center gap-1.5 px-3 py-1.5 font-bold text-xs rounded-lg transition-all" style="background: var(--primary); color: var(--primary-foreground);"><span>Full Details</span><i data-lucide="external-link" class="w-3.5 h-3.5"></i></a>
                <button @click="close()" class="p-2 rounded-lg transition-all" style="color: var(--muted-foreground);"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <template x-if="order?.dispute_id">
                <div class="p-3.5 rounded-xl flex items-center justify-between gap-3" style="background: #fef2f2; border: 1px solid #fecaca;">
                    <div class="flex items-center gap-2"><i data-lucide="alert-triangle" class="w-4 h-4 shrink-0" style="color: #ef4444;"></i><p class="text-xs font-bold" style="color: #991b1b;">Active dispute on this order.</p></div>
                    <a :href="order?.dispute_url" class="text-xs font-bold px-3 py-1 rounded-lg transition-all shrink-0" style="background: #fecaca; color: #991b1b;">View Dispute</a>
                </div>
            </template>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider mb-4" style="color: var(--muted-foreground);">Order Timeline</p>
                <div class="space-y-0">
                    <template x-for="(step, i) in (order?.timeline ?? [])" :key="i">
                        <div class="flex gap-3 pb-4 relative">
                            <template x-if="i < (order?.timeline?.length - 1)"><div class="absolute left-[13px] top-6 bottom-0 w-0.5" :class="i <= order?.current_step_idx ? 'bg-primary' : ''" :style="i > order?.current_step_idx ? 'background: var(--border)' : ''"></div></template>
                            <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center shrink-0 z-10" :class="{ 'border-foreground': i === order?.current_step_idx }" :style="i < order?.current_step_idx ? 'background: #22c55e; border-color: #22c55e;' : (i === order?.current_step_idx ? 'background: var(--primary);' : 'background: var(--card); border-color: var(--border);')">
                                <template x-if="i < order?.current_step_idx"><i data-lucide="check" class="w-3 h-3 text-white"></i></template>
                                <template x-if="i === order?.current_step_idx"><div class="w-2 h-2 rounded-full" style="background: var(--primary-foreground);"></div></template>
                            </div>
                            <div class="pt-0.5">
                                <p class="text-xs font-semibold" :style="i <= order?.current_step_idx ? 'color: var(--foreground)' : 'color: var(--muted-foreground)'" x-text="step.label"></p>
                                <p class="text-[11px] mt-0.5" style="color: var(--muted-foreground);" x-show="step.ts" x-text="step.ts"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--muted-foreground);">Buyer & Delivery</p>
                <div class="rounded-xl p-4 space-y-2 text-xs relative" style="background: var(--muted); border: 1px solid var(--border);">
                    <div class="absolute top-4 right-4"><a :href="order?.buyer_url" target="_blank" class="text-xs font-bold px-2.5 py-1.5 rounded-lg transition-all" style="border: 1px solid var(--border); color: var(--muted-foreground);">Profile</a></div>
                    <div class="flex gap-2 items-center pr-16"><i data-lucide="user" class="w-3.5 h-3.5 shrink-0" style="color: var(--muted-foreground);"></i><span class="font-semibold" x-text="order?.buyer_name"></span><span class="truncate max-w-[150px]" style="color: var(--muted-foreground);" x-text="order?.buyer_email"></span></div>
                    <div class="flex gap-2 items-start"><i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 mt-0.5" style="color: var(--muted-foreground);"></i><div><p class="font-semibold" x-text="order?.delivery_name"></p><p style="color: var(--muted-foreground);" class="leading-relaxed" x-text="order?.delivery_address"></p></div></div>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--muted-foreground);">Seller & Payout</p>
                <div class="rounded-xl p-4 flex items-center gap-3" style="background: var(--muted); border: 1px solid var(--border);">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-sm shrink-0" style="background: var(--primary); color: var(--primary-foreground);" x-text="order?.shop_name?.charAt(0)?.toUpperCase() ?? 'S'"></div>
                    <div class="min-w-0 flex-1"><p class="text-xs font-bold" x-text="order?.shop_name"></p><p class="text-[11px]" style="color: var(--muted-foreground);" x-text="order?.seller_email"></p></div>
                    <div class="text-right shrink-0"><p class="text-[10px]" style="color: var(--muted-foreground);">Payout</p><p class="text-sm font-bold" style="color: #16a34a;" x-text="'₹' + order?.seller_payout"></p></div>
                    <a :href="order?.seller_url" target="_blank" class="text-xs font-bold px-2.5 py-1.5 rounded-lg transition-all shrink-0" style="border: 1px solid var(--border); color: var(--muted-foreground);">Profile</a>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--muted-foreground);">Financials</p>
                <div class="rounded-xl p-4 space-y-2 text-xs" style="background: var(--muted); border: 1px solid var(--border);">
                    <div class="flex justify-between"><span style="color: var(--muted-foreground);">Product</span><span class="font-semibold" x-text="'₹' + order?.product_amount"></span></div>
                    <div class="flex justify-between"><span style="color: var(--muted-foreground);">Shipping</span><span class="font-semibold" x-text="'₹' + order?.shipping_amount"></span></div>
                    <div class="flex justify-between border-t pt-2" style="border-color: var(--border);"><span class="font-bold">Total</span><span class="font-bold" x-text="'₹' + order?.total_amount"></span></div>
                    <div class="flex justify-between text-[11px]" style="color: var(--muted-foreground);"><span>Commission (<span x-text="order?.commission_pct"></span>%)</span><span x-text="'₹' + order?.commission_amt"></span></div>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--muted-foreground);">Escrow Status</p>
                <div class="rounded-xl p-4 space-y-3" style="background: var(--muted); border: 1px solid var(--border);">
                    <div class="flex items-center justify-between"><div class="flex items-center gap-2"><i data-lucide="shield" class="w-4 h-4" style="color: var(--muted-foreground);"></i><span class="text-xs font-semibold">Amount Held</span></div><span class="text-xs font-bold" x-text="'₹' + order?.escrow_held"></span></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color: var(--muted-foreground);">Escrow Status</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="{ 'bg-amber-50 text-amber-700 border border-amber-200': order?.escrow_status === 'held', 'bg-emerald-50 text-emerald-700 border border-emerald-200': order?.escrow_status === 'released', 'bg-rose-50 text-rose-700 border border-rose-200': order?.escrow_status === 'disputed' }" x-text="order?.escrow_status === 'none' ? 'No Escrow' : order?.escrow_status?.charAt(0).toUpperCase() + order?.escrow_status?.slice(1)"></span>
                    </div>
                    <template x-if="order?.testing_ends_at && order?.order_status === 'testing_period'">
                        <div class="rounded-lg p-3" style="background: var(--card); border: 1px solid var(--border);">
                            <p class="text-[10px] font-bold uppercase tracking-wide">Testing Window</p>
                            <p class="text-xs mt-1">Ends: <span class="font-bold" x-text="order?.testing_ends_at"></span></p>
                            <p class="text-[11px] mt-0.5" style="color: var(--muted-foreground);"><span x-text="order?.testing_hours_left"></span> hours remaining</p>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--muted-foreground);">Invoice & Email</p>
                <div class="rounded-xl p-4 space-y-2.5" style="border: 1px solid var(--border); background: var(--card);">
                    <a :href="order?.invoice_download_url" class="w-full font-semibold py-2 px-3 rounded-lg text-xs transition-all flex items-center justify-center space-x-1.5" style="background: var(--primary); color: var(--primary-foreground);"><i data-lucide="download" class="w-4 h-4"></i><span>Download Invoice</span></a>
                    <form :action="order?.invoice_resend_url" method="POST" class="w-full"><input type="hidden" name="_token" :value="order?.csrf"><button type="submit" class="w-full font-semibold py-2 px-3 rounded-lg text-xs transition-all flex items-center justify-center space-x-1.5" style="border: 1px solid var(--border); color: var(--foreground);"><i data-lucide="mail" class="w-4 h-4"></i><span>Resend Invoice Email</span></button></form>
                </div>
            </div>

            <template x-if="order?.awb_number">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--muted-foreground);">Shipment Tracking</p>
                    <div class="rounded-xl p-4 space-y-2 text-xs" style="background: var(--muted); border: 1px solid var(--border);">
                        <div class="flex justify-between"><span style="color: var(--muted-foreground);">Courier</span><span class="font-semibold" x-text="order?.courier_name ?? '—'"></span></div>
                        <div class="flex justify-between"><span style="color: var(--muted-foreground);">AWB Number</span><span class="font-mono font-bold select-all" x-text="order?.awb_number"></span></div>
                        <div class="flex justify-between" x-show="order?.pickup_at"><span style="color: var(--muted-foreground);">Picked Up</span><span class="font-medium" x-text="order?.pickup_at"></span></div>
                        <div class="flex justify-between" x-show="order?.delivered_at"><span style="color: var(--muted-foreground);">Delivered</span><span class="font-medium" style="color: #16a34a;" x-text="order?.delivered_at"></span></div>
                        <div class="flex justify-between" x-show="order?.est_delivery"><span style="color: var(--muted-foreground);">Est. Delivery</span><span class="font-medium" x-text="order?.est_delivery"></span></div>
                    </div>
                </div>
            </template>
        </div>

        <div class="shrink-0 border-t px-6 py-4" style="border-color: var(--border); background: var(--muted);">
            <p class="text-xs font-bold uppercase tracking-wide mb-3" style="color: var(--muted-foreground);">Manual Status Update</p>
            <form :action="order?.status_url" method="POST" class="flex gap-2">
                <input type="hidden" name="_token" :value="order?.csrf">
                <select name="order_status" class="flex-1 text-sm rounded-xl px-3 py-2.5 focus:ring-1 focus:outline-none" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                    @foreach(['pending_payment' => 'Pending Payment', 'payment_received' => 'Payment Received', 'confirmed' => 'Confirmed', 'label_generated' => 'Label Generated', 'picked_up' => 'Picked Up', 'in_transit' => 'In Transit', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'testing_period' => 'Testing Period', 'completed' => 'Completed', 'disputed' => 'Disputed', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled'] as $val => $lbl)
                        <option value="{{ $val }}" x-bind:selected="order?.order_status === '{{ $val }}'">{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2.5 text-sm font-bold rounded-xl transition-all shrink-0" style="background: var(--primary); color: var(--primary-foreground);">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection