@extends('layouts.app')

@section('title', ($page->meta_title ?? $page->title) . ' | X-Buy Policy & Trust Center')

@php
    $categories = [
        'Trust & Security' => [
            ['title' => 'Buyer Protection Guarantee', 'slug' => 'buyer-protection', 'icon' => 'shield-check'],
            ['title' => 'Escrow Financial Policy', 'slug' => 'escrow-policy', 'icon' => 'lock'],
            ['title' => 'Hardware Authentication', 'slug' => 'authenticate', 'icon' => 'badge-check'],
            ['title' => 'Safe Trading Guidelines', 'slug' => 'safety-guidelines', 'icon' => 'alert-triangle'],
        ],
        'Selling & Logistics' => [
            ['title' => 'Seller Guidelines & Payouts', 'slug' => 'seller-guidelines', 'icon' => 'store'],
            ['title' => 'Shipping Made Easy', 'slug' => 'shipping', 'icon' => 'truck'],
            ['title' => 'Packaging Best Practices', 'slug' => 'packaging', 'icon' => 'package'],
            ['title' => 'Selling Fees & Commission', 'slug' => 'fees', 'icon' => 'percent'],
        ],
        'Rules & Resolution' => [
            ['title' => 'Dispute Arbitration Rules', 'slug' => 'disputes', 'icon' => 'scale'],
            ['title' => 'Returns & Refunds Policy', 'slug' => 'returns', 'icon' => 'rotate-ccw'],
            ['title' => 'Community Standards', 'slug' => 'community', 'icon' => 'users'],
        ],
        'Company & Legal' => [
            ['title' => 'About X-Buy India', 'slug' => 'about', 'icon' => 'info'],
            ['title' => 'Terms of Service', 'slug' => 'terms', 'icon' => 'file-text'],
            ['title' => 'Privacy & Cookie Policy', 'slug' => 'privacy', 'icon' => 'eye-off'],
            ['title' => 'Contact Support Desk', 'slug' => 'contact-us', 'icon' => 'life-buoy'],
        ],
    ];
    $currentSlug = $page->slug ?? '';
@endphp

