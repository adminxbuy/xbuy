<!-- Mercari-Style Native Mobile Bottom Dock (< 1024px) -->
<nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-zinc-200 z-50 h-16 bottom-dock-shadow select-none">
    <div class="grid grid-cols-5 h-full max-w-md mx-auto relative items-center px-2">
        
        <!-- 1. Home -->
        <a href="/" 
           class="flex flex-col items-center justify-center py-1 text-center group {{ request()->is('/') ? 'text-zinc-950 font-bold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <svg class="w-5 h-5 {{ request()->is('/') ? 'stroke-[2.2]' : 'stroke-[1.7]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <span class="text-[10px] mt-1 font-medium tracking-tight">Home</span>
        </a>

        <!-- 2. Explore / Browse -->
        <a href="/listings" 
           class="flex flex-col items-center justify-center py-1 text-center group {{ request()->is('listings*') ? 'text-zinc-950 font-bold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <svg class="w-5 h-5 {{ request()->is('listings*') ? 'stroke-[2.2]' : 'stroke-[1.7]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" fill="currentColor" fill-opacity="{{ request()->is('listings*') ? '0.2' : '0' }}" />
            </svg>
            <span class="text-[10px] mt-1 font-medium tracking-tight">Explore</span>
        </a>

        <!-- 3. Sell (Raised Center Floating Button) -->
        <div class="flex flex-col items-center justify-center relative">
            <a href="/dashboard/listings/create" 
               class="w-12 h-12 bg-[#FDD835] hover:bg-[#FBC02D] text-zinc-950 rounded-full shadow-lg flex items-center justify-center -translate-y-3 border-4 border-white active:scale-95 transition-transform"
               aria-label="Sell on X-Buy">
                <svg class="w-6 h-6 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </a>
            <span class="text-[10px] -mt-2 font-bold text-zinc-900 tracking-tight">Sell</span>
        </div>

        <!-- 4. Alerts / Notifications -->
        @auth
        <a href="/dashboard/notifications" 
           class="flex flex-col items-center justify-center py-1 text-center relative group {{ request()->is('dashboard/notifications*') ? 'text-zinc-950 font-bold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <div class="relative">
                <svg class="w-5 h-5 {{ request()->is('dashboard/notifications*') ? 'stroke-[2.2]' : 'stroke-[1.7]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-600 rounded-full ring-2 ring-white"></span>
            </div>
            <span class="text-[10px] mt-1 font-medium tracking-tight">Alerts</span>
        </a>
        @else
        <a href="/login" 
           class="flex flex-col items-center justify-center py-1 text-center group text-zinc-500 hover:text-zinc-900">
            <svg class="w-5 h-5 stroke-[1.7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <span class="text-[10px] mt-1 font-medium tracking-tight">Alerts</span>
        </a>
        @endauth

        <!-- 5. Profile / My Page -->
        @auth
        <a href="/dashboard" 
           class="flex flex-col items-center justify-center py-1 text-center group {{ request()->is('dashboard*') && !request()->is('dashboard/notifications*') ? 'text-zinc-950 font-bold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <div class="w-5 h-5 rounded-full bg-zinc-900 text-white flex items-center justify-center text-[10px] font-extrabold uppercase ring-1 ring-zinc-300">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <span class="text-[10px] mt-1 font-medium tracking-tight">Profile</span>
        </a>
        @else
        <a href="/login" 
           class="flex flex-col items-center justify-center py-1 text-center group text-zinc-500 hover:text-zinc-900">
            <svg class="w-5 h-5 stroke-[1.7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            <span class="text-[10px] mt-1 font-medium tracking-tight">Log in</span>
        </a>
        @endauth

    </div>
</nav>
