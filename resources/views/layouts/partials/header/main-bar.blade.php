<!-- Desktop Main Header Bar (Mercari Omnibar Architecture) -->
<div class="bg-white border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-[72px] gap-6">
            
            <!-- Left: Logo & Category Toggle -->
            <div class="flex-shrink-0 flex items-center space-x-4">
                <a href="/" class="flex items-center space-x-2">
                    @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                        <img src="{{ $logo }}" class="h-10 w-auto object-contain" alt="{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}">
                    @else
                        <div class="flex items-center gap-1.5">
                            <span class="bg-[#FDD835] text-black font-black text-xl px-2 py-0.5 rounded-md tracking-tight">X</span>
                            <span class="font-extrabold text-2xl text-zinc-950 tracking-tight">BUY</span>
                        </div>
                    @endif
                </a>
            </div>

            <!-- Center: Omnibar Search Component (Mercari Style) -->
            <div class="flex-1 max-w-2xl relative" 
                 x-data="{
                     query: '{{ request('search') }}',
                     recentSearches: [],
                     init() {
                         try {
                             this.recentSearches = JSON.parse(localStorage.getItem('xbuy_recent_searches') || '[]');
                         } catch(e) {
                             this.recentSearches = [];
                         }
                     },
                     recordSearch(term) {
                         if (!term || !term.trim()) return;
                         let searches = this.recentSearches.filter(s => s.toLowerCase() !== term.toLowerCase().trim());
                         searches.unshift(term.trim());
                         if (searches.length > 6) searches = searches.slice(0, 6);
                         this.recentSearches = searches;
                         localStorage.setItem('xbuy_recent_searches', JSON.stringify(searches));
                     },
                     clearRecent() {
                         this.recentSearches = [];
                         localStorage.removeItem('xbuy_recent_searches');
                     }
                 }" 
                 @click.away="searchFocused = false">
                
                <form action="/listings" method="GET" class="w-full" @submit="recordSearch(query)">
                    <div class="relative flex items-center h-11 bg-white border border-zinc-300 rounded-full px-4 transition-all duration-200 focus-within:border-zinc-900 focus-within:ring-2 focus-within:ring-zinc-900/10 shadow-xs">
                        <svg class="w-4 h-4 text-zinc-400 shrink-0 mr-2.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                        </svg>
                        <input name="search" 
                               x-model="query"
                               type="search" 
                               placeholder="Search for anything (e.g. RTX 4090, Ryzen 7, PS5 Pro)..." 
                               autocomplete="off"
                               @focus="searchFocused = true"
                               class="w-full text-sm text-zinc-900 placeholder-zinc-400 bg-transparent outline-none border-none pr-8">
                        <button type="submit" class="absolute right-2 p-1.5 text-zinc-400 hover:text-zinc-900 transition-colors" aria-label="Submit search">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Auto-Suggest Drawer (Alpine.js) -->
                <div x-show="searchFocused"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute left-0 right-0 mt-2 bg-white border border-zinc-200 shadow-xl rounded-2xl p-5 z-50 divide-y divide-zinc-100"
                     style="display: none;">
                    
                    <!-- Section A: Recent Searches -->
                    <div class="pb-3" x-show="recentSearches.length > 0">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">Recent Searches</span>
                            <button type="button" @click="clearRecent" class="text-[11px] font-semibold text-zinc-500 hover:text-red-600 transition-colors">Clear all</button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="item in recentSearches" :key="item">
                                <a :href="'/listings?search=' + encodeURIComponent(item)" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-medium rounded-full transition-colors">
                                    <svg class="w-3 h-3 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <span x-text="item"></span>
                                </a>
                            </template>
                        </div>
                    </div>

                    <!-- Section B: Popular on X-Buy -->
                    <div class="py-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">Popular on X-Buy 🔥</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $popularTags = ['RTX 4090', 'RTX 4080 Super', 'Ryzen 7 7800X3D', 'PlayStation 5 Pro', 'Mechanical Keyboard', 'OLED Monitor', 'DDR5 32GB', 'RX 7800 XT'];
                            @endphp
                            @foreach($popularTags as $tag)
                                <a href="/listings?search={{ urlencode($tag) }}" 
                                   class="px-3 py-1 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 text-zinc-700 hover:text-zinc-950 text-xs font-semibold rounded-full transition-all">
                                    {{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Section C: Direct Category Matches -->
                    <div class="pt-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 block mb-2">Explore Categories</span>
                        @php
                            $quickCats = \App\Models\Category::active()->parentOnly()->orderBy('sort_order')->take(4)->get();
                        @endphp
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($quickCats as $qc)
                                <a href="/listings?category={{ $qc->slug }}" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-zinc-50 transition-colors text-xs font-medium text-zinc-800">
                                    <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-[10px]">
                                        {{ substr($qc->name, 0, 1) }}
                                    </div>
                                    <span class="truncate">{{ $qc->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Action Stack (Mercari Signature) -->
            <div class="flex items-center space-x-3">
                
                <!-- Quick Sell CTA Pill -->
                <a href="/dashboard/listings/create" 
                   class="bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold px-5 py-2.5 rounded-full text-sm shadow-sm transition-transform active:scale-95 flex items-center gap-1.5 select-none">
                    <svg class="w-4 h-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Sell on X-Buy</span>
                </a>

                <!-- Wishlist Heart Pill -->
                @auth
                    <a href="/dashboard/orders?order_type=bought" 
                       class="p-2 text-zinc-700 hover:text-zinc-950 hover:bg-zinc-100 rounded-full transition-colors relative"
                       title="Saved Items">
                        <svg class="w-6 h-6 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </a>
                @else
                    <a href="/login" 
                       class="p-2 text-zinc-700 hover:text-zinc-950 hover:bg-zinc-100 rounded-full transition-colors"
                       title="Saved Items">
                        <svg class="w-6 h-6 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </a>
                @endauth

                <!-- Cart Pill (Triggers slide-out cart drawer) -->
                <button type="button" 
                        @click="window.dispatchEvent(new CustomEvent('open-cart'))"
                        class="p-2 text-zinc-700 hover:text-zinc-950 hover:bg-zinc-100 rounded-full transition-colors relative cursor-pointer"
                        title="Shopping Cart">
                    <svg class="w-6 h-6 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <span x-data="{ count: 0 }" 
                          x-init="
                              try {
                                  const items = JSON.parse(localStorage.getItem('xbuy_cart') || '[]');
                                  count = items.length;
                              } catch(e) {}
                              window.addEventListener('cart-updated', () => {
                                  try {
                                      const items = JSON.parse(localStorage.getItem('xbuy_cart') || '[]');
                                      count = items.length;
                                  } catch(e) {}
                              });
                          " 
                          x-show="count > 0" 
                          x-text="count" 
                          class="absolute top-1 right-1 w-4 h-4 bg-red-600 text-white font-extrabold text-[10px] rounded-full flex items-center justify-center ring-2 ring-white"
                          style="display: none;">
                    </span>
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-zinc-200"></div>

                <!-- User Profile / Auth Links -->
                @guest
                    <div class="flex items-center gap-2">
                        <a href="/login" class="text-xs font-bold text-zinc-800 hover:text-zinc-950 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors">Log in</a>
                        <a href="/register" class="text-xs font-extrabold text-zinc-950 bg-zinc-100 hover:bg-zinc-200 px-4 py-2 rounded-full transition-colors border border-zinc-200">Sign up</a>
                    </div>
                @else
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" 
                                class="flex items-center gap-2 text-zinc-800 hover:text-zinc-950 font-medium text-xs p-1 rounded-full hover:bg-zinc-100 transition-colors focus:outline-none cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-zinc-900 text-white flex items-center justify-center font-extrabold text-xs">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <svg class="w-3.5 h-3.5 text-zinc-500 transition-transform duration-150" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <!-- User Menu Dropdown -->
                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2.5 w-60 rounded-2xl bg-white border border-zinc-200 shadow-xl p-2 z-50 divide-y divide-zinc-100"
                             style="display: none;">
                            
                            <!-- User header -->
                            <div class="px-3 py-2.5">
                                <p class="text-xs font-bold text-zinc-950 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                                <div class="mt-2 flex items-center justify-between text-[11px] bg-amber-50 text-amber-900 px-2.5 py-1 rounded-lg font-semibold">
                                    <span>Wallet Balance:</span>
                                    <span class="font-extrabold">₹{{ number_format(auth()->user()->wallet?->balance ?? 0) }}</span>
                                </div>
                            </div>

                            <!-- Menu links -->
                            <div class="py-1.5 space-y-0.5 text-xs font-medium text-zinc-700">
                                @if(auth()->user()->is_admin || auth()->user()->is_staff)
                                    <a href="/admin/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-xl text-amber-700 hover:bg-amber-50 font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="m14 9 3 3-3 3"/></svg>
                                        Admin Panel
                                    </a>
                                @endif
                                <a href="/dashboard/orders?order_type=bought" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-zinc-100 hover:text-zinc-950">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                    My Purchases
                                </a>
                                <a href="/dashboard/orders?order_type=sold" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-zinc-100 hover:text-zinc-950">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                    My Sales & Listings
                                </a>
                                <a href="/dashboard/wallet" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-zinc-100 hover:text-zinc-950">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6m0 0H9m12 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/></svg>
                                    Wallet & Payouts
                                </a>
                                <a href="/dashboard/settings" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-zinc-100 hover:text-zinc-950">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.6 6.6 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/></svg>
                                    Account Settings
                                </a>
                            </div>

                            <!-- Logout form -->
                            <div class="pt-1">
                                <form action="/logout" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                                        Log Out
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                @endguest

            </div>

        </div>
    </div>
</div>
