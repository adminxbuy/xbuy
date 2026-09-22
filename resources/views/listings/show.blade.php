@extends('layouts.app')

@section('title', $listing->title . ' | X-Buy Verified Marketplace')

@php
    $images = $listing->images->pluck('image_url')->toArray();
    if (empty($images) && $listing->primary_image_url) {
        $images = [$listing->primary_image_url];
    }
    if (empty($images)) {
        $images = ['https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=600'];
    }

    $brandName = $listing->brand ? (is_object($listing->brand) ? $listing->brand->name : $listing->brand) : ($listing->brand_text ?? 'Verified Gear');
    $isFreeShip = ($listing->shipping_charges ?? $listing->shipping_fee ?? 0) == 0;
    $seller = $listing->seller;
    $metrics = $seller?->metrics;
    $discountPercent = 0;
    if ($listing->original_price && $listing->original_price > $listing->price) {
        $discountPercent = round((($listing->original_price - $listing->price) / $listing->original_price) * 100);
    }
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
     x-data="{
         images: {{ json_encode($images) }},
         activeIndex: 0,
         isLiked: {{ auth()->check() && auth()->user()->hasFavorited($listing->id) ? 'true' : 'false' }},
         shareCopied: false,
         addToCart() {
             let cart = [];
             try {
                 cart = JSON.parse(localStorage.getItem('xbuy_cart') || '[]');
             } catch(e) {}
             
             const existing = cart.find(i => i.id === {{ $listing->id }});
             if (!existing) {
                 cart.push({
                     id: {{ $listing->id }},
                     slug: '{{ $listing->slug }}',
                     title: '{{ addslashes($listing->title) }}',
                     price: {{ $listing->price }},
                     image: '{{ $images[0] }}',
                     shipping_fee: {{ $listing->shipping_charges ?? 0 }},
                     seller_name: '{{ addslashes($seller?->shop_name ?? 'Verified Seller') }}',
                     quantity: 1
                 });
                 localStorage.setItem('xbuy_cart', JSON.stringify(cart));
                 window.dispatchEvent(new CustomEvent('cart-updated'));
             }
             window.dispatchEvent(new CustomEvent('open-cart'));
         },
         copyLink() {
             navigator.clipboard.writeText(window.location.href);
             this.shareCopied = true;
             setTimeout(() => { this.shareCopied = false; }, 2000);
         }
     }">

    <!-- Breadcrumbs -->
    <nav class="mb-6 flex items-center space-x-2 text-xs font-semibold text-zinc-500">
        <a href="/" class="hover:text-zinc-950 transition-colors">Home</a>
        <span>/</span>
        <a href="/listings" class="hover:text-zinc-950 transition-colors">Catalog</a>
        @if($listing->category)
            <span>/</span>
            <a href="/listings?category={{ $listing->category }}" class="hover:text-zinc-950 capitalize transition-colors">
                {{ str_replace('_', ' ', $listing->category) }}
            </a>
        @endif
        <span>/</span>
        <span class="text-zinc-900 truncate max-w-xs">{{ $listing->title }}</span>
    </nav>

    <!-- Product Grid: 60% Media Gallery | 40% Sticky Conversion Card -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        
        <!-- Left: Media Reel Gallery (Span 7 - 60%) -->
        <div class="lg:col-span-7 flex flex-col gap-4">
            
            <!-- Main Photo Display (1:1 Ratio) -->
            <div class="relative w-full aspect-square bg-zinc-100 rounded-2xl border border-zinc-200 overflow-hidden flex items-center justify-center shadow-xs">
                
                <img :src="images[activeIndex]" 
                     alt="{{ $listing->title }}" 
                     class="w-full h-full object-contain p-4 select-none transition-all duration-200"
                     loading="eager">

                <!-- Left/Right Arrow Navigation -->
                <template x-if="images.length > 1">
                    <div class="absolute inset-x-3 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none">
                        <button type="button" 
                                @click="activeIndex = (activeIndex - 1 + images.length) % images.length"
                                class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-xs border border-zinc-200 shadow-md flex items-center justify-center text-zinc-800 hover:bg-white pointer-events-auto transition-transform active:scale-90"
                                aria-label="Previous photo">
                            <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </button>

                        <button type="button" 
                                @click="activeIndex = (activeIndex + 1) % images.length"
                                class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-xs border border-zinc-200 shadow-md flex items-center justify-center text-zinc-800 hover:bg-white pointer-events-auto transition-transform active:scale-90"
                                aria-label="Next photo">
                            <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>
                </template>

                <!-- Floating Wishlist & Share Pills -->
                <div class="absolute top-4 right-4 flex items-center gap-2">
                    <!-- Wishlist Button -->
                    <button type="button" 
                            @click="
                                @if(auth()->check())
                                    isLiked = !isLiked;
                                    fetch('/api/buyer/wishlist/toggle', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify({ listing_id: {{ $listing->id }} })
                                    });
                                @else
                                    window.location.href = '{{ route('login') }}';
                                @endif
                            "
                            class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-xs border border-zinc-200/80 shadow-sm flex items-center justify-center text-zinc-700 hover:bg-white hover:scale-105 active:scale-95 transition-all cursor-pointer"
                            aria-label="Wishlist">
                        <svg class="w-5 h-5 transition-colors" :class="isLiked ? 'fill-red-500 text-red-500' : 'text-zinc-700 stroke-[2]'" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>

                    <!-- Share Button -->
                    <button type="button" 
                            @click="copyLink"
                            class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-xs border border-zinc-200/80 shadow-sm flex items-center justify-center text-zinc-700 hover:bg-white hover:scale-105 active:scale-95 transition-all cursor-pointer relative"
                            aria-label="Share">
                        <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                        </svg>
                        <span x-show="shareCopied" class="absolute -bottom-8 right-0 bg-zinc-900 text-white text-[10px] font-bold px-2 py-1 rounded shadow" style="display: none;">Link Copied!</span>
                    </button>
                </div>

                <!-- Sold Overlay Banner -->
                @if($listing->listing_status === 'sold')
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center z-10">
                        <span class="bg-red-600 text-white font-black text-xl uppercase px-6 py-2 rounded-xl shadow-2xl tracking-widest transform -rotate-6">
                            SOLD OUT
                        </span>
                    </div>
                @endif
            </div>

            <!-- Thumbnail Reel (Up to 10 photos) -->
            <template x-if="images.length > 1">
                <div class="flex items-center gap-3 overflow-x-auto pb-2 custom-scrollbar">
                    <template x-for="(img, idx) in images" :key="idx">
                        <button type="button" 
                                @click="activeIndex = idx"
                                :class="activeIndex === idx ? 'ring-2 ring-[#FDD835] border-zinc-950' : 'border-zinc-200 opacity-70 hover:opacity-100'"
                                class="w-18 h-18 rounded-xl bg-zinc-50 border overflow-hidden shrink-0 transition-all cursor-pointer p-1">
                            <img :src="img" :alt="'Photo ' + (idx + 1)" class="w-full h-full object-contain">
                        </button>
                    </template>
                </div>
            </template>

            <!-- Technical Specifications & Description (Desktop Tab) -->
            <div class="pt-6 space-y-8 border-t border-zinc-200">
                
                <!-- Description -->
                <div>
                    <h3 class="text-base font-extrabold text-zinc-950 mb-3 uppercase tracking-wider">Item Description</h3>
                    <div class="text-sm text-zinc-700 leading-relaxed whitespace-pre-line bg-zinc-50/50 p-5 rounded-2xl border border-zinc-200/80">
                        {{ $listing->description }}
                    </div>
                </div>

                <!-- Dynamic Specifications -->
                @if(isset($listing->specs) && $listing->specs->count() > 0)
                    <div>
                        <h3 class="text-base font-extrabold text-zinc-950 mb-3 uppercase tracking-wider">Technical Specifications</h3>
                        <div class="rounded-2xl border border-zinc-200 overflow-hidden divide-y divide-zinc-200 text-xs">
                            @foreach($listing->specs as $spec)
                                <div class="grid grid-cols-3 p-3 bg-white hover:bg-zinc-50">
                                    <span class="font-bold text-zinc-500 uppercase">{{ $spec->spec_name ?? $spec->key }}</span>
                                    <span class="col-span-2 font-bold text-zinc-900">{{ $spec->spec_value ?? $spec->value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Inspection & Condition Guidelines -->
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-zinc-950 flex items-center gap-2">
                        <span>🔍</span>
                        <span>Condition & Inspection Guarantee</span>
                    </h4>
                    <p class="text-xs text-zinc-600 leading-relaxed">
                        Every listing on X-Buy must conform to our mandatory condition grading standards. As a buyer, you have a full 7-day hardware stress-testing period to verify serial numbers and confirm component functionality before any payment is disbursed.
                    </p>
                </div>

            </div>

        </div>

        <!-- Right: Sticky Conversion Card (Span 5 - 40%) -->
        <div class="lg:col-span-5 sticky top-24 space-y-6">
            
            <div class="bg-white rounded-3xl border border-zinc-200 p-6 sm:p-8 shadow-sm space-y-6">
                
                <!-- Header: Brand & Title -->
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full inline-block mb-2 border border-amber-200/60">
                        {{ $brandName }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight leading-snug">
                        {{ $listing->title }}
                    </h1>
                    
                    <div class="flex items-center gap-3 text-xs text-zinc-500 font-semibold mt-2">
                        @if($listing->grade)
                            <span class="px-2 py-0.5 rounded-md bg-zinc-900 text-white font-bold">Grade {{ $listing->grade }}</span>
                        @elseif($listing->condition)
                            <span class="px-2 py-0.5 rounded-md bg-zinc-100 text-zinc-800 capitalize">{{ str_replace('_', ' ', $listing->condition) }}</span>
                        @endif
                        <span>•</span>
                        <span>{{ $listing->pickup_city ?? 'Bangalore' }}, {{ $listing->pickup_state ?? 'India' }}</span>
                    </div>
                </div>

                <!-- Pricing Block -->
                <div class="p-4 bg-zinc-50 rounded-2xl border border-zinc-200/80 space-y-2">
                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl sm:text-4xl font-black text-zinc-950 tracking-tight">
                            ₹{{ number_format($listing->price) }}
                        </span>
                        @if($listing->original_price && $listing->original_price > $listing->price)
                            <span class="text-base text-zinc-400 line-through font-medium">
                                ₹{{ number_format($listing->original_price) }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-xs font-black">
                                {{ $discountPercent }}% OFF
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 text-xs font-bold text-zinc-600 pt-1">
                        <span class="text-emerald-700">{{ $isFreeShip ? 'Free Delivery' : '₹' . number_format($listing->shipping_charges) . ' Shipping' }}</span>
                        <span>•</span>
                        <span>Doorstep Insured Transit</span>
                    </div>
                </div>

                <!-- Mercari Primary Action Stack -->
                @if($listing->listing_status === 'sold')
                    <div class="p-4 bg-zinc-100 rounded-xl text-center">
                        <p class="text-sm font-extrabold text-zinc-600">This item has been purchased by another buyer.</p>
                        <a href="/listings?category={{ $listing->category }}" class="mt-2 inline-block text-xs font-bold text-[#e5c120] hover:underline">
                            Explore similar listings &rarr;
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        
                        <!-- Button A: Buy Now (Brand Yellow CTA) -->
                        <a href="/checkout?listing_id={{ $listing->id }}" 
                           class="w-full h-13 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-base rounded-2xl shadow-sm flex items-center justify-center gap-2 transition-transform active:scale-98 cursor-pointer select-none">
                            <svg class="w-5 h-5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            <span>Buy Now (Escrow Protected)</span>
                        </a>

                        <!-- Button B: Make an Offer (White pill with border) -->
                        <button type="button" 
                                @click="window.dispatchEvent(new CustomEvent('open-offer-modal'))"
                                class="w-full h-12 bg-white border-2 border-zinc-950 text-zinc-950 hover:bg-zinc-50 font-extrabold text-sm rounded-2xl flex items-center justify-center gap-2 transition-transform active:scale-98 cursor-pointer shadow-2xs">
                            <svg class="w-4 h-4 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Make an Offer</span>
                        </button>

                        <!-- Button C: Add to Cart -->
                        <button type="button" 
                                @click="addToCart"
                                class="w-full py-3 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            <span>Add to Cart</span>
                        </button>

                    </div>
                @endif

                <!-- Buyer Protection Escrow Guarantee Card (Mercari Trust Element) -->
                <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-amber-900">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 0 1 2.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0 1 10 1.944ZM11 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm0-7a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0V7Z" clip-rule="evenodd"/>
                        </svg>
                        <span>X-Buy 7-Day Escrow Protection</span>
                    </div>
                    <p class="text-[11px] text-zinc-600 leading-relaxed font-medium">
                        Your payment is held securely in X-Buy Escrow. We do not release money to the seller until you receive the package, inspect it for <strong>7 days</strong>, and confirm it works.
                    </p>
                    <a href="/p/buyer-protection" class="text-[11px] font-bold text-amber-800 hover:underline block pt-1">
                        Learn how escrow protects you &rarr;
                    </a>
                </div>

                <!-- Verified Seller Profile Card -->
                <div class="rounded-2xl border border-zinc-200 p-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-[#FDD835] text-black font-extrabold text-sm flex items-center justify-center border border-black/10 shrink-0">
                            {{ substr($seller?->shop_name ?? 'Seller', 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-extrabold text-zinc-950 truncate">
                                {{ $seller?->shop_name ?? 'Verified Seller' }}
                            </h4>
                            <div class="flex items-center gap-1.5 text-xs text-amber-600 font-bold mt-0.5">
                                <span>★ {{ number_format($metrics?->star_rating ?? 4.9, 1) }}</span>
                                <span class="text-zinc-400 font-normal">({{ $metrics?->total_ratings ?? 14 }} ratings)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold">
                            ✓ Verified Seller
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-800 border border-blue-200 text-[10px] font-bold">
                            ⚡ Fast Shipper (24h)
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-bold">
                            ★ Top Rated
                        </span>
                    </div>

                    <!-- Chat with seller link -->
                    @auth
                        <a href="/p/contact-us?seller_id={{ $seller?->id }}&listing_id={{ $listing->id }}" 
                           class="w-full py-2 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-xs font-bold text-zinc-800 flex items-center justify-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-1.154-.784c.15-.718.375-1.59.61-2.42A7.906 7.906 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                            <span>Message Seller</span>
                        </a>
                    @else
                        <a href="/login" 
                           class="w-full py-2 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-xs font-bold text-zinc-800 flex items-center justify-center gap-1.5 transition-colors">
                            <span>Log in to Chat</span>
                        </a>
                    @endauth
                </div>

            </div>

        </div>

    </div>

    <!-- Section: Related Gear -->
    @if(isset($similarListings) && $similarListings->count() > 0)
        <div class="mt-16 pt-10 border-t border-zinc-200 space-y-5">
            <h3 class="text-xl font-extrabold text-zinc-950 tracking-tight">Similar Hardware You Might Like</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($similarListings as $similar)
                    <x-frontend.product-card :listing="$similar" />
                @endforeach
            </div>
        </div>
    @endif

    <!-- Offer Modal Component Attached -->
    <x-frontend.offer-modal :listing="$listing" />

</div>
@endsection
