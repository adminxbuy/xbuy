@extends('layouts.app')

@section('title', 'Browse Listings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header with Search Info -->
    <div class="mb-8">
        @if(request()->has('search') && request('search') !== '')
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight">Search Results for "{{ request('search') }}"</h1>
            <p class="text-zinc-500 mt-1">Found {{ $listings->total() }} matching listings</p>
        @elseif(request()->has('category') && request('category') !== '')
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight capitalize">{{ request('category') }} Listings</h1>
            <p class="text-zinc-500 mt-1">Showing all items in {{ request('category') }}</p>
        @else
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight">All Listings</h1>
            <p class="text-zinc-500 mt-1">Explore all verified PC components on X-Buy</p>
        @endif
    </div>

    <!-- Listings Grid -->
    @if($listings->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($listings as $listing)
                <a href="{{ route('listings.show', $listing->slug) }}" class="group bg-white rounded-xl border border-zinc-200/60 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <!-- Image Container -->
                        <div class="aspect-square bg-zinc-50 border-b border-zinc-150 flex items-center justify-center overflow-hidden relative">
                            <img src="{{ $listing->primary_image_url ?? 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400' }}" 
                                 alt="{{ $listing->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 select-none">
                            
                            <!-- Condition Grade Tag -->
                            <span class="absolute top-3 left-3 bg-zinc-900/90 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">
                                GRADE {{ $listing->grade }}
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 space-y-2">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">
                                {{ $listing->brand ?? 'NVIDIA' }}
                            </span>
                            <h3 class="text-sm font-bold text-zinc-900 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                {{ $listing->title }}
                            </h3>
                            <div class="flex items-center gap-1.5 text-xs text-zinc-500">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>{{ $listing->seller ? $listing->seller->shop_name : 'Gadget Zone' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer (Price) -->
                    <div class="px-4 pb-4 pt-1 flex items-baseline justify-between border-t border-zinc-50 mt-2">
                        <span class="text-base font-extrabold text-zinc-900">₹{{ number_format($listing->price) }}</span>
                        <span class="text-[10px] text-indigo-600 font-semibold uppercase tracking-wider">Escrow Holds</span>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $listings->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-16 bg-zinc-50 rounded-xl border border-dashed border-zinc-300 max-w-lg mx-auto flex flex-col items-center">
            <i data-lucide="package-open" class="w-12 h-12 text-zinc-400 mb-3"></i>
            <h2 class="text-lg font-bold text-zinc-850">No Listings Found</h2>
            <p class="text-zinc-500 text-sm mt-1 max-w-xs">We couldn't find any products matching your filters. Try checking your spelling or selecting another category.</p>
            <a href="/listings" class="mt-5 inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-all">
                Clear Filters
            </a>
        </div>
    @endif
</div>
@endsection
