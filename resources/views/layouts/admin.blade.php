<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>@yield('title', 'Admin Panel') | {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</title>
  <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
 <script src="https://unpkg.com/lucide@latest"></script>
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
 <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

 <style>
 [x-cloak] { display: none !important; }
 </style>

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
<body class="h-screen overflow-hidden flex flex-col md:flex-row transition-colors duration-200"
 x-data="{  sidebarOpen: window.innerWidth >= 768,  themePreset: localStorage.getItem('themePreset') || 'default',  themeScale: localStorage.getItem('themeScale') || 'default',
 themeRadius: localStorage.getItem('themeRadius') || 'lg',
 isDark: localStorage.getItem('darkMode') === 'true',
 layoutMode: localStorage.getItem('layoutMode') || 'full',
 sidebarMode: localStorage.getItem('sidebarMode') || 'default',
 searchOpen: false
 }"  :class="{ 'dark': isDark, 'scale-xs': themeScale === 'xs', 'scale-lg': themeScale === 'lg' }"
 :style="'--radius: ' + (themeRadius === 'none' ? '0px' : (themeRadius === 'sm' ? '0.25rem' : (themeRadius === 'md' ? '0.375rem' : (themeRadius === 'lg' ? '0.5rem' : '0.75rem'))))"
 :data-theme="themePreset"
 @keydown.escape="searchOpen = false"
 @keydown.window.prevent.meta.k="searchOpen = !searchOpen"
 @keydown.window.prevent.ctrl.k="searchOpen = !searchOpen">

 <!-- Sidebar -->
 <aside class="w-full md:h-screen flex-shrink-0 flex flex-col transition-all duration-300 border-b md:border-b-0 md:border-r"
 style="background: var(--sidebar); border-color: var(--sidebar-border); color: var(--sidebar-foreground);"
 :class="sidebarOpen ? (sidebarMode === 'default' ? 'block md:flex md:w-64' : 'block md:flex md:w-16 sidebar-collapsed') : 'hidden md:flex md:w-0 md:overflow-hidden md:border-r-0'">
  <!-- Logo -->
 <div class="h-14 flex items-center justify-between px-4 border-b" style="border-color: var(--sidebar-border);">
 <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5">
 <div class="logo-expanded flex items-center space-x-2.5">
 @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
 <img src="{{ $logo }}" class="h-9 w-auto max-w-[140px] object-contain" alt="Logo">
 @else
 <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0" style="background: var(--primary);">XB</div>
 <span class="font-semibold text-sm tracking-tight">{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</span>
 @endif
 </div>
 <div class="logo-collapsed w-full flex justify-center">
 <img src="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}" class="w-7 h-7 object-contain rounded-lg flex-shrink-0" alt="Favicon" style="box-shadow: 0 0 0 1px var(--border);">
 </div>
 <span class="logo-expanded text-[9px] px-1.5 py-0.5 rounded-md font-semibold" style="background: var(--muted); color: var(--muted-foreground);">Admin</span>
 </a>
 <button class="md:hidden p-1 rounded-lg" style="color: var(--muted-foreground);" @click="sidebarOpen = false">
 <i data-lucide="x" class="w-5 h-5"></i>
 </button>
 </div>
  <!-- Navigation -->
 <nav class="sidebar-nav flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
 <p class="text-[10px] font-medium uppercase tracking-widest px-3 mb-1.5 mt-1" style="color: var(--muted-foreground); opacity: 0.6;">Commerce</p>
  <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.dashboard') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
 <span>Dashboard</span>
 </a>

 <a href="{{ route('admin.sales-overview') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.sales-overview') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.sales-overview') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="trending-up" class="w-4 h-4"></i>
 <span>Sales Overview</span>
 </a>

 @if(Auth::user()->canAccess('sellers'))
 <a href="{{ route('admin.sellers') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.sellers*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.sellers*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="users" class="w-4 h-4"></i>
 <span>Sellers</span>
 @php $pendingKyc = \App\Models\SellerProfile::where('kyc_status', 'pending')->count(); @endphp
 @if($pendingKyc > 0)
 <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center" style="background: var(--primary); color: var(--primary-foreground);">{{ $pendingKyc }}</span>
 @endif
 </a>
 @endif

 @if(Auth::user()->canAccess('listings'))
 <a href="{{ route('admin.listings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.listings*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.listings*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="package" class="w-4 h-4"></i>
 <span>Listings</span>
 @php $pendingListings = \App\Models\Listing::where('listing_status', 'pending_approval')->count(); @endphp
 @if($pendingListings > 0)
 <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center" style="background: var(--primary); color: var(--primary-foreground);">{{ $pendingListings }}</span>
 @endif
 </a>
 @endif

 @if(Auth::user()->canAccess('categories'))
 <a href="{{ route('admin.categories') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.categories*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.categories*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="folder-tree" class="w-4 h-4"></i>
 <span>Categories</span>
 </a>

 <a href="{{ route('admin.spec-templates') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.spec-templates*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.spec-templates*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="sliders" class="w-4 h-4"></i>
 <span>Spec Templates</span>
 </a>

 <a href="{{ route('admin.pages.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.pages*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.pages*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="file-text" class="w-4 h-4"></i>
 <span>Policy Pages</span>
 </a>
 @endif

 @if(Auth::user()->canAccess('orders'))
 <a href="{{ route('admin.orders') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.orders*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.orders*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="shopping-cart" class="w-4 h-4"></i>
 <span>Orders</span>
 </a>
 @endif
  @if(Auth::user()->canAccess('disputes'))
 <a href="{{ route('admin.disputes') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.disputes*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.disputes*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="alert-triangle" class="w-4 h-4"></i>
 <span>Disputes</span>
 @php $openDisputes = \App\Models\Dispute::whereIn('status', ['open', 'seller_responded', 'under_review'])->count(); @endphp
 @if($openDisputes > 0)
 <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center" style="background: var(--destructive); color: var(--destructive-foreground);">{{ $openDisputes }}</span>
 @endif
 </a>
 @endif

 @if(Auth::user()->canAccess('escrow'))
 <a href="{{ route('admin.escrow') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.escrow*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.escrow*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="shield" class="w-4 h-4"></i>
 <span>Escrow</span>
 @php $heldCount = \App\Models\Escrow::where('status','held')->count(); @endphp
 @if($heldCount > 0)
 <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center" style="background: var(--primary); color: var(--primary-foreground);">{{ $heldCount }}</span>
 @endif
 </a>
 @endif

 @if(Auth::user()->canAccess('payouts'))
 <a href="{{ route('admin.payouts') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.payouts*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.payouts*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="wallet" class="w-4 h-4"></i>
 <span>Payouts</span>
 </a>
 @endif

 @if(Auth::user()->canAccess('tickets'))
 <a href="{{ route('admin.tickets') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.tickets*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.tickets*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="help-circle" class="w-4 h-4"></i>
 <span>Support Tickets</span>
 @php $openTickets = \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count(); @endphp
 @if($openTickets > 0)
 <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center" style="background: var(--primary); color: var(--primary-foreground);">{{ $openTickets }}</span>
 @endif
 </a>
 @endif

 <p class="text-[10px] font-medium uppercase tracking-widest px-3 pt-5 mb-1.5" style="color: var(--muted-foreground); opacity: 0.6;">Management</p>

 @if(Auth::user()->canAccess('articles'))
 <a href="{{ route('admin.articles.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.articles*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.articles*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="book-open" class="w-4 h-4"></i>
 <span>Articles / Blog</span>
 </a>

 <a href="{{ route('admin.mails.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.mails*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.mails*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="mail" class="w-4 h-4"></i>
 <span>Mail Management</span>
 </a>
 @endif

 @if(Auth::user()->canAccess('analytics'))
 <a href="{{ route('admin.analytics') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.analytics*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.analytics*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
 <span>Analytics</span>
 </a>
 @endif

 @if(Auth::user()->canAccess('users'))
 <a href="{{ route('admin.users') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.users*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.users*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="users" class="w-4 h-4"></i>
 <span>Users</span>
 </a>
 @endif

 <a href="{{ route('admin.my-earnings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.my-earnings*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.my-earnings*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="wallet" class="w-4 h-4"></i>
 <span>My Earnings</span>
 </a>

 @if(Auth::user()->isSuperAdmin())
 <a href="{{ route('admin.content') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.content*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.content*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="folder-open" class="w-4 h-4"></i>
 <span>Content Manager</span>
 </a>

 <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.settings*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.settings*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="settings" class="w-4 h-4"></i>
 <span>Settings</span>
 </a>

 <a href="{{ route('admin.accounts.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.accounts*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.accounts*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="shield" class="w-4 h-4"></i>
 <span>Staff Management</span>
 </a>

 <a href="{{ route('admin.staff.directory') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.staff.*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.staff.*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="users" class="w-4 h-4"></i>
 <span>Staff Directory</span>
 </a>

 <a href="{{ route('admin.payroll') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.payroll*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.payroll*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="banknote" class="w-4 h-4"></i>
 <span>Payroll Overview</span>
 </a>

 <a href="{{ route('admin.audit-logs') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.audit-logs*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.audit-logs*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="file-text" class="w-4 h-4"></i>
 <span>Audit Logs</span>
 </a>
 @endif

 @if(Auth::user()->canAccess('ratings'))
 <a href="{{ route('admin.ratings') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.ratings*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.ratings*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="star" class="w-4 h-4"></i>
 <span>Ratings</span>
 </a>
 @endif

 @if(Auth::user()->canAccess('alerts'))
 @php  $unreadAlertsCount = \App\Models\AdminAlert::where('is_read', false)
 ->where(function($q) {
 $q->whereNull('user_id')->orWhere('user_id', Auth::id());
 })->count();  @endphp
 <a href="{{ route('admin.alerts') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.alerts*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.alerts*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="bell" class="w-4 h-4"></i>
 <span>Alerts</span>
 @if($unreadAlertsCount > 0)
 <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center" style="background: var(--destructive); color: var(--destructive-foreground);">{{ $unreadAlertsCount }}</span>
 @endif
 </a>
 @endif

 @if(Auth::user()->canAccess('fraud_flags'))
 @php $pendingFraudCount = \App\Models\FraudFlag::where('status', 'pending')->count(); @endphp
 <a href="{{ route('admin.fraud-flags.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.fraud-flags*') ? 'sidebar-active' : '' }}" style="{{ request()->routeIs('admin.fraud-flags*') ? '' : 'color: var(--muted-foreground);' }}">
 <i data-lucide="shield-alert" class="w-4 h-4"></i>
 <span>Fraud Flags</span>
 @if($pendingFraudCount > 0)
 <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-semibold min-w-[20px] text-center" style="background: var(--destructive); color: var(--destructive-foreground);">{{ $pendingFraudCount }}</span>
 @endif
 </a>
 @endif
 </nav>

 <!-- User Info -->
 <div class="px-3 py-3 border-t" style="border-color: var(--sidebar-border);">
 <div class="flex items-center justify-between">
 <div class="flex items-center space-x-2.5">
 <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=e4e4e7&color=18181b&size=32" class="w-8 h-8 rounded-lg object-cover" alt="avatar" style="box-shadow: 0 0 0 1px var(--border);">
 <div class="text-left">
 <p class="text-xs font-medium truncate max-w-[120px]">{{ Auth::user()->name }}</p>
 @php
 $roleLabels = [
 'super_admin' => 'Super Admin',
 'operations' => 'Operations',
 'support' => 'Support',
 'finance' => 'Finance',
 'content' => 'Content',
 'moderator' => 'Moderator',
 ];
 $roleLabel = $roleLabels[Auth::user()->admin_role] ?? 'Admin';
 @endphp
 <p class="text-[10px] font-medium" style="color: var(--muted-foreground);">{{ $roleLabel }}</p>
 </div>
 </div>
 <form action="{{ route('admin.logout') }}" method="POST">
 @csrf
 <button type="submit" class="p-1.5 rounded-lg transition-all" style="color: var(--muted-foreground);">
 <i data-lucide="log-out" class="w-4 h-4"></i>
 </button>
 </form>
 </div>
 </div>
 </aside>

 <!-- Content Area -->
 <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
 <!-- Header -->
 <header class="h-14 border-b flex items-center justify-between px-4 lg:px-6 flex-shrink-0" style="border-color: var(--border); background: var(--background);">
 <div class="flex items-center gap-1 lg:gap-2">
 <button class="p-1.5 rounded-lg transition-colors -ml-1" style="color: var(--muted-foreground);"  @click="if (window.innerWidth >= 768) { sidebarOpen = true; sidebarMode = (sidebarMode === 'default' ? 'icon' : 'default'); localStorage.setItem('sidebarMode', sidebarMode); } else { sidebarOpen = !sidebarOpen; }">
 <i data-lucide="panel-left" class="w-4 h-4"></i>
 </button>
  <div class="hidden lg:block h-4 w-px mx-2" style="background: var(--border);"></div>

 <!-- Search -->
 <button @click="searchOpen = true" class="flex items-center gap-2 h-9 px-3 rounded-lg text-sm transition-all" style="border: 1px solid var(--border); color: var(--muted-foreground);">
 <i data-lucide="search" class="w-3.5 h-3.5"></i>
 <span class="hidden sm:inline">Search...</span>
 <kbd class="hidden sm:inline-flex h-5 select-none items-center gap-1 rounded px-1.5 font-mono text-[10px] font-medium" style="border: 1px solid var(--border); background: var(--muted); color: var(--muted-foreground);">
 <span class="text-xs">⌘</span>K
 </kbd>
 </button>
 </div>
  <div class="flex items-center gap-1">
 <a href="/" target="_blank" class="flex items-center gap-1.5 text-xs font-medium transition-colors p-1.5 rounded-lg" style="color: var(--muted-foreground);">
 <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
 <span class="hidden sm:inline">Homepage</span>
 </a>

 <!-- Notifications -->
 <div class="relative" x-data="{ open: false }" @click.outside="open = false">
 <button @click="open = !open" class="relative p-1.5 rounded-lg flex items-center justify-center transition-colors" style="color: var(--muted-foreground);">
 @if(isset($unreadAlertsCount) && $unreadAlertsCount > 0)
 <span class="absolute top-1 right-1 w-1.5 h-1.5 rounded-full" style="background: var(--destructive);"></span>
 @endif
 <i data-lucide="bell" class="w-4 h-4"></i>
 </button>
  <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-80 rounded-xl shadow-lg py-1 z-50 text-left" style="background: var(--card); border: 1px solid var(--border);" x-cloak>
 <div class="px-4 py-2 border-b flex items-center justify-between" style="border-color: var(--border);">
 <span class="text-xs font-semibold">Notifications</span>
 <a href="{{ route('admin.alerts') }}" class="text-[10px] hover:underline" style="color: var(--muted-foreground);">View All</a>
 </div>
 <div class="max-h-60 overflow-y-auto">
 @forelse(\App\Models\AdminAlert::where('is_read', false)->where(function($q) { $q->whereNull('user_id')->orWhere('user_id', Auth::id()); })->latest()->take(5)->get() as $alert)
 <a href="{{ route('admin.alerts') }}" class="block px-4 py-2 border-b transition-colors last:border-0" style="border-color: var(--border); opacity: 0.5;">
 <p class="text-xs font-medium truncate">{{ $alert->title }}</p>
 <p class="text-[10px] mt-0.5" style="color: var(--muted-foreground);">{{ $alert->created_at->diffForHumans() }}</p>
 </a>
 @empty
 <div class="px-4 py-6 text-center text-xs" style="color: var(--muted-foreground);">No unread alerts</div>
 @endforelse
 </div>
 </div>
 </div>

 <!-- Dark Mode -->
 <button @click="isDark = !isDark; localStorage.setItem('darkMode', isDark); $nextTick(() => lucide.createIcons())" class="p-1.5 rounded-lg flex items-center justify-center transition-colors" style="color: var(--muted-foreground);">
 <i data-lucide="sun" x-show="isDark" class="w-4 h-4" x-cloak></i>
 <i data-lucide="moon" x-show="!isDark" class="w-4 h-4"></i>
 </button>

 <!-- Theme Customizer -->
 <div class="relative" x-data="{ open: false }" @click.outside="open = false">
 <button @click="open = !open" class="p-1.5 rounded-lg flex items-center justify-center transition-colors" style="color: var(--muted-foreground);">
 <i data-lucide="palette" class="w-4 h-4"></i>
 </button>
 <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-72 rounded-xl shadow-lg p-4 z-50 text-left space-y-3.5" style="background: var(--card); border: 1px solid var(--border);" x-cloak>
  <div>
 <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: var(--muted-foreground);">Theme:</label>
 <div class="grid grid-cols-5 gap-1 p-0.5 rounded-lg" style="background: var(--muted); border: 1px solid var(--border);">
 <button @click="themePreset = 'default'; localStorage.setItem('themePreset', 'default')" class="py-1 text-xs font-semibold rounded transition-all" :class="themePreset === 'default' ? 'bg-white text-zinc-900 border border-zinc-200' : 'hover:text-zinc-800'" style="color: var(--muted-foreground);">
 <span class="w-4 h-4 rounded-full inline-block" style="background: #fdd835;"></span>
 </button>
 <button @click="themePreset = 'violet'; localStorage.setItem('themePreset', 'violet')" class="py-1 text-xs font-semibold rounded transition-all" :class="themePreset === 'violet' ? 'bg-white text-zinc-900 border border-zinc-200' : 'hover:text-zinc-800'" style="color: var(--muted-foreground);">
 <span class="w-4 h-4 rounded-full inline-block" style="background: #8b5cf6;"></span>
 </button>
 <button @click="themePreset = 'emerald'; localStorage.setItem('themePreset', 'emerald')" class="py-1 text-xs font-semibold rounded transition-all" :class="themePreset === 'emerald' ? 'bg-white text-zinc-900 border border-zinc-200' : 'hover:text-zinc-800'" style="color: var(--muted-foreground);">
 <span class="w-4 h-4 rounded-full inline-block" style="background: #10b981;"></span>
 </button>
 <button @click="themePreset = 'blue'; localStorage.setItem('themePreset', 'blue')" class="py-1 text-xs font-semibold rounded transition-all" :class="themePreset === 'blue' ? 'bg-white text-zinc-900 border border-zinc-200' : 'hover:text-zinc-800'" style="color: var(--muted-foreground);">
 <span class="w-4 h-4 rounded-full inline-block" style="background: #3b82f6;"></span>
 </button>
 <button @click="themePreset = 'rose'; localStorage.setItem('themePreset', 'rose')" class="py-1 text-xs font-semibold rounded transition-all" :class="themePreset === 'rose' ? 'bg-white text-zinc-900 border border-zinc-200' : 'hover:text-zinc-800'" style="color: var(--muted-foreground);">
 <span class="w-4 h-4 rounded-full inline-block" style="background: #f43f5e;"></span>
 </button>
 </div>
 </div>

 <div>
 <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: var(--muted-foreground);">Scale:</label>
 <div class="grid grid-cols-3 gap-1 p-0.5 rounded-lg" style="background: var(--muted); border: 1px solid var(--border);">
 <button @click="themeScale = 'xs'; localStorage.setItem('themeScale', 'xs')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeScale === 'xs' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">XS</button>
 <button @click="themeScale = 'default'; localStorage.setItem('themeScale', 'default')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeScale === 'default' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">MD</button>
 <button @click="themeScale = 'lg'; localStorage.setItem('themeScale', 'lg')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeScale === 'lg' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">LG</button>
 </div>
 </div>

 <div>
 <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: var(--muted-foreground);">Radius:</label>
 <div class="grid grid-cols-5 gap-1 p-0.5 rounded-lg" style="background: var(--muted); border: 1px solid var(--border);">
 <button @click="themeRadius = 'none'; localStorage.setItem('themeRadius', 'none')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeRadius === 'none' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">0</button>
 <button @click="themeRadius = 'sm'; localStorage.setItem('themeRadius', 'sm')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeRadius === 'sm' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">SM</button>
 <button @click="themeRadius = 'md'; localStorage.setItem('themeRadius', 'md')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeRadius === 'md' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">MD</button>
 <button @click="themeRadius = 'lg'; localStorage.setItem('themeRadius', 'lg')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeRadius === 'lg' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">LG</button>
 <button @click="themeRadius = 'xl'; localStorage.setItem('themeRadius', 'xl')" class="py-1 text-xs font-semibold rounded transition-all" :class="themeRadius === 'xl' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">XL</button>
 </div>
 </div>

 <div>
 <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: var(--muted-foreground);">Color mode:</label>
 <div class="grid grid-cols-2 gap-1 p-0.5 rounded-lg" style="background: var(--muted); border: 1px solid var(--border);">
 <button @click="isDark = false; localStorage.setItem('darkMode', 'false')" class="py-1 text-xs font-semibold rounded transition-all" :class="!isDark ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">Light</button>
 <button @click="isDark = true; localStorage.setItem('darkMode', 'true')" class="py-1 text-xs font-semibold rounded transition-all" :class="isDark ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">Dark</button>
 </div>
 </div>

 <div>
 <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: var(--muted-foreground);">Layout:</label>
 <div class="grid grid-cols-2 gap-1 p-0.5 rounded-lg" style="background: var(--muted); border: 1px solid var(--border);">
 <button @click="layoutMode = 'full'; localStorage.setItem('layoutMode', 'full')" class="py-1 text-xs font-semibold rounded transition-all" :class="layoutMode === 'full' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">Full</button>
 <button @click="layoutMode = 'centered'; localStorage.setItem('layoutMode', 'centered')" class="py-1 text-xs font-semibold rounded transition-all" :class="layoutMode === 'centered' ? 'bg-white text-zinc-900 border border-zinc-200' : ''" style="color: var(--muted-foreground);">Centered</button>
 </div>
 </div>

 <button @click="
 themePreset = 'default'; localStorage.setItem('themePreset', 'default');
 themeScale = 'default'; localStorage.setItem('themeScale', 'default');
 themeRadius = 'lg'; localStorage.setItem('themeRadius', 'lg');
 isDark = false; localStorage.setItem('darkMode', 'false');
 layoutMode = 'full'; localStorage.setItem('layoutMode', 'full');
 sidebarMode = 'default'; localStorage.setItem('sidebarMode', 'default');
 " class="w-full py-2 rounded-lg text-xs font-semibold transition-colors text-center" style="background: var(--primary); color: var(--primary-foreground);">
 Reset to Default
 </button>
 </div>
 </div>

 <div class="h-6 w-px mx-1" style="background: var(--border);"></div>

 <!-- Profile Dropdown -->
 <div class="relative" x-data="{ open: false }" @click.outside="open = false">
 <button @click="open = !open" class="flex items-center focus:outline-none">
 <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=e4e4e7&color=71717a&size=36" class="w-9 h-9 rounded-full object-cover hover:opacity-90 transition-opacity" alt="avatar" style="border: 1px solid var(--border);">
 </button>
  <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-64 rounded-xl shadow-xl py-1.5 z-50 text-left" style="background: var(--card); border: 1px solid var(--border);" x-cloak>
  <div class="flex items-center space-x-3 p-3">
 <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=e4e4e7&color=71717a&size=40" class="w-10 h-10 rounded-full object-cover" alt="avatar" style="border: 1px solid var(--border);">
 <div class="text-left truncate">
 <p class="text-xs font-bold truncate leading-tight">{{ Auth::user()->name }}</p>
 <p class="text-[10px] truncate leading-none mt-1" style="color: var(--muted-foreground);">{{ Auth::user()->email }}</p>
 </div>
 </div>
  <div class="border-t" style="border-color: var(--border);"></div>

 <a href="{{ route('admin.profile') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold transition-colors" style="color: var(--muted-foreground);">
 <i data-lucide="user" class="w-4 h-4"></i>
 <span>Account</span>
 </a>
  <a href="{{ route('admin.alerts') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold transition-colors" style="color: var(--muted-foreground);">
 <i data-lucide="bell" class="w-4 h-4"></i>
 <span>Notifications</span>
 </a>
  <div class="border-t" style="border-color: var(--border);"></div>

 <form action="{{ route('admin.logout') }}" method="POST">
 @csrf
 <button type="submit" class="flex w-full items-center space-x-2.5 px-3.5 py-2.5 text-xs font-bold text-left transition-colors" style="color: var(--destructive);">
 <i data-lucide="log-out" class="w-4 h-4"></i>
 <span>Log out</span>
 </button>
 </form>
 </div>
 </div>
 </div>
 </header>

 <!-- Main Content -->
 <main class="flex-1 overflow-y-auto p-6" style="background: var(--background);">
 <div class="mx-auto w-full transition-all duration-300" :class="layoutMode === 'full' ? 'max-w-[1600px]' : 'max-w-4xl'">
 @if(trim(View::yieldContent('page_title', 'Overview')) !== '')
 <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
 <div class="flex flex-col">
 <h2 class="text-page-title text-foreground">@yield('page_title', 'Overview')</h2>
 </div>
 <div class="flex items-center gap-2">
 @yield('header_actions')
 </div>
 </div>
 @endif

 @if(session('success'))
 <div class="mb-6 p-4 rounded-lg flex items-center justify-between border border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800/50 dark:bg-emerald-950/50 dark:text-emerald-200" x-data="{ show: true }" x-show="show">
 <div class="flex items-center gap-3">
 <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
 <span class="text-sm font-medium">{{ session('success') }}</span>
 </div>
 <button @click="show = false" class="opacity-70 hover:opacity-100 transition-opacity">
 <i data-lucide="x" class="w-4 h-4"></i>
 </button>
 </div>
 @endif

 @if(session('error'))
 <div class="mb-6 p-4 rounded-lg flex items-center justify-between border border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800/50 dark:bg-rose-950/50 dark:text-rose-200" x-data="{ show: true }" x-show="show">
 <div class="flex items-center gap-3">
 <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 dark:text-rose-400"></i>
 <span class="text-sm font-medium">{{ session('error') }}</span>
 </div>
 <button @click="show = false" class="opacity-70 hover:opacity-100 transition-opacity">
 <i data-lucide="x" class="w-4 h-4"></i>
 </button>
 </div>
 @endif

 @yield('content')
 </div>
 </main>
 </div>

 <!-- Command Palette (Cmd+K) -->
 <div x-show="searchOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[100] flex items-start justify-center pt-[20vh]" x-cloak>
 <div class="fixed inset-0" @click="searchOpen = false" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"></div>
 <div class="relative w-full max-w-lg rounded-xl shadow-2xl overflow-hidden" style="background: var(--card); border: 1px solid var(--border);">
 <div class="flex items-center border-b px-4" style="border-color: var(--border);">
 <i data-lucide="search" class="w-4 h-4 shrink-0" style="color: var(--muted-foreground);"></i>
 <input type="text" placeholder="Type a command or search..." class="flex-1 h-12 bg-transparent text-sm outline-none px-3" style="color: var(--foreground);" x-ref="searchInput" @keydown.escape="searchOpen = false" x-init="$watch('searchOpen', v => { if(v) $nextTick(() => $refs.searchInput?.focus()) })">
 <kbd class="h-5 select-none items-center gap-1 rounded px-1.5 font-mono text-[10px] font-medium" style="border: 1px solid var(--border); background: var(--muted); color: var(--muted-foreground);">ESC</kbd>
 </div>
 <div class="max-h-80 overflow-y-auto p-2">
 <p class="px-2 py-1.5 text-[10px] font-semibold uppercase tracking-wider" style="color: var(--muted-foreground);">Navigation</p>
 <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-sm transition-colors" style="color: var(--foreground);" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
 <i data-lucide="layout-dashboard" class="w-4 h-4" style="color: var(--muted-foreground);"></i> Dashboard
 </a>
 <a href="{{ route('admin.sellers') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-sm transition-colors" style="color: var(--foreground);" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
 <i data-lucide="users" class="w-4 h-4" style="color: var(--muted-foreground);"></i> Sellers
 </a>
 <a href="{{ route('admin.orders') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-sm transition-colors" style="color: var(--foreground);" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
 <i data-lucide="shopping-cart" class="w-4 h-4" style="color: var(--muted-foreground);"></i> Orders
 </a>
 <a href="{{ route('admin.disputes') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-sm transition-colors" style="color: var(--foreground);" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
 <i data-lucide="alert-triangle" class="w-4 h-4" style="color: var(--muted-foreground);"></i> Disputes
 </a>
 <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-sm transition-colors" style="color: var(--foreground);" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
 <i data-lucide="settings" class="w-4 h-4" style="color: var(--muted-foreground);"></i> Settings
 </a>
 </div>
 </div>
 </div>

 <!-- Initialize -->
 <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
 <script>
 lucide.createIcons();
 document.addEventListener('alpine:init', () => {
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
 let labelText = '';
 const labelEl = this.$el.querySelector('label');
 if (labelEl) labelText = labelEl.textContent.trim();

 this.$el.innerHTML = '';
 this.$el.className = "date-range-picker-container flex flex-col gap-1.5 relative text-left";

 if (labelText) {
 const lbl = document.createElement('label');
 lbl.className = "text-xs font-bold uppercase tracking-wider block";
 lbl.style.color = 'var(--muted-foreground)';
 lbl.textContent = labelText;
 this.$el.appendChild(lbl);
 }

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

 const pickerWrapper = document.createElement('div');
 pickerWrapper.className = "relative inline-block text-left";

 const trigger = document.createElement('button');
 trigger.type = 'button';
 trigger.className = "inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all cursor-pointer h-9 w-full sm:w-auto";
 trigger.style.cssText = "border: 1px solid var(--border); color: var(--foreground); background: var(--card);";
 trigger.addEventListener('click', (e) => { e.stopPropagation(); this.open = !this.open; });
  trigger.innerHTML = `
 <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--muted-foreground);"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
 <span class="date-display-text">All Time</span>
 `;
 pickerWrapper.appendChild(trigger);

 const popover = document.createElement('div');
 popover.className = "absolute right-0 mt-2 rounded-xl shadow-xl z-[999] flex overflow-hidden p-1 hidden";
 popover.style.cssText = "min-width: 450px; background: var(--card); border: 1px solid var(--border);";

 const presetsPane = document.createElement('div');
 presetsPane.className = "w-36 p-1 flex flex-col gap-0.5";
 presetsPane.style.borderRight = '1px solid var(--border)';
  const presets = [
 { label: 'Today', value: 'today' }, { label: 'Yesterday', value: 'yesterday' },
 { label: 'This Week', value: 'this_week' }, { label: 'Last 7 Days', value: '7days' },
 { label: 'Last 28 Days', value: '28days' }, { label: 'This Month', value: 'this_month' },
 { label: 'Last Month', value: 'last_month' }, { label: 'This Year', value: 'this_year' },
 { label: 'All Time', value: 'all' }
 ];

 presets.forEach(p => {
 const btn = document.createElement('button');
 btn.type = 'button';
 btn.className = "w-full text-left px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all";
 btn.style.color = 'var(--muted-foreground)';
 btn.textContent = p.label;
 btn.dataset.value = p.value;
 btn.addEventListener('click', (e) => { e.stopPropagation(); this.applyPreset(p.value); this.updateActivePresetClass(); });
 presetsPane.appendChild(btn);
 });
 popover.appendChild(presetsPane);

 const calPane = document.createElement('div');
 calPane.className = "p-1 flex flex-col justify-center";
 const fpContainer = document.createElement('div');
 fpContainer.className = "flatpickr-inline-container";
 calPane.appendChild(fpContainer);
 popover.appendChild(calPane);

 pickerWrapper.appendChild(popover);
 this.$el.appendChild(pickerWrapper);

 this.triggerText = trigger.querySelector('.date-display-text');
 this.popoverEl = popover;
 this.startInputEl = startInput;
 this.endInputEl = endInput;

 this.fp = flatpickr(fpContainer, {
 mode: "range", inline: true, dateFormat: "Y-m-d",
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

 document.addEventListener('click', (e) => { if (!this.$el.contains(e.target)) this.open = false; });
 this.$watch('open', value => { if (value) popover.classList.remove('hidden'); else popover.classList.add('hidden'); });
 this.determinePreset();
 this.updateDisplayText();
 this.updateActivePresetClass();
 },

 updateDisplayText() {
 if (!this.dateStart || !this.dateEnd) this.triggerText.textContent = 'All Time';
 else this.triggerText.textContent = `${this.formatDisplayDate(this.dateStart)} - ${this.formatDisplayDate(this.dateEnd)}`;
 },

 updateActivePresetClass() {
 const buttons = this.popoverEl.querySelectorAll('button[data-value]');
 buttons.forEach(btn => {
 if (btn.dataset.value === this.currentPreset) {
 btn.className = "w-full text-left px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all";
 btn.style.cssText = "background: var(--primary); color: var(--primary-foreground);";
 } else {
 btn.className = "w-full text-left px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all";
 btn.style.color = 'var(--muted-foreground)';
 btn.style.background = 'transparent';
 }
 });
 },

 determinePreset() {
 if (!this.dateStart && !this.dateEnd) { this.currentPreset = 'all'; return; }
 const todayStr = this.getFormattedDate(0);
 const yesterdayStr = this.getFormattedDate(1);
 if (this.dateStart === todayStr && this.dateEnd === todayStr) this.currentPreset = 'today';
 else if (this.dateStart === yesterdayStr && this.dateEnd === yesterdayStr) this.currentPreset = 'yesterday';
 else if (this.dateStart === this.getFormattedDate(6) && this.dateEnd === todayStr) this.currentPreset = '7days';
 else if (this.dateStart === this.getFormattedDate(27) && this.dateEnd === todayStr) this.currentPreset = '28days';
 else {
 const now = new Date();
 const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
 const firstDayStr = this.formatLocalDate(firstDay);
 const firstDayYear = new Date(now.getFullYear(), 0, 1);
 const firstDayYearStr = this.formatLocalDate(firstDayYear);
 if (this.dateStart === firstDayStr && this.dateEnd === todayStr) this.currentPreset = 'this_month';
 else if (this.dateStart === firstDayYearStr && this.dateEnd === todayStr) this.currentPreset = 'this_year';
 else this.currentPreset = 'custom';
 }
 },

 getFormattedDate(daysAgo) { const d = new Date(); d.setDate(d.getDate() - daysAgo); return this.formatLocalDate(d); },
 formatLocalDate(date) { const offset = date.getTimezoneOffset(); const localDate = new Date(date.getTime() - (offset * 60 * 1000)); return localDate.toISOString().split('T')[0]; },
 formatDisplayDate(dateStr) { if (!dateStr) return ''; const d = new Date(dateStr); const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`; },

 applyPreset(val) {
 this.currentPreset = val;
 const today = this.getFormattedDate(0);
 if (val === 'all') {
 this.dateStart = ''; this.dateEnd = '';
 this.startInputEl.value = ''; this.endInputEl.value = '';
 if (this.fp) this.fp.clear();
 this.updateDisplayText(); this.submitForm();
 } else if (val === 'today') {
 this.dateStart = today; this.dateEnd = today;
 this.startInputEl.value = today; this.endInputEl.value = today;
 if (this.fp) this.fp.setDate([today, today]);
 this.updateDisplayText(); this.submitForm();
 } else if (val === 'yesterday') {
 const yesterday = this.getFormattedDate(1);
 this.dateStart = yesterday; this.dateEnd = yesterday;
 this.startInputEl.value = yesterday; this.endInputEl.value = yesterday;
 if (this.fp) this.fp.setDate([yesterday, yesterday]);
 this.updateDisplayText(); this.submitForm();
 } else if (val === 'this_week') {
 const now = new Date(); const day = now.getDay(); const diff = now.getDate() - day + (day === 0 ? -6 : 1);
 const start = this.formatLocalDate(new Date(now.setDate(diff)));
 this.dateStart = start; this.dateEnd = today;
 this.startInputEl.value = start; this.endInputEl.value = today;
 if (this.fp) this.fp.setDate([start, today]);
 this.updateDisplayText(); this.submitForm();
 } else if (val === '7days') {
 const start = this.getFormattedDate(6);
 this.dateStart = start; this.dateEnd = today;
 this.startInputEl.value = start; this.endInputEl.value = today;
 if (this.fp) this.fp.setDate([start, today]);
 this.updateDisplayText(); this.submitForm();
 } else if (val === '28days') {
 const start = this.getFormattedDate(27);
 this.dateStart = start; this.dateEnd = today;
 this.startInputEl.value = start; this.endInputEl.value = today;
 if (this.fp) this.fp.setDate([start, today]);
 this.updateDisplayText(); this.submitForm();
 } else if (val === 'this_month') {
 const now = new Date(); const start = this.formatLocalDate(new Date(now.getFullYear(), now.getMonth(), 1));
 this.dateStart = start; this.dateEnd = today;
 this.startInputEl.value = start; this.endInputEl.value = today;
 if (this.fp) this.fp.setDate([start, today]);
 this.updateDisplayText(); this.submitForm();
 } else if (val === 'last_month') {
 const now = new Date(); const start = this.formatLocalDate(new Date(now.getFullYear(), now.getMonth() - 1, 1));
 const end = this.formatLocalDate(new Date(now.getFullYear(), now.getMonth(), 0));
 this.dateStart = start; this.dateEnd = end;
 this.startInputEl.value = start; this.endInputEl.value = end;
 if (this.fp) this.fp.setDate([start, end]);
 this.updateDisplayText(); this.submitForm();
 } else if (val === 'this_year') {
 const start = this.formatLocalDate(new Date(new Date().getFullYear(), 0, 1));
 this.dateStart = start; this.dateEnd = today;
 this.startInputEl.value = start; this.endInputEl.value = today;
 if (this.fp) this.fp.setDate([start, today]);
 this.updateDisplayText(); this.submitForm();
 }
 },

 submitForm() { this.$el.closest('form')?.submit(); }
 }));
 });
 </script>
</body>
</html>