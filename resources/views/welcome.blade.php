@extends('layouts.app')

@section('title', 'Welcome to ' . \App\Models\SiteSetting::getVal('platform_name', 'X-Buy'))

@section('content')
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
  <!-- Section 1: Promo Banner -->
 <div class="w-full rounded-xl overflow-hidden border border-zinc-200/50 group cursor-pointer">
 <a href="/listings">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/anon-banner-july-free-shipping-summer-2026-desktop_1785082076.avif"  alt="Free Shipping Summer 2026"  class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-[1.015]">
 </a>
 </div>

 <!-- Section 2: Three Category Highlight Cards (Mercari Style) -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-[1280px]">
  <!-- Card 1: Graphics Cards (GPUs) -->
 <a href="/listings?category=gpu" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/gaming_1785082243.avif"  alt="GPUs"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 2: Processors (CPUs) -->
 <a href="/listings?category=cpu" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/figurines_1785082421.avif"  alt="CPUs"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 3: Essential Components (Motherboard & RAM) -->
 <a href="/listings?category=motherboard" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/tradingcards_1785082544.avif"  alt="Essential Gear"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>
  </div>

 <!-- Section 3: Four Category Highlight Cards (Mercari Style) -->
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-[1280px]">
  <!-- Card 1: Electronics -->
 <a href="/listings?category=electronics" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/electronics_1785082632.avif"  alt="Electronics"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 2: Toys -->
 <a href="/listings?category=toys" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/toys_1785082762.avif"  alt="Toys"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 3: Beauty -->
 <a href="/listings?category=beauty" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/beauty_1785082818.avif"  alt="Beauty"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 4: Handbags -->
 <a href="/listings?category=handbags" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/handbags_1785082857.avif"  alt="Handbags"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 </div>

 <!-- Section 4: Vertical Full-Bleed Image Category Cards (Mercari Style) -->
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-[1280px]">
  <!-- Card 1: Women -->
 <a href="/listings?category=women" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/women_1785082959.avif"  alt="Women"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 2: Men -->
 <a href="/listings?category=men" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/men_1785083043.avif"  alt="Men"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 3: Kids -->
 <a href="/listings?category=kids" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/kids_1785083226.avif"  alt="Kids"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 <!-- Card 4: Home -->
 <a href="/listings?category=home" class="block w-full overflow-hidden rounded-xl border border-zinc-200/50 group">
 <img src="https://mediumblue-goldfish-835128.hostingersite.com/website_assets/images/home_1785083399.avif"  alt="Home"  class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-[1.015]">
 </a>

 </div>

 <!-- Section 5: Top Brands circular list (Mercari Style) -->
 <div class="space-y-6 w-full max-w-[1280px] pt-4">
 <h2 class="text-2xl font-bold text-zinc-900 tracking-tight select-none">Top brands</h2>
  <div class="relative flex items-center justify-center w-full">
 <div class="scrollbar-none pb-2" style="display: flex; align-items: center; justify-content: center; gap: 2.5rem; width: 100%; overflow-x: auto; flex-wrap: wrap;">
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
 <div class="shrink-0 bg-[#f5f5f5] flex items-center justify-center overflow-hidden transition-all duration-200 hover:scale-105" style="width: 140px; height: 140px; border-radius: 50%; aspect-ratio: 1 / 1;">
 <img src="{{ $brand['img'] }}" alt="{{ $brand['name'] }}" class="select-none" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
 </div>
 <span class="text-sm font-semibold text-zinc-800 mt-3 text-center select-none">{{ $brand['name'] }}</span>
 </a>
 @endforeach
 </div>
 </div>
 </div>

 </div>
@endsection