@section('content')
<div class="bg-zinc-50 border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <nav class="flex mb-4 text-xs font-semibold text-zinc-500 gap-2 items-center" aria-label="Breadcrumb">
            <a href="/" class="hover:text-zinc-900 transition-colors">Home</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-400"></i>
            <a href="/p/buyer-protection" class="hover:text-zinc-900 transition-colors">Trust & Help Center</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-zinc-400"></i>
            <span class="text-zinc-950 font-bold truncate max-w-xs">{{ $page->title }}</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100/70 border border-amber-300 text-amber-900 text-xs font-black uppercase tracking-wider mb-2">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-700"></i>
                    <span>Official Policy Document</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-zinc-950 tracking-tight">
                    {{ $page->title }}
                </h1>
            </div>
            <div class="text-xs text-zinc-500 font-medium shrink-0">
                Last reviewed: <span class="font-bold text-zinc-800">{{ $page->updated_at ? $page->updated_at->format('M d, Y') : now()->format('M d, Y') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ searchFilter: '', feedbackSubmitted: false }">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- Left Sidebar Navigation -->
        <aside class="lg:col-span-4 xl:col-span-3 space-y-6">
            <div class="bg-white rounded-2xl border border-zinc-200 p-5 shadow-xs sticky top-24">
                <div class="mb-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-zinc-400 mb-2">Search Policies</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input 
                            type="text" 
                            x-model="searchFilter"
                            placeholder="Filter documentation..."
                            class="w-full pl-9 pr-3 py-2 text-xs font-medium bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-hidden focus:border-zinc-900 focus:bg-white transition-all"
                        />
                    </div>
                </div>

                <div class="space-y-6 divide-y divide-zinc-100">
                    @foreach($categories as $groupTitle => $items)
                        <div class="pt-4 first:pt-0">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-zinc-400 mb-2 px-2">
                                {{ $groupTitle }}
                            </h4>
                            <ul class="space-y-1">
                                @foreach($items as $item)
                                    @php
                                        $isActive = ($item['slug'] === $currentSlug);
                                    @endphp
                                    <li x-show="!searchFilter || '{{ strtolower($item['title']) }}'.includes(searchFilter.toLowerCase())">
                                        <a 
                                            href="/p/{{ $item['slug'] }}" 
                                            class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-all {{ $isActive ? 'bg-[#FDD835] text-zinc-950 font-black shadow-xs' : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100' }}"
                                        >
                                            <div class="flex items-center gap-2 truncate">
                                                <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5 shrink-0 {{ $isActive ? 'text-zinc-950' : 'text-zinc-400' }}"></i>
                                                <span class="truncate">{{ $item['title'] }}</span>
                                            </div>
                                            @if($isActive)
                                                <span class="w-1.5 h-1.5 rounded-full bg-zinc-950 shrink-0"></span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                <!-- Contact Help Desk Box -->
                <div class="mt-6 p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 text-center space-y-2">
                    <p class="text-xs font-bold text-amber-950">Have a specific case?</p>
                    <p class="text-[11px] text-amber-800 leading-snug">Our Escrow team resolves disputes & inquiries 24/7.</p>
                    <a href="/p/contact-us" class="inline-block mt-1 px-4 py-1.5 rounded-full bg-[#FDD835] hover:bg-[#FBC02D] text-zinc-950 font-black text-xs shadow-xs transition-transform active:scale-95">
                        Contact Escrow Support
                    </a>
                </div>
            </div>
        </aside>

        <!-- Right Main Article Body -->
        <main class="lg:col-span-8 xl:col-span-9 space-y-8">
            <div class="bg-white rounded-3xl border border-zinc-200 p-6 sm:p-10 shadow-xs">
                
                <!-- Escrow Reassurance Callout -->
                <div class="mb-8 p-4 rounded-2xl bg-zinc-50 border border-zinc-200/80 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-[#FDD835]/30 text-zinc-900 flex items-center justify-center shrink-0">
                        <i data-lucide="shield" class="w-5 h-5 text-amber-700"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-zinc-950 mb-0.5">
                            Standard Operating Policy Notice
                        </h4>
                        <p class="text-xs text-zinc-600 leading-relaxed">
                            This document serves as binding policy for transactions executed on the X-Buy platform. All monetary disbursements and dispute resolutions adhere to the provisions set forth herein.
                        </p>
                    </div>
                </div>

                <!-- Rich Article Content -->
                <article class="prose prose-zinc max-w-none 
                    prose-headings:text-zinc-950 prose-headings:font-black prose-headings:tracking-tight
                    prose-h1:text-2xl prose-h1:mb-6
                    prose-h2:text-xl prose-h2:mt-8 prose-h2:mb-4 prose-h2:pb-2 prose-h2:border-b prose-h2:border-zinc-100
                    prose-h3:text-base prose-h3:mt-6 prose-h3:mb-3
                    prose-p:text-zinc-700 prose-p:text-sm prose-p:leading-relaxed prose-p:mb-5
                    prose-li:text-zinc-700 prose-li:text-sm prose-li:my-1.5
                    prose-strong:text-zinc-950 prose-strong:font-bold
                    prose-ol:list-decimal prose-ul:list-disc">
                    {!! $page->content !!}
                </article>

                <!-- Feedback Section -->
                <div class="mt-12 pt-8 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs font-bold text-zinc-700">
                        <span x-show="!feedbackSubmitted">Was this policy information helpful?</span>
                        <span x-show="feedbackSubmitted" class="text-emerald-600 font-bold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Thank you for your feedback!
                        </span>
                    </div>
                    <div class="flex items-center gap-2" x-show="!feedbackSubmitted">
                        <button 
                            type="button"
                            @click="feedbackSubmitted = true"
                            class="px-3.5 py-1.5 rounded-lg border border-zinc-200 text-xs font-bold text-zinc-700 hover:bg-zinc-100 transition-colors"
                        >
                            👍 Yes, clear
                        </button>
                        <button 
                            type="button"
                            @click="feedbackSubmitted = true"
                            class="px-3.5 py-1.5 rounded-lg border border-zinc-200 text-xs font-bold text-zinc-700 hover:bg-zinc-100 transition-colors"
                        >
                            👎 Need more info
                        </button>
                    </div>
                </div>

            </div>

            <!-- Bottom Support CTA Banner -->
            <div class="rounded-3xl bg-zinc-950 text-white p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-md">
                <div class="space-y-1 text-center sm:text-left">
                    <h3 class="text-lg font-black tracking-tight">Need assistance with an active Escrow transaction?</h3>
                    <p class="text-xs text-zinc-400 max-w-xl leading-relaxed">
                        Our specialized mediation officers are ready to help with order tracking, hardware testing extensions, or dispute arbitration.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="/p/contact-us" class="px-5 py-2.5 rounded-full bg-[#FDD835] hover:bg-[#FBC02D] text-zinc-950 font-black text-xs shadow-xs transition-transform active:scale-95">
                        Open Support Ticket
                    </a>
                </div>
            </div>

        </main>

    </div>
</div>
@endsection
