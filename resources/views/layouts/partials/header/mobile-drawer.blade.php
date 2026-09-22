@php
    $drawerCategories = \App\Models\Category::active()->parentOnly()->with(['children' => function($q) {
        $q->active()->orderBy('sort_order');
    }])->orderBy('sort_order')->get();
@endphp

<!-- Side Drawer Component (Desktop & Mobile) -->
<div x-show="mobileOpen" 
     class="fixed inset-0 z-[100]" 
     style="display: none;" 
     role="dialog" 
     aria-modal="true"
     @keydown.window.escape="mobileOpen = false">
    
    <!-- Backdrop -->
    <div x-show="mobileOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs"
         @click="mobileOpen = false"></div>

    <!-- Slide-over panel -->
    <div x-show="mobileOpen"
         x-transition:enter="transition-transform ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 flex max-w-sm w-full bg-white shadow-2xl flex-col z-[101]">
        
        <!-- Drawer Header -->
        <div class="px-5 py-4 bg-[#FDD835] flex items-center justify-between border-b border-zinc-200/50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="bg-black text-white font-black text-sm px-1.5 py-0.5 rounded">X</span>
                <span class="font-extrabold text-black text-lg tracking-tight">Navigation</span>
            </div>
            <button @click="mobileOpen = false" class="text-black hover:bg-black/10 p-1.5 rounded-lg transition-all cursor-pointer" aria-label="Close menu">
                <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
            
            <!-- Quick Sell Button in Drawer -->
            <a href="/dashboard/listings/create" 
               @click="mobileOpen = false"
               class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-zinc-950 text-white font-extrabold text-sm shadow-sm transition-transform active:scale-95">
                <svg class="w-4 h-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ List an Item to Sell</span>
            </a>

            <!-- Categories Accordion -->
            <div>
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 px-2 mb-2">Browse Departments</h4>
                <div class="space-y-1">
                    @foreach($drawerCategories as $pCat)
                        <div x-data="{ openSub: false }" class="rounded-xl border border-zinc-100 overflow-hidden">
                            @if($pCat->children->count() > 0)
                                <button type="button" 
                                        @click="openSub = !openSub" 
                                        class="w-full flex items-center justify-between p-3 text-left hover:bg-zinc-50 transition-colors">
                                    <span class="text-xs font-bold text-zinc-800">{{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}</span>
                                    <svg class="w-4 h-4 text-zinc-400 transition-transform duration-200" :class="openSub ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="openSub" x-collapse class="bg-zinc-50/70 px-4 py-2 border-t border-zinc-100 space-y-1" style="display: none;">
                                    <a href="/listings?category={{ $pCat->slug }}" 
                                       @click="mobileOpen = false"
                                       class="block py-1 text-xs font-bold text-amber-700 hover:underline">
                                        Browse All {{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }} &rarr;
                                    </a>
                                    @foreach($pCat->children as $cCat)
                                        <a href="/listings?category={{ $cCat->slug }}" 
                                           @click="mobileOpen = false"
                                           class="block py-1 text-xs text-zinc-600 hover:text-zinc-950">
                                            {{ $cCat->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <a href="/listings?category={{ $pCat->slug }}" 
                                   @click="mobileOpen = false"
                                   class="block p-3 text-xs font-bold text-zinc-800 hover:bg-zinc-50 transition-colors">
                                    {{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 px-2 mb-2">Explore & Deals</h4>
                <div class="space-y-1 text-xs font-semibold text-zinc-700">
                    <a href="/p/deals" @click="mobileOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-zinc-100">
                        <span>Hot Deals & Drops 🔥</span>
                        <span class="text-amber-600">&rarr;</span>
                    </a>
                    <a href="/listings?free_shipping=1" @click="mobileOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-zinc-100">
                        <span>Free Shipping Gear</span>
                        <span class="text-emerald-600">&rarr;</span>
                    </a>
                    <a href="/p/buyer-protection" @click="mobileOpen = false" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-zinc-100">
                        <span>Buyer Protection & Escrow</span>
                        <span>🛡️</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Bottom: User Profile / Auth Section -->
        <div class="border-t border-zinc-200 bg-zinc-50 p-4 flex-shrink-0">
            @auth
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-zinc-950 text-white flex items-center justify-center font-bold text-sm">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-zinc-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-center text-xs font-bold">
                    <a href="/dashboard/orders?order_type=bought" @click="mobileOpen = false" class="py-2 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-100 text-zinc-800">
                        My Purchases
                    </a>
                    <a href="/dashboard/orders?order_type=sold" @click="mobileOpen = false" class="py-2 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-100 text-zinc-800">
                        My Sales
                    </a>
                </div>

                <form action="/logout" method="POST" class="mt-2.5">
                    @csrf
                    <button type="submit" class="w-full py-2 text-center text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer">
                        Log Out
                    </button>
                </form>
            @else
                <div class="space-y-2">
                    <p class="text-xs text-zinc-500 font-medium text-center">Log in to buy, sell, and track your gear securely.</p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="/login" @click="mobileOpen = false" class="py-2 bg-white border border-zinc-300 rounded-xl text-xs font-bold text-zinc-900 text-center hover:bg-zinc-100">Log In</a>
                        <a href="/register" @click="mobileOpen = false" class="py-2 bg-[#FDD835] hover:bg-[#FBC02D] rounded-xl text-xs font-extrabold text-black text-center">Sign Up</a>
                    </div>
                </div>
            @endauth
        </div>

    </div>
</div>
