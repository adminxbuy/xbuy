<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') | {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* ═══════════════════════════════════════════════════════════
           Shadcn Design System — CSS Variables
           ═══════════════════════════════════════════════════════════ */
        :root {
            --radius: 0.625rem;
            --color-primary: #09090b;
            --color-primary-hover: #27272a;
            --color-primary-text: #ffffff;
            --background: #ffffff;
            --foreground: #09090b;
            --card: #ffffff;
            --card-foreground: #09090b;
            --muted: #f4f4f5;
            --muted-foreground: #71717a;
            --border: #e4e4e7;
            --input: #e4e4e7;
            --ring: #a1a1aa;
            --sidebar-bg: #fafafa;
            --sidebar-foreground: #09090b;
            --sidebar-border: #f4f4f5;
            --sidebar-accent: #f4f4f5;
            --sidebar-accent-foreground: #09090b;
        }
        [data-theme="violet"] {
            --color-primary: #8b5cf6;
            --color-primary-hover: #7c3aed;
            --color-primary-text: #ffffff;
        }
        [data-theme="emerald"] {
            --color-primary: #10b981;
            --color-primary-hover: #059669;
            --color-primary-text: #ffffff;
        }
        [data-theme="blue"] {
            --color-primary: #3b82f6;
            --color-primary-hover: #2563eb;
            --color-primary-text: #ffffff;
        }
        [data-theme="rose"] {
            --color-primary: #f43f5e;
            --color-primary-hover: #e11d48;
            --color-primary-text: #ffffff;
        }

        /* Dynamic Border Radius Overrides */
        .rounded-lg, .rounded-xl, .rounded-md, .rounded-sm, .rounded-2xl, .rounded-3xl, 
        input, select, textarea, button, a.rounded-lg, .flatpickr-calendar {
            border-radius: var(--radius) !important;
        }

        /* Dynamic Typography Scale */
        .scale-xs {
            font-size: 0.825rem !important;
        }
        .scale-xs h1, .scale-xs h2, .scale-xs h3, .scale-xs h4, .scale-xs h5, .scale-xs h6 {
            font-size: calc(100% - 2px) !important;
        }
        .scale-xs .text-sm {
            font-size: 0.75rem !important;
        }
        .scale-xs .text-base {
            font-size: 0.875rem !important;
        }
        .scale-xs .sidebar-nav a {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
        .scale-lg {
            font-size: 1.05rem !important;
        }
        .scale-lg .text-sm {
            font-size: 0.95rem !important;
        }
        .scale-lg .text-base {
            font-size: 1.125rem !important;
        }

        /* Shadcn Active Link Override — replaces yellow accent */
        .sidebar-nav a.sidebar-active {
            background-color: var(--color-primary) !important;
            color: var(--color-primary-text) !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }
        .sidebar-nav a.sidebar-active i,
        .sidebar-nav a.sidebar-active span {
            color: var(--color-primary-text) !important;
        }
        .flatpickr-day.selected, 
        .flatpickr-day.startRange, 
        .flatpickr-day.endRange,
        .flatpickr-day.selected:hover,
        .flatpickr-day.today:hover {
            background: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
            color: var(--color-primary-text) !important;
        }
        .flatpickr-day.today {
            border-color: var(--color-primary) !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover svg, 
        .flatpickr-months .flatpickr-next-month:hover svg {
            fill: var(--color-primary) !important;
        }

        /* ═══════════════════════════════════════════════════════════
           Dark Mode — Shadcn dark tokens
           ═══════════════════════════════════════════════════════════ */
        .dark {
            --background: #09090b;
            --foreground: #fafafa;
            --card: #09090b;
            --card-foreground: #fafafa;
            --muted: #27272a;
            --muted-foreground: #a1a1aa;
            --border: #27272a;
            --input: #27272a;
            --ring: #52525b;
            --sidebar-bg: #09090b;
            --sidebar-foreground: #fafafa;
            --sidebar-border: #27272a;
            --sidebar-accent: #27272a;
            --sidebar-accent-foreground: #fafafa;
        }
        .dark body, .dark aside, .dark header, .dark main {
            background-color: var(--background) !important;
            color: var(--foreground) !important;
        }
        .dark .bg-white {
            background-color: var(--background) !important;
        }
        .dark .bg-zinc-50, .dark .bg-zinc-100 {
            background-color: var(--muted) !important;
        }
        .dark .text-zinc-900, 
        .dark .text-zinc-800 {
            color: #fafafa !important;
        }
        .dark h2, .dark h3 {
            color: #fafafa !important;
        }
        .dark .text-zinc-600, 
        .dark .text-zinc-500,
        .dark .text-zinc-400 {
            color: var(--muted-foreground) !important;
        }
        .dark .border-zinc-200, 
        .dark .border-zinc-100 {
            border-color: var(--border) !important;
        }
        .dark .ring-zinc-950\/5 {
            --tw-ring-color: rgb(250 250 250 / 0.1) !important;
        }
        .dark input, 
        .dark select, 
        .dark textarea {
            background-color: #18181b !important;
            border-color: var(--border) !important;
            color: #ffffff !important;
        }
        .dark .sidebar-nav a:not(.sidebar-active):hover {
            background-color: var(--sidebar-accent) !important;
            color: var(--sidebar-accent-foreground) !important;
        }
        .dark .hover\:bg-zinc-100:hover, 
        .dark .hover\:bg-zinc-50:hover {
            background-color: var(--muted) !important;
        }

        /* ═══════════════════════════════════════════════════════════
           Collapsed Sidebar Rules
           ═══════════════════════════════════════════════════════════ */
        .sidebar-collapsed span, 
        .sidebar-collapsed p, 
        .sidebar-collapsed .text-left {
            display: none !important;
        }
        .sidebar-collapsed a {
            justify-content: center !important;
            padding-left: 0px !important;
            padding-right: 0px !important;
        }
        .sidebar-collapsed .px-6, .sidebar-collapsed .px-4 {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        .sidebar-collapsed .h-14 a {
            justify-content: center !important;
            width: 100% !important;
        }

        /* Logo visibility toggling based on sidebar collapse state */
        .logo-collapsed {
            display: none !important;
        }
        .sidebar-collapsed .logo-expanded {
            display: none !important;
        }
        .sidebar-collapsed .logo-collapsed {
            display: flex !important;
        }

        /* ═══════════════════════════════════════════════════════════
           Base Typography & Body
           ═══════════════════════════════════════════════════════════ */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--background);
            color: var(--foreground);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }

        /* Global custom scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #d4d4d8;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1aa;
        }

        /* Sidebar — show scrollbar only on hover */
        .sidebar-nav {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
            transition: scrollbar-color 0.2s;
        }
        .sidebar-nav:hover {
            scrollbar-color: #d4d4d8 transparent;
        }
        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 9999px;
            transition: background 0.2s;
        }
        .sidebar-nav:hover::-webkit-scrollbar-thumb {
            background: #d4d4d8;
        }

        /* ═══════════════════════════════════════════════════════════
           Flatpickr Overrides — Shadcn style
           ═══════════════════════════════════════════════════════════ */
        .flatpickr-calendar {
            background: var(--card) !important;
            border: 1px solid var(--border) !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
            border-radius: 0.75rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            padding: 4px;
        }
        .flatpickr-day.selected, 
        .flatpickr-day.startRange, 
        .flatpickr-day.endRange, 
        .flatpickr-day.selected.inRange, 
        .flatpickr-day.startRange.inRange, 
        .flatpickr-day.endRange.inRange, 
        .flatpickr-day.selected:focus, 
        .flatpickr-day.startRange:focus, 
        .flatpickr-day.endRange:focus, 
        .flatpickr-day.selected:hover, 
        .flatpickr-day.startRange:hover, 
        .flatpickr-day.endRange:hover, 
        .flatpickr-day.prevMonthDay.selected, 
        .flatpickr-day.nextMonthDay.selected {
            background: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
            color: var(--color-primary-text) !important;
            font-weight: 600 !important;
        }
        .flatpickr-day.today {
            border-color: var(--color-primary) !important;
        }
        .flatpickr-day.today:hover {
            background: var(--color-primary) !important;
            color: var(--color-primary-text) !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            font-weight: 600 !important;
        }
        .flatpickr-day:hover {
            background: var(--muted) !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover svg, 
        .flatpickr-months .flatpickr-next-month:hover svg {
            fill: var(--color-primary) !important;
        }

        /* ═══════════════════════════════════════════════════════════
           Shadcn Card Utility — ring-based borders
           ═══════════════════════════════════════════════════════════ */
        .shadcn-card {
            background: var(--card);
            border-radius: 0.75rem;
            box-shadow: 0 0 0 1px rgb(9 9 11 / 0.05);
            overflow: hidden;
        }
        .dark .shadcn-card {
            box-shadow: 0 0 0 1px rgb(250 250 250 / 0.1);
        }
    </style>
    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.tinymce) {
                tinymce.init({
                    selector: '.tinymce-editor',
                    height: 450,
                    menubar: false,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
                    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code fullscreen preview',
                    branding: false,
                    promotion: false,
                    setup: function (editor) {
                        editor.on('change keyup paste', function () {
                            editor.save();
                            let event = new Event('input', { bubbles: true });
                            editor.getElement().dispatchEvent(event);
                        });
                    }
                });
            }
        });
    </script>
