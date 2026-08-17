@extends('layouts.app')

@section('title', $listing->seo_title)

@section('content')
<div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8 py-10"
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
         toastMessage: '',
         showToast: false,
         showShareModal: false,
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
             this.toastMessage = 'Listing link copied to clipboard!';
             this.showToast = true;
             setTimeout(() => { this.showToast = false; }, 2000);
         },
         shareTo(platform) {
             const url = encodeURIComponent(window.location.href);
             const text = encodeURIComponent(document.title);
             let shareUrl = '';
             if (platform === 'facebook') {
                 shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
             } else if (platform === 'messenger') {
                 shareUrl = `https://www.facebook.com/dialog/send?link=${url}&app_id=291494419107518&redirect_uri=${url}`;
             } else if (platform === 'x') {
                 shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
             } else if (platform === 'pinterest') {
                 shareUrl = `https://pinterest.com/pin/create/button/?url=${url}&description=${text}`;
             } else if (platform === 'whatsapp') {
                 shareUrl = `https://api.whatsapp.com/send?text=${text}%20${url}`;
             } else if (platform === 'email') {
                 shareUrl = `mailto:?subject=${text}&body=${url}`;
             }
             window.open(shareUrl, '_blank');
         }
     }">
    <!-- Breadcrumbs -->
    <nav class="mb-6 flex items-center space-x-2 text-xs font-semibold text-zinc-400 uppercase tracking-wider">
        <a href="/" class="hover:text-zinc-700 transition-colors">Home</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-300"></i>
        <a href="/listings" class="hover:text-zinc-700 transition-colors">Listings</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-300"></i>
        <span class="text-zinc-600">{{ $listing->title }}</span>
    </nav>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
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

                    <!-- Floating Action Buttons (Wishlist & Share) -->
                    <div class="absolute top-4 right-4 flex flex-col gap-2.5 z-20">
                        <!-- Wishlist Button -->
                        <button @click="toggleLike" class="w-10 h-10 bg-white hover:bg-zinc-50 border border-zinc-200/80 rounded-xl flex items-center justify-center shadow-xs transition-all duration-200">
                            <svg class="w-5 h-5 transition-colors duration-200" :class="isLiked ? 'text-red-500 fill-red-500 stroke-red-500' : 'text-zinc-700'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                <path x-show="!isLiked" d="M16 16h6M19 13v6" />
                            </svg>
                        </button>
                        <!-- Share Button -->
                        <button @click="showShareModal = true" class="w-10 h-10 bg-white hover:bg-zinc-50 border border-zinc-200/80 rounded-xl flex items-center justify-center shadow-xs transition-all duration-200">
                            <svg class="w-5 h-5 text-zinc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m22 2-7 20-4-9-9-4Z" />
                                <path d="M22 2 11 13" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Details & Description Sections -->
            <div class="mt-8 pt-8 space-y-8 pl-2">
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
                <div class="pt-8">
                    <h2 class="text-xl font-bold text-zinc-900 mb-4">Description</h2>
                    <p class="text-zinc-700 text-sm leading-relaxed whitespace-pre-line">{{ $listing->description }}</p>
                </div>

                <!-- Seller Profile Section -->
                <div class="pt-8 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-full overflow-hidden border border-zinc-200 shadow-sm shrink-0">
                            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=100" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 hover:text-indigo-600 cursor-pointer">{{ $listing->seller ? $listing->seller->shop_name : 'Gadget Zone' }}</h3>
                            <span class="text-xs text-zinc-400 block mt-0.5">&#64;{{ Str::slug($listing->seller ? $listing->seller->shop_name : 'Gadget Zone') }}</span>
                            
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


                </div>
            </div>
        </div>

        <!-- Right Column: Details and Checkout Actions (Span 5) -->
        <div class="lg:col-span-5 flex flex-col justify-between">
            <div class="space-y-6">
                <!-- Title & Tags -->
                <div>
                    <h1 class="text-2xl font-bold text-zinc-950 tracking-tight leading-snug">{{ $listing->title }}</h1>
                    <div class="flex items-center gap-2 text-sm font-normal text-zinc-500 mt-1.5">
                        <span class="capitalize">{{ $listing->category }}</span>
                        <span>|</span>
                        <span>Grade {{ $listing->grade }}</span>
                        <span>|</span>
                        <span>{{ $listing->brand ?? 'ASUS' }}</span>
                    </div>
                </div>

                <!-- Pricing & protection -->
                <div class="pt-2 pb-4">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-zinc-950 tracking-tight">₹{{ number_format($listing->price) }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-sm font-normal text-zinc-700 mt-1">
                        <span>+₹85 Buyer Protection fee</span>
                        <div class="w-4 h-4 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-2.5 h-2.5 text-blue-600"></i>
                        </div>
                    </div>
                </div>


                <!-- CTA Action Buttons -->
                <div class="space-y-3">
                    <button class="w-full h-11 bg-zinc-50 border border-zinc-300 hover:bg-zinc-100 text-zinc-800 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        Add to cart
                    </button>

                    <button class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">
                        Buy now
                    </button>

                    <button class="w-full h-12 bg-zinc-900 hover:bg-black text-white text-sm font-bold rounded-lg flex items-center justify-center gap-1 transition-all shadow-sm">
                        <span class="font-extrabold tracking-wide">Razorpay</span> Checkout
                    </button>

                    <div class="flex items-center justify-center gap-1 text-[10px] text-zinc-400 font-semibold mt-1">
                        <span>Secured by <strong>Razorpay</strong> (UPI, Cards, Netbanking)</span>
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-green-600"></i>
                    </div>
                </div>

                <div class="text-[11px] text-zinc-400 leading-normal leading-relaxed">
                    * By continuing to checkout, you agree to the <a href="#" class="underline hover:text-zinc-650">X-Buy Privacy Policy</a> and <a href="#" class="underline hover:text-zinc-650">Terms of Service</a>
                </div>

                <!-- Trust badges -->
                <div class="space-y-5 pt-3">
                    <div class="flex gap-3 text-sm">
                        <i data-lucide="shopping-cart" class="w-5 h-5 text-zinc-500 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="text-zinc-950 block font-bold">1 person has this item in their cart</strong>
                            <span class="text-zinc-500 block mt-0.5">There's only one. Grab it before someone else does.</span>
                        </div>
                    </div>

                    <div class="flex gap-3 text-sm">
                        <i data-lucide="shield-check" class="w-5 h-5 text-zinc-500 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="text-zinc-950 block font-bold">Buyer Protection</strong>
                            <span class="text-zinc-500 block mt-0.5">Receive your item as described, or get your money back. <a href="#" class="text-indigo-650 font-bold hover:underline">Learn more</a></span>
                        </div>
                    </div>
                </div>

                <!-- Delivery, Payments & Sell Actions (Right Column Stack) -->
                <div class="pt-6 space-y-6">
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
                    <div class="pt-6">
                        <h4 class="text-base font-extrabold text-zinc-900 mb-3">Payment</h4>
                        <!-- Payments Card Grid -->
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <div class="h-6 w-11 bg-white border border-zinc-200 rounded flex items-center justify-center p-1 shadow-xs">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="h-6 w-11 bg-white border border-zinc-200 rounded flex items-center justify-center p-1 shadow-xs">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="h-6 w-11 bg-white border border-zinc-200 rounded flex items-center justify-center p-1 shadow-xs">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/American_Express_logo_%282018%29.svg" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="h-6 w-11 bg-white border border-zinc-200 rounded flex items-center justify-center p-1 shadow-xs">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/85/Discover_Card_logo.svg" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="h-6 w-11 bg-white border border-zinc-200 rounded flex items-center justify-center p-1 shadow-xs">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="h-6 w-11 bg-white border border-zinc-200 rounded flex items-center justify-center p-1 shadow-xs">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/UPI-Logo-vector.svg" class="max-h-full max-w-full object-contain">
                            </div>
                        </div>
                        <div class="text-[10px] text-zinc-400 font-semibold">
                            Secured by <strong>Razorpay</strong>
                        </div>
                    </div>

                    <!-- Sell Yours CTA -->
                    <div class="pt-6">
                        <button class="w-full h-11 bg-white border border-indigo-600 hover:bg-indigo-50 text-indigo-600 text-xs font-bold rounded-lg transition-all shadow-xs">
                            Have a similar item? Sell yours
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Item Modal -->
    <div x-show="showShareModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
         
        <!-- Modal Card -->
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl relative"
             @click.away="showShareModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
             
            <!-- Header -->
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-zinc-950">Share item</h3>
                <button @click="showShareModal = false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-zinc-100 transition-colors text-zinc-500 hover:text-zinc-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Product Preview Box -->
            <div class="flex gap-4 p-3 bg-zinc-50/50 rounded-xl border border-zinc-100 mb-6">
                <div class="w-16 h-16 bg-white border border-zinc-200 rounded-lg overflow-hidden shrink-0 flex items-center justify-center">
                    <img src="{{ $listing->primary_image_url ?? 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400' }}" 
                         class="max-w-full max-h-full object-contain">
                </div>
                <div class="flex flex-col justify-center">
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wide">{{ $listing->brand ?? 'ASUS' }}</p>
                    <h4 class="text-sm font-semibold text-zinc-800 line-clamp-2 leading-snug mt-0.5">{{ $listing->title }}</h4>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="space-y-3">
                <!-- Copy Link Button -->
                <button @click="copyShareLink()" class="w-full h-11 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-sm font-semibold rounded-xl flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="link" class="w-4 h-4 text-zinc-650"></i>
                    <span>Copy Link</span>
                </button>
                
                <!-- Social Sharing Grid -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button @click="shareTo('facebook')" class="h-11 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-800 text-xs font-semibold flex items-center gap-3 px-4 transition-all">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        <span>Facebook</span>
                    </button>
                    <button @click="shareTo('messenger')" class="h-11 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-800 text-xs font-semibold flex items-center gap-3 px-4 transition-all">
                        <svg class="w-4 h-4 text-indigo-500 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.975 12-11.11S18.627 0 12 0zm1.118 14.932l-2.82-3.006-5.5 3.006 6.052-6.431 2.82 3.006 5.5-3.006-6.052 6.431z"/></svg>
                        <span>Messenger</span>
                    </button>
                    <button @click="shareTo('x')" class="h-11 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-800 text-xs font-semibold flex items-center gap-3 px-4 transition-all">
                        <svg class="w-4 h-4 text-black shrink-0 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        <span>X (Twitter)</span>
                    </button>
                    <button @click="shareTo('pinterest')" class="h-11 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-800 text-xs font-semibold flex items-center gap-3 px-4 transition-all">
                        <svg class="w-4 h-4 text-red-600 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.27 1.042-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                        <span>Pinterest</span>
                    </button>
                    <button @click="shareTo('whatsapp')" class="h-11 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-800 text-xs font-semibold flex items-center gap-3 px-4 transition-all">
                        <svg class="w-4 h-4 text-green-500 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.457L0 24zm6.59-4.846c1.6-1.155 3.483-1.772 5.407-1.771 5.314.003 9.639 4.33 9.642 9.647.001 2.574-1.001 4.995-2.825 6.82-1.824 1.823-4.244 2.823-6.82 2.822-5.315-.003-9.641-4.33-9.643-9.647 0-1.926.504-3.81 1.458-5.412L2.73 21.28l4.917-1.126z"/></svg>
                        <span>WhatsApp</span>
                    </button>
                    <button @click="shareTo('email')" class="h-11 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-800 text-xs font-semibold flex items-center gap-3 px-4 transition-all">
                        <i data-lucide="mail" class="w-4 h-4 text-zinc-650 shrink-0"></i>
                        <span>Email</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Floating Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         x-cloak
         class="fixed bottom-6 right-6 z-50 bg-zinc-900 text-white text-xs font-semibold px-4 py-3 rounded-xl shadow-lg border border-zinc-800 flex items-center gap-2">
        <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
        <span x-text="toastMessage"></span>
    </div>
</div>
@endsection
