@extends('layouts.app')

@section('title', 'Blog & Resource Center')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 space-y-12">
 <!-- Header Hero -->
 <div class="text-center max-w-3xl mx-auto space-y-4">
 <span class="px-3 py-1 bg-yellow-100 border border-yellow-200 text-yellow-900 rounded-full text-xs font-bold uppercase tracking-widest">Resources & Articles</span>
 <h1 class="text-3xl md:text-5xl font-bold text-zinc-900 tracking-tight leading-tight">The Hardware Hub</h1>
 <p class="text-zinc-500 text-sm md:text-base">Get the latest PC component guides, buying tips, sales alerts, and tech updates compiled by our hardware experts.</p>
 </div>

 <!-- Featured Post -->
 @if($featuredArticle)
 <a href="{{ route('blog.show', $featuredArticle->slug) }}" class="block bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:shadow-md transition-all group duration-300">
 <div class="grid grid-cols-1 lg:grid-cols-2">
 <!-- Image -->
 <div class="aspect-[16/10] lg:aspect-auto w-full bg-zinc-100 overflow-hidden relative">
 @if($featuredArticle->cover_image)
 <img src="{{ $featuredArticle->cover_image }}" class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500">
 @else
 <div class="w-full h-full flex items-center justify-center text-zinc-400 bg-zinc-50">
 <i data-lucide="image" class="w-12 h-12 stroke-1"></i>
 </div>
 @endif
 <span class="absolute top-4 left-4 bg-[#fdd835] text-black font-semibold text-[10px] px-3 py-1.5 rounded-full uppercase tracking-wider ">Featured</span>
 </div>
 <!-- Content -->
 <div class="p-8 md:p-12 flex flex-col justify-between space-y-6">
 <div class="space-y-4">
 <div class="flex items-center space-x-2 text-xs font-semibold text-zinc-400">
 <span>{{ $featuredArticle->category ?: 'General' }}</span>
 <span>•</span>
 <span>{{ $featuredArticle->reading_time }} min read</span>
 </div>
 <h2 class="text-xl md:text-3xl font-bold text-zinc-950 tracking-tight group-hover:underline">{{ $featuredArticle->title }}</h2>
 @if($featuredArticle->subtitle)
 <p class="text-zinc-500 text-sm md:text-base leading-relaxed font-medium line-clamp-3">{{ $featuredArticle->subtitle }}</p>
 @endif
 </div>
  <div class="flex items-center justify-between pt-4 border-t border-zinc-100">
 <span class="text-xs text-zinc-450 font-medium">{{ $featuredArticle->published_at ? $featuredArticle->published_at->format('M d, Y') : $featuredArticle->created_at->format('M d, Y') }}</span>
 <span class="inline-flex items-center text-xs font-bold text-black group-hover:translate-x-1 transition-transform">
 <span>Read Article</span>
 <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
 </span>
 </div>
 </div>
 </div>
 </a>
 @endif

 <!-- Toolbar: Search & Category Chips -->
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-4 border-t border-zinc-200">
 <!-- Tags/Categories -->
 <div class="flex flex-wrap items-center gap-2">
 <a href="{{ route('blog.index') }}" class="px-4 py-2 text-xs font-bold rounded-full transition-all border {{ !request('category') ? 'bg-black text-white border-black' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300' }}">All Articles</a>
 @foreach($categories as $cat)
 <a href="{{ route('blog.index', ['category' => $cat]) }}"  class="px-4 py-2 text-xs font-bold rounded-full transition-all border {{ request('category') === $cat ? 'bg-black text-white border-black' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300' }}">{{ $cat }}</a>
 @endforeach
 </div>

 <!-- Search input -->
 <form action="{{ route('blog.index') }}" method="GET" class="relative max-w-sm w-full">
 @if(request('category'))
 <input type="hidden" name="category" value="{{ request('category') }}">
 @endif
 <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."  class="w-full pl-9 pr-4 py-2 border border-zinc-200 rounded-full text-xs focus:outline-none focus:ring-2 focus:ring-[#fdd835]">
 <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 w-3.5 h-3.5"></i>
 </form>
 </div>

 <!-- Article grid -->
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 @forelse($articles as $article)
 <article class="bg-white border border-zinc-200 rounded-2xl overflow-hidden hover:shadow-md transition-all group flex flex-col justify-between">
 <div>
 <!-- Image -->
 <div class="aspect-[16/10] bg-zinc-150 w-full overflow-hidden">
 @if($article->cover_image)
 <img src="{{ $article->cover_image }}" class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500">
 @else
 <div class="w-full h-full flex items-center justify-center text-zinc-400 bg-zinc-50">
 <i data-lucide="image" class="w-8 h-8 stroke-1"></i>
 </div>
 @endif
 </div>
 <!-- Body Content -->
 <div class="p-6 space-y-3">
 <div class="flex items-center space-x-2 text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">
 <span>{{ $article->category ?: 'General' }}</span>
 <span>•</span>
 <span>{{ $article->reading_time }} min read</span>
 </div>
 <h3 class="text-lg font-bold text-zinc-950 leading-snug group-hover:underline line-clamp-2">
 <a href="{{ route('blog.show', $article->slug) }}">{{ $article->title }}</a>
 </h3>
 @if($article->subtitle)
 <p class="text-zinc-500 text-xs font-medium leading-relaxed line-clamp-2">{{ $article->subtitle }}</p>
 @endif
 </div>
 </div>
 <!-- Footer Info -->
 <div class="px-6 pb-6 pt-4 border-t border-zinc-100 flex items-center justify-between">
 <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">{{ $article->published_at ? $article->published_at->format('M d, Y') : $article->created_at->format('M d, Y') }}</span>
 <a href="{{ route('blog.show', $article->slug) }}" class="inline-flex items-center text-xs font-bold text-zinc-900 group-hover:text-black">
 <span>Read More</span>
 <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1 group-hover:translate-x-0.5 transition-transform"></i>
 </a>
 </div>
 </article>
 @empty
 <div class="col-span-full py-16 text-center text-zinc-400">
 <i data-lucide="book-open" class="w-12 h-12 mx-auto stroke-1.5 mb-3 text-zinc-350"></i>
 <p class="text-sm font-semibold">No articles found in this category.</p>
 <a href="{{ route('blog.index') }}" class="text-xs text-yellow-600 underline font-bold mt-1 inline-block">Back to all articles</a>
 </div>
 @endforelse
 </div>

 <!-- Pagination -->
 @if($articles->hasPages())
 <div class="pt-8 border-t border-zinc-200 flex justify-center">
 {{ $articles->links() }}
 </div>
 @endif
</div>
@endsection
