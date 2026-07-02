@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)

@section('content')
<style>
    /* Restore visible bullet points and list styles for policy/careers page content */
    .prose ul {
        list-style-type: disc !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        margin-bottom: 1rem !important;
    }
    .prose ol {
        list-style-type: decimal !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        margin-bottom: 1rem !important;
    }
    .prose li {
        list-style: inherit !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0.25rem !important;
        display: list-item !important;
    }
</style>
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
    <!-- Breadcrumbs -->
    <div class="mb-8 flex items-center space-x-2 text-xs font-semibold text-zinc-400 uppercase tracking-wider">
        <a href="/" class="hover:text-zinc-700 transition-colors">Home</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-300"></i>
        <span class="hover:text-zinc-700 transition-colors">Pages</span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-300"></i>
        <span class="text-zinc-600">{{ $page->title }}</span>
    </div>

    <!-- Title Header -->
    <div class="border-b border-zinc-250 pb-5 mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-zinc-900 tracking-tight mb-2">{{ $page->title }}</h1>
        @if($page->updated_at)
            <div class="flex items-center space-x-2 text-[10px] text-zinc-450 font-medium uppercase tracking-wider">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-zinc-400"></i>
                <span>Last updated: {{ $page->updated_at->format('d M, Y') }}</span>
            </div>
        @endif
    </div>

    <!-- Safe Content injection from Rich Text Editor -->
    <article class="prose max-w-none prose-yellow text-zinc-800 leading-relaxed prose-headings:font-bold prose-headings:text-zinc-900 prose-a:text-yellow-700 prose-a:underline hover:prose-a:text-yellow-900 transition-colors">
        {!! $page->content !!}
    </article>
</div>
@endsection
