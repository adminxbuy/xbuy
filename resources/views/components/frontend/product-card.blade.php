@props(['listing'])

@php
    $imgUrl = $listing->primary_image_url;
    if (!$imgUrl && $listing->images && $listing->images->count() > 0) {
        $imgUrl = $listing->images->first()->image_url;
    }
    if (!$imgUrl) {
        $imgUrl = 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400';
    }
    $brandName = $listing->brand ? (is_object($listing->brand) ? $listing->brand->name : $listing->brand) : ($listing->brand_text ?? 'Verified Gear');
    $conditionText = $listing->grade ? 'Grade ' . $listing->grade : ($listing->condition ? str_replace('_', ' ', $listing->condition) : null);
    $isFreeShip = ($listing->shipping_charges ?? $listing->shipping_fee ?? 0) == 0;
@endphp

<div class="group relative flex flex-col bg-white rounded-xl border border-zinc-200/80 overflow-hidden hover:shadow-lg hover:border-zinc-300 transition-all duration-200"
     x-data="{ isLiked: {{ auth()->check() && auth()->user()->hasFavorited($listing->id) ? 'true' : 'false' }} }">
    
    <!-- 1:1 Aspect Ratio Photo Container -->
    <a href="{{ route('listings.show', $listing->slug) }}" class="relative block aspect-square w-full bg-zinc-100 overflow-hidden">
        <img 
            src="{{ $imgUrl }}" 
            alt="{{ $listing->title }}"
            loading="lazy"
            class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300 select-none"
        />

        <!-- Top-Right Floating Wishlist Heart -->
        <button 
            type="button"
            @click.prevent.stop="
                @if(auth()->check())
                    isLiked = !isLiked;
                    fetch('/api/buyer/wishlist/toggle', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ listing_id: {{ $listing->id }} })
                    });
                @else
                    window.location.href = '{{ route('login') }}';
                @endif
            "
            class="absolute top-2.5 right-2.5 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 backdrop-blur-xs text-zinc-600 shadow-sm transition-all hover:bg-white hover:scale-110 active:scale-95 cursor-pointer"
            aria-label="Save to Wishlist"
        >
            <svg class="h-4.5 w-4.5 transition-colors" :class="isLiked ? 'fill-red-500 text-red-500' : 'text-zinc-600 stroke-2'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </button>

        <!-- Bottom-Left Badge: Free Shipping / Condition -->
        <div class="absolute bottom-2 left-2 flex flex-wrap gap-1">
            @if($isFreeShip)
                <span class="rounded-md bg-zinc-900/80 backdrop-blur-xs px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">
                    Free Ship
                </span>
            @endif
            @if($conditionText)
                <span class="rounded-md bg-white/90 backdrop-blur-xs px-2 py-0.5 text-[10px] font-semibold text-zinc-800 capitalize shadow-xs">
                    {{ $conditionText }}
                </span>
            @endif
        </div>

        <!-- Sold Overlay Banner -->
        @if($listing->listing_status === 'sold')
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center z-10">
                <span class="bg-red-600 text-white font-extrabold text-sm uppercase px-4 py-1.5 rounded-md shadow-lg tracking-widest transform -rotate-6">
                    SOLD
                </span>
            </div>
        @endif
    </a>

    <!-- Card Content Details -->
    <div class="flex flex-col flex-1 p-3">
        <!-- Price & MSRP -->
        <div class="flex items-baseline gap-1.5 mb-1">
            <span class="text-base font-extrabold text-zinc-950 tracking-tight">
                ₹{{ number_format($listing->price) }}
            </span>
            @if($listing->original_price && $listing->original_price > $listing->price)
                <span class="text-xs text-zinc-400 line-through">
                    ₹{{ number_format($listing->original_price) }}
                </span>
            @endif
        </div>

        <!-- Truncated 2-Line Title -->
        <a href="{{ route('listings.show', $listing->slug) }}" class="text-xs sm:text-sm font-medium text-zinc-800 line-clamp-2 leading-snug hover:underline group-hover:text-zinc-950 mb-2">
            {{ $listing->title }}
        </a>

        <!-- Bottom Meta: Brand & Date -->
        <div class="mt-auto pt-2 border-t border-zinc-100 flex items-center justify-between text-[11px] text-zinc-500 font-medium">
            <span class="truncate max-w-[120px]">{{ $brandName }}</span>
            <span class="text-zinc-400">{{ $listing->created_at ? $listing->created_at->diffForHumans(null, true, true) : 'recently' }}</span>
        </div>
    </div>
</div>