</head>
<body class="bg-white h-screen overflow-hidden flex flex-col md:flex-row transition-colors duration-200" 
      x-data="{ 
          sidebarOpen: window.innerWidth >= 768, 
          themePreset: localStorage.getItem('themePreset') || 'default', 
          themeScale: localStorage.getItem('themeScale') || 'default',
          themeRadius: localStorage.getItem('themeRadius') || 'lg',
          isDark: localStorage.getItem('darkMode') === 'true',
          layoutMode: localStorage.getItem('layoutMode') || 'full',
          sidebarMode: localStorage.getItem('sidebarMode') || 'default'
      }" 
      :class="{
          'dark': isDark,
          'scale-xs': themeScale === 'xs',
          'scale-lg': themeScale === 'lg'
      }" 
      :style="'--radius: ' + (themeRadius === 'none' ? '0px' : (themeRadius === 'sm' ? '0.25rem' : (themeRadius === 'md' ? '0.375rem' : (themeRadius === 'lg' ? '0.5rem' : '0.75rem'))))"
      :data-theme="themePreset">

    <!-- Sidebar -->
    <aside class="w-full md:w-64 md:h-screen bg-white border-b md:border-b-0 md:border-r border-zinc-200/80 flex-shrink-0 flex flex-col transition-all duration-300"
           :class="sidebarOpen ? (sidebarMode === 'default' ? 'block md:flex md:w-64' : 'block md:flex md:w-20 sidebar-collapsed') : 'hidden md:flex md:w-0 md:overflow-hidden md:border-r-0'">
        <div class="h-14 flex items-center justify-between px-4 border-b border-zinc-100">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5">
                <!-- Website Logo (Shows when sidebar is expanded) -->
                <div class="logo-expanded flex items-center space-x-2.5">
                    @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                        <img src="{{ $logo }}" class="h-9 w-auto max-w-[140px] object-contain" alt="Logo">
                    @else
                        <div class="w-7 h-7 bg-zinc-900 rounded-lg flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0">XB</div>
                        <span class="font-semibold text-sm tracking-tight text-zinc-900">{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</span>
                    @endif
                </div>

                <!-- Website Favicon (Shows when sidebar is collapsed/icon mode) -->
                <div class="logo-collapsed w-full flex justify-center">
                    <img src="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}" class="w-7 h-7 object-contain rounded-lg ring-1 ring-zinc-950/5 flex-shrink-0" alt="Favicon">
                </div>

                <span class="logo-expanded text-[9px] bg-zinc-100 text-zinc-600 px-1.5 py-0.5 rounded-md font-semibold">Admin</span>
            </a>
            <button class="md:hidden text-zinc-400 hover:text-zinc-800 p-1" @click="sidebarOpen = false">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <p class="text-[10px] font-medium text-zinc-400 uppercase tracking-widest px-3 mb-1.5 mt-1">Commerce</p>
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.dashboard') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.sales-overview') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.sales-overview') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                <span>Sales Overview</span>
            </a>

            @if(Auth::user()->canAccess('sellers'))
            <a href="{{ route('admin.sellers') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.sellers*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Sellers</span>
                @php $pendingKyc = \App\Models\SellerProfile::where('kyc_status', 'pending')->count(); @endphp
                @if($pendingKyc > 0)
                    <span class="ml-auto bg-zinc-900 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center">{{ $pendingKyc }}</span>
                @endif
            </a>
            @endif

            @if(Auth::user()->canAccess('listings'))
            <a href="{{ route('admin.listings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.listings*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Listings</span>
                @php $pendingListings = \App\Models\Listing::where('listing_status', 'pending_approval')->count(); @endphp
                @if($pendingListings > 0)
                    <span class="ml-auto bg-zinc-900 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center">{{ $pendingListings }}</span>
                @endif
            </a>
            @endif

            @if(Auth::user()->canAccess('categories'))
            <a href="{{ route('admin.categories') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.categories*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="folder-tree" class="w-4 h-4"></i>
                <span>Categories</span>
            </a>

            <a href="{{ route('admin.spec-templates') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.spec-templates*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="sliders" class="w-4 h-4"></i>
                <span>Spec Templates</span>
            </a>

            <a href="{{ route('admin.pages.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.pages*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Policy Pages</span>
            </a>
            @endif

            @if(Auth::user()->canAccess('orders'))
            <a href="{{ route('admin.orders') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.orders*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>Orders</span>
            </a>
            @endif
            
            @if(Auth::user()->canAccess('disputes'))
            <a href="{{ route('admin.disputes') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.disputes*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>Disputes</span>
                @php $openDisputes = \App\Models\Dispute::whereIn('status', ['open', 'seller_responded', 'under_review'])->count(); @endphp
                @if($openDisputes > 0)
                    <span class="ml-auto bg-rose-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center">{{ $openDisputes }}</span>
                @endif
            </a>
            @endif

            @if(Auth::user()->canAccess('escrow'))
            <a href="{{ route('admin.escrow') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.escrow*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="shield" class="w-4 h-4"></i>
                <span>Escrow</span>
                @php $heldCount = \App\Models\Escrow::where('status','held')->count(); @endphp
                @if($heldCount > 0)
                    <span class="ml-auto bg-zinc-900 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center">{{ $heldCount }}</span>
                @endif
            </a>
            @endif

            @if(Auth::user()->canAccess('payouts'))
            <a href="{{ route('admin.payouts') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.payouts*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                <span>Payouts</span>
            </a>
            @endif

            @if(Auth::user()->canAccess('tickets'))
            <a href="{{ route('admin.tickets') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.tickets*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span>Support Tickets</span>
                @php $openTickets = \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count(); @endphp
                @if($openTickets > 0)
                    <span class="ml-auto bg-zinc-900 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center">{{ $openTickets }}</span>
                @endif
            </a>
            @endif

            <p class="text-[10px] font-medium text-zinc-400 uppercase tracking-widest px-3 pt-5 mb-1.5">Management</p>

            @if(Auth::user()->canAccess('articles'))
            <a href="{{ route('admin.articles.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.articles*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Articles / Blog</span>
            </a>

            <a href="{{ route('admin.mails.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.mails*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="mail" class="w-4 h-4"></i>
                <span>Mail Management</span>
            </a>
            @endif

            @if(Auth::user()->canAccess('analytics'))
            <a href="{{ route('admin.analytics') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.analytics*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                <span>Analytics</span>
            </a>
            @endif

            @if(Auth::user()->canAccess('users'))
            <a href="{{ route('admin.users') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.users*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Users</span>
            </a>
            @endif

            <a href="{{ route('admin.my-earnings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.my-earnings*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                <span>My Earnings</span>
            </a>

            @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('admin.content') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.content*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="folder-open" class="w-4 h-4"></i>
                <span>Content Manager</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.settings*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Settings</span>
            </a>

            <a href="{{ route('admin.accounts.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.accounts*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="shield" class="w-4 h-4"></i>
                <span>Staff Management</span>
            </a>

            <a href="{{ route('admin.staff.directory') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.staff.*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Staff Directory</span>
            </a>

            <a href="{{ route('admin.payroll') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.payroll*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="banknote" class="w-4 h-4"></i>
                <span>Payroll Overview</span>
            </a>

            <a href="{{ route('admin.audit-logs') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.audit-logs*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Audit Logs</span>
            </a>
            @endif

            @if(Auth::user()->canAccess('ratings'))
            <a href="{{ route('admin.ratings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.ratings*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="star" class="w-4 h-4"></i>
                <span>Ratings</span>
            </a>
            @endif

            @if(Auth::user()->canAccess('alerts'))
            @php 
                $unreadAlertsCount = \App\Models\AdminAlert::where('is_read', false)
                    ->where(function($q) {
                        $q->whereNull('user_id')->orWhere('user_id', Auth::id());
                    })->count(); 
            @endphp
            <a href="{{ route('admin.alerts') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.alerts*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="bell" class="w-4 h-4"></i>
                <span>Alerts</span>
                @if($unreadAlertsCount > 0)
                    <span class="ml-auto bg-rose-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center">{{ $unreadAlertsCount }}</span>
                @endif
            </a>
            @endif

            @if(Auth::user()->canAccess('fraud_flags'))
            @php $pendingFraudCount = \App\Models\FraudFlag::where('status', 'pending')->count(); @endphp
            <a href="{{ route('admin.fraud-flags.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium  {{ request()->routeIs('admin.fraud-flags*') ? 'sidebar-active' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                <span>Fraud Flags</span>
                @if($pendingFraudCount > 0)
                    <span class="ml-auto bg-rose-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center">{{ $pendingFraudCount }}</span>
                @endif
            </a>
            @endif
        </nav>

        <div class="px-3 py-3 border-t border-zinc-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=e4e4e7&color=18181b&size=32" class="w-8 h-8 rounded-lg ring-1 ring-zinc-950/5" alt="avatar">
                    <div class="text-left">
                        <p class="text-xs font-medium text-zinc-800 truncate max-w-[120px]">{{ Auth::user()->name }}</p>
                        @php
                            $roleEmojis = [
                                'super_admin' => '👑 Mallik',
                                'operations' => '⚙️ Ops Manager',
                                'support' => '🎧 Support',
                                'finance' => '💰 Finance',
                                'content' => '📝 Content',
                                'moderator' => '🛡️ Moderator',
                            ];
                            $roleLabel = $roleEmojis[Auth::user()->admin_role] ?? 'Admin';
                        @endphp
                        <p class="text-[10px] text-zinc-400 font-medium">{{ $roleLabel }}</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-zinc-400 hover:text-zinc-800 p-1.5 rounded-lg hover:bg-zinc-100 transition-all">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        <!-- Topbar -->
        <!-- Topbar -->
        <header class="h-12 border-b border-zinc-200/80 bg-white flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
            <div class="flex items-center gap-1 lg:gap-2">
                 <!-- Sidebar Toggle (Mobile & Desktop) -->
                <button class="text-zinc-500 hover:text-zinc-900 p-1.5 hover:bg-zinc-100 rounded-lg transition-colors -ml-1" 
                        @click="if (window.innerWidth >= 768) { sidebarOpen = true; sidebarMode = (sidebarMode === 'default' ? 'icon' : 'default'); localStorage.setItem('sidebarMode', sidebarMode); } else { sidebarOpen = !sidebarOpen; }">
                    <i data-lucide="panel-left" class="w-4 h-4"></i>
                </button>
                
                <!-- Separator -->
                <div class="hidden lg:block h-4 w-px bg-zinc-200 mx-2"></div>

                <!-- Search Bar (Shadcn style) -->
                <div class="relative w-56 md:w-72">
                    <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400"></i>
                    <input type="text" id="admin-global-search" placeholder="Search..." class="w-full pl-8 pr-12 py-1.5 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 transition-all h-8">
                    <kbd class="absolute right-2.5 top-1/2 -translate-y-1/2 hidden sm:inline-flex h-5 select-none items-center gap-1 rounded border border-zinc-200 bg-zinc-50 px-1.5 font-mono text-[10px] font-medium text-zinc-400">
                        <span class="text-xs">⌘</span>K
                    </kbd>
                </div>
            </div>
            
            <div class="flex items-center gap-1">
                <!-- Go to Homepage Link -->
                <a href="/" target="_blank" class="flex items-center gap-1.5 text-zinc-500 hover:text-zinc-900 text-xs font-medium transition-colors p-1.5 hover:bg-zinc-100 rounded-lg">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Homepage</span>
                </a>

                <!-- Notification Bell & Dropdown -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="relative text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 transition-colors p-1.5 rounded-lg flex items-center justify-center focus:outline-none">
                        @if($unreadAlertsCount > 0)
                            <span class="absolute top-1 right-1 w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                        @endif
                        <i data-lucide="bell" class="w-4 h-4"></i>
                    </button>
                    
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white ring-1 ring-zinc-950/5 rounded-xl shadow-lg py-1 z-50 text-left"
                         x-cloak>
                        <div class="px-4 py-2 border-b border-zinc-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-zinc-800">Unread Alerts ({{ $unreadAlertsCount }})</span>
                            <a href="{{ route('admin.alerts') }}" class="text-[10px] text-zinc-500 hover:underline">View All</a>
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            @forelse(\App\Models\AdminAlert::where('is_read', false)->where(function($q) { $q->whereNull('user_id')->orWhere('user_id', Auth::id()); })->latest()->take(5)->get() as $alert)
                                <a href="{{ route('admin.alerts') }}" class="block px-4 py-2 hover:bg-zinc-50 border-b border-zinc-50 last:border-0 transition-colors">
                                    <p class="text-xs text-zinc-800 font-medium truncate">{{ $alert->title }}</p>
                                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ $alert->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-center text-xs text-zinc-400">
                                    No unread alerts
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Dark Mode Toggle -->
                <button @click="isDark = !isDark; localStorage.setItem('darkMode', isDark); $nextTick(() => lucide.createIcons())" class="text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 transition-colors p-1.5 rounded-lg flex items-center justify-center focus:outline-none">
                    <i data-lucide="sun" x-show="isDark" class="w-4 h-4" x-cloak></i>
                    <i data-lucide="moon" x-show="!isDark" class="w-4 h-4"></i>
                </button>

                <!-- Palette Customizer -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 transition-colors p-1.5 rounded-lg flex items-center justify-center focus:outline-none">
                        <i data-lucide="palette" class="w-4 h-4"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-72 bg-white ring-1 ring-zinc-950/5 rounded-xl shadow-lg p-4 z-50 text-left space-y-3.5"
                         x-cloak>
                        
                        <!-- Theme Preset -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 mb-1.5 uppercase tracking-wide">Theme preset:</label>
                            <div class="relative">
                                <select @change="themePreset = $event.target.value; localStorage.setItem('themePreset', themePreset)" 
                                        class="w-full pl-8 pr-10 py-1.5 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all appearance-none cursor-pointer">
                                    <option value="default" :selected="themePreset === 'default'">Default</option>
                                    <option value="violet" :selected="themePreset === 'violet'">Violet</option>
                                    <option value="emerald" :selected="themePreset === 'emerald'">Emerald</option>
                                    <option value="blue" :selected="themePreset === 'blue'">Blue</option>
                                    <option value="rose" :selected="themePreset === 'rose'">Rose</option>
                                </select>
                                <!-- Custom color dot next to selected theme -->
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 rounded-full border border-black/10" 
                                     :class="{
                                         'bg-[#fdd835]': themePreset === 'default',
                                         'bg-[#8b5cf6]': themePreset === 'violet',
                                         'bg-[#10b981]': themePreset === 'emerald',
                                         'bg-[#3b82f6]': themePreset === 'blue',
                                         'bg-[#f43f5e]': themePreset === 'rose'
                                     }">
                                </div>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-zinc-400 flex items-center">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Scale -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 mb-1.5 uppercase tracking-wide">Scale:</label>
                            <div class="grid grid-cols-3 gap-1 bg-zinc-50 border border-zinc-200 rounded-lg p-0.5">
                                <button @click="themeScale = 'default'; localStorage.setItem('themeScale', 'default')" 
                                        class="py-1 text-xs font-semibold rounded transition-all flex items-center justify-center"
                                        :class="themeScale === 'default' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                </button>
                                <button @click="themeScale = 'xs'; localStorage.setItem('themeScale', 'xs')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="themeScale === 'xs' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    XS
                                </button>
                                <button @click="themeScale = 'lg'; localStorage.setItem('themeScale', 'lg')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="themeScale === 'lg' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    LG
                                </button>
                            </div>
                        </div>

                        <!-- Radius -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 mb-1.5 uppercase tracking-wide">Radius:</label>
                            <div class="grid grid-cols-5 gap-1 bg-zinc-50 border border-zinc-200 rounded-lg p-0.5">
                                <button @click="themeRadius = 'none'; localStorage.setItem('themeRadius', 'none')" 
                                        class="py-1 text-xs font-semibold rounded transition-all flex items-center justify-center"
                                        :class="themeRadius === 'none' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                </button>
                                <button @click="themeRadius = 'sm'; localStorage.setItem('themeRadius', 'sm')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="themeRadius === 'sm' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    SM
                                </button>
                                <button @click="themeRadius = 'md'; localStorage.setItem('themeRadius', 'md')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="themeRadius === 'md' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    MD
                                </button>
                                <button @click="themeRadius = 'lg'; localStorage.setItem('themeRadius', 'lg')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="themeRadius === 'lg' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    LG
                                </button>
                                <button @click="themeRadius = 'xl'; localStorage.setItem('themeRadius', 'xl')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="themeRadius === 'xl' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    XL
                                </button>
                            </div>
                        </div>

                        <!-- Color Mode -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 mb-1.5 uppercase tracking-wide">Color mode:</label>
                            <div class="grid grid-cols-2 gap-1 bg-zinc-50 border border-zinc-200 rounded-lg p-0.5">
                                <button @click="isDark = false; localStorage.setItem('darkMode', 'false')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="!isDark ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    Light
                                </button>
                                <button @click="isDark = true; localStorage.setItem('darkMode', 'true')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="isDark ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    Dark
                                </button>
                            </div>
                        </div>

                        <!-- Content Layout -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 mb-1.5 uppercase tracking-wide">Content layout:</label>
                            <div class="grid grid-cols-2 gap-1 bg-zinc-50 border border-zinc-200 rounded-lg p-0.5">
                                <button @click="layoutMode = 'full'; localStorage.setItem('layoutMode', 'full')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="layoutMode === 'full' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    Full
                                </button>
                                <button @click="layoutMode = 'centered'; localStorage.setItem('layoutMode', 'centered')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="layoutMode === 'centered' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    Centered
                                </button>
                            </div>
                        </div>

                        <!-- Sidebar Mode -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 mb-1.5 uppercase tracking-wide">Sidebar mode:</label>
                            <div class="grid grid-cols-2 gap-1 bg-zinc-50 border border-zinc-200 rounded-lg p-0.5">
                                <button @click="sidebarMode = 'default'; localStorage.setItem('sidebarMode', 'default')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="sidebarMode === 'default' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    Default
                                </button>
                                <button @click="sidebarMode = 'icon'; localStorage.setItem('sidebarMode', 'icon')" 
                                        class="py-1 text-xs font-semibold rounded transition-all"
                                        :class="sidebarMode === 'icon' ? 'bg-white shadow-sm text-zinc-900 border border-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                                    Icon
                                </button>
                            </div>
                        </div>

                        <!-- Reset Button -->
                        <button @click="
                            themePreset = 'default'; localStorage.setItem('themePreset', 'default');
                            themeScale = 'default'; localStorage.setItem('themeScale', 'default');
                            themeRadius = 'lg'; localStorage.setItem('themeRadius', 'lg');
                            isDark = false; localStorage.setItem('darkMode', 'false');
                            layoutMode = 'full'; localStorage.setItem('layoutMode', 'full');
                            sidebarMode = 'default'; localStorage.setItem('sidebarMode', 'default');
                        " 
                        class="w-full py-2 bg-zinc-950 hover:bg-zinc-800 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm text-center">
                            Reset to Default
                        </button>
                    </div>
                </div>

                <!-- Vertical Divider -->
                <div class="h-6 w-px bg-zinc-200 mx-1"></div>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="flex items-center focus:outline-none">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=e4e4e7&color=71717a&size=36" class="w-9 h-9 rounded-full border border-zinc-200 shadow-sm object-cover hover:opacity-90 transition-opacity" alt="avatar">
                    </button>
                    
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 bg-white border border-zinc-200 rounded-xl shadow-xl py-1.5 z-50 text-left"
                         x-cloak>
                        
                        <!-- User Header Info -->
                        <div class="flex items-center space-x-3 p-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=e4e4e7&color=71717a&size=40" class="w-10 h-10 rounded-full border border-zinc-200 object-cover" alt="avatar">
                            <div class="text-left truncate">
                                <p class="text-xs font-bold text-zinc-800 truncate leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-zinc-500 truncate leading-none mt-1">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-zinc-100"></div>

                        <!-- Menu Items -->
                        <a href="{{ route('admin.profile') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 text-zinc-400"></i>
                            <span>Account</span>
                        </a>
                        
                        <a href="{{ route('admin.alerts') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            <i data-lucide="bell" class="w-4 h-4 text-zinc-400"></i>
                            <span>Notifications</span>
                        </a>
                        
                        <div class="border-t border-zinc-100"></div>

                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex w-full items-center space-x-2.5 px-3.5 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors text-left">
                                <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                                <span>Log out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 bg-zinc-50">
            <div class="mx-auto w-full transition-all duration-300" :class="layoutMode === 'full' ? 'max-w-[1600px]' : 'max-w-4xl'">
                <!-- Page Title -->
                @if(trim(View::yieldContent('page_title', 'Overview')) !== '')
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                    <div class="flex flex-col">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-zinc-900 tracking-tight leading-none">@yield('page_title', 'Overview')</h2>
                    </div>
                    <div class="flex items-center space-x-3">
                        @yield('header_actions')
                    </div>
                </div>
                @endif
                <!-- Toast notification messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between shadow-sm animate-fade-in" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center space-x-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                        <button class="text-emerald-500 hover:text-emerald-800" @click="show = false">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center space-x-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                        <button class="text-red-500 hover:text-red-800" @click="show = false">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Initialize Lucide Icons & Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        lucide.createIcons();
        document.addEventListener('alpine:init', () => {
            // Style overrides for inline Flatpickr calendar
            const style = document.createElement('style');
            style.textContent = `
                .flatpickr-inline-container .flatpickr-calendar {
                    border: none !important;
                    box-shadow: none !important;
                    background: transparent !important;
                    width: 300px !important;
                }
                .flatpickr-inline-container .flatpickr-innerContainer {
                    width: 300px !important;
                }
                .flatpickr-inline-container .flatpickr-rContainer {
                    width: 300px !important;
                }
                .flatpickr-inline-container .flatpickr-days {
                    width: 300px !important;
                }
                .flatpickr-inline-container .dayContainer {
                    width: 300px !important;
                    min-width: 300px !important;
                    max-width: 300px !important;
                }
            `;
            document.head.appendChild(style);

            Alpine.data('dateRangePicker', (config) => ({
                config: config,
                dateStart: config.start || '',
                dateEnd: config.end || '',
                currentPreset: 'all',
                open: false,
                fp: null,
                triggerText: null,
                popoverEl: null,
                startInputEl: null,
                endInputEl: null,

                init() {
                    const self = this;
                    
                    // 1. Find the label text if it exists
                    let labelText = '';
                    const labelEl = this.$el.querySelector('label');
                    if (labelEl) {
                        labelText = labelEl.textContent.trim();
                    }

                    // 2. Clear the element and set layout
                    this.$el.innerHTML = '';
                    this.$el.className = "date-range-picker-container flex flex-col gap-1.5 relative text-left";

                    // 3. Re-add the label if it existed
                    if (labelText) {
                        const lbl = document.createElement('label');
                        lbl.className = "text-xs font-bold text-zinc-500 uppercase tracking-wider block";
                        lbl.textContent = labelText;
                        this.$el.appendChild(lbl);
                    }

                    // 4. Create hidden inputs
                    const startInput = document.createElement('input');
                    startInput.type = 'hidden';
                    startInput.name = this.config.startName || 'date_start';
                    startInput.value = this.dateStart;
                    this.$el.appendChild(startInput);

                    const endInput = document.createElement('input');
                    endInput.type = 'hidden';
                    endInput.name = this.config.endName || 'date_end';
                    endInput.value = this.dateEnd;
                    this.$el.appendChild(endInput);

                    // 5. Create absolute positioning wrapper for trigger and popover
                    const pickerWrapper = document.createElement('div');
                    pickerWrapper.className = "relative inline-block text-left";

                    // 6. Create trigger button
                    const trigger = document.createElement('button');
                    trigger.type = 'button';
                    trigger.className = "inline-flex items-center gap-2 px-3 py-2 border border-zinc-200 rounded-lg text-xs font-semibold bg-white hover:bg-zinc-50 shadow-sm text-zinc-800 transition-all cursor-pointer h-9 w-full sm:w-auto";
                    trigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.open = !this.open;
                    });
                    
                    trigger.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-zinc-500"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <span class="date-display-text">All Time</span>
                    `;
                    pickerWrapper.appendChild(trigger);

                    // 7. Create Popover Dropdown
                    const popover = document.createElement('div');
                    popover.className = "absolute right-0 mt-2 bg-white border border-zinc-200 rounded-xl shadow-xl z-[999] flex overflow-hidden p-1 hidden";
                    popover.style.minWidth = "450px";

                    // Left Presets Pane
                    const presetsPane = document.createElement('div');
                    presetsPane.className = "w-36 border-r border-zinc-150 p-1 flex flex-col gap-0.5";
                    
                    const presets = [
                        { label: 'Today', value: 'today' },
                        { label: 'Yesterday', value: 'yesterday' },
                        { label: 'This Week', value: 'this_week' },
                        { label: 'Last 7 Days', value: '7days' },
                        { label: 'Last 28 Days', value: '28days' },
                        { label: 'This Month', value: 'this_month' },
                        { label: 'Last Month', value: 'last_month' },
                        { label: 'This Year', value: 'this_year' },
                        { label: 'All Time', value: 'all' }
                    ];

                    presets.forEach(p => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = "w-full text-left px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all text-zinc-650 hover:bg-zinc-50";
                        btn.textContent = p.label;
                        btn.dataset.value = p.value;
                        btn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            this.applyPreset(p.value);
                            this.updateActivePresetClass();
                        });
                        presetsPane.appendChild(btn);
                    });
                    popover.appendChild(presetsPane);

                    // Right Calendar Pane
                    const calPane = document.createElement('div');
                    calPane.className = "p-1 flex flex-col justify-center";
                    
                    const fpContainer = document.createElement('div');
                    fpContainer.className = "flatpickr-inline-container";
                    calPane.appendChild(fpContainer);
                    popover.appendChild(calPane);

                    pickerWrapper.appendChild(popover);
                    this.$el.appendChild(pickerWrapper);

                    // Keep references
                    this.triggerText = trigger.querySelector('.date-display-text');
                    this.popoverEl = popover;
                    this.startInputEl = startInput;
                    this.endInputEl = endInput;

                    // Initialize Flatpickr inline
                    this.fp = flatpickr(fpContainer, {
                        mode: "range",
                        inline: true,
                        dateFormat: "Y-m-d",
                        defaultDate: (this.dateStart && this.dateEnd) ? [this.dateStart, this.dateEnd] : null,
                        onChange: (selectedDates, dateStr, instance) => {
                            if (selectedDates.length === 2) {
                                self.dateStart = self.formatLocalDate(selectedDates[0]);
                                self.dateEnd = self.formatLocalDate(selectedDates[1]);
                                self.startInputEl.value = self.dateStart;
                                self.endInputEl.value = self.dateEnd;
                                self.currentPreset = 'custom';
                                self.updateDisplayText();
                                self.updateActivePresetClass();
                                self.submitForm();
                            }
                        }
                    });

                    // Handle outside clicks to close the popover
                    document.addEventListener('click', (e) => {
                        if (!this.$el.contains(e.target)) {
                            this.open = false;
                        }
                    });

                    // Watch open state
                    this.$watch('open', value => {
                        if (value) {
                            popover.classList.remove('hidden');
                        } else {
                            popover.classList.add('hidden');
                        }
                    });

                    this.determinePreset();
                    this.updateDisplayText();
                    this.updateActivePresetClass();
                },

                updateDisplayText() {
                    if (!this.dateStart || !this.dateEnd) {
                        this.triggerText.textContent = 'All Time';
                    } else {
                        this.triggerText.textContent = `${this.formatDisplayDate(this.dateStart)} - ${this.formatDisplayDate(this.dateEnd)}`;
                    }
                },

                updateActivePresetClass() {
                    const buttons = this.popoverEl.querySelectorAll('button[data-value]');
                    buttons.forEach(btn => {
                        if (btn.dataset.value === this.currentPreset) {
                            btn.className = "w-full text-left px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all bg-zinc-950 text-white";
                        } else {
                            btn.className = "w-full text-left px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all text-zinc-650 hover:bg-zinc-50";
                        }
                    });
                },

                determinePreset() {
                    if (!this.dateStart && !this.dateEnd) {
                        this.currentPreset = 'all';
                        return;
                    }
                    
                    const todayStr = this.getFormattedDate(0);
                    const yesterdayStr = this.getFormattedDate(1);
                    
                    if (this.dateStart === todayStr && this.dateEnd === todayStr) {
                        this.currentPreset = 'today';
                    } else if (this.dateStart === yesterdayStr && this.dateEnd === yesterdayStr) {
                        this.currentPreset = 'yesterday';
                    } else if (this.dateStart === this.getFormattedDate(6) && this.dateEnd === todayStr) {
                        this.currentPreset = '7days';
                    } else if (this.dateStart === this.getFormattedDate(27) && this.dateEnd === todayStr) {
                        this.currentPreset = '28days';
                    } else {
                        const now = new Date();
                        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                        const firstDayStr = this.formatLocalDate(firstDay);
                        const firstDayYear = new Date(now.getFullYear(), 0, 1);
                        const firstDayYearStr = this.formatLocalDate(firstDayYear);
                        
                        if (this.dateStart === firstDayStr && this.dateEnd === todayStr) {
                            this.currentPreset = 'this_month';
                        } else if (this.dateStart === firstDayYearStr && this.dateEnd === todayStr) {
                            this.currentPreset = 'this_year';
                        } else {
                            this.currentPreset = 'custom';
                        }
                    }
                },

                getFormattedDate(daysAgo) {
                    const d = new Date();
                    d.setDate(d.getDate() - daysAgo);
                    return this.formatLocalDate(d);
                },

                formatLocalDate(date) {
                    const offset = date.getTimezoneOffset();
                    const localDate = new Date(date.getTime() - (offset * 60 * 1000));
                    return localDate.toISOString().split('T')[0];
                },

                formatDisplayDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                },

                applyPreset(val) {
                    this.currentPreset = val;
                    const today = this.getFormattedDate(0);
                    
                    if (val === 'all') {
                        this.dateStart = '';
                        this.dateEnd = '';
                        this.startInputEl.value = '';
                        this.endInputEl.value = '';
                        if (this.fp) this.fp.clear();
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === 'today') {
                        this.dateStart = today;
                        this.dateEnd = today;
                        this.startInputEl.value = today;
                        this.endInputEl.value = today;
                        if (this.fp) this.fp.setDate([today, today]);
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === 'yesterday') {
                        const yesterday = this.getFormattedDate(1);
                        this.dateStart = yesterday;
                        this.dateEnd = yesterday;
                        this.startInputEl.value = yesterday;
                        this.endInputEl.value = yesterday;
                        if (this.fp) this.fp.setDate([yesterday, yesterday]);
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === 'this_week') {
                        const now = new Date();
                        const day = now.getDay();
                        const diff = now.getDate() - day + (day === 0 ? -6 : 1);
                        const start = this.formatLocalDate(new Date(now.setDate(diff)));
                        this.dateStart = start;
                        this.dateEnd = today;
                        this.startInputEl.value = start;
                        this.endInputEl.value = today;
                        if (this.fp) this.fp.setDate([start, today]);
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === '7days') {
                        const start = this.getFormattedDate(6);
                        this.dateStart = start;
                        this.dateEnd = today;
                        this.startInputEl.value = start;
                        this.endInputEl.value = today;
                        if (this.fp) this.fp.setDate([start, today]);
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === '28days') {
                        const start = this.getFormattedDate(27);
                        this.dateStart = start;
                        this.dateEnd = today;
                        this.startInputEl.value = start;
                        this.endInputEl.value = today;
                        if (this.fp) this.fp.setDate([start, today]);
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === 'this_month') {
                        const now = new Date();
                        const start = this.formatLocalDate(new Date(now.getFullYear(), now.getMonth(), 1));
                        this.dateStart = start;
                        this.dateEnd = today;
                        this.startInputEl.value = start;
                        this.endInputEl.value = today;
                        if (this.fp) this.fp.setDate([start, today]);
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === 'last_month') {
                        const now = new Date();
                        const start = this.formatLocalDate(new Date(now.getFullYear(), now.getMonth() - 1, 1));
                        const end = this.formatLocalDate(new Date(now.getFullYear(), now.getMonth(), 0));
                        this.dateStart = start;
                        this.dateEnd = end;
                        this.startInputEl.value = start;
                        this.endInputEl.value = end;
                        if (this.fp) this.fp.setDate([start, end]);
                        this.updateDisplayText();
                        this.submitForm();
                    } else if (val === 'this_year') {
                        const now = new Date();
                        const start = this.formatLocalDate(new Date(now.getFullYear(), 0, 1));
                        this.dateStart = start;
                        this.dateEnd = today;
                        this.startInputEl.value = start;
                        this.endInputEl.value = today;
                        if (this.fp) this.fp.setDate([start, today]);
                        this.updateDisplayText();
                        this.submitForm();
                    }
                },

                submitForm() {
                    this.$nextTick(() => {
                        const form = this.$el.closest('form');
                        if (form) {
                            form.submit();
                        }
                    });
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize basic date picker for non-range elements
            flatpickr(".flatpickr-date:not([x-ref])", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                allowInput: true,
                static: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (instance.element.form) {
                        const hasSubmitButton = instance.element.form.querySelector('button[type="submit"], input[type="submit"]') !== null;
                        if (!hasSubmitButton) {
                            instance.element.form.submit();
                        }
                    }
                }
            });

            // Initialize basic datetime picker
            flatpickr(".flatpickr-datetime:not([x-ref])", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altFormat: "F j, Y h:i K",
                allowInput: true,
                static: true,
            });

            // Preserve sidebar scroll position
            const sidebarNav = document.querySelector('.sidebar-nav');
            if (sidebarNav) {
                const storedScroll = localStorage.getItem('admin-sidebar-scroll');
                if (storedScroll) {
                    sidebarNav.scrollTop = parseFloat(storedScroll);
                }
                sidebarNav.addEventListener('scroll', function() {
                    localStorage.setItem('admin-sidebar-scroll', sidebarNav.scrollTop);
                });
            }

            // Setup auto-submit on change for select fields & search inputs ONLY if there is no submit/apply button
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const hasSubmit = form.querySelector('button[type="submit"], input[type="submit"]') !== null;
                
                if (!hasSubmit) {
                    form.querySelectorAll('select').forEach(select => {
                        if (!select.closest('.date-range-picker-container')) {
                            select.addEventListener('change', () => form.submit());
                        }
                    });

                    form.querySelectorAll('input[name="search"]').forEach(input => {
                        let timeout = null;
                        input.addEventListener('input', () => {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => {
                                form.submit();
                            }, 500);
                        });
                    });
                }
            });

            // Focus global search on Command+K or Ctrl+K
            window.addEventListener('keydown', function(e) {
                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    const globalSearch = document.getElementById('admin-global-search');
                    if (globalSearch) globalSearch.focus();
                }
            });

            // Client-side sidebar link filter
            const searchInput = document.getElementById('admin-global-search');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const query = e.target.value.toLowerCase();
                    const links = document.querySelectorAll('.sidebar-nav a');
                    links.forEach(link => {
                        const text = link.textContent.toLowerCase();
                        if (text.includes(query)) {
                            link.style.display = '';
                        } else {
                            link.style.display = 'none';
                        }
                    });
                    
                    // Toggle visibility of category headers based on matching links
                    const headers = document.querySelectorAll('.sidebar-nav p');
                    headers.forEach(header => {
                        let sibling = header.nextElementSibling;
                        let hasVisibleSibling = false;
                        while (sibling && sibling.tagName === 'A') {
                            if (sibling.style.display !== 'none') {
                                hasVisibleSibling = true;
                                break;
                            }
                            sibling = sibling.nextElementSibling;
                        }
                        header.style.display = hasVisibleSibling ? '' : 'none';
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
