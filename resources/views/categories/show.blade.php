@extends('layouts.app')

@section('title', ($category->name ?? 'Category') . ' | X-Buy Verified PC Components')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs font-semibold text-zinc-500">
        <a href="/" class="hover:text-zinc-950 transition-colors">Home</a>
        <span>/</span>
        <a href="/listings" class="hover:text-zinc-950 transition-colors">Catalog</a>
        <span>/</span>
        <span class="text-zinc-900 font-bold">{{ $category->name }}</span>
    </nav>

    <!-- Category Hero Banner -->
    <div class="rounded-2xl border border-zinc-200 bg-gradient-to-r from-amber-500/15 via-zinc-50 to-white p-8 sm:p-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xs">
        <div class="space-y-2 max-w-2xl">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-800 bg-amber-200/60 px-3 py-1 rounded-full inline-block">
                Verified Category Hub
            </span>
            <h1 class="text-3xl sm:text-4xl font-black text-zinc-950 tracking-tight">
                {{ $category->name }}
            </h1>
            <p class="text-xs sm:text-sm text-zinc-600 font-medium leading-relaxed">
                Browse tested, benchmark-verified {{ strtolower($category->name) }} with 100% money-back escrow and 7-day hardware inspection.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/dashboard/listings/create" class="px-5 py-2.5 rounded-full bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-xs shadow-sm transition-transform active:scale-95">
                + Sell in this Category
            </a>
        </div>
    </div>

    <!-- Subcategories Pill Carousel -->
    @if(isset($category->children) && $category->children->count() > 0)
        <div class="space-y-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Subcategories</h3>
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <a href="/listings?category={{ $category->slug }}" 
                   class="px-4 py-2 bg-zinc-950 text-white rounded-full text-xs font-bold shrink-0">
                    All {{ $category->name }}
                </a>
                @foreach($category->children as $child)
                    <a href="/listings?category={{ $child->slug }}" 
                       class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 rounded-full text-xs font-semibold shrink-0 transition-colors">
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Listings Feed -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
            <h2 class="text-xl font-extrabold text-zinc-950">
                Available {{ $category->name }} ({{ $listings->total() }})
            </h2>
            <a href="/listings?category={{ $category->slug }}" class="text-xs font-bold text-amber-700 hover:underline">
                Filter & Sort &rarr;
            </a>
        </div>

        @if($listings->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($listings as $listing)
                    <x-frontend.product-card :listing="$listing" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $listings->links() }}
            </div>
        @else
            <div class="py-16 text-center bg-zinc-50 border border-dashed border-zinc-300 rounded-2xl p-8">
                <p class="text-sm font-semibold text-zinc-600">No active listings in {{ $category->name }} at the moment.</p>
                <a href="/dashboard/listings/create" class="mt-3 inline-block px-5 py-2.5 bg-[#FDD835] text-black font-extrabold text-xs rounded-full">
                    List Your {{ $category->name }} First
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
