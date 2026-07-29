@php
 $navCategories = \App\Models\Category::active()->parentOnly()->with('children')->orderBy('sort_order')->take(10)->get();
@endphp

<div class="relative bg-white border-b border-zinc-200/50"
 @mouseleave="clearTimeout(closeCategoriesTimer); closeCategoriesTimer = setTimeout(() => { categoriesOpen = false; }, 200)">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="flex items-center justify-between h-12 w-full">
 @foreach($navCategories as $pCat)
 <div class="relative py-3.5">
 <button @mouseenter="clearTimeout(closeCategoriesTimer); categoriesOpen = true; activeParent = {{ $pCat->id }};"
 @click="window.location.href='/listings?category={{ $pCat->slug }}'"
 class="text-sm font-semibold text-zinc-700 hover:text-black transition-colors focus:outline-none cursor-pointer whitespace-nowrap"
 :class="activeParent === {{ $pCat->id }} && categoriesOpen ? 'text-black border-b-2 border-[#e6c019]' : ''">
 {{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}
 </button>
 </div>
 @endforeach
 </div>
 </div>

 <!-- Mega Menu: Subcategories Dropdown Pane -->
 <div x-show="categoriesOpen"
 x-transition:enter="transition ease-out duration-150"
 x-transition:enter-start="opacity-0 -translate-y-1"
 x-transition:enter-end="opacity-100 translate-y-0"
 x-transition:leave="transition ease-in duration-100"
 x-transition:leave-start="opacity-100 translate-y-0"
 x-transition:leave-end="opacity-0 -translate-y-1"
 class="absolute left-0 w-full bg-white border-b border-zinc-200/80 z-[100]"
 style="display: none;"
 @mouseenter="clearTimeout(closeCategoriesTimer);"
 @mouseleave="clearTimeout(closeCategoriesTimer); closeCategoriesTimer = setTimeout(() => { categoriesOpen = false; }, 200);">
  <div class="max-w-7xl mx-auto px-8 py-5">
 @foreach($navCategories as $pCat)
 <div x-show="activeParent === {{ $pCat->id }}" style="display: none;">
 <!-- Header row -->
 <div class="flex items-center justify-between border-b border-zinc-100 pb-3 mb-4">
 <div class="flex items-center gap-2">
 <h3 class="text-sm font-bold text-zinc-900">{{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}</h3>
 @if($pCat->children->count() > 0)
 <span class="text-[11px] bg-zinc-100 border border-zinc-200 text-zinc-500 font-semibold px-2 py-0.5 rounded-full">
 {{ $pCat->children->count() }} subcategories
 </span>
 @endif
 </div>
 <a href="/listings?category={{ $pCat->slug }}"
 class="text-xs font-semibold text-[#e6c019] hover:text-[#c9a800] flex items-center gap-1 group">
 <span>Browse All {{ preg_replace('/\s*\(.*?\)/', '', $pCat->name) }}</span>
 <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
 </svg>
 </a>
 </div>
  @if($pCat->children->count() > 0)
 <div class="grid grid-cols-8 gap-3">
 @foreach($pCat->children as $cCat)
 <a href="/listings?category={{ $cCat->slug }}"
 class="flex flex-col items-center gap-2 p-2.5 rounded-xl border border-zinc-100 bg-white text-center group cursor-pointer"
 style="text-decoration: none;">
 <!-- Image / Icon box -->
 <div class="w-14 h-14 rounded-xl overflow-hidden flex items-center justify-center bg-zinc-50 border border-zinc-100 group-hover:border-[#e6c019] group-hover:bg-amber-50/30 transition-colors">
 @if($cCat->image)
 <img src="{{ $cCat->image }}" alt="{{ $cCat->name }}" class="w-full h-full object-cover">
 @else
 <svg class="w-6 h-6 text-zinc-350 group-hover:text-[#c9a800] transition-colors" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
 <rect x="3" y="3" width="7" height="9" rx="1" />
 <rect x="14" y="3" width="7" height="5" rx="1" />
 <rect x="14" y="12" width="7" height="9" rx="1" />
 <rect x="3" y="16" width="7" height="5" rx="1" />
 </svg>
 @endif
 </div>
 <span class="text-[11px] font-semibold text-zinc-600 group-hover:text-zinc-900 leading-tight line-clamp-2 px-0.5 transition-colors">{{ $cCat->name }}</span>
 </a>
 @endforeach
 </div>
 @else
 <div class="py-8 text-center text-zinc-400 text-sm flex flex-col items-center gap-2">
 <svg class="w-8 h-8 text-zinc-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"></path>
 </svg>
 <span>No subcategories available yet.</span>
 </div>
 @endif
 </div>
 @endforeach
 </div>
 </div>
</div>