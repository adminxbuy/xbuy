<div class="bg-zinc-50 border-b border-zinc-200/60 text-[11px] text-zinc-500 font-medium">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
 <!-- Left: Slogan/Tagline -->
 <div class="flex items-center space-x-1.5">
 <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2"
 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path stroke-linecap="round" stroke-linejoin="round"
 d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z">
 </path>
 </svg>
 <span>India's Safest PC Parts Escrow Marketplace</span>
 </div>

 <!-- Right: Utility Links -->
 <div class="flex items-center space-x-5">
 <!-- App download link -->
 @php $playstoreUrl = \App\Models\SiteSetting::getVal('playstore_url', '#'); @endphp
 <a href="{{ $playstoreUrl }}" @if($playstoreUrl !== '#') target="_blank" @endif
 class="hover:text-black transition-colors flex items-center space-x-1">
 <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
 stroke-linecap="round" stroke-linejoin="round">
 <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
 <line x1="12" y1="18" x2="12.01" y2="18"></line>
 </svg>
 <span>Get App</span>
 </a>

 <a href="/v1/pages/faq" class="hover:text-black transition-colors">Help Center</a>
 <a href="/dashboard/listings/create" class="hover:text-[#ffd747] transition-colors font-medium">Sell on
 X-Buy</a>

 @auth
 <div class="h-3 w-px bg-zinc-300"></div>
 <span class="text-zinc-400">Hello, <span
 class="font-medium text-zinc-650">{{ explode(' ', trim(auth()->user()->name))[0] }}</span></span>
 <a href="/dashboard" class="hover:text-black transition-colors font-medium">Dashboard</a>
 <form action="/logout" method="POST" class="inline">
 @csrf
 <button type="submit" class="hover:text-red-500 transition-colors font-medium cursor-pointer">Log
 out</button>
 </form>
 @endauth
 </div>
 </div>
</div>