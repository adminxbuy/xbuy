@extends('layouts.app')

@section('title', 'Direct Checkout | X-Buy Escrow Secure')

@php
    $user = auth()->user();
    $walletBalance = (float) ($user ? ($user->wallet?->balance ?? 0) : 0);
    $price = (float) ($listing->price ?? 0);
    $shippingFee = (float) ($listing->shipping_charges ?? $listing->shipping_fee ?? 0);
    $protectionFee = 85.00;
    $total = $price + $shippingFee + $protectionFee;
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         step: 1,
         name: '{{ addslashes($user->name ?? '') }}',
         phone: '{{ addslashes($user->phone ?? '') }}',
         street: '{{ addslashes($user->street ?? '') }}',
         city: '{{ addslashes($user->city ?? '') }}',
         state: '{{ addslashes($user->state ?? '') }}',
         pincode: '{{ addslashes($user->pincode ?? '') }}',
         useWallet: false,
         walletBalance: {{ $walletBalance }},
         price: {{ $price }},
         shippingFee: {{ $shippingFee }},
         protectionFee: {{ $protectionFee }},
         get total() {
             return this.price + this.shippingFee + this.protectionFee;
         },
         get walletDeduction() {
             return this.useWallet ? Math.min(this.walletBalance, this.total) : 0;
         },
         get remainingToPay() {
             return Math.max(0, this.total - this.walletDeduction);
         },
         agreedToEscrow: true,
         submitting: false,
         errorMessage: '',
         async submitOrder() {
             if (!this.name || !this.phone || !this.street || !this.city || !this.state || !this.pincode) {
                 this.errorMessage = 'Please complete all delivery address fields.';
                 this.step = 1;
                 return;
             }
             if (!this.agreedToEscrow) {
                 this.errorMessage = 'You must agree to the X-Buy Escrow 7-Day testing terms to place an order.';
                 this.step = 4;
                 return;
             }

             this.submitting = true;
             this.errorMessage = '';

             try {
                 const res = await fetch('/checkout', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({
                         listing_id: {{ $listing->id }},
                         use_wallet: this.useWallet,
                         delivery_address: {
                             name: this.name,
                             phone: this.phone,
                             street: this.street,
                             city: this.city,
                             state: this.state,
                             pincode: this.pincode
                         }
                     })
                 });

                 const data = await res.json();
                 if (!res.ok || data.error) {
                     this.errorMessage = data.message || data.error || 'Failed to create order.';
                     this.submitting = false;
                     return;
                 }

                 // Order placed successfully
                 const orderId = data.data?.id || data.data?.order?.id || data.order?.id || data.id || '';
                 window.location.href = `/checkout/success/${orderId}`;
             } catch(err) {
                 this.errorMessage = 'Connection error. Please try again.';
                 this.submitting = false;
             }
         }
     }">

    <!-- Breadcrumbs -->
    <nav class="mb-6 flex items-center space-x-2 text-xs font-semibold text-zinc-500">
        <a href="/" class="hover:text-zinc-950">Home</a>
        <span>/</span>
        <a href="{{ route('listings.show', $listing->slug) }}" class="hover:text-zinc-950 truncate max-w-xs">{{ $listing->title }}</a>
        <span>/</span>
        <span class="text-zinc-900 font-bold">Checkout</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: 4-Step Accordion (Span 7) -->
        <div class="lg:col-span-7 space-y-4">
            
            <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">
                Secure Escrow Checkout
            </h1>

            <div x-show="errorMessage" x-text="errorMessage" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-bold rounded-xl" style="display: none;"></div>

            <!-- Step 1: Delivery Address -->
            <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-2xs">
                <div class="p-5 flex items-center justify-between border-b border-zinc-100 bg-zinc-50/60 cursor-pointer"
                     @click="step = 1">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-zinc-950 text-white flex items-center justify-center font-bold text-xs">1</span>
                        <h3 class="text-sm font-black text-zinc-950 uppercase tracking-wider">Delivery Address</h3>
                    </div>
                    <button type="button" class="text-xs font-bold text-amber-700 hover:underline">
                        <span x-text="step === 1 ? 'Collapse' : 'Edit'"></span>
                    </button>
                </div>

                <div x-show="step === 1" class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-zinc-700 mb-1">Full Name</label>
                            <input type="text" x-model="name" placeholder="John Doe" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-700 mb-1">Phone Number</label>
                            <input type="tel" x-model="phone" placeholder="+91 98765 43210" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-zinc-700 mb-1">Street Address</label>
                            <input type="text" x-model="street" placeholder="Flat / House No, Building, Area" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-700 mb-1">City</label>
                            <input type="text" x-model="city" placeholder="Mumbai" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-700 mb-1">State</label>
                            <input type="text" x-model="state" placeholder="Maharashtra" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-700 mb-1">Pincode</label>
                            <input type="text" x-model="pincode" placeholder="400001" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                        </div>
                    </div>

                    <button type="button" @click="step = 2" class="px-5 py-2.5 bg-zinc-900 text-white font-bold text-xs rounded-xl hover:bg-black">
                        Continue to Delivery &rarr;
                    </button>
                </div>
            </div>

            <!-- Step 2: Delivery Method -->
            <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-2xs">
                <div class="p-5 flex items-center justify-between border-b border-zinc-100 bg-zinc-50/60 cursor-pointer"
                     @click="step = 2">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-zinc-950 text-white flex items-center justify-center font-bold text-xs">2</span>
                        <h3 class="text-sm font-black text-zinc-950 uppercase tracking-wider">Delivery Method</h3>
                    </div>
                    <span class="text-xs font-bold text-zinc-400">Insured Shiprocket</span>
                </div>

                <div x-show="step === 2" class="p-5 space-y-3 text-xs">
                    <label class="p-3.5 rounded-xl border-2 border-zinc-950 bg-amber-50/30 flex items-center justify-between cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="radio" checked class="accent-[#FDD835]">
                            <div>
                                <h4 class="font-extrabold text-zinc-950">Insured Express Courier (Shiprocket Network)</h4>
                                <p class="text-zinc-500 mt-0.5">Estimated delivery: 2-3 business days. Real-time GPS tracking.</p>
                            </div>
                        </div>
                        <span class="font-extrabold text-emerald-700" x-text="shippingFee === 0 ? 'FREE' : '₹' + shippingFee"></span>
                    </label>

                    <button type="button" @click="step = 3" class="px-5 py-2.5 bg-zinc-900 text-white font-bold text-xs rounded-xl hover:bg-black mt-2">
                        Continue to Payment &rarr;
                    </button>
                </div>
            </div>

            <!-- Step 3: Payment Method -->
            <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-2xs">
                <div class="p-5 flex items-center justify-between border-b border-zinc-100 bg-zinc-50/60 cursor-pointer"
                     @click="step = 3">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-zinc-950 text-white flex items-center justify-center font-bold text-xs">3</span>
                        <h3 class="text-sm font-black text-zinc-950 uppercase tracking-wider">Payment Method</h3>
                    </div>
                </div>

                <div x-show="step === 3" class="p-5 space-y-4 text-xs">
                    <!-- Wallet toggle -->
                    @if($walletBalance > 0)
                        <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/50 flex items-center justify-between">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" x-model="useWallet" class="rounded border-amber-400 text-[#FDD835] focus:ring-0">
                                <div>
                                    <span class="font-bold text-zinc-950">Use X-Buy Wallet Balance</span>
                                    <span class="text-zinc-500 block">Available: ₹{{ number_format($walletBalance, 2) }}</span>
                                </div>
                            </label>
                            <span class="font-black text-emerald-700" x-show="useWallet" x-text="'-₹' + walletDeduction.toFixed(2)"></span>
                        </div>
                    @endif

                    <!-- Gateway options -->
                    <div class="space-y-2">
                        <span class="font-bold uppercase tracking-wider text-zinc-400 block">Payment Gateway</span>
                        <label class="p-3 rounded-xl border border-zinc-300 bg-white flex items-center justify-between cursor-pointer hover:border-zinc-950">
                            <div class="flex items-center gap-3">
                                <input type="radio" checked name="gateway" class="accent-[#FDD835]">
                                <div>
                                    <h4 class="font-extrabold text-zinc-950">Razorpay Secure Checkout</h4>
                                    <p class="text-zinc-500 text-[11px]">Instant UPI (GPay, PhonePe, Paytm), Credit/Debit Cards, NetBanking, EMI</p>
                                </div>
                            </div>
                            <div class="h-6 px-2 bg-zinc-100 rounded text-[10px] font-black text-blue-700 flex items-center">
                                Razorpay
                            </div>
                        </label>
                    </div>

                    <button type="button" @click="step = 4" class="px-5 py-2.5 bg-zinc-900 text-white font-bold text-xs rounded-xl hover:bg-black mt-2">
                        Continue to Escrow Agreement &rarr;
                    </button>
                </div>
            </div>

            <!-- Step 4: Escrow Terms Agreement -->
            <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-2xs">
                <div class="p-5 flex items-center justify-between border-b border-zinc-100 bg-zinc-50/60 cursor-pointer"
                     @click="step = 4">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-zinc-950 text-white flex items-center justify-center font-bold text-xs">4</span>
                        <h3 class="text-sm font-black text-zinc-950 uppercase tracking-wider">Escrow Protection Agreement</h3>
                    </div>
                </div>

                <div x-show="step === 4" class="p-5 space-y-3 text-xs">
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200/80 space-y-2 text-zinc-700 leading-relaxed">
                        <div class="flex items-center gap-2 font-black text-amber-900 uppercase tracking-wider text-[11px]">
                            <span>🛡️</span>
                            <span>Binding Escrow Protocol</span>
                        </div>
                        <p>
                            By placing this order, you agree that your payment will be held securely in the X-Buy Escrow account. The seller is required to ship the component within 48 hours. Upon delivery, your <strong>7-day testing window</strong> begins. The money will only be disbursed to the seller once you inspect and approve the item or if the window elapses without a dispute.
                        </p>
                    </div>

                    <label class="flex items-start gap-2.5 pt-2 cursor-pointer">
                        <input type="checkbox" x-model="agreedToEscrow" class="rounded border-zinc-400 text-zinc-900 focus:ring-0 mt-0.5">
                        <span class="font-bold text-zinc-900">
                            I agree to the <a href="/p/escrow-policy" target="_blank" class="underline text-amber-800">Escrow Financial Policy</a> and confirm I understand the 7-day hardware inspection window.
                        </span>
                    </label>
                </div>
            </div>

        </div>

        <!-- Right: Sticky Order Summary (Span 5) -->
        <div class="lg:col-span-5 sticky top-24 space-y-5">
            
            <div class="bg-white rounded-3xl border border-zinc-200 p-6 shadow-sm space-y-5">
                <h2 class="text-lg font-black text-zinc-950 tracking-tight pb-3 border-b border-zinc-100">
                    Order Summary
                </h2>

                <!-- Item thumbnail & info -->
                <div class="flex gap-4 items-center">
                    <div class="w-18 h-18 rounded-xl bg-zinc-100 border border-zinc-200 overflow-hidden shrink-0">
                        <img src="{{ $listing->primary_image_url ?? ($listing->images->first()->image_url ?? '/website_assets/images/placeholder.png') }}" 
                             alt="{{ $listing->title }}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xs font-bold text-zinc-900 line-clamp-2 leading-snug">{{ $listing->title }}</h3>
                        <span class="text-[11px] text-zinc-400 font-medium mt-1 block">
                            Seller: {{ $listing->seller?->shop_name ?? 'Verified Seller' }}
                        </span>
                    </div>
                </div>

                <!-- Price breakdown -->
                <div class="space-y-2 text-xs pt-3 border-t border-zinc-100 text-zinc-600">
                    <div class="flex justify-between">
                        <span>Component Price</span>
                        <span class="font-bold text-zinc-900">₹{{ number_format($price) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shiprocket Express Logistics</span>
                        <span class="font-bold text-emerald-600">{{ $shippingFee == 0 ? 'FREE' : '₹' . number_format($shippingFee) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Buyer Protection Fee</span>
                        <span class="font-bold text-zinc-900">₹{{ number_format($protectionFee) }}</span>
                    </div>
                    
                    <div x-show="useWallet && walletDeduction > 0" class="flex justify-between text-emerald-700 font-bold" style="display: none;">
                        <span>Wallet Balance Deduction</span>
                        <span x-text="'-₹' + walletDeduction.toFixed(2)"></span>
                    </div>

                    <div class="flex justify-between text-sm font-extrabold text-zinc-950 pt-3 border-t border-zinc-200">
                        <span>Total Due</span>
                        <span class="text-lg font-black text-zinc-950" x-text="'₹' + remainingToPay.toLocaleString('en-IN')"></span>
                    </div>
                </div>

                <!-- Primary CTA -->
                <button type="button" 
                        @click="submitOrder"
                        :disabled="submitting"
                        class="w-full py-4 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-sm rounded-2xl shadow-sm transition-transform active:scale-98 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                    <svg x-show="!submitting" class="w-5 h-5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span x-text="submitting ? 'Placing Escrow Order...' : 'Pay & Place Order'"></span>
                </button>

                <!-- Escrow Badge Seal -->
                <div class="flex items-center justify-center gap-2 text-[11px] text-zinc-500 font-semibold pt-1 text-center">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 0 1 2.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0 1 10 1.944ZM11 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm0-7a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0V7Z" clip-rule="evenodd"/></svg>
                    <span>Funds held in Razorpay Escrow until 7-day test ends</span>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection
