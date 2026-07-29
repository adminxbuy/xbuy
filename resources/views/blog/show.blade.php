@extends('layouts.app')

@section('title', $article->seo_title ?: $article->title)

@section('content')
 <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
 <!-- Breadcrumb -->
 <nav class="flex items-center space-x-2 text-xs font-semibold text-zinc-400 mb-6 uppercase tracking-wider">
 <a href="/" class="hover:text-zinc-700">Home</a>
 <i data-lucide="chevron-right" class="w-3 h-3"></i>
 <a href="{{ route('blog.index') }}" class="hover:text-zinc-700">Blog</a>
 <i data-lucide="chevron-right" class="w-3 h-3"></i>
 <span class="text-zinc-600 line-clamp-1">{{ $article->title }}</span>
 </nav>

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
 <!-- Left 2 Cols: Main Article Body -->
 <div class="lg:col-span-2 space-y-8 bg-white border border-zinc-200 rounded-3xl p-6 md:p-10 ">
 <!-- Article Headers -->
 <div class="space-y-4">
 <div class="flex items-center space-x-2.5 text-xs font-bold text-zinc-400">
 <span
 class="px-2.5 py-0.5 bg-zinc-100 text-zinc-800 rounded-full font-bold uppercase tracking-wider">{{ $article->category ?: 'General' }}</span>
 <span>•</span>
 <span>{{ $article->reading_time }} min read</span>
 </div>
 <h1 class="text-2xl md:text-4xl font-bold text-zinc-950 tracking-tight leading-tight">
 {{ $article->title }}</h1>
 @if($article->subtitle)
 <p class="text-zinc-500 text-sm md:text-lg font-medium leading-relaxed">{{ $article->subtitle }}</p>
 @endif

 <div class="flex items-center space-x-3 pt-4 text-xs font-semibold text-zinc-500">
 <span>Published on
 {{ $article->published_at ? $article->published_at->format('M d, Y') : $article->created_at->format('M d, Y') }}</span>
 <span>•</span>
 <span class="flex items-center"><i data-lucide="eye" class="w-3.5 h-3.5 mr-1 text-zinc-400"></i>
 {{ number_format($article->views_count) }} views</span>
 </div>
 </div>

 <!-- Cover Image -->
 @if($article->cover_image)
 <div class="w-full aspect-[16/9] bg-zinc-100 rounded-2xl overflow-hidden border border-zinc-150 ">
 <img src="{{ $article->cover_image }}" class="w-full h-full object-cover" alt="{{ $article->title }}">
 </div>
 @endif

 <!-- Article Content -->
 <div class="prose max-w-none text-zinc-800 leading-relaxed font-normal text-sm md:text-base space-y-6 pt-4">
 {!! $article->content !!}
 </div>
 </div>

 <!-- Right 1 Col: Sidebar widgets -->
 <div class="space-y-8">
 <!-- Alert Subscriber box widget -->
 <div class="bg-gradient-to-br from-zinc-950 via-zinc-900 to-zinc-800 text-white rounded-3xl p-6 shadow-lg border border-white/5 space-y-4 relative overflow-hidden"
 x-data="{  email: '',  name: '',  loading: false,  message: '',  success: false,
 async subscribe() {
 this.loading = true;
 this.message = '';
 try {
 let response = await fetch('{{ route('subscribers.subscribe') }}', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}'
 },
 body: JSON.stringify({ email: this.email, name: this.name, source: 'blog_sidebar' })
 });
 let res = await response.json();
 this.success = res.success;
 this.message = res.message;
 if(res.success) {
 this.email = '';
 this.name = '';
 }
 } catch(e) {
 this.message = 'Something went wrong. Please try again.';
 this.success = false;
 } finally {
 this.loading = false;
 }
 }
 }">
 <!-- Abstract glowing accent -->
 <div class="absolute -right-20 -top-20 w-44 h-44 rounded-full bg-[#fdd835]/10 blur-3xl"></div>

 <div class="relative z-10 space-y-3">
 <div class="w-10 h-10 bg-[#fdd835]/15 text-[#fdd835] rounded-xl flex items-center justify-center">
 <i data-lucide="bell-ring" class="w-5 h-5"></i>
 </div>
 <h3 class="text-lg font-bold tracking-tight">Stay updated with Sales & Alerts</h3>
 <p class="text-zinc-400 text-xs leading-relaxed font-medium">Subscribe now to get price drop alerts,
 weekly hardware digests, and exclusive sales alerts directly in your inbox.</p>

 <form @submit.prevent="subscribe" class="space-y-2.5 pt-2">
 <input type="text" x-model="name" placeholder="Your Name" required
 class="w-full px-3.5 py-2 border border-white/10 rounded-xl bg-white/5 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-1 focus:ring-[#fdd835]">
 <input type="email" x-model="email" placeholder="email@example.com" required
 class="w-full px-3.5 py-2 border border-white/10 rounded-xl bg-white/5 text-white placeholder-zinc-500 text-xs focus:outline-none focus:ring-1 focus:ring-[#fdd835]">
 <button type="submit" :disabled="loading"
 class="w-full py-2 bg-[#fdd835] hover:bg-[#ffd747] text-black font-semibold rounded-xl text-xs transition-all tracking-wide disabled:opacity-50">
 <span x-text="loading ? 'Subscribing...' : 'Subscribe'"></span>
 </button>
 </form>

 <!-- Feedback message -->
 <template x-if="message">
 <p class="text-[11px] font-bold mt-2" :class="success ? 'text-emerald-400' : 'text-rose-400'"
 x-text="message"></p>
 </template>
 </div>
 </div>

 <!-- Recent Articles Widget -->
 <div class="bg-white border border-zinc-200 rounded-3xl p-6 space-y-4">
 <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider pb-2 border-b border-zinc-100">
 Recent Posts</h3>
 <div class="space-y-4">
 @forelse($recentArticles as $recent)
 <a href="{{ route('blog.show', $recent->slug) }}" class="flex items-center space-x-3.5 group">
 <div class="w-14 h-12 rounded-lg bg-zinc-100 border border-zinc-200 overflow-hidden shrink-0">
 @if($recent->cover_image)
 <img src="{{ $recent->cover_image }}" class="w-full h-full object-cover">
 @else
 <div class="w-full h-full flex items-center justify-center text-zinc-400 bg-zinc-50">
 <i data-lucide="image" class="w-3.5 h-3.5"></i>
 </div>
 @endif
 </div>
 <div class="space-y-0.5">
 <h4
 class="text-xs font-bold text-zinc-900 leading-snug group-hover:text-black group-hover:underline line-clamp-2">
 {{ $recent->title }}</h4>
 <div class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">
 {{ $recent->published_at ? $recent->published_at->format('M d, Y') : $recent->created_at->format('M d, Y') }}
 </div>
 </div>
 </a>
 @empty
 <p class="text-xs text-zinc-400">No other articles published yet.</p>
 @endforelse
 </div>
 </div>
 </div>
 </div>
 </div>
@endsection