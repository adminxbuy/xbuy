<!-- Global Header Component (Mercari Style) -->
<div x-data="{ 
    mobileOpen: false, 
    categoriesOpen: false, 
    brandsOpen: false, 
    userMenuOpen: false, 
    activeParent: null, 
    searchFocused: false,
    closeCategoriesTimer: null 
}" class="relative z-50 bg-white border-zinc-200/80 sticky top-0">

    <!-- Desktop Header Layout (>=1024px) -->
    <div class="hidden lg:block">
        <!-- Main Header Bar -->
        @include('layouts.partials.header.main-bar')

        <!-- Sub-navigation Bar -->
        @include('layouts.partials.header.sub-nav')
    </div>

    <!-- Mobile Header Layout (<1024px) -->
    <div class="block lg:hidden">
        @include('layouts.partials.header.mobile-header')
    </div>

    <!-- Mobile Side Drawer Navigation -->
    @include('layouts.partials.header.mobile-drawer')
</div>