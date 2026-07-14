@extends('layouts.app')

@section('title', 'Welcome to ' . \App\Models\SiteSetting::getVal('platform_name', 'X-Buy'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        
        <!-- Section 1: Flash Sale Event Banner (Mercari Style) -->
        <div class="relative bg-sky-300 rounded-[2rem] overflow-hidden shadow-xs border border-sky-400/20 p-6 md:p-10 flex flex-col lg:flex-row items-center justify-between gap-8 min-h-[320px]">
            <!-- Dot pattern background overlay -->
            <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(#0369a1 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
            
            <!-- Polaroid images arrangement (Left side) -->
            <div class="relative flex items-center justify-center w-full lg:w-1/3 min-h-[220px] select-none order-2 lg:order-1">
                <!-- Polaroid 1 (Gaming mouse/keyboard or GPU) -->
                <div class="absolute left-4 rotate-[-8deg] bg-white p-3 pb-6 rounded-lg shadow-md border border-zinc-200/40 w-44 hover:rotate-0 hover:scale-105 hover:z-20 transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=300" alt="GPU" class="w-full aspect-square object-cover rounded-sm border border-zinc-100">
                    <span class="block text-center font-bold text-zinc-700 text-xs mt-3 select-none">RTX 4070 Ti</span>
                </div>
                
                <!-- Polaroid 2 (Custom PC Rig) -->
                <div class="absolute right-4 rotate-[6deg] bg-white p-3 pb-6 rounded-lg shadow-md border border-zinc-200/40 w-44 hover:rotate-0 hover:scale-105 hover:z-20 transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&q=80&w=300" alt="PC Setup" class="w-full aspect-square object-cover rounded-sm border border-zinc-100">
                    <span class="block text-center font-bold text-zinc-700 text-xs mt-3 select-none">Custom Rigs</span>
                </div>
            </div>

            <!-- Banner content (Center-Right) -->
            <div class="relative z-10 flex-1 flex flex-col items-center text-center space-y-4 max-w-2xl order-1 lg:order-2">
                <div class="flex items-center space-x-3">
                    <span class="bg-[#1e3a8a] text-white text-[10px] font-extrabold px-3 py-1 rounded-md uppercase tracking-wider select-none">live now</span>
                    <span class="bg-yellow-400 text-blue-900 text-[10px] font-extrabold px-3 py-1 rounded-md uppercase tracking-wider select-none">July 13-15</span>
                </div>
                
                <div>
                    <!-- Outline text effect via css -->
                    <h1 class="text-4xl md:text-5xl font-black tracking-tight text-blue-900 leading-none select-none" style="text-shadow: 2px 2px 0px #fff, -2px -2px 0px #fff, 2px -2px 0px #fff, -2px 2px 0px #fff;">
                        FLASH SALE EVENT
                    </h1>
                    <p class="text-blue-950 font-bold text-sm md:text-base mt-2 select-none">
                        *Coupon for <span class="underline">10% off</span> item price(s)*
                    </p>
                </div>
                
                <div class="bg-yellow-300 text-blue-950 px-6 py-2 rounded-2xl border border-yellow-400 font-extrabold text-sm shadow-xs select-none">
                    Use Code: <span class="font-mono text-base font-black">JULYFLASH10</span>
                </div>
            </div>

            <!-- Learn more CTA Button (Right side) -->
            <div class="relative z-10 w-full lg:w-auto flex justify-center lg:justify-end order-3">
                <a href="/listings" class="bg-blue-800 hover:bg-blue-900 text-white font-bold px-8 py-3.5 rounded-full shadow-md flex items-center justify-center space-x-2 transition-all hover:scale-103">
                    <span>Learn more</span>
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </a>
            </div>
            
            <!-- Green leaf decoration (Right corner) -->
            <div class="absolute right-0 bottom-0 pointer-events-none select-none opacity-20 lg:opacity-40">
                <svg width="150" height="150" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 100C70 100 30 70 30 30C30 50 50 90 100 100Z" fill="#15803d"></path>
                    <path d="M100 100C85 80 60 40 40 10C50 30 70 80 100 100Z" fill="#16a34a"></path>
                </svg>
            </div>
        </div>

        <!-- Section 2: Three Category Highlight Cards (Mercari Style) -->
        <div class="flex flex-wrap lg:flex-nowrap justify-center lg:justify-start gap-6">
            
            <!-- Card 1: Graphics Cards (GPUs) -->
            <div class="w-full max-w-[312px] h-[198px] bg-blue-50/70 border border-blue-100 rounded-2xl p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-xs transition-shadow shrink-0">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] text-blue-600 font-semibold uppercase tracking-wider block">Gaming & AI GPUs</span>
                        <h3 class="text-xl font-bold text-zinc-900 mt-0.5 select-none">Graphics Cards</h3>
                    </div>
                    <a href="/listings?category=gpu" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-0.5">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
                <!-- Polaroid styled float component image -->
                <div class="w-28 bg-white p-1.5 pb-3 rounded-lg border border-zinc-200/50 shadow-xs absolute -bottom-3 right-3 rotate-[12deg] group-hover:rotate-[6deg] group-hover:-translate-y-1.5 transition-all select-none">
                    <img src="https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=200" alt="GPU" class="w-full aspect-square object-cover rounded-md border border-zinc-100">
                </div>
            </div>

            <!-- Card 2: Processors (CPUs) -->
            <div class="w-full max-w-[312px] h-[198px] bg-purple-50/70 border border-purple-100 rounded-2xl p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-xs transition-shadow shrink-0">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] text-purple-600 font-semibold uppercase tracking-wider block">Core speed & Power</span>
                        <h3 class="text-xl font-bold text-zinc-900 mt-0.5 select-none">Processors</h3>
                    </div>
                    <a href="/listings?category=cpu" class="text-[11px] font-bold text-purple-600 hover:text-purple-700 flex items-center gap-0.5">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
                <!-- Polaroid styled float component image -->
                <div class="w-28 bg-white p-1.5 pb-3 rounded-lg border border-zinc-200/50 shadow-xs absolute -bottom-3 right-3 rotate-[-8deg] group-hover:rotate-[-4deg] group-hover:-translate-y-1.5 transition-all select-none">
                    <img src="https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&q=80&w=200" alt="CPU" class="w-full aspect-square object-cover rounded-md border border-zinc-100">
                </div>
            </div>

            <!-- Card 3: Essential Components (Motherboard & RAM) -->
            <div class="w-full max-w-[312px] h-[198px] bg-amber-50/70 border border-amber-100 rounded-2xl p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-xs transition-shadow shrink-0">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] text-amber-700 font-semibold uppercase tracking-wider block">Bridges & Fast Memory</span>
                        <h3 class="text-xl font-bold text-zinc-900 mt-0.5 select-none">Gear & Kits</h3>
                    </div>
                    <a href="/listings?category=motherboard" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 flex items-center gap-0.5">
                        <span>See more</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
                <!-- Polaroid styled float component image -->
                <div class="w-28 bg-white p-1.5 pb-3 rounded-lg border border-zinc-200/50 shadow-xs absolute -bottom-3 right-3 rotate-[15deg] group-hover:rotate-[8deg] group-hover:-translate-y-1.5 transition-all select-none">
                    <img src="https://images.unsplash.com/photo-1562976540-1502c2145186?auto=format&fit=crop&q=80&w=200" alt="RAM" class="w-full aspect-square object-cover rounded-md border border-zinc-100">
                </div>
            </div>
            
        </div>

        <!-- Section 3: Interactive Collection Explorer Component -->
        <div class="space-y-6" x-data="collectionExplorer()" x-init="init()">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Explore PC Components</h2>
                    <p class="text-zinc-500 mt-1">Browse parts by category and connect with verified sellers safely.</p>
                </div>
            </div>

            <!-- Parent Categories Horizontal Bar -->
            <div class="relative border-b border-zinc-200 pb-2">
                <div class="flex items-center overflow-x-auto space-x-8 scrollbar-none pb-2">
                    <template x-for="cat in categories" :key="cat.id">
                        <button 
                            @click="selectParent(cat)"
                            class="flex flex-col items-center space-y-2 text-zinc-500 hover:text-zinc-950 transition-all focus:outline-none min-w-[80px] cursor-pointer pb-2 relative group"
                            :class="selectedParent && selectedParent.id === cat.id ? 'text-zinc-950 font-semibold' : ''"
                        >
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all bg-zinc-50 border border-zinc-200 group-hover:bg-zinc-100"
                                 :class="selectedParent && selectedParent.id === cat.id ? 'bg-yellow-100 border-yellow-300 text-yellow-800' : 'text-zinc-600'">
                                <i :data-lucide="cat.icon || 'tag'" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs tracking-wide whitespace-nowrap" x-text="cat.name"></span>
                            
                            <!-- Active Indicator Line -->
                            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-yellow-500 rounded-full transition-all"
                                 x-show="selectedParent && selectedParent.id === cat.id"></div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Subcategories and Listings Grid -->
            <div class="flex flex-col md:flex-row gap-8 items-start">
                
                <!-- Left Subcategories List -->
                <div class="w-full md:w-64 shrink-0 bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
                    <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider px-3 mb-3">Subcategories</h3>
                    <div class="space-y-1">
                        <template x-for="sub in (selectedParent ? selectedParent.children : [])" :key="sub.id">
                            <button 
                                @click="selectSubcategory(sub)"
                                class="w-full text-left py-2 px-3 rounded-xl text-sm transition-all hover:bg-zinc-50 flex items-center justify-between group cursor-pointer"
                                :class="selectedSubcategory && selectedSubcategory.id === sub.id ? 'bg-zinc-100 font-semibold text-zinc-950' : 'text-zinc-600'"
                            >
                                <span x-text="sub.name"></span>
                                <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 group-hover:text-zinc-950 transition-all"
                                   :class="selectedSubcategory && selectedSubcategory.id === sub.id ? 'text-zinc-950 translate-x-0.5' : ''"></i>
                            </button>
                        </template>
                        
                        <!-- View All Subcategories Option -->
                        <button 
                            @click="viewAllListings()"
                            class="w-full text-left py-2 px-3 rounded-xl text-sm transition-all hover:bg-zinc-50 flex items-center justify-between group cursor-pointer text-yellow-600 font-semibold"
                            :class="selectedSubcategory === null ? 'bg-zinc-100' : ''"
                        >
                            <span>Browse All</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-yellow-600 group-hover:translate-x-0.5 transition-all"></i>
                        </button>
                    </div>
                </div>

                <!-- Main Listings Content -->
                <div class="flex-1 w-full min-h-[400px] relative">
                    <!-- Loading Spinner -->
                    <div x-show="loadingListings" class="absolute inset-0 bg-white/70 backdrop-blur-xs flex items-center justify-center z-10 rounded-2xl">
                        <div class="flex flex-col items-center space-y-3">
                            <div class="w-8 h-8 border-4 border-yellow-400 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-sm text-zinc-500 font-medium">Fetching components...</span>
                        </div>
                    </div>

                    <!-- Listings Grid -->
                    <div x-show="!loadingListings && listings.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="item in listings" :key="item.id">
                            <div class="bg-white border border-zinc-200 rounded-2xl overflow-hidden hover:shadow-md hover:border-zinc-300 transition-all flex flex-col group relative">
                                <!-- Image Container -->
                                <div class="aspect-video bg-zinc-50 border-b border-zinc-150 overflow-hidden relative">
                                    <img :src="item.images && item.images.length > 0 ? (item.images[0].image_url || '/storage/' + item.images[0].image_path) : 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&q=80&w=400'" 
                                         :alt="item.title"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                         
                                    <!-- Grade Badge -->
                                    <div class="absolute top-3 left-3">
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg border uppercase tracking-wider"
                                              :class="item.grade === 'A' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                                                       (item.grade === 'B' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200')"
                                              x-text="'Grade ' + item.grade"></span>
                                    </div>
                                </div>

                                <!-- Card Content -->
                                <div class="p-4 flex-1 flex flex-col space-y-3">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400" x-text="item.brand && item.brand.name ? item.brand.name : 'Component'"></span>
                                            <span class="text-[10px] text-zinc-400 flex items-center gap-1">
                                                <i data-lucide="eye" class="w-3 h-3"></i>
                                                <span x-text="item.views_count || 0"></span>
                                            </span>
                                        </div>
                                        <h4 class="font-bold text-zinc-900 text-sm group-hover:text-yellow-600 transition-colors line-clamp-1 mt-1" x-text="item.title"></h4>
                                    </div>

                                    <div class="flex items-baseline justify-between pt-1">
                                        <div>
                                            <span class="text-xs text-zinc-400">Price</span>
                                            <div class="text-base font-extrabold text-zinc-900" x-text="'₹' + Number(item.price).toLocaleString('en-IN')"></div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-zinc-400 block">Seller</span>
                                            <span class="text-xs font-semibold text-zinc-700" x-text="item.seller && item.seller.shop_name ? item.seller.shop_name : 'Verified Seller'"></span>
                                        </div>
                                    </div>

                                    <a :href="'/listings/' + item.slug" 
                                       class="w-full py-2 bg-zinc-50 hover:bg-[#fdd835] hover:text-black text-zinc-700 text-xs font-bold rounded-xl transition-all border border-zinc-200 text-center block mt-3">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loadingListings && listings.length === 0" class="flex flex-col items-center justify-center bg-white border border-zinc-200 rounded-2xl p-12 text-center space-y-4">
                        <div class="w-16 h-16 bg-yellow-50 text-yellow-600 rounded-2xl flex items-center justify-center">
                            <i data-lucide="alert-circle" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-800">No Listings Yet</h3>
                            <p class="text-sm text-zinc-500 mt-1 max-w-sm">No components are currently listed under this category. Be the first to list yours or check back later!</p>
                        </div>
                        <a href="/dashboard/listings/create" class="py-2 px-6 bg-black hover:bg-zinc-950 text-white font-semibold rounded-xl text-xs transition-all shadow-md">
                            Sell a Component
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function collectionExplorer() {
            return {
                categories: [],
                selectedParent: null,
                selectedSubcategory: null,
                listings: [],
                loadingCategories: false,
                loadingListings: false,
                error: null,

                init() {
                    this.loadingCategories = true;
                    fetch('/api/v1/categories')
                        .then(res => res.json())
                        .then(res => {
                            if (res.success && res.data.length > 0) {
                                this.categories = res.data;
                                // Select first category
                                this.selectParent(this.categories[0]);
                            }
                        })
                        .catch(err => {
                            console.error('Failed to load categories', err);
                            this.error = 'Failed to load categories';
                        })
                        .finally(() => {
                            this.loadingCategories = false;
                        });
                },

                selectParent(parent) {
                    this.selectedParent = parent;
                    // Select first subcategory if available, else null (which triggers View All)
                    if (parent.children && parent.children.length > 0) {
                        this.selectSubcategory(parent.children[0]);
                    } else {
                        this.viewAllListings();
                    }
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },

                selectSubcategory(sub) {
                    this.selectedSubcategory = sub;
                    this.fetchListings(sub.slug);
                },

                viewAllListings() {
                    this.selectedSubcategory = null;
                    this.fetchListings(this.selectedParent.slug);
                },

                fetchListings(slug) {
                    this.loadingListings = true;
                    fetch(`/api/v1/categories/${slug}/listings`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                // Check if paginated or flat data
                                this.listings = res.data.data || res.data;
                            } else {
                                this.listings = [];
                            }
                        })
                        .catch(err => {
                            console.error('Failed to fetch listings', err);
                            this.listings = [];
                        })
                        .finally(() => {
                            this.loadingListings = false;
                            this.$nextTick(() => {
                                if (window.lucide) {
                                    window.lucide.createIcons();
                                }
                            });
                        });
                }
            }
        }
    </script>
@endsection