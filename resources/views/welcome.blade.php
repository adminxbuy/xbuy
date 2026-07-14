@extends('layouts.app')

@section('title', 'Welcome to ' . \App\Models\SiteSetting::getVal('platform_name', 'X-Buy'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        
        <!-- Section 1: Flash Sale Event Banner (Mercari Style) -->
        <div class="relative bg-sky-300 rounded-[2rem] overflow-hidden shadow-xs border border-sky-400/20 p-6 md:p-10 flex flex-col lg:flex-row items-center justify-between gap-8 min-h-[320px]">
            <!-- Dot pattern background overlay -->
            <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(#0369a1 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
            
            <!-- Polaroid images arrangement (Left side) -->
            <div class="relative flex items-center justify-center w-full lg:w-1/3 min-h-[220px] select-none order-2 lg:order-1">
                <!-- Polaroid 1 (Gaming mouse/keyboard or GPU) -->
                <div class="absolute left-4 rotate-[-8deg] bg-white p-3 pb-6 rounded-lg shadow-md border border-zinc-200/40 w-44">
                    <img src="https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=300" alt="GPU" class="w-full aspect-square object-cover rounded-sm border border-zinc-100">
                    <span class="block text-center font-bold text-zinc-700 text-xs mt-3 select-none">RTX 4070 Ti</span>
                </div>
                
                <!-- Polaroid 2 (Custom PC Rig) -->
                <div class="absolute right-4 rotate-[6deg] bg-white p-3 pb-6 rounded-lg shadow-md border border-zinc-200/40 w-44">
                    <img src="https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&q=80&w=300" alt="PC Setup" class="w-full aspect-square object-cover rounded-sm border border-zinc-100">
                    <span class="block text-center font-bold text-zinc-700 text-xs mt-3 select-none">Custom Rigs</span>
                </div>
            </div>

            <!-- Banner content (Center-Right) -->
            <div class="relative z-10 flex-1 flex flex-col items-center text-center space-y-4 max-w-2xl order-1 lg:order-2">
                <div class="flex items-center space-x-3">
                    <span class="bg-[#1e3a8a] text-white text-[10px] font-extrabold px-3 py-1 rounded-md uppercase tracking-wider select-none">live now</span>
                    <span class="bg-yellow-400 text-blue-900 text-[10px] font-extrabold px-3 py-1 rounded-md uppercase tracking-wider select-none">July 13-15</span>
                </div>
                
                <div>
                    <!-- Outline text effect via css -->
                    <h1 class="text-4xl md:text-5xl font-black tracking-tight text-blue-900 leading-none select-none" style="text-shadow: 2px 2px 0px #fff, -2px -2px 0px #fff, 2px -2px 0px #fff, -2px 2px 0px #fff;">
                        FLASH SALE EVENT
                    </h1>
                    <p class="text-blue-950 font-bold text-sm md:text-base mt-2 select-none">
                        *Coupon for <span class="underline">10% off</span> item price(s)*
                    </p>
                </div>
                
                <div class="bg-yellow-300 text-blue-950 px-6 py-2 rounded-2xl border border-yellow-400 font-extrabold text-sm shadow-xs select-none">
                    Use Code: <span class="font-mono text-base font-black">JULYFLASH10</span>
                </div>
            </div>

            <!-- Learn more CTA Button (Right side) -->
            <div class="relative z-10 w-full lg:w-auto flex justify-center lg:justify-end order-3">
                <a href="/listings" class="bg-blue-800 text-white font-bold px-8 py-3.5 rounded-full shadow-md flex items-center justify-center space-x-2">
                    <span>Learn more</span>
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </a>
            </div>
            
            <!-- Green leaf decoration (Right corner) -->
            <div class="absolute right-0 bottom-0 pointer-events-none select-none opacity-20 lg:opacity-40">
                <svg width="150" height="150" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 100C70 100 30 70 30 30C30 50 50 90 100 100Z" fill="#15803d"></path>
                    <path d="M100 100C85 80 60 40 40 10C50 30 70 80 100 100Z" fill="#16a34a"></path>
                </svg>
            </div>
        </div>

        <!-- Section 2: Three Category Highlight Cards (Mercari Style) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-[1280px]">
            
            <!-- Card 1: Graphics Cards (GPUs) -->
            <div class="relative w-full h-[320px] bg-[#dbeafe]/70 border border-blue-100 rounded-[1.25rem] p-8 flex flex-col justify-between overflow-hidden shadow-xs">
                <div class="flex items-start justify-between z-10">
                    <div>
                        <span class="text-xs md:text-[13px] text-zinc-600 font-semibold tracking-tight block">Graphics cards, gaming GPUs</span>
                        <h3 class="text-3xl md:text-[32px] font-black text-zinc-900 mt-1 select-none tracking-tight leading-none">GPUs</h3>
                    </div>
                    <a href="/listings?category=gpu" class="text-xs font-bold text-zinc-800 flex items-center gap-1">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <!-- Float component image at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[220px] overflow-hidden select-none">
                    <img src="https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400" alt="GPU" class="w-full h-full object-cover object-center rounded-b-[1.25rem]">
                </div>
            </div>

            <!-- Card 2: Processors (CPUs) -->
            <div class="relative w-full h-[320px] bg-[#f3e8ff]/70 border border-purple-100 rounded-[1.25rem] p-8 flex flex-col justify-between overflow-hidden shadow-xs">
                <div class="flex items-start justify-between z-10">
                    <div>
                        <span class="text-xs md:text-[13px] text-zinc-600 font-semibold tracking-tight block">Processors, core speed power</span>
                        <h3 class="text-3xl md:text-[32px] font-black text-zinc-900 mt-1 select-none tracking-tight leading-none">CPUs</h3>
                    </div>
                    <a href="/listings?category=cpu" class="text-xs font-bold text-zinc-800 flex items-center gap-1">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <!-- Float component image at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[220px] overflow-hidden select-none">
                    <img src="https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&q=80&w=400" alt="CPU" class="w-full h-full object-cover object-center rounded-b-[1.25rem]">
                </div>
            </div>

            <!-- Card 3: Essential Components (Motherboard & RAM) -->
            <div class="relative w-full h-[320px] bg-[#fef3c7]/70 border border-amber-100 rounded-[1.25rem] p-8 flex flex-col justify-between overflow-hidden shadow-xs">
                <div class="flex items-start justify-between z-10">
                    <div>
                        <span class="text-xs md:text-[13px] text-zinc-700 font-semibold tracking-tight block">Memory, boards, custom rigs</span>
                        <h3 class="text-3xl md:text-[32px] font-black text-zinc-900 mt-1 select-none tracking-tight leading-none">Essential Gear</h3>
                    </div>
                    <a href="/listings?category=motherboard" class="text-xs font-bold text-zinc-800 flex items-center gap-1">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <!-- Float component image at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[220px] overflow-hidden select-none">
                    <img src="https://images.unsplash.com/photo-1562976540-1502c2145186?auto=format&fit=crop&q=80&w=400" alt="RAM" class="w-full h-full object-cover object-center rounded-b-[1.25rem]">
                </div>
            </div>
            
        </div>

        <!-- Section 3: Four Category Highlight Cards (Mercari Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-[1280px]">
            
            <!-- Card 1: Electronics -->
            <div class="relative w-full h-[300px] bg-[#dbeafe]/70 border border-blue-100 rounded-[1.25rem] p-6 flex flex-col justify-between overflow-hidden shadow-xs">
                <div class="flex items-start justify-between z-10">
                    <div>
                        <span class="text-[10px] md:text-[11px] text-zinc-600 font-semibold tracking-tight block">Cell phones, tablets, laptops</span>
                        <h3 class="text-2xl font-black text-zinc-900 mt-0.5 select-none tracking-tight leading-none">Electronics</h3>
                    </div>
                    <a href="/listings?category=electronics" class="text-xs font-bold text-zinc-800 flex items-center gap-1">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <!-- Float component image at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[200px] overflow-hidden select-none">
                    <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&q=80&w=300" alt="Electronics" class="w-full h-full object-cover object-center rounded-b-[1.25rem]">
                </div>
            </div>

            <!-- Card 2: Toys -->
            <div class="relative w-full h-[300px] bg-[#dbeafe]/70 border border-blue-100 rounded-[1.25rem] p-6 flex flex-col justify-between overflow-hidden shadow-xs">
                <div class="flex items-start justify-between z-10">
                    <div>
                        <span class="text-[10px] md:text-[11px] text-zinc-600 font-semibold tracking-tight block">Plushies, building blocks, dolls</span>
                        <h3 class="text-2xl font-black text-zinc-900 mt-0.5 select-none tracking-tight leading-none">Toys</h3>
                    </div>
                    <a href="/listings?category=toys" class="text-xs font-bold text-zinc-800 flex items-center gap-1">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <!-- Float component image at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[200px] overflow-hidden select-none">
                    <img src="https://images.unsplash.com/photo-1566577134770-3d85bb3a9cc4?auto=format&fit=crop&q=80&w=300" alt="Toys" class="w-full h-full object-cover object-center rounded-b-[1.25rem]">
                </div>
            </div>

            <!-- Card 3: Beauty -->
            <div class="relative w-full h-[300px] bg-[#dbeafe]/70 border border-blue-100 rounded-[1.25rem] p-6 flex flex-col justify-between overflow-hidden shadow-xs">
                <div class="flex items-start justify-between z-10">
                    <div>
                        <span class="text-[10px] md:text-[11px] text-zinc-600 font-semibold tracking-tight block">Fragrance, makeup, accessories</span>
                        <h3 class="text-2xl font-black text-zinc-900 mt-0.5 select-none tracking-tight leading-none">Beauty</h3>
                    </div>
                    <a href="/listings?category=beauty" class="text-xs font-bold text-zinc-800 flex items-center gap-1">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <!-- Float component image at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[200px] overflow-hidden select-none">
                    <img src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&q=80&w=300" alt="Beauty" class="w-full h-full object-cover object-center rounded-b-[1.25rem]">
                </div>
            </div>

            <!-- Card 4: Handbags -->
            <div class="relative w-full h-[300px] bg-[#dbeafe]/70 border border-blue-100 rounded-[1.25rem] p-6 flex flex-col justify-between overflow-hidden shadow-xs">
                <div class="flex items-start justify-between z-10">
                    <div>
                        <span class="text-[10px] md:text-[11px] text-zinc-600 font-semibold tracking-tight block">Authentic, designer, trend</span>
                        <h3 class="text-2xl font-black text-zinc-900 mt-0.5 select-none tracking-tight leading-none">Handbags</h3>
                    </div>
                    <a href="/listings?category=handbags" class="text-xs font-bold text-zinc-800 flex items-center gap-1">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <!-- Float component image at bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-[200px] overflow-hidden select-none">
                    <img src="https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&q=80&w=300" alt="Handbags" class="w-full h-full object-cover object-center rounded-b-[1.25rem]">
                </div>
            </div>

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
                            ['name' => 'Funko', 'img' => 'https://images.unsplash.com/photo-1559868483-72ce86331f7c?auto=format&fit=crop&q=80&w=200'],
                            ['name' => 'Pokemon', 'img' => 'https://images.unsplash.com/photo-1613771404724-11d2d7a698e4?auto=format&fit=crop&q=80&w=200'],
                        ];
                    @endphp
                    @foreach($brands as $brand)
                        <a href="/listings?search={{ urlencode($brand['name']) }}" class="flex flex-col items-center shrink-0 group">
                            <div class="w-[150px] h-[150px] shrink-0 rounded-full bg-[#f4f4f5] flex items-center justify-center overflow-hidden transition-all duration-200">
                                <img src="{{ $brand['img'] }}" alt="{{ $brand['name'] }}" class="w-[75%] h-[75%] object-contain select-none">
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