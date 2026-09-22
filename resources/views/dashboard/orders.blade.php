@extends('layouts.app')

@section('title', 'Order Management & Escrow Stepper | X-Buy')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-8"
     x-data="{
         reviewModalOpen: false,
         disputeModalOpen: false,
         activeOrderId: null,
         ratingStars: 5,
         ratingTags: [],
         reviewComment: '',
         disputeReason: 'item_not_working',
         disputeDesc: '',
         toggleTag(tag) {
             if (this.ratingTags.includes(tag)) {
                 this.ratingTags = this.ratingTags.filter(t => t !== tag);
             } else {
                 this.ratingTags.push(tag);
             }
         },
         openReview(id) {
             this.activeOrderId = id;
             this.ratingStars = 5;
             this.ratingTags = ['Accurate Description', 'Fast Shipping'];
             this.reviewComment = '';
             this.reviewModalOpen = true;
         },
         openDispute(id) {
             this.activeOrderId = id;
             this.disputeReason = 'item_not_working';
             this.disputeDesc = '';
             this.disputeModalOpen = true;
         },
         async submitReview() {
             if (!this.activeOrderId) return;
             try {
                 const res = await fetch(`/api/buyer/orders/${this.activeOrderId}/confirm-delivery`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     }
                 });
                 // Also submit rating
                 await fetch(`/api/buyer/orders/${this.activeOrderId}/rate`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify({
                         rating: this.ratingStars,
                         tags: this.ratingTags,
                         review: this.reviewComment
                     })
                 }).catch(() => {});

                 window.location.reload();
             } catch(e) {
                 alert('Error confirming delivery. Please try again.');
             }
         },
         async submitDispute() {
             if (!this.activeOrderId || !this.disputeDesc.trim()) {
                 alert('Please provide a description of the issue.');
                 return;
             }
             try {
                 const res = await fetch(`/api/buyer/orders/${this.activeOrderId}/dispute`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify({
                         reason: this.disputeReason,
                         description: this.disputeDesc,
                         evidence_images: []
                     })
                 });
                 window.location.reload();
             } catch(e) {
                 alert('Error raising dispute. Please try again.');
             }
         }
     }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header & Top Tabs: My Purchases (Buyer) vs My Sales (Seller) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">
                    Order Management & Escrow
                </h1>
                <p class="text-xs text-zinc-500 mt-1">
                    Track shipments, inspect hardware in your 7-day testing window, and manage escrow payouts.
                </p>
            </div>

            <!-- Buyer / Seller Segmented Tabs (Mercari Style) -->
            <div class="flex items-center p-1 bg-zinc-200/80 rounded-2xl self-start md:self-auto text-xs font-extrabold select-none">
                <a href="{{ route('dashboard.orders', ['tab' => 'buying', 'order_type' => 'bought']) }}" 
                   class="px-5 py-2 rounded-xl transition-all {{ $type === 'bought' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-600 hover:text-zinc-950' }}">
                    🛒 My Purchases (Buyer)
                </a>
                <a href="{{ route('dashboard.orders', ['tab' => 'selling', 'order_type' => 'sold']) }}" 
                   class="px-5 py-2 rounded-xl transition-all {{ $type === 'sold' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-600 hover:text-zinc-950' }}">
                    📦 My Sales (Seller)
                </a>
            </div>
        </div>

        <!-- Sub-status filter pills -->
        <div class="flex items-center gap-2 py-4 overflow-x-auto scrollbar-none text-xs font-bold">
            <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'all']) }}" 
               class="px-4 py-1.5 rounded-full transition-colors {{ $status === 'all' ? 'bg-zinc-950 text-white' : 'bg-white text-zinc-700 border border-zinc-200 hover:border-zinc-400' }}">
                All ({{ $orders->total() }})
            </a>
            <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'in_progress']) }}" 
               class="px-4 py-1.5 rounded-full transition-colors {{ $status === 'in_progress' ? 'bg-[#FDD835] text-black' : 'bg-white text-zinc-700 border border-zinc-200 hover:border-zinc-400' }}">
                Active In-Progress
            </a>
            <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'completed']) }}" 
               class="px-4 py-1.5 rounded-full transition-colors {{ $status === 'completed' ? 'bg-zinc-950 text-white' : 'bg-white text-zinc-700 border border-zinc-200 hover:border-zinc-400' }}">
                Completed
            </a>
            <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'cancelled']) }}" 
               class="px-4 py-1.5 rounded-full transition-colors {{ $status === 'cancelled' ? 'bg-zinc-950 text-white' : 'bg-white text-zinc-700 border border-zinc-200 hover:border-zinc-400' }}">
                Disputed / Cancelled
            </a>
        </div>

        <!-- Orders Feed -->
        @if($orders->count() === 0)
            <div class="bg-white rounded-3xl border border-zinc-200 p-16 text-center space-y-4 max-w-lg mx-auto my-8">
                <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
                <h3 class="text-lg font-black text-zinc-950">No orders found</h3>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    @if($type === 'sold')
                        When a buyer purchases your listed components, the escrow and shipping stepper will appear right here.
                    @else
                        You have not placed any orders yet. Browse verified PC components with 7-day escrow protection.
                    @endif
                </p>
                <a href="{{ $type === 'sold' ? '/dashboard/listings/create' : '/listings' }}" 
                   class="inline-block px-6 py-2.5 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-xs rounded-full">
                    {{ $type === 'sold' ? '+ Sell an Item' : 'Browse Hardware' }}
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($orders as $order)
                    @php
                        $st = $order->order_status;
                        $stage1 = in_array($st, ['payment_received', 'confirmed', 'label_generated', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'testing_period', 'completed']);
                        $stage2 = in_array($st, ['label_generated', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'testing_period', 'completed']);
                        $stage3 = in_array($st, ['picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'testing_period', 'completed']);
                        $stage4 = in_array($st, ['delivered', 'testing_period', 'completed']);
                        $stage5 = in_array($st, ['completed']);

                        $inTesting = in_array($st, ['delivered', 'testing_period']);
                        $isDisputed = $st === 'disputed';
                    @endphp

                    <div class="bg-white rounded-3xl border border-zinc-200 p-6 shadow-xs space-y-6">
                        
                        <!-- Order Top Bar -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-zinc-100 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="font-extrabold text-zinc-950 text-sm">#{{ $order->order_number }}</span>
                                <span class="text-zinc-400 font-medium">• {{ $order->created_at->format('d M Y, h:i A') }}</span>
                                <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px]"
                                      style="background-color: {{ $order->status_color ?? '#e6c019' }}20; color: {{ $order->status_color ?? '#b38f00' }};">
                                    {{ ucwords(str_replace('_', ' ', $st)) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-zinc-500 font-medium">Total:</span>
                                <span class="text-base font-black text-zinc-950">₹{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- 5-Stage Shipment Stepper Bar (Section 10.1) -->
                        <div class="py-2">
                            <div class="grid grid-cols-5 relative items-center text-center">
                                
                                <!-- Connecting track behind nodes -->
                                <div class="absolute inset-x-8 top-4 h-1 bg-zinc-200 -z-0"></div>
                                <div class="absolute left-8 top-4 h-1 bg-emerald-500 -z-0 transition-all duration-500"
                                     style="width: {{ $stage5 ? '100%' : ($stage4 ? '75%' : ($stage3 ? '50%' : ($stage2 ? '25%' : '0%'))) }};"></div>

                                <!-- Node 1: Payment Confirmed -->
                                <div class="flex flex-col items-center gap-1.5 z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $stage1 ? 'bg-emerald-500 text-white' : 'bg-zinc-200 text-zinc-500' }}">
                                        ✓
                                    </div>
                                    <span class="text-[11px] font-bold text-zinc-800">Payment Held</span>
                                </div>

                                <!-- Node 2: Label Generated -->
                                <div class="flex flex-col items-center gap-1.5 z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $stage2 ? 'bg-emerald-500 text-white' : ($st === 'confirmed' ? 'bg-[#FDD835] text-black stepper-active-pulse' : 'bg-zinc-200 text-zinc-500') }}">
                                        {{ $stage2 ? '✓' : '2' }}
                                    </div>
                                    <span class="text-[11px] font-bold text-zinc-800">Label Ready</span>
                                </div>

                                <!-- Node 3: In Transit -->
                                <div class="flex flex-col items-center gap-1.5 z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $stage3 ? 'bg-emerald-500 text-white' : ($st === 'label_generated' || $st === 'picked_up' ? 'bg-[#FDD835] text-black stepper-active-pulse' : 'bg-zinc-200 text-zinc-500') }}">
                                        {{ $stage3 ? '✓' : '3' }}
                                    </div>
                                    <span class="text-[11px] font-bold text-zinc-800">In Transit</span>
                                </div>

                                <!-- Node 4: Testing Period -->
                                <div class="flex flex-col items-center gap-1.5 z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $stage4 ? ($stage5 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-black stepper-active-pulse') : 'bg-zinc-200 text-zinc-500' }}">
                                        📦
                                    </div>
                                    <span class="text-[11px] font-bold text-zinc-800">7-Day Testing</span>
                                </div>

                                <!-- Node 5: Completed -->
                                <div class="flex flex-col items-center gap-1.5 z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $stage5 ? 'bg-emerald-600 text-white' : 'bg-zinc-200 text-zinc-500' }}">
                                        ★
                                    </div>
                                    <span class="text-[11px] font-bold text-zinc-800">Released</span>
                                </div>

                            </div>
                        </div>

                        <!-- 7-Day Testing Countdown Badge (Section 10.2) -->
                        @if($inTesting)
                            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-md bg-amber-200 text-amber-900 font-extrabold text-[11px]">
                                            ⏳ Testing Window Active
                                        </span>
                                        <span class="text-xs font-bold text-amber-950">
                                            @if($order->testing_window_ends_at)
                                                {{ now()->diffForHumans($order->testing_window_ends_at, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]) }} remaining
                                            @else
                                                6 Days, 18 Hours remaining
                                            @endif
                                        </span>
                                    </div>
                                    <p class="text-xs text-zinc-600">
                                        Verify hardware performance with 3DMark or Furmark benchmarks. Ensure serial numbers match.
                                    </p>
                                </div>

                                @if($type === 'bought')
                                    <!-- Two Big Action Buttons -->
                                    <div class="flex items-center gap-2.5 self-end sm:self-auto shrink-0">
                                        <button type="button" 
                                                @click="openReview({{ $order->id }})"
                                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition-transform active:scale-95 cursor-pointer">
                                            ✓ Confirm & Release Funds
                                        </button>

                                        <button type="button" 
                                                @click="openDispute({{ $order->id }})"
                                                class="px-4 py-2.5 rounded-xl bg-white border border-red-500 text-red-600 hover:bg-red-50 font-bold text-xs transition-colors cursor-pointer">
                                            Raise Dispute
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Disputed Notice -->
                        @if($isDisputed)
                            <div class="p-4 bg-red-50 rounded-2xl border border-red-200 text-xs text-red-900 space-y-1">
                                <span class="font-extrabold uppercase tracking-wider block">⚠️ Escrow Frozen — Dispute Under Investigation</span>
                                <p class="text-zinc-600">
                                    Our trust & safety mediation team is actively evaluating photo and benchmark proof. Escrow funds will not be released until resolution.
                                </p>
                            </div>
                        @endif

                        <!-- Order Item Body & Metadata -->
                        <div class="flex flex-col sm:flex-row gap-5 items-start sm:items-center justify-between pt-2">
                            <div class="flex items-center gap-4">
                                <div class="w-18 h-18 rounded-2xl bg-zinc-100 overflow-hidden shrink-0 border border-zinc-200">
                                    <img src="{{ $order->listing->primary_image_url ?? ($order->listing->images->first()->image_url ?? '/website_assets/images/placeholder.png') }}" 
                                         alt="{{ $order->listing->title }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-zinc-950">{{ $order->listing->title ?? 'PC Component' }}</h3>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        @if($type === 'sold')
                                            Buyer: <strong class="text-zinc-800">{{ $order->buyer->name ?? 'Buyer' }}</strong>
                                        @else
                                            Seller: <strong class="text-zinc-800">{{ $order->seller->shop_name ?? 'Verified Seller' }}</strong>
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] text-zinc-400 font-semibold">
                                        <span>Testing Window: {{ $order->testing_window_days ?? 7 }} Days</span>
                                        <span>•</span>
                                        <span>Escrow: {{ ucfirst($order->escrow?->status ?? 'held') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right actions -->
                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                @if($type === 'sold' && $order->seller_payout_amount)
                                    <div class="text-right mr-3 hidden sm:block">
                                        <span class="text-[11px] text-zinc-400 font-semibold block">Net Bank Payout:</span>
                                        <span class="text-sm font-black text-emerald-700">₹{{ number_format($order->seller_payout_amount, 2) }}</span>
                                    </div>
                                @endif

                                @if($order->wallet_invoices_count || true)
                                    <a href="{{ route('dashboard.wallet.invoices.view', $order->id) }}" target="_blank"
                                       class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl text-xs font-bold text-zinc-800 transition-colors">
                                        View Invoice
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif

    </div>

    <!-- Review Modal (Section 10.2) -->
    <div x-show="reviewModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         style="display: none;">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="reviewModalOpen = false"></div>
        <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-zinc-200 space-y-5 z-[101]">
            <h3 class="text-lg font-black text-zinc-950">Release Funds & Rate Seller</h3>
            <p class="text-xs text-zinc-500">By confirming delivery, you release escrow funds directly to the seller's bank account.</p>

            <!-- 5-Star Picker -->
            <div class="text-center py-2">
                <span class="text-xs font-bold text-zinc-400 block mb-2">Overall Experience</span>
                <div class="flex items-center justify-center gap-2">
                    <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                        <button type="button" @click="ratingStars = star" class="text-3xl transition-transform hover:scale-110 cursor-pointer">
                            <span :class="star <= ratingStars ? 'text-amber-400' : 'text-zinc-200'">★</span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Feedback Tags -->
            <div>
                <span class="text-xs font-bold text-zinc-700 block mb-2">Select Feedback Tags:</span>
                <div class="flex flex-wrap gap-2 text-xs">
                    <template x-for="tag in ['Accurate Description', 'Fast Shipping', 'Great Packaging', 'Responsive Seller']" :key="tag">
                        <button type="button" 
                                @click="toggleTag(tag)" 
                                :class="ratingTags.includes(tag) ? 'bg-[#FDD835] text-black border-[#FDD835] font-bold' : 'bg-zinc-100 text-zinc-700 border-zinc-200'"
                                class="px-3 py-1.5 rounded-full border transition-colors cursor-pointer text-xs">
                            <span x-text="tag"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Written Comment -->
            <div>
                <label class="text-xs font-bold text-zinc-700 block mb-1">Write Public Feedback (Optional)</label>
                <textarea x-model="reviewComment" rows="3" placeholder="Tested the hardware, runs cool and stable!" class="w-full text-xs p-3 border border-zinc-300 rounded-xl outline-none focus:border-zinc-950"></textarea>
            </div>

            <button type="button" @click="submitReview" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-xs">
                Confirm & Release Funds Instantly
            </button>
        </div>
    </div>

    <!-- Dispute Wizard Modal (Section 10.2) -->
    <div x-show="disputeModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         style="display: none;">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="disputeModalOpen = false"></div>
        <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-zinc-200 space-y-5 z-[101]">
            <div class="flex items-center gap-2 text-red-600">
                <span class="text-xl">⚠️</span>
                <h3 class="text-lg font-black text-zinc-950">Raise Dispute & Freeze Escrow</h3>
            </div>
            <p class="text-xs text-zinc-500">
                Submitting a dispute immediately freezes auto-release timers and notifies the X-Buy arbitration team.
            </p>

            <div>
                <label class="text-xs font-bold text-zinc-700 block mb-1">Reason for Dispute</label>
                <select x-model="disputeReason" class="w-full text-xs p-2.5 border border-zinc-300 rounded-xl bg-zinc-50 font-bold">
                    <option value="item_not_working">Item Not Working / Defective Hardware</option>
                    <option value="item_not_as_described">Item Not As Described / Cosmetic Damage</option>
                    <option value="wrong_item">Wrong Item or Missing Accessories</option>
                    <option value="counterfeit">Suspected Counterfeit or Fake Hardware</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-700 block mb-1">Detailed Explanation & Benchmark Notes</label>
                <textarea x-model="disputeDesc" rows="4" placeholder="Explain the symptoms (e.g. GPU artifacts in Furmark, crashes on boot, incorrect serial number)..." class="w-full text-xs p-3 border border-zinc-300 rounded-xl outline-none focus:border-zinc-950"></textarea>
            </div>

            <button type="button" @click="submitDispute" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm rounded-xl shadow-xs">
                Freeze Escrow & Submit Dispute
            </button>
        </div>
    </div>

</div>
@endsection
