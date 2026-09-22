@php
    $navCategories = \App\Models\Category::active()->parentOnly()->with(['children' => function($q) {
        $q->active()->orderBy('sort_order');
    }])->orderBy('sort_order')->take(8)->get();
    $featuredBrands = \App\Models\Brand::where('is_featured', true)->take(6)->get();
    if ($featuredBrands->isEmpty()) {
        $featuredBrands = \App\Models\Brand::take(6)->get();
    }
@endphp

<!-- Category Strip & 3-Column Mega Menu (Mercari Style) -->
<div class="relative bg-white border-b border-zinc-200"
     x-data="{ megaMenuOpen: false, activeCatId: {{ $navCategories->first()?->id ?? 'null' }}, timer: null }"
     @mouseleave="clearTimeout(timer); timer = setTimeout(() => { megaMenuOpen = false; }, 250)">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-11 text-xs font-semibold text-zinc-700 overflow-x-auto scrollbar-none">
            
            <!-- Left: 'All Categories' Trigger -->
            <div class="relative flex-shrink-0"
                 @mouseenter="clearTimeout(timer); megaMenuOpen = true">
                <button type="button" 
                        class="flex items-center gap-1.5 px-3 py-2 text-zinc-950 font-bold hover:text-black hover:bg-zinc-100 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                    <span>All Categories</span>
                    <svg class="w-3 h-3 text-zinc-400 transition-transform duration-150" :class="megaMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>

            <!-- Middle: High-Frequency Quick Category Links -->
            <div class="flex items-center gap-1 md:gap-4 whitespace-nowrap px-2">
                @foreach($navCategories as $cat)
                    <a href="/listings?category={{ $cat->slug }}" 
                       @mouseenter="clearTimeout(timer); activeCatId = {{ $cat->id }};"
                       class="px-2.5 py-1.5 rounded-lg text-zinc-700 hover:text-zinc-950 hover:bg-zinc-50 transition-colors">
                        {{ preg_replace('/\s*\(.*?\)/', '', $cat->name) }}
                    </a>
                @endforeach
                <a href="/p/deals" class="px-2.5 py-1.5 rounded-lg text-amber-600 hover:text-amber-700 font-bold hover:bg-amber-50 transition-colors flex items-center gap-1">
                    <span>Deals</span>
                    <span>🔥</span>
                </a>
                <a href="/listings?free_shipping=1" class="px-2.5 py-1.5 rounded-lg text-emerald-700 hover:text-emerald-800 font-bold hover:bg-emerald-50 transition-colors">
                    Free Shipping
                </a>
            </div>

            <!-- Right: Escrow Guarantee Micro-pill -->
            <div class="hidden xl:flex items-center gap-1.5 text-[11px] text-zinc-500 font-medium shrink-0">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 0 1 2.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0 1 10 1.944ZM11 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm0-7a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0V7Z" clip-rule="evenodd"/>
                </svg>
                <span>7-Day Escrow Protection</span>
            </div>

        </div>
    </div>

    <!-- 3-Column Mega Menu Flyout Pane -->
    <div x-show="megaMenuOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="absolute left-0 w-full bg-white border-b border-zinc-200 mega-dropdown-shadow z-50"
         style="display: none;"
         @mouseenter="clearTimeout(timer);"
         @mouseleave="clearTimeout(timer); timer = setTimeout(() => { megaMenuOpen = false; }, 250);">
        
        <div class="max-w-7xl mx-auto px-6 py-6">
            <div class="grid grid-cols-12 gap-6 min-h-[300px]">
                
                <!-- Column 1: Parent Categories (Span 3) -->
                <div class="col-span-3 border-r border-zinc-100 pr-4 space-y-1">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 px-3 pb-2">Departments</h4>
                    @foreach($navCategories as $pCat)
                        <button type="button" 
                                @mouseenter="activeCatId = {{ $pCat->id }}"
                                @click="window.location.href='/listings?category={{ $pCat->slug }}'"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-all text-left cursor-pointer"
                                :class="activeCatId === {{ $pCat->id }} ? 'bg-[#FDD835]/25 text-zinc-950 font-extrabold' : 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950'">
                            <span class="truncate">{{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}</span>
                            <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    @endforeach
                </div>

                <!-- Column 2: Child Subcategories Grid (Span 6) -->
                <div class="col-span-6 border-r border-zinc-100 px-4">
                    @foreach($navCategories as $pCat)
                        <div x-show="activeCatId === {{ $pCat->id }}" style="display: none;">
                            <div class="flex items-center justify-between border-b border-zinc-100 pb-2 mb-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900">
                                    {{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }} Subcategories
                                </h4>
                                <a href="/listings?category={{ $pCat->slug }}" class="text-xs font-bold text-[#e5c120] hover:underline">
                                    Browse All &rarr;
                                </a>
                            </div>

                            @if($pCat->children->count() > 0)
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($pCat->children as $cCat)
                                        <a href="/listings?category={{ $cCat->slug }}" 
                                           class="flex items-center gap-3 p-2 rounded-xl hover:bg-zinc-50 border border-transparent hover:border-zinc-200 transition-all group">
                                            <div class="w-9 h-9 rounded-lg bg-zinc-100 flex items-center justify-center shrink-0 overflow-hidden text-zinc-500 group-hover:text-black">
                                                @if($cCat->image)
                                                    <img src="{{ $cCat->image }}" alt="{{ $cCat->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-xs font-extrabold">{{ substr($cCat->name, 0, 2) }}</span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-zinc-800 group-hover:text-black truncate">{{ $cCat->name }}</p>
                                                <span class="text-[10px] text-zinc-400">View gear &rarr;</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-12 text-center text-zinc-400 text-xs">
                                    <p>Discover verified listings in this category.</p>
                                    <a href="/listings?category={{ $pCat->slug }}" class="mt-3 inline-block px-4 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-full text-zinc-900 font-bold">
                                        View Listings
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Column 3: Featured Brands in Category (Span 3) -->
                <div class="col-span-3 pl-4 space-y-3">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Top Brands</h4>
                    <div class="space-y-1.5">
                        @foreach($featuredBrands as $b)
                            <a href="/listings?search={{ urlencode($b->name) }}" 
                               class="flex items-center justify-between p-2 rounded-xl hover:bg-zinc-50 border border-zinc-100 transition-colors group">
                                <span class="text-xs font-bold text-zinc-800 group-hover:text-black">{{ $b->name }}</span>
                                <span class="text-[10px] font-semibold text-zinc-400">Shop &rarr;</span>
                            </a>
                        @endforeach
                    </div>

                    <!-- Trust banner box -->
                    <div class="mt-4 p-3 bg-zinc-50 rounded-xl border border-zinc-200/80">
                        <div class="flex items-center gap-2 text-xs font-bold text-zinc-950">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Authenticity Guarantee</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 mt-1 leading-relaxed">
                            Serial numbers and stress-test benchmark proof verified on all listed hardware.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>