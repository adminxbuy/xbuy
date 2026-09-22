@extends('layouts.app')

@section('title', 'Browse Verified PC Components & Gear | X-Buy')

@php
    $activeFiltersCount = 0;
    if (request('search')) $activeFiltersCount++;
    if (request('category')) $activeFiltersCount++;
    if (request('brand')) $activeFiltersCount++;
    if (request('min_price') || request('max_price')) $activeFiltersCount++;
    if (request('condition')) $activeFiltersCount++;
    if (request('free_shipping')) $activeFiltersCount++;
    if (request('status') && request('status') !== 'active') $activeFiltersCount++;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
     x-data="{
         mobileFiltersOpen: false,
         brandSearch: '',
         minPrice: '{{ request('min_price') }}',
         maxPrice: '{{ request('max_price') }}',
         setPrice(min, max) {
             const url = new URL(window.location.href);
             if (min !== null) url.searchParams.set('min_price', min);
             else url.searchParams.delete('min_price');
             if (max !== null) url.searchParams.set('max_price', max);
             else url.searchParams.delete('max_price');
             window.location.href = url.toString();
         }
     }">
    
    <!-- Breadcrumbs -->
    <nav class="mb-5 flex items-center space-x-2 text-xs font-semibold text-zinc-500">
        <a href="/" class="hover:text-zinc-950 transition-colors">Home</a>
        <span>/</span>
        <a href="/listings" class="hover:text-zinc-950 transition-colors">Catalog</a>
        @if(request('category'))
            <span>/</span>
            <span class="text-zinc-900 capitalize">{{ request('category') }}</span>
        @endif
        @if(request('search'))
            <span>/</span>
            <span class="text-zinc-900">"{{ request('search') }}"</span>
        @endif
    </nav>

    <!-- Main Layout: 2 Columns (Sticky Sidebar + Catalog Feed) -->
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- Left Sticky Filter Sidebar (Desktop >= 1024px) -->
        <aside class="hidden lg:block w-72 shrink-0 sticky top-24 space-y-6 select-none max-h-[calc(100vh-120px)] overflow-y-auto custom-scrollbar pr-2">
            
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-zinc-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    <h3 class="text-sm font-black text-zinc-950 uppercase tracking-wider">Filters</h3>
                </div>
                @if($activeFiltersCount > 0)
                    <a href="/listings" class="text-xs font-bold text-red-600 hover:underline">
                        Reset all
                    </a>
                @endif
            </div>

            <!-- Filter: Availability / Status -->
            <div class="space-y-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-900 block">Availability</span>
                <div class="flex items-center gap-4 text-xs font-semibold text-zinc-700">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="desktop_status" value="active" 
                               {{ request('status', 'active') === 'active' ? 'checked' : '' }}
                               onchange="window.location.href = updateQueryString('status', 'active')"
                               class="accent-[#FDD835]">
                        <span>For Sale</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="desktop_status" value="sold" 
                               {{ request('status') === 'sold' ? 'checked' : '' }}
                               onchange="window.location.href = updateQueryString('status', 'sold')"
                               class="accent-[#FDD835]">
                        <span>Sold Items</span>
                    </label>
                </div>
            </div>

            <!-- Filter: Category -->
            <div class="space-y-2 pt-2 border-t border-zinc-100" x-data="{ catOpen: true }">
                <button type="button" @click="catOpen = !catOpen" class="w-full flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-zinc-900">
                    <span>Category</span>
                    <svg class="w-3.5 h-3.5 text-zinc-400 transition-transform" :class="catOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="catOpen" class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar pt-1 text-xs">
                    <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" 
                       class="flex items-center justify-between py-1 px-2 rounded-lg {{ !request('category') ? 'bg-[#FDD835]/30 font-bold text-black' : 'text-zinc-700 hover:bg-zinc-100' }}">
                        <span>All Categories</span>
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $cat->slug]) }}" 
                           class="flex items-center justify-between py-1 px-2 rounded-lg {{ request('category') === $cat->slug ? 'bg-[#FDD835]/30 font-bold text-black' : 'text-zinc-700 hover:bg-zinc-100' }}">
                            <span class="truncate">{{ preg_replace('/\s*\(.*?\)/', '', $cat->name) }}</span>
                            <span class="text-[10px] text-zinc-400 font-normal">({{ $cat->listings_count ?? 0 }})</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Filter: Brand Searchable -->
            <div class="space-y-2 pt-2 border-t border-zinc-100" x-data="{ brandOpen: true }">
                <button type="button" @click="brandOpen = !brandOpen" class="w-full flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-zinc-900">
                    <span>Brand</span>
                    <svg class="w-3.5 h-3.5 text-zinc-400 transition-transform" :class="brandOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="brandOpen" class="space-y-2 pt-1">
                    <input type="text" 
                           x-model="brandSearch" 
                           placeholder="Search brands (e.g. ASUS)..." 
                           class="w-full text-xs px-2.5 py-1.5 bg-zinc-50 border border-zinc-200 rounded-lg outline-none focus:border-zinc-900">
                    
                    <div class="space-y-1 max-h-44 overflow-y-auto custom-scrollbar text-xs">
                        @foreach($allBrands as $b)
                            <label class="flex items-center gap-2 py-1 px-2 hover:bg-zinc-50 rounded cursor-pointer"
                                   x-show="!brandSearch || '{{ strtolower($b->name) }}'.includes(brandSearch.toLowerCase())">
                                <input type="checkbox" 
                                       value="{{ $b->name }}"
                                       {{ in_array($b->name, (array)request('brand')) || request('brand') === $b->name ? 'checked' : '' }}
                                       onchange="window.location.href = updateQueryString('brand', this.checked ? '{{ $b->name }}' : null)"
                                       class="rounded border-zinc-300 text-zinc-900 focus:ring-0">
                                <span class="text-zinc-700 font-medium truncate">{{ $b->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filter: Price Range -->
            <div class="space-y-3 pt-2 border-t border-zinc-100">
                <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-900 block">Price Range</span>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-2.5 top-1.5 text-xs text-zinc-400">₹</span>
                        <input type="number" 
                               x-model="minPrice" 
                               placeholder="Min" 
                               class="w-full pl-6 pr-2 py-1.5 text-xs bg-zinc-50 border border-zinc-200 rounded-lg outline-none focus:border-zinc-900">
                    </div>
                    <span class="text-zinc-400 text-xs">-</span>
                    <div class="relative flex-1">
                        <span class="absolute left-2.5 top-1.5 text-xs text-zinc-400">₹</span>
                        <input type="number" 
                               x-model="maxPrice" 
                               placeholder="Max" 
                               class="w-full pl-6 pr-2 py-1.5 text-xs bg-zinc-50 border border-zinc-200 rounded-lg outline-none focus:border-zinc-900">
                    </div>
                    <button type="button" 
                            @click="setPrice(minPrice || null, maxPrice || null)"
                            class="px-3 py-1.5 bg-zinc-900 hover:bg-black text-white text-xs font-bold rounded-lg transition-colors">
                        Go
                    </button>
                </div>
                <!-- Quick Pills -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <button type="button" @click="setPrice(null, 5000)" class="px-2.5 py-1 text-[11px] rounded-full border border-zinc-200 bg-white hover:bg-zinc-100 text-zinc-700 font-medium">
                        Under ₹5k
                    </button>
                    <button type="button" @click="setPrice(5000, 20000)" class="px-2.5 py-1 text-[11px] rounded-full border border-zinc-200 bg-white hover:bg-zinc-100 text-zinc-700 font-medium">
                        ₹5k - ₹20k
                    </button>
                    <button type="button" @click="setPrice(20000, 50000)" class="px-2.5 py-1 text-[11px] rounded-full border border-zinc-200 bg-white hover:bg-zinc-100 text-zinc-700 font-medium">
                        ₹20k - ₹50k
                    </button>
                    <button type="button" @click="setPrice(50000, null)" class="px-2.5 py-1 text-[11px] rounded-full border border-zinc-200 bg-white hover:bg-zinc-100 text-zinc-700 font-medium">
                        ₹50k+
                    </button>
                </div>
            </div>

            <!-- Filter: Condition -->
            <div class="space-y-2 pt-2 border-t border-zinc-100">
                <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-900 block">Condition</span>
                <div class="space-y-1.5 text-xs font-medium text-zinc-700">
                    @php
                        $conditions = [
                            'new' => 'Brand New (Sealed)',
                            'like_new' => 'Like New (Mint)',
                            'good' => 'Good (Gently Used)',
                            'fair' => 'Fair (Visible Wear)',
                            'for_parts' => 'For Parts / Defective'
                        ];
                    @endphp
                    @foreach($conditions as $key => $label)
                        <label class="flex items-center gap-2 cursor-pointer py-0.5">
                            <input type="checkbox" 
                                   value="{{ $key }}"
                                   {{ request('condition') === $key ? 'checked' : '' }}
                                   onchange="window.location.href = updateQueryString('condition', this.checked ? '{{ $key }}' : null)"
                                   class="rounded border-zinc-300 text-zinc-900 focus:ring-0">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Filter: Free Shipping -->
            <div class="pt-2 border-t border-zinc-100">
                <label class="flex items-center gap-2 text-xs font-bold text-zinc-900 cursor-pointer">
                    <input type="checkbox" 
                           value="1"
                           {{ request('free_shipping') ? 'checked' : '' }}
                           onchange="window.location.href = updateQueryString('free_shipping', this.checked ? '1' : null)"
                           class="rounded border-zinc-300 text-zinc-900 focus:ring-0">
                    <span>Free Shipping Only</span>
                </label>
            </div>

        </aside>

        <!-- Right: Catalog Results & Grid -->
        <main class="flex-1 min-w-0 w-full">
            
            <!-- Top Controls Strip: Results Count & Sort Dropdown -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-zinc-200 mb-5">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">
                        @if(request('search'))
                            Search results for "{{ request('search') }}"
                        @elseif(request('category'))
                            {{ ucwords(str_replace('_', ' ', request('category'))) }}
                        @else
                            All Verified PC Gear
                        @endif
                    </h1>
                    <p class="text-xs text-zinc-500 mt-0.5">Showing {{ $listings->total() }} items with 7-day escrow protection</p>
                </div>

                <!-- Sort & Mobile Filter Trigger -->
                <div class="flex items-center gap-3 self-end sm:self-auto">
                    <!-- Mobile Filter Trigger Button -->
                    <button type="button" 
                            @click="mobileFiltersOpen = true"
                            class="lg:hidden inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full border border-zinc-300 bg-white text-xs font-bold text-zinc-900 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                        <span>Filter & Sort</span>
                        @if($activeFiltersCount > 0)
                            <span class="w-4 h-4 rounded-full bg-[#FDD835] text-black text-[10px] font-extrabold flex items-center justify-center">{{ $activeFiltersCount }}</span>
                        @endif
                    </button>

                    <!-- Sort Select -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-zinc-500 font-medium hidden sm:inline">Sort by:</span>
                        <select onchange="window.location.href = updateQueryString('sort', this.value)"
                                class="bg-white border border-zinc-300 text-zinc-900 text-xs font-bold rounded-lg px-3 py-2 outline-none focus:border-zinc-900 cursor-pointer shadow-2xs">
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest Listed</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Active Filter Dismissible Pills -->
            @if($activeFiltersCount > 0)
                <div class="flex flex-wrap items-center gap-2 mb-6">
                    <span class="text-xs font-bold text-zinc-400 mr-1">Active:</span>
                    
                    @if(request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-full transition-colors">
                            <span>"{{ request('search') }}"</span>
                            <span class="text-zinc-500 hover:text-black">✕</span>
                        </a>
                    @endif

                    @if(request('category'))
                        <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-full transition-colors">
                            <span class="capitalize">Category: {{ request('category') }}</span>
                            <span class="text-zinc-500 hover:text-black">✕</span>
                        </a>
                    @endif

                    @if(request('brand'))
                        <a href="{{ request()->fullUrlWithQuery(['brand' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-full transition-colors">
                            <span>Brand: {{ request('brand') }}</span>
                            <span class="text-zinc-500 hover:text-black">✕</span>
                        </a>
                    @endif

                    @if(request('min_price') || request('max_price'))
                        <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-full transition-colors">
                            <span>Price: ₹{{ request('min_price', 0) }} - ₹{{ request('max_price', 'Any') }}</span>
                            <span class="text-zinc-500 hover:text-black">✕</span>
                        </a>
                    @endif

                    @if(request('free_shipping'))
                        <a href="{{ request()->fullUrlWithQuery(['free_shipping' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-full transition-colors">
                            <span>Free Shipping Only</span>
                            <span class="text-zinc-500 hover:text-black">✕</span>
                        </a>
                    @endif

                    @if(request('status') && request('status') !== 'active')
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-full transition-colors">
                            <span>Sold Items</span>
                            <span class="text-zinc-500 hover:text-black">✕</span>
                        </a>
                    @endif

                    <a href="/listings" class="text-xs font-bold text-red-600 hover:underline ml-2">Clear All</a>
                </div>
            @endif

            <!-- 4-Column Product Grid -->
            @if($listings->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($listings as $listing)
                        <x-frontend.product-card :listing="$listing" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-10">
                    {{ $listings->links() }}
                </div>
            @else
                <!-- Mercari Empty State -->
                <div class="text-center py-20 bg-zinc-50 rounded-2xl border border-dashed border-zinc-200 p-8 flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900">No matching gear found</h3>
                    <p class="text-xs text-zinc-500 mt-1 max-w-sm">We couldn't find items matching your active filters. Try removing filters or searching for alternative hardware.</p>
                    <a href="/listings" class="mt-5 inline-flex items-center px-5 py-2.5 rounded-full bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-xs shadow-xs transition-transform active:scale-95">
                        Clear All Filters
                    </a>
                </div>
            @endif

        </main>
    </div>

    <!-- Mobile Slide-Up Filter Bottom Sheet (< 1024px) -->
    <div x-show="mobileFiltersOpen" 
         class="fixed inset-0 z-[100] lg:hidden"
         style="display: none;">
        
        <!-- Backdrop -->
        <div x-show="mobileFiltersOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-xs"
             @click="mobileFiltersOpen = false"></div>

        <!-- Sheet Panel (Slide from bottom) -->
        <div x-show="mobileFiltersOpen"
             x-transition:enter="transition-transform ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition-transform ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white rounded-t-3xl shadow-2xl flex flex-col z-[101]">
            
            <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                <h3 class="text-base font-extrabold text-zinc-950">Filters & Sort</h3>
                <button @click="mobileFiltersOpen = false" class="p-1 text-zinc-400 hover:text-black">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-6 text-xs">
                <!-- Mobile Sort -->
                <div>
                    <span class="font-extrabold uppercase tracking-wider text-zinc-900 block mb-2">Sort</span>
                    <select onchange="window.location.href = updateQueryString('sort', this.value)"
                            class="w-full bg-zinc-50 border border-zinc-300 rounded-xl p-3 font-bold">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest Listed</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                    </select>
                </div>

                <!-- Mobile Availability -->
                <div>
                    <span class="font-extrabold uppercase tracking-wider text-zinc-900 block mb-2">Availability</span>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2.5 border rounded-xl flex items-center justify-center gap-2 cursor-pointer {{ request('status', 'active') === 'active' ? 'border-zinc-950 bg-zinc-100 font-bold' : 'border-zinc-200' }}">
                            <input type="radio" name="mobile_status" value="active" {{ request('status', 'active') === 'active' ? 'checked' : '' }} onchange="window.location.href = updateQueryString('status', 'active')" class="sr-only">
                            <span>For Sale</span>
                        </label>
                        <label class="p-2.5 border rounded-xl flex items-center justify-center gap-2 cursor-pointer {{ request('status') === 'sold' ? 'border-zinc-950 bg-zinc-100 font-bold' : 'border-zinc-200' }}">
                            <input type="radio" name="mobile_status" value="sold" {{ request('status') === 'sold' ? 'checked' : '' }} onchange="window.location.href = updateQueryString('status', 'sold')" class="sr-only">
                            <span>Sold Items</span>
                        </label>
                    </div>
                </div>

                <!-- Mobile Categories -->
                <div>
                    <span class="font-extrabold uppercase tracking-wider text-zinc-900 block mb-2">Categories</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($categories as $cat)
                            <a href="{{ request()->fullUrlWithQuery(['category' => $cat->slug]) }}" 
                               class="p-2.5 border rounded-xl text-center truncate {{ request('category') === $cat->slug ? 'border-zinc-950 bg-zinc-100 font-bold text-black' : 'border-zinc-200 text-zinc-700' }}">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sticky Apply Button -->
            <div class="p-4 border-t border-zinc-200 bg-zinc-50">
                <button @click="mobileFiltersOpen = false" 
                        class="w-full py-3 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-sm rounded-xl">
                    Show {{ $listings->total() }} Results
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    function updateQueryString(key, value) {
        const url = new URL(window.location.href);
        if (value === null || value === '') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, value);
        }
        url.searchParams.delete('page'); // Reset to page 1 on filter change
        return url.toString();
    }
</script>
@endsection
