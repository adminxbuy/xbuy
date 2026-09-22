<!-- Compact Mobile Header (< 1024px) -->
<div class="px-4 py-3 bg-white border-b border-zinc-200">
    <div class="flex items-center justify-between gap-3">
        <!-- Left: Hamburger button -->
        <button @click="mobileOpen = true" aria-label="Open menu" class="text-zinc-900 hover:bg-zinc-100 p-2 rounded-xl transition-all cursor-pointer">
            <svg class="w-6 h-6 stroke-[2]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="3" y1="6" x2="21" y2="6" stroke-linecap="round"/>
                <line x1="3" y1="12" x2="21" y2="12" stroke-linecap="round"/>
                <line x1="3" y1="18" x2="21" y2="18" stroke-linecap="round"/>
            </svg>
        </button>

        <!-- Center: Branding Logo -->
        <div class="flex-1 flex justify-center">
            <a href="/" class="flex items-center">
                @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                    <img src="{{ $logo }}" class="h-8 w-auto object-contain" alt="{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}">
                @else
                    <div class="flex items-center gap-1">
                        <span class="bg-[#FDD835] text-black font-black text-base px-1.5 py-0.5 rounded-md">X</span>
                        <span class="font-black text-xl text-zinc-950 tracking-tight">BUY</span>
                    </div>
                @endif
            </a>
        </div>

        <!-- Right: Cart & Wishlist Trigger -->
        <div class="flex items-center space-x-1 text-zinc-900">
            <!-- Cart Icon (Triggers drawer) -->
            <button type="button" 
                    @click="window.dispatchEvent(new CustomEvent('open-cart'))" 
                    class="p-2 hover:bg-zinc-100 rounded-xl transition-all relative cursor-pointer" 
                    aria-label="Shopping Cart">
                <svg class="w-5 h-5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Search Row Below (Full Width Mercari Pill) -->
    <div class="mt-2.5 relative" @click.away="searchFocused = false">
        <form action="/listings" method="GET" class="w-full">
            <div class="relative flex items-center bg-zinc-100 border border-zinc-200 rounded-full overflow-hidden w-full focus-within:bg-white focus-within:border-zinc-900 focus-within:ring-2 focus-within:ring-zinc-900/10 transition-all">
                <svg class="w-4 h-4 text-zinc-400 ml-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                </svg>
                <input name="search" 
                       value="{{ request('search') }}" 
                       type="search" 
                       placeholder="Search RTX 4090, CPUs, brands..." 
                       autocomplete="off"
                       @focus="searchFocused = true"
                       class="w-full pl-2.5 pr-10 py-2.5 text-xs text-zinc-900 placeholder-zinc-400 bg-transparent outline-none border-none">
                <button type="submit" class="absolute right-2 p-1.5 text-zinc-400 hover:text-black transition-colors rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Mobile Quick Tag Dropdown Card -->
        <div x-show="searchFocused"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="absolute left-0 right-0 mt-2 bg-white border border-zinc-200 shadow-xl rounded-2xl p-4 z-50"
             style="display: none;">
            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                <h3 class="text-xs font-bold text-zinc-900">Trending gear</h3>
                <a href="/listings" class="text-[10px] font-bold text-amber-600 hover:underline">View all</a>
            </div>
            <div class="flex flex-wrap gap-1.5 mt-2.5">
                @php
                    $mobileTags = ['RTX 4080', 'Ryzen 7', 'PS5 Pro', 'OLED Monitor', 'DDR5 32GB', 'Gaming Headset'];
                @endphp
                @foreach($mobileTags as $tag)
                    <a href="/listings?search={{ urlencode($tag) }}" 
                       class="px-2.5 py-1 bg-zinc-100 text-[11px] font-medium text-zinc-800 rounded-full hover:bg-zinc-200">
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
