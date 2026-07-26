@extends('layouts.app')

@section('title', 'Welcome to ' . \App\Models\SiteSetting::getVal('platform_name', 'X-Buy'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        
        <!-- Section 1: Promo Banner -->
        <div class="w-full rounded-xl overflow-hidden shadow-xs border border-zinc-200/50 group cursor-pointer">
            <a href="/listings">
                <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/anon-banner-july-free-shipping-summer-2026-desktop_1785082076.avif" 
                     alt="Free Shipping Summer 2026" 
                     class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-[1.015]">
            </a>
        </div>

        <!-- Section 2: Three Category Highlight Cards (Mercari Style) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-[1280px]">
            
            <!-- Card 1: Graphics Cards (GPUs) -->
            <a href="/listings?category=gpu" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 shadow-xs group">
                <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/gaming_1785082243.avif" 
                     alt="GPUs" 
                     class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
            </a>

            <!-- Card 2: Processors (CPUs) -->
            <a href="/listings?category=cpu" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 shadow-xs group">
                <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/figurines_1785082421.avif" 
                     alt="CPUs" 
                     class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
            </a>

            <!-- Card 3: Essential Components (Motherboard & RAM) -->
            <a href="/listings?category=motherboard" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 shadow-xs group">
                <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/tradingcards_1785082544.avif" 
                     alt="Essential Gear" 
                     class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
            </a>
            
        </div>

        <!-- Section 3: Four Category Highlight Cards (Mercari Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-[1280px]">
            
            <!-- Card 1: Electronics -->
            <a href="/listings?category=electronics" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 shadow-xs group">
                <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/electronics_1785082632.avif" 
                     alt="Electronics" 
                     class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
            </a>

            <!-- Card 2: Toys -->
            <a href="/listings?category=toys" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 shadow-xs group">
                <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/toys_1785082762.avif" 
                     alt="Toys" 
                     class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
            </a>

            <!-- Card 3: Beauty -->
            <a href="/listings?category=beauty" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 shadow-xs group">
                <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/beauty_1785082818.avif" 
                     alt="Beauty" 
                     class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
            </a>

            <!-- Card 4: Handbags -->
            <a href="/listings?category=handbags" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 shadow-xs group">
                <img src="https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&q=80&w=300" 
                     alt="Handbags" 
                     class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
            </a>

        </div>

        <!-- Section 4: Vertical Full-Bleed Image Category Cards (Mercari Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-[1280px]">
            
            <!-- Card 1: Women -->
            <div class="relative w-full h-[480px] rounded-[1.25rem] overflow-hidden group shadow-xs">
                <!-- Background Full-bleed Image -->
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=400" alt="Women" class="absolute inset-0 w-full h-full object-cover">
                <!-- White/Light subtle top overlay to make text readable -->
                <div class="absolute inset-0 bg-gradient-to-b from-white/40 via-transparent to-transparent"></div>
                <!-- Content -->
                <div class="relative p-8 z-10">
                    <span class="text-xs md:text-[13px] text-zinc-800 font-semibold tracking-tight block">Blouses, dresses, and more</span>
                    <h3 class="text-3xl md:text-[32px] font-black text-zinc-950 mt-1 select-none tracking-tight leading-none">Women</h3>
                </div>
            </div>

            <!-- Card 2: Men -->
            <div class="relative w-full h-[480px] rounded-[1.25rem] overflow-hidden group shadow-xs">
                <!-- Background Full-bleed Image -->
                <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&q=80&w=400" alt="Men" class="absolute inset-0 w-full h-full object-cover">
                <!-- White/Light subtle top overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-white/40 via-transparent to-transparent"></div>
                <!-- Content -->
                <div class="relative p-8 z-10">
                    <span class="text-xs md:text-[13px] text-zinc-800 font-semibold tracking-tight block">Tees, polos, and more</span>
                    <h3 class="text-3xl md:text-[32px] font-black text-zinc-950 mt-1 select-none tracking-tight leading-none">Men</h3>
                </div>
            </div>

            <!-- Card 3: Kids -->
            <div class="relative w-full h-[480px] rounded-[1.25rem] overflow-hidden group shadow-xs">
                <!-- Background Full-bleed Image -->
                <img src="https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?auto=format&fit=crop&q=80&w=400" alt="Kids" class="absolute inset-0 w-full h-full object-cover">
                <!-- White/Light subtle top overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-white/40 via-transparent to-transparent"></div>
                <!-- Content -->
                <div class="relative p-8 z-10">
                    <span class="text-xs md:text-[13px] text-zinc-800 font-semibold tracking-tight block">Pajamas, outfits, and more</span>
                    <h3 class="text-3xl md:text-[32px] font-black text-zinc-950 mt-1 select-none tracking-tight leading-none">Kids</h3>
                </div>
            </div>

            <!-- Card 4: Home -->
            <div class="relative w-full h-[480px] rounded-[1.25rem] overflow-hidden group shadow-xs">
                <!-- Background Full-bleed Image -->
                <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&q=80&w=400" alt="Home" class="absolute inset-0 w-full h-full object-cover">
                <!-- White/Light subtle top overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-white/40 via-transparent to-transparent"></div>
                <!-- Content -->
                <div class="relative p-8 z-10">
                    <span class="text-xs md:text-[13px] text-zinc-800 font-semibold tracking-tight block">Decor, appliances, and more</span>
                    <h3 class="text-3xl md:text-[32px] font-black text-zinc-950 mt-1 select-none tracking-tight leading-none">Home</h3>
                </div>
            </div>

        </div>

        <!-- Section 5: Top Brands circular list (Mercari Style) -->
        <div class="space-y-6 w-full max-w-[1280px] pt-4">
            <h2 class="text-2xl font-bold text-zinc-900 tracking-tight select-none">Top brands</h2>
            
            <div class="relative flex items-center">
                <div class="flex items-center space-x-8 md:space-x-12 lg:space-x-16 overflow-x-auto scrollbar-none pb-2 w-full pr-12">
                    @php
                        $brands = [
                            ['name' => 'Apple', 'img' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&q=80&w=200'],
                            ['name' => 'Sony', 'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&q=80&w=200'],
                            ['name' => 'Nike', 'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=200'],
                            ['name' => 'Nintendo', 'img' => 'https://images.unsplash.com/photo-1564865878688-9a244444042a?auto=format&fit=crop&q=80&w=200'],
                            ['name' => 'Funko', 'img' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&q=80&w=200'],
                            ['name' => 'Pokemon', 'img' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&q=80&w=200'],
                        ];
                    @endphp
                    @foreach($brands as $brand)
                        <a href="/listings?search={{ urlencode($brand['name']) }}" class="flex flex-col items-center shrink-0 group">
                            <div class="w-[140px] h-[140px] shrink-0 rounded-full bg-[#f5f5f5] flex items-center justify-center overflow-hidden transition-all duration-200">
                                <img src="{{ $brand['img'] }}" alt="{{ $brand['name'] }}" class="w-[70%] h-[70%] object-contain select-none">
                            </div>
                            <span class="text-sm font-semibold text-zinc-800 mt-3 text-center select-none">{{ $brand['name'] }}</span>
                        </a>
                    @endforeach
                </div>
                
                <!-- Carousel Right Arrow overlay -->
                <button aria-label="Next brands" class="absolute right-0 top-[38%] w-10 h-10 rounded-full bg-white border border-zinc-200 shadow-md flex items-center justify-center text-zinc-700 hover:text-black transition-colors focus:outline-none z-10">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

    </div>
@endsection