@extends('layouts.app')

@section('title', 'X-Buy | Buy & Sell Verified PC Hardware with 7-Day Escrow Protection')

@php
    $recentListings = \App\Models\Listing::active()->with(['images', 'seller', 'brand'])->latest()->take(10)->get();
    
    // Top Brands from DB or standard list
    $popularBrands = \App\Models\Brand::take(8)->get();
    if ($popularBrands->isEmpty()) {
        $popularBrands = collect([
            (object)['name' => 'NVIDIA', 'slug' => 'nvidia', 'logo_url' => ''],
            (object)['name' => 'ASUS', 'slug' => 'asus', 'logo_url' => ''],
            (object)['name' => 'MSI', 'slug' => 'msi', 'logo_url' => ''],
            (object)['name' => 'Intel', 'slug' => 'intel', 'logo_url' => ''],
            (object)['name' => 'AMD', 'slug' => 'amd', 'logo_url' => ''],
            (object)['name' => 'Gigabyte', 'slug' => 'gigabyte', 'logo_url' => ''],
            (object)['name' => 'Corsair', 'slug' => 'corsair', 'logo_url' => ''],
            (object)['name' => 'Sony', 'slug' => 'sony', 'logo_url' => ''],
        ]);
    }

    $topCategories = \App\Models\Category::active()->parentOnly()->orderBy('sort_order')->take(4)->get();
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-10">

    <!-- Section 1: Hero Promo Banner Slider (Mercari Auto-play Carousel) -->
    <div class="w-full relative overflow-hidden rounded-2xl border border-zinc-200/80 shadow-xs bg-zinc-950 text-white"
         x-data="{
             current: 0,
             slides: [
                 {
                     title: 'Sell Your PC Hardware with 0% Seller Fees',
                     subtitle: 'List in 3 minutes. Zero commission promotion on all graphics cards and processors this month.',
                     cta: 'Start Selling Today',
                     link: '/dashboard/listings/create',
                     badge: 'Limited Time Offer 🔥',
                     bgClass: 'from-zinc-900 via-zinc-900 to-amber-950/40',
                     accentColor: '#FDD835'
                 },
                 {
                     title: '100% Escrow Buyer Protection Guaranteed',
                     subtitle: 'Stress-test your GPU and CPU for 7 full days before seller gets paid. Doorstep insured courier.',
                     cta: 'Explore Verified Gear',
                     link: '/listings',
                     badge: 'Safe & Verified 🛡️',
                     bgClass: 'from-zinc-900 via-neutral-900 to-emerald-950/40',
                     accentColor: '#10B981'
                 },
                 {
                     title: 'Verified RTX 40-Series GPUs In Stock',
                     subtitle: 'Benchmark verified, zero coil-whine certified pre-owned GPUs with original invoices and warranties.',
                     cta: 'Browse Graphics Cards',
                     link: '/listings?category=gpu',
                     badge: 'Top Picks ⚡',
                     bgClass: 'from-zinc-900 via-zinc-900 to-blue-950/40',
                     accentColor: '#3B82F6'
                 }
             ],
             timer: null,
             init() {
                 this.startTimer();
             },
             startTimer() {
                 this.timer = setInterval(() => {
                     this.current = (this.current + 1) % this.slides.length;
                 }, 5000);
             },
             goTo(idx) {
                 clearInterval(this.timer);
                 this.current = idx;
                 this.startTimer();
             }
         }">
        
        <div class="relative min-h-[260px] sm:min-h-[300px] md:min-h-[340px] flex items-center">
            <template x-for="(slide, index) in slides" :key="index">
                <div x-show="current === index"
                     x-transition:enter="transition ease-out duration-400"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 -translate-x-4"
                     class="absolute inset-0 p-8 sm:p-12 md:p-14 flex flex-col justify-center bg-gradient-to-r"
                     :class="slide.bgClass">
                    
                    <div class="max-w-2xl space-y-3">
                        <span class="inline-block px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-[11px] font-extrabold uppercase tracking-wider text-[#FDD835]"
                              x-text="slide.badge"></span>
                        
                        <h2 class="text-2xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight"
                            x-text="slide.title"></h2>
                        
                        <p class="text-xs sm:text-sm md:text-base text-zinc-300 font-medium max-w-xl leading-relaxed"
                           x-text="slide.subtitle"></p>
                        
                        <div class="pt-3">
                            <a :href="slide.link" 
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-sm shadow-md transition-transform active:scale-95">
                                <span x-text="slide.cta"></span>
                                <svg class="w-4 h-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        <!-- Slider Carousel Dot Controls -->
        <div class="absolute bottom-4 right-6 flex items-center gap-2 z-20">
            <template x-for="(slide, index) in slides" :key="index">
                <button @click="goTo(index)" 
                        class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                        :class="current === index ? 'w-8 bg-[#FDD835]' : 'w-2 bg-white/40 hover:bg-white/70'"
                        :aria-label="'Go to slide ' + (index + 1)"></button>
            </template>
        </div>
    </div>

    <!-- Section 2: Three-Card Category Grid (Mercari High-Velocity Visual Teasers) -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">Top Hardware Categories</h3>
            <a href="/listings" class="text-xs font-bold text-[#e5c120] hover:underline flex items-center gap-1">
                <span>View all catalog</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Teaser 1: GPUs -->
            <a href="/listings?category=gpu" 
               class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-amber-500/10 via-zinc-50 to-white p-6 hover:shadow-lg hover:border-amber-400 transition-all duration-300">
                <div class="relative z-10 flex flex-col h-44 justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-100/80 px-2.5 py-0.5 rounded-full">High Demand</span>
                        <h4 class="text-2xl font-black text-zinc-950 mt-2 tracking-tight group-hover:text-amber-600 transition-colors">Graphics Cards</h4>
                        <p class="text-xs text-zinc-500 mt-1">RTX 4090, 4080, RX 7900 XTX & budget 1080p cards.</p>
                    </div>
                    <div class="flex items-center text-xs font-extrabold text-zinc-900 group-hover:translate-x-1 transition-transform">
                        <span>Shop GPUs &rarr;</span>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 w-32 h-32 opacity-15 group-hover:opacity-25 transition-opacity">
                    <svg class="w-full h-full text-zinc-950" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2zm0 2v8h16V8H4zm2 2h3v4H6v-4zm5 0h3v4h-3v-4z"/>
                    </svg>
                </div>
            </a>

            <!-- Teaser 2: CPUs -->
            <a href="/listings?category=cpu" 
               class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-blue-500/10 via-zinc-50 to-white p-6 hover:shadow-lg hover:border-blue-400 transition-all duration-300">
                <div class="relative z-10 flex flex-col h-44 justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-100/80 px-2.5 py-0.5 rounded-full">Gaming Power</span>
                        <h4 class="text-2xl font-black text-zinc-950 mt-2 tracking-tight group-hover:text-blue-600 transition-colors">Processors (CPUs)</h4>
                        <p class="text-xs text-zinc-500 mt-1">AMD Ryzen 7000/9000 & Intel Core i7/i9 processors.</p>
                    </div>
                    <div class="flex items-center text-xs font-extrabold text-zinc-900 group-hover:translate-x-1 transition-transform">
                        <span>Shop Processors &rarr;</span>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 w-32 h-32 opacity-15 group-hover:opacity-25 transition-opacity">
                    <svg class="w-full h-full text-zinc-950" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/>
                    </svg>
                </div>
            </a>

            <!-- Teaser 3: Motherboards & RAM -->
            <a href="/listings?category=motherboard" 
               class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-purple-500/10 via-zinc-50 to-white p-6 hover:shadow-lg hover:border-purple-400 transition-all duration-300">
                <div class="relative z-10 flex flex-col h-44 justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 bg-purple-100/80 px-2.5 py-0.5 rounded-full">Core Components</span>
                        <h4 class="text-2xl font-black text-zinc-950 mt-2 tracking-tight group-hover:text-purple-600 transition-colors">Motherboards & RAM</h4>
                        <p class="text-xs text-zinc-500 mt-1">B650, Z790 motherboards and high-speed DDR5 memory kits.</p>
                    </div>
                    <div class="flex items-center text-xs font-extrabold text-zinc-900 group-hover:translate-x-1 transition-transform">
                        <span>Shop Motherboards &rarr;</span>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 w-32 h-32 opacity-15 group-hover:opacity-25 transition-opacity">
                    <svg class="w-full h-full text-zinc-950" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 19v-3"/><path d="M10 19v-3"/><path d="M14 19v-3"/><path d="M18 19v-3"/><rect x="2" y="3" width="20" height="14" rx="2"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>

    <!-- Section 3: Four-Card Category Teasers (Mercari Style) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $fourCards = [
                ['name' => 'Complete Rigs', 'slug' => 'full_build', 'badge' => 'Pre-Built', 'desc' => 'Plug & Play PCs', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                ['name' => 'Fast Storage', 'slug' => 'storage', 'badge' => 'Gen4 / Gen5', 'desc' => 'NVMe SSDs & HDDs', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                ['name' => 'Power Supplies', 'slug' => 'psu', 'badge' => '80+ Gold/Plat', 'desc' => 'Modular PSUs', 'color' => 'bg-amber-50 text-amber-800 border-amber-200'],
                ['name' => 'Gaming Gear', 'slug' => 'peripheral', 'badge' => 'Peripherals', 'desc' => 'Mice, Keys & Audio', 'color' => 'bg-rose-50 text-rose-800 border-rose-200'],
            ];
        @endphp
        @foreach($fourCards as $fc)
            <a href="/listings?category={{ $fc['slug'] }}" 
               class="p-4 rounded-xl border border-zinc-200 bg-white hover:border-zinc-300 hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border {{ $fc['color'] }} inline-block mb-2">
                        {{ $fc['badge'] }}
                    </span>
                    <h5 class="text-sm font-extrabold text-zinc-950 group-hover:underline">{{ $fc['name'] }}</h5>
                    <p class="text-[11px] text-zinc-500 mt-0.5">{{ $fc['desc'] }}</p>
                </div>
                <div class="mt-4 pt-2 border-t border-zinc-100 flex items-center justify-between text-[11px] font-bold text-zinc-700 group-hover:text-black">
                    <span>Explore</span>
                    <span>&rarr;</span>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Section 4: 🛡️ X-Buy Escrow Buyer Protection 3-Pillar Banner -->
    <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-6 sm:p-8">
        <div class="max-w-3xl mb-6">
            <span class="text-xs font-black uppercase tracking-wider text-amber-800 bg-amber-200/60 px-3 py-1 rounded-full">
                Guaranteed Buyer Confidence
            </span>
            <h3 class="text-xl sm:text-2xl font-black text-zinc-950 mt-2 tracking-tight">
                Inspect for 7 Days Before The Seller Gets Paid
            </h3>
            <p class="text-xs sm:text-sm text-zinc-600 mt-1 font-medium leading-relaxed">
                We eliminate the risk of peer-to-peer buying. Every rupee you pay is held safely in escrow until your package arrives and passes all stress-test benchmarks.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
            <!-- Pillar 1 -->
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-white border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-zinc-950">100% Escrow Protection</h4>
                    <p class="text-xs text-zinc-600 mt-0.5 leading-relaxed">Payment is held securely in Razorpay Escrow. Funds are never wired directly to an unverified individual.</p>
                </div>
            </div>

            <!-- Pillar 2 -->
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-white border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-zinc-950">7-Day Testing Window</h4>
                    <p class="text-xs text-zinc-600 mt-0.5 leading-relaxed">Run 3DMark, Furmark, or Cinebench stress tests. If defective or misdescribed, trigger instant dispute.</p>
                </div>
            </div>

            <!-- Pillar 3 -->
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-white border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75A1.125 1.125 0 0 0 13.125 2.625h-9.75A1.125 1.125 0 0 0 2.25 3.75v10.5m12-6.75h-9" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-zinc-950">Insured Shiprocket Transit</h4>
                    <p class="text-xs text-zinc-600 mt-0.5 leading-relaxed">Doorstep insured courier with real-time tracking from seller's bench to your gaming setup.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: 🔥 Recently Listed (Live 5-Column Grid with <x-frontend.product-card>) -->
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight flex items-center gap-2">
                    <span>Recently Listed</span>
                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 bg-red-100 text-red-700 rounded-full">Live Feed</span>
                </h3>
                <p class="text-xs text-zinc-500 mt-0.5">Freshly verified computer components submitted by sellers today.</p>
            </div>
            <a href="/listings" class="text-xs font-bold text-zinc-900 hover:underline flex items-center gap-1">
                <span>View all listings</span>
                <span>&rarr;</span>
            </a>
        </div>

        @if($recentListings->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($recentListings as $listing)
                    <x-frontend.product-card :listing="$listing" />
                @endforeach
            </div>
        @else
            <div class="py-12 text-center bg-zinc-50 border border-dashed border-zinc-300 rounded-2xl">
                <p class="text-sm text-zinc-500 font-medium">No live listings found yet.</p>
                <a href="/dashboard/listings/create" class="mt-3 inline-block px-5 py-2 bg-[#FDD835] text-black font-extrabold text-xs rounded-full">
                    Be the First to List Gear
                </a>
            </div>
        @endif
    </div>

    <!-- Section 6: 🏷️ Popular Brands on X-Buy -->
    <div class="pt-4 border-t border-zinc-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">Popular Brands on X-Buy</h3>
            <a href="/listings" class="text-xs font-bold text-[#e5c120] hover:underline">All brands &rarr;</a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 text-center">
            @foreach($popularBrands as $brand)
                <a href="/listings?search={{ urlencode($brand->name) }}" 
                   class="group flex flex-col items-center p-3.5 rounded-xl border border-zinc-200/80 bg-white hover:border-zinc-400 hover:shadow-sm transition-all">
                    <div class="w-12 h-12 rounded-full bg-zinc-100 flex items-center justify-center font-extrabold text-sm text-zinc-800 group-hover:scale-105 group-hover:bg-[#FDD835]/30 transition-all">
                        {{ substr($brand->name, 0, 2) }}
                    </div>
                    <span class="text-xs font-bold text-zinc-800 mt-2.5 truncate max-w-full">{{ $brand->name }}</span>
                </a>
            @endforeach
        </div>
    </div>

</div>
@endsection