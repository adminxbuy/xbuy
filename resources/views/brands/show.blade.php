@extends('layouts.app')

@section('title', ($brand->name ?? 'Brand') . ' Hardware & Gear | X-Buy')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs font-semibold text-zinc-500">
        <a href="/" class="hover:text-zinc-950 transition-colors">Home</a>
        <span>/</span>
        <a href="/listings" class="hover:text-zinc-950 transition-colors">Brands</a>
        <span>/</span>
        <span class="text-zinc-900 font-bold">{{ $brand->name }}</span>
    </nav>

    <!-- Brand Hero Banner -->
    <div class="rounded-2xl border border-zinc-200 bg-white p-8 sm:p-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xs">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-zinc-100 border border-zinc-200 flex items-center justify-center font-black text-2xl text-zinc-900 shrink-0">
                @if($brand->logo_url)
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="max-w-full max-h-full object-contain p-2">
                @else
                    {{ substr($brand->name, 0, 2) }}
                @endif
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-black text-zinc-950 tracking-tight">{{ $brand->name }}</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-[11px] font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        <span>Verified Brand</span>
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-zinc-500 font-medium">
                    Genuine {{ $brand->name }} hardware verified with serial number checks and 7-day testing protection.
                </p>
            </div>
        </div>

        <div>
            <a href="/listings?search={{ urlencode($brand->name) }}" class="px-5 py-2.5 rounded-full bg-zinc-950 text-white font-extrabold text-xs shadow-xs hover:bg-zinc-800 transition-colors">
                Search in {{ $brand->name }}
            </a>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
            <h2 class="text-xl font-extrabold text-zinc-950">
                Verified {{ $brand->name }} Gear ({{ $listings->total() }})
            </h2>
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
                <p class="text-sm font-semibold text-zinc-600">No active {{ $brand->name }} listings right now.</p>
                <a href="/dashboard/listings/create" class="mt-3 inline-block px-5 py-2.5 bg-[#FDD835] text-black font-extrabold text-xs rounded-full">
                    Sell {{ $brand->name }} Hardware
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
