@php
    $drawerCategories = \App\Models\Category::active()->parentOnly()->with('children')->orderBy('sort_order')->get();
@endphp

<!-- Side Drawer Component (Desktop & Mobile) -->
<div x-show="mobileOpen" 
     class="fixed inset-0 z-[100]" 
     style="display: none;" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Backdrop -->
    <div x-show="mobileOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm"
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
        <div class="px-4 py-5 bg-[#fdd835] flex items-center justify-between border-b border-zinc-200/50 flex-shrink-0">
            <span class="font-bold text-black text-lg tracking-tight">Navigation</span>
            <button @click="mobileOpen = false" class="text-black hover:bg-black/10 p-1.5 rounded-lg transition-all cursor-pointer" aria-label="Close menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            
            <!-- Top: Category Dropdowns List -->
            <div class="p-4">
                <div class="px-2 space-y-1.5">
                    @foreach($drawerCategories as $pCat)
                        <div x-data="{ openSub: false }" class="py-1">
                            <!-- Parent Category Row -->
                            @if($pCat->children->count() > 0)
                                <div @click="openSub = !openSub" class="flex items-center justify-between p-1.5 rounded-xl hover:bg-zinc-50 transition-all cursor-pointer">
                                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-zinc-200/50 flex items-center justify-center bg-white">
                                            @if($pCat->image)
                                                <img src="{{ $pCat->image }}" alt="{{ $pCat->name }}" class="w-full h-full object-cover">
                                            @else
                                                <!-- Fallback Grid Icon -->
                                                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <rect x="3" y="3" width="7" height="9" rx="1" />
                                                    <rect x="14" y="3" width="7" height="5" rx="1" />
                                                    <rect x="14" y="12" width="7" height="9" rx="1" />
                                                    <rect x="3" y="16" width="7" height="5" rx="1" />
                                                </svg>
                                            @endif
                                        </div>
                                        <span class="text-xs font-semibold text-zinc-800 hover:text-black transition-colors truncate">{{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}</span>
                                    </div>
                                    <button class="p-2 text-zinc-400 hover:text-black hover:bg-zinc-100 rounded-lg transition-all focus:outline-none">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openSub ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <a href="/listings?category={{ $pCat->slug }}" class="flex items-center justify-between p-1.5 rounded-xl hover:bg-zinc-50 transition-all cursor-pointer">
                                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-zinc-200/50 flex items-center justify-center bg-white">
                                            @if($pCat->image)
                                                <img src="{{ $pCat->image }}" alt="{{ $pCat->name }}" class="w-full h-full object-cover">
                                            @else
                                                <!-- Fallback Grid Icon -->
                                                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <rect x="3" y="3" width="7" height="9" rx="1" />
                                                    <rect x="14" y="3" width="7" height="5" rx="1" />
                                                    <rect x="14" y="12" width="7" height="9" rx="1" />
                                                    <rect x="3" y="16" width="7" height="5" rx="1" />
                                                </svg>
                                            @endif
                                        </div>
                                        <span class="text-xs font-semibold text-zinc-800 hover:text-black transition-colors truncate">{{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}</span>
                                    </div>
                                </a>
                            @endif
                            
                            <!-- Subcategories Dropdown -->
                            @if($pCat->children->count() > 0)
                                <div x-show="openSub" x-collapse class="pl-14 pr-2 py-1 space-y-1.5" style="display: none;">
                                    <a href="/listings?category={{ $pCat->slug }}" class="block py-1 text-zinc-500 hover:text-[#fbc02d] text-[11px] font-medium">
                                        Browse All {{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}
                                    </a>
                                    @foreach($pCat->children as $cCat)
                                        <a href="/listings?category={{ $cCat->slug }}" class="block py-1 text-zinc-500 hover:text-[#fbc02d] text-[11px] font-medium">
                                            {{ $cCat->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Bottom: User Profile or Login/Signup Section (Sticky/Fixed to Bottom) -->
        <div class="border-t border-zinc-100 bg-zinc-50 p-5 flex-shrink-0">
                @auth
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-[#fdd835] flex items-center justify-center text-black font-bold border border-black/10">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-zinc-900 text-sm truncate">{{ auth()->user()->name }}</h4>
                            <p class="text-xs text-zinc-505 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-2">
                        @if(auth()->user()->is_admin || auth()->user()->is_staff)
                            <a href="/admin/dashboard" class="block w-full px-4 py-2.5 text-[15px] text-zinc-900 hover:bg-zinc-100 rounded-lg transition-colors font-semibold">
                                Admin Dashboard
                            </a>
                        @endif

                        <a href="/dashboard" class="block w-full px-4 py-2.5 text-[15px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
                            Profile
                        </a>
                        
                        <a href="/dashboard/settings" class="block w-full px-4 py-2.5 text-[15px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
                            Settings
                        </a>

                        <a href="/dashboard/wallet" class="block w-full px-4 py-2.5 text-[15px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
                            Wallet
                        </a>

                        <a href="/dashboard/orders" class="block w-full px-4 py-2.5 text-[15px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
                            My orders
                        </a>

                        <form action="/logout" method="POST" class="block w-full">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2.5 text-[15px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal cursor-pointer">
                                Log out
                            </button>
                        </form>
                    </div>
                @else
                    <div class="space-y-3">
                        <p class="text-xs text-zinc-500 font-medium text-center">Log in to buy, sell, and track your components securely.</p>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="/login" class="bg-black text-white text-center py-2.5 rounded-[0.45rem] text-xs font-medium border border-black/10">Log In</a>
                            <a href="/register" class="bg-[#fdd835] text-black text-center py-2.5 rounded-[0.45rem] text-xs font-medium border border-[#fdd835]">Sign Up</a>
                        </div>
                    </div>
                @endauth
            </div>

    </div>
</div>
