@extends('layouts.app')

@section('title', 'Order Confirmed #' . ($order->order_number ?? 'Order') . ' | X-Buy Escrow')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="bg-white rounded-3xl border border-zinc-200 p-8 sm:p-12 shadow-sm text-center space-y-6">
        
        <!-- Animated Success Check Circle -->
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-xs">
            <svg class="w-10 h-10 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full inline-block">
                Order Successfully Placed
            </span>
            <h1 class="text-3xl sm:text-4xl font-black text-zinc-950 tracking-tight">
                Thank You for Your Order!
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 font-medium">
                Order Reference: <strong class="text-zinc-950 font-bold">#{{ $order->order_number ?? 'XBUY-ORD-SUCCESS' }}</strong>
            </p>
        </div>

        <!-- Escrow Held Banner -->
        <div class="p-5 bg-amber-50/80 border border-amber-200 rounded-2xl text-left space-y-2">
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-amber-900">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 0 1 2.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0 1 10 1.944ZM11 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm0-7a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0V7Z" clip-rule="evenodd"/></svg>
                <span>Payment Held in Escrow</span>
            </div>
            <p class="text-xs text-zinc-700 leading-relaxed">
                Your payment of <strong class="text-zinc-950 font-bold">₹{{ number_format($order->total_amount ?? 0) }}</strong> has been placed safely in X-Buy Escrow. The seller has been notified to dispatch your hardware within 48 hours. When you receive the shipment, you will have <strong>7 full days</strong> to test and confirm everything works.
            </p>
        </div>

        <!-- Order Summary Mini Card -->
        @if(isset($order->listing))
            <div class="flex items-center gap-4 p-4 rounded-2xl border border-zinc-200 bg-zinc-50/60 text-left">
                <div class="w-16 h-16 rounded-xl bg-zinc-100 overflow-hidden shrink-0 border border-zinc-200">
                    <img src="{{ $order->listing->primary_image_url ?? ($order->listing->images->first()->image_url ?? '/website_assets/images/placeholder.png') }}" 
                         alt="{{ $order->listing->title }}" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs font-bold text-zinc-900 truncate">{{ $order->listing->title }}</h4>
                    <span class="text-[11px] text-zinc-500 block mt-0.5">Total Amount: ₹{{ number_format($order->total_amount) }}</span>
                    <span class="text-[10px] font-bold text-blue-700 block mt-0.5">Status: {{ ucwords(str_replace('_', ' ', $order->order_status)) }}</span>
                </div>
            </div>
        @endif

        <!-- Action CTAs -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4">
            <a href="/dashboard/orders?order_type=bought" 
               class="w-full sm:w-auto px-8 py-3.5 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-xs rounded-full shadow-sm transition-transform active:scale-95">
                View in My Purchases &rarr;
            </a>

            @if(isset($order->seller_id))
                <a href="/p/contact-us?order_id={{ $order->id }}" 
                   class="w-full sm:w-auto px-6 py-3.5 bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-800 font-bold text-xs rounded-full transition-colors">
                    Message Seller
                </a>
            @endif

            <a href="/" class="text-xs font-bold text-zinc-500 hover:text-zinc-950 px-4 py-2">
                Continue Browsing
            </a>
        </div>

    </div>

</div>
@endsection
