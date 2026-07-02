@extends('layouts.app')

@section('title', $user->name . '\'s Profile')

@section('content')
<div class="relative bg-zinc-50 z-10 min-h-[calc(100vh-120px)] py-8">
    <div class="max-w-7xl mx-auto px-5" x-data="{ activeTab: 'listings' }">
        <!-- Profile Header Card -->
        <div class="flex flex-col md:flex-row items-start justify-between gap-6 pb-8 border-b border-zinc-200">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 w-full md:w-auto">
            <!-- Avatar -->
            <div class="relative w-28 h-28 md:w-32 md:h-32 rounded-full overflow-hidden border border-zinc-200 shrink-0">
                <img src="{{ $user->display_avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
            </div>

            <!-- Profile Main Info -->
            <div class="text-center md:text-left space-y-3.5">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $user->name }}</h2>
                    <!-- Rating Stars -->
                    <div class="flex items-center justify-center md:justify-start gap-1 mt-1 text-sm text-zinc-500">
                        <div class="flex gap-0.5">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 fill-current {{ $i < $rating ? 'text-[#e6c019]' : 'text-zinc-200' }}" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="ml-1">{{ $reviewsCount }} reviews</span>
                    </div>
                </div>

                <!-- About and Verification Grid -->
                <div class="flex flex-col sm:flex-row gap-6 md:gap-10 text-sm text-zinc-650">
                    <!-- About column -->
                    <div class="space-y-2">
                        <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">About:</p>
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-zinc-400"></i>
                            <span>{{ $location }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-zinc-400"></i>
                            <span>Last seen recently</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-zinc-400"></i>
                            <span>0 followers, 0 following</span>
                        </div>
                    </div>

                    <!-- Verified Column -->
                    <div class="space-y-2">
                        <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Verified info:</p>
                        <div class="flex items-center gap-2">
                            <div class="w-4.5 h-4.5 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </div>
                            <span>Google</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4.5 h-4.5 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </div>
                            <span>Email</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share and Edit Profile Buttons -->
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto mt-4 md:mt-0" x-data="{ copied: false }">
            <button type="button" 
                    @click="navigator.clipboard.writeText('{{ route('member.profile', $user->profile_id) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-[#e6c019] text-black rounded-lg text-sm font-bold hover:bg-[#fdd835] transition-colors cursor-pointer border border-[#e6c019]">
                <i data-lucide="share-2" x-show="!copied" class="w-4 h-4"></i>
                <i data-lucide="check" x-show="copied" class="w-4 h-4"></i>
                <span x-text="copied ? 'Copied!' : 'Share profile'"></span>
            </button>
            @if($isOwner)
                <a href="/dashboard/settings" class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 border border-zinc-200 bg-white rounded-lg text-sm text-zinc-700 font-medium hover:bg-zinc-50 transition-colors">
                    <i data-lucide="pencil" class="w-4 h-4 text-zinc-500"></i>
                    <span>Edit profile</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-zinc-200 mt-6">
        <div class="flex gap-6">
            <button @click="activeTab = 'listings'" 
                    class="pb-3 border-b-2 text-sm focus:outline-none cursor-pointer"
                    :class="activeTab === 'listings' ? 'border-[#e6c019] text-zinc-900 font-bold' : 'border-transparent text-zinc-400 font-medium'">
                Listings
            </button>
            <button @click="activeTab = 'reviews'" 
                    class="pb-3 border-b-2 text-sm focus:outline-none cursor-pointer"
                    :class="activeTab === 'reviews' ? 'border-[#e6c019] text-zinc-900 font-bold' : 'border-transparent text-zinc-400 font-medium'">
                Reviews
            </button>
        </div>
    </div>

    <!-- Listings Tab Content -->
    <div x-show="activeTab === 'listings'">
        @if($isOwner)
            <!-- Streak/Shipping Challenges Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <!-- Streak Card -->
                <div class="flex items-center justify-between p-4 bg-white border border-zinc-200 rounded-xl gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#fdd835]/15 flex items-center justify-center text-[#e6c019] shrink-0">
                            <i data-lucide="flame" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-zinc-900">Start a new listing streak!</h4>
                            <p class="text-xs text-zinc-500 mt-0.5">List 5 items in 30 days to earn your Frequent Uploads badge.</p>
                        </div>
                    </div>
                    <button class="text-zinc-400 cursor-pointer" aria-label="More Info">
                        <i data-lucide="info" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Shipping Challenge Card -->
                <div class="flex items-center justify-between p-4 bg-white border border-zinc-200 rounded-xl gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#fdd835]/15 flex items-center justify-center text-[#e6c019] shrink-0">
                            <i data-lucide="truck" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-zinc-900">Up for the Speedy Shipping challenge?</h4>
                            <p class="text-xs text-zinc-500 mt-0.5">Aim to send items within the 24 hours for buyers to see this badge.</p>
                        </div>
                    </div>
                    <button class="text-zinc-400 cursor-pointer" aria-label="More Info">
                        <i data-lucide="info" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        @endif

        <!-- Empty State Listings Section -->
        <div class="flex flex-col items-center justify-center text-center py-20 px-4 mt-8">
            <div class="w-24 h-24 text-zinc-300 mb-6 flex items-center justify-center">
                <div class="relative w-full h-full flex items-center justify-center">
                    <i data-lucide="package" class="w-16 h-16 absolute -top-2 text-zinc-300"></i>
                    <i data-lucide="cpu" class="w-10 h-10 absolute -bottom-1 -left-1 text-zinc-300"></i>
                    <i data-lucide="tag" class="w-8 h-8 absolute bottom-1 right-1 text-[#e6c019]/40"></i>
                </div>
            </div>
            @if($isOwner)
                <h3 class="text-lg font-bold text-zinc-900">List items to start selling</h3>
                <p class="text-sm text-zinc-500 max-w-sm mt-1.5">Declutter your life. Sell what you no longer need!</p>
                <a href="/dashboard/listings/create" 
                   class="mt-6 inline-flex items-center justify-center px-8 py-3 rounded-xl text-sm font-bold text-black bg-[#fdd835] border border-black/10">
                    List now
                </a>
            @else
                <h3 class="text-lg font-bold text-zinc-900">No listings yet</h3>
                <p class="text-sm text-zinc-500 max-w-sm mt-1.5">This user hasn't listed any items for sale yet.</p>
            @endif
        </div>
    </div>

    <!-- Reviews Tab Content -->
    <div x-show="activeTab === 'reviews'" style="display: none;">
        <!-- Empty State Reviews Section -->
        <div class="flex flex-col items-center justify-center text-center py-24 px-4 mt-8">
            <div class="w-20 h-20 text-[#e6c019] mb-4 flex items-center justify-center">
                <svg class="w-16 h-16 stroke-[1.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-zinc-900">No reviews yet</h3>
            <p class="text-sm text-zinc-500 mt-1.5 max-w-md">Collecting reviews takes time, so check back soon</p>
        </div>
    </div>
</div>
</div>
@endsection
