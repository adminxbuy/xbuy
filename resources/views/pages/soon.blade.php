@extends('layouts.app')

@section('title', ($title ?? 'Page') . ' | Coming Soon')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-20">
    <div class="max-w-md w-full text-center space-y-6">
        
        <!-- Animated Icon Container -->
        <div class="relative flex items-center justify-center w-24 h-24 mx-auto bg-yellow-50 rounded-full border border-yellow-250 animate-pulse">
            <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"></path>
            </svg>
        </div>

        <!-- Heading & Details -->
        <div class="space-y-3">
            <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight">
                {{ $title ?? 'Page' }}
            </h1>
            <p class="text-sm font-semibold text-zinc-500 uppercase tracking-widest">
                Incoming Soon / Not Available Yet
            </p>
            <p class="text-xs text-zinc-400 max-w-sm mx-auto leading-relaxed">
                We are currently building this page to ensure you have the best possible experience. Please check back in a few days.
            </p>
        </div>

        <!-- Go Back Home CTA -->
        <div class="pt-4">
            <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 bg-zinc-950 text-white font-bold text-xs rounded-xl shadow-xs hover:bg-zinc-900 hover:scale-101 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                </svg>
                <span>Back to Homepage</span>
            </a>
        </div>

    </div>
</div>
@endsection
