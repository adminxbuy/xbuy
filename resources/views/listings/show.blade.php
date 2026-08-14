@extends('layouts.app')

@section('title', $listing->seo_title)

@section('content')
<div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumbs -->
    <nav class="mb-6 flex items-center space-x-2 text-xs font-semibold text-zinc-400 uppercase tracking-wider">
        <a href="/" class="hover:text-zinc-700 transition-colors">Home</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-300"></i>
        <a href="/listings" class="hover:text-zinc-700 transition-colors">Listings</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-300"></i>
        <span class="text-zinc-600">{{ $listing->title }}</span>
    </nav>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8"
         x-data="{
             images: [
                 @foreach($listing->images as $img)
                     '{{ $img->image_url }}',
                 @endforeach
                 @if($listing->images->count() < 5)
                     @for($i = $listing->images->count(); $i < 5; $i++)
                         '{{ $listing->primary_image_url ?? 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400' }}',
                     @endfor
                 @endif
             ],
             activeIndex: 0,
             likesCount: 0,
             isLiked: false,
             nextImage() {
                 this.activeIndex = (this.activeIndex + 1) % this.images.length;
             },
             prevImage() {
                 this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length;
             },
             toggleLike() {
                 this.isLiked = !this.isLiked;
                 this.likesCount += this.isLiked ? 1 : -1;
             },
             copyShareLink() {
                 navigator.clipboard.writeText(window.location.href);
                 alert('Listing link copied to clipboard!');
             }
         }">
        
        <!-- Left Column: Interactive Image Gallery (Span 7) -->
        <div class="lg:col-span-7 flex flex-col">
            <div class="grid grid-cols-12 gap-4">
                <!-- Vertical Thumbnails -->
                <div class="col-span-2 flex flex-col gap-3 max-h-[480px] overflow-y-auto scrollbar-none items-center">
                    <template x-for="(img, idx) in images" :key="idx">
                        <div class="w-full aspect-square rounded-lg border-2 overflow-hidden cursor-pointer transition-all duration-200 hover:border-zinc-400"
                             :class="activeIndex === idx ? 'border-indigo-600' : 'border-transparent bg-zinc-50'"
                             @click="activeIndex = idx">
                            <img :src="img" 
                                 :style="idx > 0 ? 'filter: hue-rotate(' + (idx * 45) + 'deg);' : ''" 
                                 class="w-full h-full object-cover">
                        </div>
                    </template>
                    <div class="text-zinc-400 hover:text-zinc-600 cursor-pointer pt-1 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5"></i>
                    </div>
                </div>

                <!-- Main Preview Image -->
                <div class="col-span-10 relative bg-zinc-50 rounded-xl border border-zinc-200/60 aspect-[4/3] flex items-center justify-center overflow-hidden">
                    <!-- Left Arrow -->
                    <button @click="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 border border-zinc-200 hover:border-zinc-300 rounded-full flex items-center justify-center shadow-sm text-zinc-700 hover:text-indigo-600 transition-all z-10">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>

                    <!-- Active Image -->
                    <img :src="images[activeIndex]" 
                         :style="activeIndex > 0 ? 'filter: hue-rotate(' + (activeIndex * 45) + 'deg);' : ''" 
                         class="max-w-full max-h-full object-contain select-none transition-all duration-300">

                    <!-- Right Arrow -->
                    <button @click="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 border border-zinc-200 hover:border-zinc-300 rounded-full flex items-center justify-center shadow-sm text-zinc-700 hover:text-indigo-600 transition-all z-10">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Gallery Toolbar -->
            <div class="flex items-center gap-8 pl-2 mt-5 text-sm font-bold text-zinc-500">
                <button @click="toggleLike" class="flex items-center gap-2 hover:text-indigo-600 transition-colors">
                    <i data-lucide="heart" class="w-5 h-5 transition-colors" :class="isLiked ? 'text-red-500 fill-red-500' : ''"></i>
                    <span>Like (<span x-text="likesCount"></span>)</span>
                </button>

                <button @click="copyShareLink" class="flex items-center gap-2 hover:text-indigo-600 transition-colors">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                    <span>Share</span>
                </button>

                <button class="flex items-center gap-2 hover:text-indigo-600 transition-colors">
                    <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                    <span>More</span>
                </button>
            </div>

            <!-- Details & Description Sections -->
            <div class="border-t border-zinc-200 mt-8 pt-8 space-y-8 pl-2">
                <!-- Details Section -->
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 mb-6">Details</h2>
                    <div class="space-y-4 max-w-lg">
                        <!-- Condition -->
                        <div class="flex items-center text-sm">
                            <span class="w-32 text-zinc-400 font-bold flex items-center gap-1">
                                Condition <i data-lucide="help-circle" class="w-3.5 h-3.5 cursor-pointer"></i>
                            </span>
                            <span class="text-zinc-800 font-bold">Grade {{ $listing->grade }}</span>
                        </div>
                        
                        <!-- Brand -->
                        <div class="flex items-center text-sm">
                            <span class="w-32 text-zinc-400 font-bold">Brand</span>
                            <a href="#" class="text-indigo-600 font-bold hover:underline underline decoration-zinc-300">{{ $listing->brand ?? 'NVIDIA' }}</a>
                        </div>
                        
                        <!-- Category -->
                        <div class="flex items-start text-sm">
                            <span class="w-32 text-zinc-400 font-bold shrink-0">Category</span>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 text-indigo-600 font-bold">
                                    <a href="#" class="hover:underline underline decoration-zinc-300 capitalize">{{ $listing->category }}</a>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-400"></i>
                                    <a href="#" class="hover:underline underline decoration-zinc-300 capitalize">{{ $listing->brand ?? 'NVIDIA' }}</a>
                                </div>
                                <a href="#" class="text-indigo-650 font-bold hover:underline underline decoration-zinc-300 text-xs">{{ $listing->title }}</a>
                            </div>
                        </div>

                        <!-- Model -->
                        <div class="flex items-center text-sm">
                            <span class="w-32 text-zinc-400 font-bold flex items-center gap-1">
                                Model <i data-lucide="help-circle" class="w-3.5 h-3.5 cursor-pointer"></i>
                            </span>
                            <span class="text-zinc-800 font-bold">{{ $listing->model_name ?? 'RTX 4090 FE' }}</span>
                        </div>

                        <!-- Posted Date -->
                        <div class="flex items-center text-sm">
                            <span class="w-32 text-zinc-400 font-bold">Posted</span>
                            <span class="text-zinc-800 font-bold">{{ $listing->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="border-t border-zinc-150 pt-8">
                    <h2 class="text-xl font-bold text-zinc-900 mb-4">Description</h2>
                    <p class="text-zinc-700 text-sm leading-relaxed whitespace-pre-line">{{ $listing->description }}</p>
                </div>

                <!-- Seller Profile Section -->
                <div class="border-t border-zinc-150 pt-8 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full overflow-hidden border border-zinc-200 shadow-sm shrink-0">
                            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=100" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 hover:text-indigo-600 cursor-pointer">{{ $listing->seller ? $listing->seller->shop_name : 'Gadget Zone' }}</h3>
                            <span class="text-xs text-zinc-400 block mt-0.5">@&#123;&#123; Str::slug($listing->seller ? $listing->seller->shop_name : 'Gadget Zone') &#125;&#125;</span>
                            
                            <!-- Rating and Listed Statistics -->
                            <div class="flex items-center gap-2 mt-1.5 text-xs text-zinc-500 font-semibold">
                                <span class="text-amber-500 flex items-center">
                                    &#9733;&#9733;&#9733;&#9733;&#9733;
                                </span>
                                <span>{{ $listing->seller->metrics->star_rating ?? 4.5 }} Rating ({{ $listing->seller->metrics->total_ratings ?? 1 }} reviews)</span>
                                <span>|</span>
                                <span>{{ $listing->seller ? $listing->seller->listings()->count() : 5 }} listed</span>
                                <span>|</span>
                                <span>{{ $listing->seller->metrics->completed_orders ?? 4 }} sales</span>
                            </div>

                            <!-- Verified Badge -->
                            <div class="flex items-center gap-1 mt-2 text-xs font-bold text-blue-600">
                                <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                                <span>Profile verified</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bundle Discounts Panel -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-zinc-900">Bundle discounts from {{ $listing->seller ? $listing->seller->shop_name : 'Gadget Zone' }}</h4>
                        <div class="bg-purple-50 border border-purple-150 rounded-xl p-4 space-y-4 shadow-xs">
                            <p class="text-xs text-purple-750 font-semibold">Add items from this seller to your cart to unlock discounts—plus potential savings on shipping!</p>
                            
                            <!-- Bundle progress timeline -->
                            <div class="relative pt-1">
                                <div class="h-1.5 bg-zinc-200 rounded-full w-full relative">
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-indigo-600 rounded-full border-2 border-white shadow-sm"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-zinc-500 font-bold mt-2">
                                    <div class="text-left">
                                        <span>1 item</span>
                                        <span class="block text-zinc-400">0% off</span>
                                    </div>
                                    <div class="text-center">
                                        <span>2 items</span>
                                        <span class="block text-indigo-600">5% off</span>
                                    </div>
                                    <div class="text-right">
                                        <span>3+ items</span>
                                        <span class="block text-indigo-600">10% off</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Details and Checkout Actions (Span 5) -->
        <div class="lg:col-span-5 flex flex-col justify-between">
            <div class="space-y-6">
                <!-- Title & Tags with Wishlist -->
                <div class="flex items-start justify-between gap-6">
                    <div>
                        <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest">{{ $listing->brand ?? 'NVIDIA' }}</span>
                        <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight leading-snug mt-1">{{ $listing->title }}</h1>
                        <div class="flex items-center gap-2 text-xs font-semibold text-zinc-400 mt-2">
                            <span class="capitalize">{{ $listing->category }}</span>
                            <span>|</span>
                            <span>Grade {{ $listing->grade }}</span>
                            <span>|</span>
                            <span class="text-indigo-600 hover:underline cursor-pointer">{{ $listing->brand ?? 'NVIDIA' }}</span>
                        </div>
                    </div>

                    <!-- Heart / Likes Wishlist Column -->
                    <div class="flex flex-col items-center shrink-0">
                        <button @click="toggleLike" 
                                class="w-12 h-12 rounded-full border border-zinc-200 hover:border-zinc-300 flex items-center justify-center transition-all bg-white shadow-sm text-zinc-650"
                                :class="isLiked ? 'text-red-500 border-red-200' : 'text-zinc-600'">
                            <template x-if="!isLiked">
                                <i data-lucide="heart" class="w-5 h-5"></i>
                            </template>
                            <template x-if="isLiked">
                                <i data-lucide="heart" class="w-5 h-5 text-red-500 fill-red-500"></i>
                            </template>
                        </button>
                        <span class="text-[10px] font-bold text-zinc-500 mt-1 select-none">
                            <span x-text="likesCount"></span> Likes
                        </span>
                    </div>
                </div>

                <!-- Pricing & protection -->
                <div class="border-t border-b border-zinc-150 py-4">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-extrabold text-zinc-900">₹{{ number_format($listing->price) }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-650 mt-1">
                        <span>+₹85 Buyer Protection fee</span>
                        <i data-lucide="info" class="w-3.5 h-3.5 cursor-pointer"></i>
                    </div>
                </div>

                <!-- Purple discount banner -->
                <div class="bg-purple-50 border border-purple-150 rounded-xl p-3.5 flex items-center justify-between text-xs text-purple-750 font-semibold shadow-xs">
                    <span>Up to <strong class="text-purple-900 font-extrabold">10% off</strong> when you bundle items from this seller</span>
                </div>

                <!-- CTA Action Buttons -->
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <button class="h-11 bg-zinc-50 border border-zinc-300 hover:bg-zinc-100 text-zinc-800 text-xs font-bold rounded-lg transition-all">
                            Make offer
                        </button>
                        <button class="h-11 bg-zinc-50 border border-zinc-300 hover:bg-zinc-100 text-zinc-800 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            Add to cart
                        </button>
                    </div>

                    <button class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
                        Buy now
                    </button>

                    <button class="w-full h-12 bg-zinc-900 hover:bg-black text-white text-sm font-bold rounded-lg flex items-center justify-center gap-1 transition-all shadow-sm">
                        <span class="text-blue-400 italic">PayPal</span> Checkout
                    </button>

                    <div class="flex items-center justify-center gap-1 text-[10px] text-zinc-400 font-semibold mt-1">
                        <span>Pay in 4 payments for eligible items with <strong>PayPal</strong></span>
                        <i data-lucide="info" class="w-3.5 h-3.5 cursor-pointer"></i>
                    </div>
                </div>

                <div class="text-[11px] text-zinc-400 leading-normal leading-relaxed">
                    * By continuing to checkout, you agree to the <a href="#" class="underline hover:text-zinc-650">X-Buy Privacy Policy</a> and <a href="#" class="underline hover:text-zinc-650">Terms of Service</a>
                </div>

                <!-- Trust badges -->
                <div class="space-y-3 pt-2">
                    <div class="flex gap-3 bg-amber-50 border border-amber-250 p-3.5 rounded-xl text-xs">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="text-zinc-900 block font-bold">1 person has this item in their cart</strong>
                            <span class="text-zinc-500 block mt-0.5">There's only one. Grab it before someone else does.</span>
                        </div>
                    </div>

                    <div class="flex gap-3 bg-zinc-50 border border-zinc-200 p-3.5 rounded-xl text-xs">
                        <i data-lucide="shield-check" class="w-5 h-5 text-green-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="text-zinc-900 block font-bold">Buyer Protection</strong>
                            <span class="text-zinc-500 block mt-0.5">Receive your item as described, or get your money back. <a href="#" class="text-indigo-650 font-bold hover:underline">Learn more</a></span>
                        </div>
                    </div>
                </div>

                <!-- Delivery, Payments & Sell Actions (Right Column Stack) -->
                <div class="border-t border-zinc-150 pt-6 space-y-6">
                    <!-- Delivery -->
                    <div>
                        <h4 class="text-base font-extrabold text-zinc-900 mb-3">Delivery</h4>
                        <div class="space-y-2 text-xs font-semibold text-zinc-500">
                            <div class="flex justify-between">
                                <span>From</span>
                                <span class="text-zinc-800 font-bold">{{ $listing->pickup_city }}, {{ $listing->pickup_state }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="flex items-center gap-1">Shipping <i data-lucide="help-circle" class="w-3.5 h-3.5 cursor-pointer"></i></span>
                                <span class="text-zinc-800 font-bold">₹{{ number_format($listing->shipping_charges) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="border-t border-zinc-150 pt-6">
                        <h4 class="text-base font-extrabold text-zinc-900 mb-3">Payment</h4>
                        <!-- Payments Card Grid -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-2.5 py-1 bg-zinc-100 rounded text-[10px] font-extrabold text-zinc-600 uppercase tracking-widest border border-zinc-200">Visa</span>
                            <span class="px-2.5 py-1 bg-zinc-100 rounded text-[10px] font-extrabold text-zinc-600 uppercase tracking-widest border border-zinc-200">MC</span>
                            <span class="px-2.5 py-1 bg-zinc-100 rounded text-[10px] font-extrabold text-zinc-600 uppercase tracking-widest border border-zinc-200">Amex</span>
                            <span class="px-2.5 py-1 bg-zinc-100 rounded text-[10px] font-extrabold text-zinc-650 italic border border-zinc-200">PayPal</span>
                            <span class="px-2.5 py-1 bg-zinc-100 rounded text-[10px] font-extrabold text-blue-600 uppercase tracking-widest border border-zinc-200">UPI</span>
                        </div>
                        <div class="text-[10px] text-zinc-400 font-semibold">
                            Pay in 4 payments for eligible items with <strong>PayPal</strong>
                        </div>
                    </div>

                    <!-- Sell Yours CTA -->
                    <div class="border-t border-zinc-150 pt-6">
                        <button class="w-full h-11 bg-white border border-indigo-600 hover:bg-indigo-50 text-indigo-600 text-xs font-bold rounded-lg transition-all shadow-xs">
                            Have a similar item? Sell yours
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
