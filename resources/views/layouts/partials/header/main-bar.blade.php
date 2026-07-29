<div class="bg-[#fdd835]">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="flex items-center justify-between h-[72px] gap-6">
  <!-- Logo & Menu Button -->
 <div class="flex-shrink-0 flex items-center space-x-3">
 <button @click="mobileOpen = true" aria-label="Open menu" class="text-black hover:bg-black/5 p-1.5 rounded-lg transition-all cursor-pointer">
 <svg class="w-6 h-6" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
 <rect width="18" height="2" rx="1" fill="currentColor"></rect>
 <rect y="6" width="18" height="2" rx="1" fill="currentColor"></rect>
 <rect y="12" width="18" height="2" rx="1" fill="currentColor"></rect>
 </svg>
 </button>
 <a href="/" class="flex items-center space-x-2">
 @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
 <img src="{{ $logo }}" class="h-10 w-auto object-contain" alt="{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}">
 @else
 <span class="font-extrabold text-3xl text-black tracking-tight">{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</span>
 @endif
 </a>
 </div>

 <!-- Center: Search Bar (Mercari Style) -->
 <div class="flex-1 max-w-3xl relative" @click.away="searchFocused = false">
 <form action="/listings" method="GET" class="w-full">
 <div class="relative flex items-center bg-white border border-zinc-200/50 rounded-full overflow-hidden transition-all duration-200 focus-within:ring-2 focus-within:ring-[#fdd835]/40 focus-within:border-[#fdd835]/80 focus-within:">
 <input name="search"  value="{{ request('search') }}"  type="search"  placeholder="Search for components, brands, parts..."  autocomplete="off"
 @focus="searchFocused = true"
 class="w-full pl-6 pr-12 py-3 text-sm text-zinc-900 placeholder-zinc-400 bg-transparent outline-none border-none">
 <button type="submit" class="absolute right-3 p-2 text-zinc-400 hover:text-black transition-colors rounded-full">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z"></path>
 </svg>
 </button>
 </div>
 </form>

 <!-- Search Dropdown Card -->
 <div x-show="searchFocused"
 x-transition:enter="transition ease-out duration-150"
 x-transition:enter-start="opacity-0 translate-y-1"
 x-transition:enter-end="opacity-100 translate-y-0"
 x-transition:leave="transition ease-in duration-100"
 x-transition:leave-start="opacity-100 translate-y-0"
 x-transition:leave-end="opacity-0 translate-y-1"
 class="absolute left-0 right-0 mt-2 bg-white border border-zinc-200 shadow-xl rounded-2xl p-6 z-50"
 style="display: none;">
  <div class="flex items-center justify-between">
 <h3 class="text-sm font-bold text-zinc-900">Trending brands</h3>
 <a href="/listings" class="text-xs font-bold text-yellow-600 hover:text-yellow-700 transition-colors">View more</a>
 </div>
  <div class="flex flex-wrap gap-2.5 mt-4">
 @php
 $trendingBrands = ['NVIDIA', 'Intel', 'AMD', 'ASUS', 'MSI', 'Gigabyte', 'Corsair', 'G.Skill', 'Samsung', 'Crucial', 'Kingston', 'NZXT', 'Logitech', 'Razer', 'Seasonic'];
 @endphp
 @foreach($trendingBrands as $brand)
 <a href="/listings?search={{ urlencode($brand) }}"  class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 transition-all text-xs font-semibold text-zinc-700 hover:text-zinc-900 rounded-full">
 {{ $brand }}
 </a>
 @endforeach
 </div>
 </div>
 </div>

 <!-- Right Side: User Menu and CTA -->
 <div class="flex items-center space-x-4">

 @guest
 <a href="/register" class="text-black font-medium text-sm">Sign up</a>
 <a href="/login" class="text-black font-medium text-sm">Log in</a>
 @endguest

 <!-- Separator -->
 <div class="h-5 w-px bg-black/30"></div>

 <!-- Wishlist & Cart Group (with smaller gap) -->
 <div class="flex items-center space-x-1">
 <!-- Wishlist Icon -->
 @auth
 <a href="/dashboard/listings" class="text-black p-1.5 hover:scale-105 transition-all" aria-label="Wishlist">
 @else
 <a href="/login" class="text-black p-1.5 hover:scale-105 transition-all" aria-label="Wishlist">
 @endauth
 <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"></path>
 </svg>
 </a>

 <!-- Cart Icon -->
 <a href="/cart" class="relative text-black p-1.5 hover:scale-105 transition-all" aria-label="Cart">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"></path>
 </svg>
 @auth
 <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-600 rounded-full ring-2 ring-white"></span>
 @endauth
 </a>

 @auth
 <!-- Notifications -->
 <a href="/dashboard/notifications" class="relative text-black p-1.5 hover:scale-105 transition-all" aria-label="Notifications">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"></path>
 </svg>
 <span class="absolute top-1 right-1 w-2 h-2 bg-rose-600 rounded-full ring-2 ring-white"></span>
 </a>
 @endauth
 </div>

 @auth
 <!-- User Dropdown Menu -->
 <div class="relative" @click.away="userMenuOpen = false">
 <button @click="userMenuOpen = !userMenuOpen"  class="flex items-center space-x-1.5 text-zinc-700 hover:text-black font-medium text-sm transition-all focus:outline-none cursor-pointer">
 <div class="w-8 h-8 rounded-full bg-zinc-100 flex items-center justify-center font-bold text-xs text-black border border-zinc-200/80 hover:bg-[#fdd835]/20 hover:border-[#fdd835] transition-all">
 {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
 </div>
 <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
 </svg>
 </button>

 <!-- Profile Dropdown Card -->
 <div x-show="userMenuOpen"
 x-transition:enter="transition ease-out duration-150"
 x-transition:enter-start="opacity-0 scale-95"
 x-transition:enter-end="opacity-100 scale-100"
 x-transition:leave="transition ease-in duration-100"
 x-transition:leave-start="opacity-100 scale-100"
 x-transition:leave-end="opacity-0 scale-95"
 class="absolute right-0 mt-2.5 w-56 rounded-xl bg-white border border-zinc-200 shadow-lg p-1.5 z-50"
 style="display: none;">
  @if(auth()->user()->is_admin || auth()->user()->is_staff)
 <a href="/admin/dashboard" class="block w-full px-4 py-2 text-[14px] text-zinc-900 hover:bg-zinc-100 rounded-lg transition-colors font-semibold">
 Admin Dashboard
 </a>
 @endif

 <a href="/dashboard" class="block w-full px-4 py-2 text-[14px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
 Profile
 </a>

 <a href="/dashboard/settings" class="block w-full px-4 py-2 text-[14px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
 Settings
 </a>

 <a href="/dashboard/wallet" class="block w-full px-4 py-2 text-[14px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
 Wallet
 </a>

 <a href="/dashboard/orders" class="block w-full px-4 py-2 text-[14px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal">
 My orders
 </a>

 <form action="/logout" method="POST" class="block w-full">
 @csrf
 <button type="submit" class="block w-full text-left px-4 py-2 text-[14px] text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors font-normal cursor-pointer">
 Log out
 </button>
 </form>
 </div>
 </div>
 @endauth

 <!-- Sell CTA -->
 <a href="/dashboard/listings/create"  class="bg-black text-white font-semibold px-6 py-1.5 rounded-[0.45rem] text-sm flex items-center justify-center">
 <span>Sell</span>
 </a>
 </div>
 </div>
</div>
</div>
