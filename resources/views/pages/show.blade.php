@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Breadcrumbs -->
    <nav class="flex mb-8 text-xs font-semibold text-zinc-400 gap-2 items-center" aria-label="Breadcrumb">
        <a href="/" class="hover:text-zinc-600 transition-colors">Home</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        <span class="text-zinc-800">{{ $page->title }}</span>
    </nav>

    <!-- Page Header -->
    <header class="mb-10 pb-6 border-b border-zinc-100">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-950 tracking-tight leading-tight mb-3">
            {{ $page->title }}
        </h1>
        <div class="flex items-center gap-2.5 text-xs text-zinc-400 font-medium">
            <span>Last updated {{ $page->updated_at ? $page->updated_at->format('F d, Y') : now()->format('F d, Y') }}</span>
        </div>
    </header>

    <!-- Page Content (Rich HTML) -->
    <article class="prose prose-zinc max-w-none prose-headings:text-zinc-950 prose-headings:font-bold prose-h2:text-xl prose-h2:mt-8 prose-h2:mb-4 prose-p:text-zinc-650 prose-p:leading-relaxed prose-p:mb-5 prose-li:text-zinc-650">
        {!! $page->content !!}
    </article>
</div>
@endsection
