<div class="px-4 py-3 bg-[#fdd835]">
    <div class="flex items-center justify-between gap-4">
        <!-- Left: Hamburger button -->
        <button @click="mobileOpen = true" aria-label="Open menu" class="text-black hover:bg-black/5 p-2 rounded-xl transition-all cursor-pointer">
            <svg class="w-6 h-6" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="18" height="2" rx="1" fill="currentColor"></rect>
                <rect y="6" width="18" height="2" rx="1" fill="currentColor"></rect>
                <rect y="12" width="18" height="2" rx="1" fill="currentColor"></rect>
            </svg>
        </button>

        <!-- Center: Branding Logo -->
        <div class="flex-1 flex justify-center">
            <a href="/" class="flex items-center">
                @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                    <img src="{{ $logo }}" class="h-6 w-auto object-contain" alt="{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}">
                @else
                    <span class="font-extrabold text-xl text-black tracking-tight">{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</span>
                @endif
            </a>
        </div>

        <!-- Right: Actions/Icons -->
        <div class="flex items-center space-x-1 sm:space-x-2 text-black">

            @guest
                <!-- Guest User Login Icon -->
                <a href="/login" class="p-2 hover:bg-black/5 rounded-xl transition-all" aria-label="Log in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"></path>
                    </svg>
                </a>
            @else
                <!-- Notifications -->
                <a href="/dashboard/notifications" class="p-2 hover:bg-black/5 rounded-xl transition-all relative" aria-label="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"></path>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-600 rounded-full"></span>
                </a>

                <!-- Cart -->
                <a href="/cart" class="p-2 hover:bg-black/5 rounded-xl transition-all relative" aria-label="Shopping Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"></path>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-600 rounded-full"></span>
                </a>

                <!-- Dashboard Icon (Avatar) -->
                <a href="/dashboard" class="flex items-center ml-1" aria-label="Dashboard">
                    <div class="w-7 h-7 rounded-full bg-black/10 flex items-center justify-center font-bold text-xs text-black border border-black/10">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </a>
            @endguest
        </div>
    </div>

    <!-- Search Row Below (Full Width) -->
    <div class="mt-2">
        <form action="/listings" method="GET" class="w-full">
            <div class="relative flex items-center bg-white border border-black/10 rounded-xl overflow-hidden shadow-sm w-full focus-within:ring-2 focus-within:ring-black/10 focus-within:border-black/20 transition-all">
                <input name="search" value="{{ request('search') }}" type="search" placeholder="Search components, brands, parts..." autocomplete="off"
                    class="w-full pl-4 pr-10 py-2.5 text-xs text-zinc-900 placeholder-zinc-450 bg-transparent outline-none border-none">
                <button type="submit" class="absolute right-2 p-1.5 text-zinc-500 hover:text-black transition-colors rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2".5 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
