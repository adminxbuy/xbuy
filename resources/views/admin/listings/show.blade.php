@extends('layouts.admin')

@section('title', 'Verify Listing')
@section('page_title', 'Listing Moderation Review')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.listings') }}"
            class="inline-flex items-center space-x-1.5 text-sm font-semibold text-zinc-500 hover:text-zinc-800">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Directory</span>
        </a>
        @if($listing->listing_number)
            <span
                class="inline-flex items-center gap-2 bg-blue-600 text-white text-xs font-bold font-mono px-3 py-1.5 rounded-lg shadow-sm">
                <i data-lucide="hash" class="w-3.5 h-3.5"></i>
                {{ $listing->listing_number }}
            </span>
        @endif
    </div>

    @if($listing->is_previously_sold)
        <div
            class="mb-6 p-4 bg-amber-50 border border-amber-200/60 text-amber-800 rounded-xl flex items-center space-x-3 shadow-sm">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0"></i>
            <div>
                <h4 class="font-bold text-sm">Previously sold serial number</h4>
                <p class="text-xs text-amber-700 mt-0.5">This serial number has been previously sold on X-Buy. Please verify
                    authenticity during moderation.</p>
            </div>
        </div>
    @endif


    <!-- Main Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Listing Details and Carousel -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Image Carousel / Viewer -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-4">Product Evidence Images</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($listing->images as $image)
                        <div
                            class="relative group aspect-video rounded-lg overflow-hidden border border-zinc-200 bg-zinc-50 shadow-sm">
                            <img src="{{ $image->image_url }}"
                                class="w-full h-full object-cover transition-all duration-300 group-hover:scale-105"
                                alt="product-image">
                            @if($image->is_primary)
                                <span
                                    class="absolute top-2 left-2 bg-zinc-900 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm border border-black/10">PRIMARY
                                    IMAGE</span>
                            @endif
                        </div>
                    @empty
                        <div class="sm:col-span-2 py-12 text-center text-zinc-400 text-sm flex flex-col items-center">
                            <i data-lucide="image-off" class="w-8 h-8 mb-2"></i>
                            <span>No images uploaded for this listing.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-6 pb-4 border-b border-zinc-100">Product Specifications</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    @if($listing->listing_number)
                        <div class="sm:col-span-2">
                            <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Listing ID</p>
                            <p class="mt-1">
                                <span
                                    class="inline-flex items-center gap-1.5 font-bold font-mono text-zinc-800 bg-zinc-100 border border-zinc-205 px-3 py-1.5 rounded-lg text-xs">
                                    {{ $listing->listing_number }}
                                </span>
                            </p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Product Title</p>
                        <p class="text-zinc-800 mt-1 font-semibold text-base">{{ $listing->title }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Category</p>
                        <p class="text-zinc-800 mt-1 font-semibold capitalize">{{ $listing->category }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Brand / Manufacturer</p>
                        <p class="text-zinc-800 mt-1 font-medium">{{ $listing->brand ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Model Name / Number</p>
                        <p class="text-zinc-800 mt-1 font-medium">{{ $listing->model_name ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Serial Number (Critical for
                            Escrow)</p>
                        <p
                            class="text-zinc-800 mt-1 font-mono font-bold bg-zinc-50 border border-zinc-150 px-3 py-1.5 rounded-lg inline-block select-all text-xs">
                            {{ $listing->serial_number }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Cosmetic Grade Condition</p>
                        <p class="text-zinc-800 mt-1 font-bold">Grade {{ $listing->grade }}</p>
                    </div>

                    <div class="sm:col-span-2 border-t border-zinc-100 pt-6">
                        <p class="text-xs text-zinc-400 font-semibold uppercase tracking-wider">Seller Condition Notes &
                            Description</p>
                        <div
                            class="text-zinc-650 mt-2 p-4 bg-zinc-50 border border-zinc-150 rounded-xl whitespace-pre-wrap leading-relaxed text-xs">
                            {{ $listing->description }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Sidebar Admin Panel actions -->
        <div class="space-y-6">

            <!-- Verification Card -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-4">Moderation Board</h3>

                <div class="mb-6">
                    <span class="inline-flex items-center px-2.5 py-1 text-xs rounded-full font-semibold border
                        @if($listing->listing_status === 'pending_approval') bg-amber-50 text-amber-705 border-amber-200/60
                        @elseif($listing->listing_status === 'active') bg-emerald-50 text-emerald-705 border-emerald-200/60
                        @elseif($listing->listing_status === 'sold') bg-zinc-100 text-zinc-850 border-zinc-200
                        @else bg-rose-50 text-rose-705 border-rose-200/60 @endif">
                        Status: {{ ucfirst(str_replace('_', ' ', $listing->listing_status)) }}
                    </span>

                    @if($listing->rejection_reason)
                        <p class="text-xs text-rose-600 font-semibold mt-3 p-3 bg-rose-50 border border-rose-100 rounded-xl">
                            Rejection details: "{{ $listing->rejection_reason }}"
                        </p>
                    @endif
                </div>

                @if($listing->listing_status === 'pending_approval')
                    <div class="space-y-3 pt-4 border-t border-zinc-100" x-data="{ rejecting: false }">
                        <form action="{{ route('admin.listings.approve', $listing->id) }}" method="POST" x-show="!rejecting">
                            @csrf
                            <input type="hidden" name="status" value="active">
                            <button type="submit"
                                class="w-full bg-zinc-900 hover:bg-zinc-800 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center space-x-2 text-xs">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Approve Listing</span>
                            </button>
                        </form>

                        <button @click="rejecting = true" x-show="!rejecting"
                            class="w-full bg-zinc-100 hover:bg-rose-50 text-zinc-700 hover:text-rose-700 font-semibold py-2.5 px-4 rounded-lg transition-all border border-zinc-200/50 hover:border-rose-200/60 flex items-center justify-center space-x-2 text-xs">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Reject Listing</span>
                        </button>

                        <form action="{{ route('admin.listings.approve', $listing->id) }}" method="POST" x-show="rejecting"
                            class="space-y-4 bg-zinc-50 p-4 border border-zinc-200/80 rounded-lg">
                            @csrf
                            <input type="hidden" name="status" value="rejected">

                            <div>
                                <label for="reason" class="block text-[10px] font-semibold text-zinc-500 mb-1.5 uppercase tracking-wide">Reason for Rejection</label>
                                <textarea id="reason" name="reason" rows="3" required
                                    placeholder="State reason, e.g., missing serial photos, bad price..."
                                    class="w-full p-2.5 border border-zinc-200 rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white"></textarea>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" @click="rejecting = false"
                                    class="flex-1 bg-white hover:bg-zinc-100 text-zinc-700 text-xs font-semibold py-2 px-3 border border-zinc-200 rounded-lg transition-all">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="flex-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold py-2 px-3 rounded-lg shadow-sm transition-all">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-3 bg-zinc-50 border border-zinc-200/60 rounded-lg text-center text-xs text-zinc-500">
                        Decision processed on {{ $listing->updated_at->format('M d, Y H:i') }}.
                    </div>
                @endif
            </div>

            <!-- Pricing Card -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-4">Pricing Breakdown</h3>

                <div class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                        <span class="text-zinc-500">Listing Price</span>
                        <span class="font-bold text-zinc-900 text-sm">₹{{ number_format($listing->price, 2) }}</span>
                    </div>
                    @if($listing->original_price)
                        <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                            <span class="text-zinc-500">Original Price</span>
                            <span class="text-zinc-550 line-through">₹{{ number_format($listing->original_price, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                        <span class="text-zinc-500">Shipping Type</span>
                        <span class="font-medium text-zinc-800 uppercase text-[10px]">{{ $listing->shipping_type }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-zinc-500">Shipping Cost</span>
                        <span class="font-medium text-zinc-800">₹{{ number_format($listing->shipping_charges, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Seller profile brief -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-4">Associated Seller</h3>
                <div class="flex items-center space-x-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($listing->seller->user->name) }}&background=e4e4e7&color=71717a"
                        class="w-8 h-8 rounded-lg ring-1 ring-zinc-950/5" alt="avatar">
                    <div>
                        <h4 class="font-bold text-zinc-800 text-xs">{{ $listing->seller->shop_name }}</h4>
                        <p class="text-[10px] text-zinc-400">{{ $listing->seller->user->email }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.sellers.show', $listing->seller_id) }}"
                    class="w-full bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold py-2 px-3 rounded-lg text-center text-xs transition-colors block border border-zinc-200/50">
                    View Seller Profile
                </a>
            </div>

        </div>

    </div></div>
@endsection