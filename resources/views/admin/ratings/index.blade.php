@extends('layouts.admin')

@section('title', 'Ratings Management')
@section('page_title', 'Supplier Ratings & Reviews')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'ratings') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200/50 text-xs font-bold transition-all border border-red-200 shadow-sm">
        <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
        <span>Trash ({{ $trashedRatings->count() }})</span>
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <!-- Average Star Rating -->
            <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block mb-1">Average Star
                        Rating</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-zinc-900">{{ $stats['average_rating'] }}</span>
                        <span class="text-sm font-semibold text-zinc-400">/ 5.00</span>
                    </div>
                    <div class="flex items-center gap-1 mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($stats['average_rating']))
                                <i data-lucide="star" class="w-4 h-4 fill-yellow-400 text-yellow-400"></i>
                            @else
                                <i data-lucide="star" class="w-4 h-4 text-zinc-300"></i>
                            @endif
                        @endfor
                    </div>
                </div>
                <div class="p-3 bg-zinc-50 text-zinc-650 rounded-xl">
                    <i data-lucide="star" class="w-6 h-6"></i>
                </div>
            </div>

            <!-- Total Reviews Count -->
            <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block mb-1">Total Ratings</span>
                    <span class="text-3xl font-bold text-zinc-900">{{ $stats['total_count'] }}</span>
                    <span class="text-xs text-zinc-500 block mt-2">Aggregated across all suppliers</span>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <i data-lucide="message-square" class="w-6 h-6"></i>
                </div>
            </div>

            <!-- Star Breakdown Progress -->
            <div class="col-span-1 md:col-span-2 bg-white border border-zinc-200 rounded-xl p-5 shadow-sm">
                <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block mb-3">Rating Distribution</span>
                <div class="space-y-2 text-xs">
                    <!-- 5 Stars -->
                    <div class="flex items-center gap-2">
                        <span class="w-10 text-zinc-650 font-semibold">5 Star</span>
                        <div class="flex-1 h-2 bg-zinc-100 rounded-full overflow-hidden">
                            <div class="h-full bg-zinc-900"
                                style="width: {{ $stats['total_count'] > 0 ? ($stats['5_star'] / $stats['total_count']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="w-8 text-right text-zinc-500 font-medium">{{ $stats['5_star'] }}</span>
                    </div>
                    <!-- 4 Stars -->
                    <div class="flex items-center gap-2">
                        <span class="w-10 text-zinc-650 font-semibold">4 Star</span>
                        <div class="flex-1 h-2 bg-zinc-100 rounded-full overflow-hidden">
                            <div class="h-full bg-zinc-900/80"
                                style="width: {{ $stats['total_count'] > 0 ? ($stats['4_star'] / $stats['total_count']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="w-8 text-right text-zinc-500 font-medium">{{ $stats['4_star'] }}</span>
                    </div>
                    <!-- 3 Stars -->
                    <div class="flex items-center gap-2">
                        <span class="w-10 text-zinc-650 font-semibold">3 Star</span>
                        <div class="flex-1 h-2 bg-zinc-100 rounded-full overflow-hidden">
                            <div class="h-full bg-zinc-900/60"
                                style="width: {{ $stats['total_count'] > 0 ? ($stats['3_star'] / $stats['total_count']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span class="w-8 text-right text-zinc-500 font-medium">{{ $stats['3_star'] }}</span>
                    </div>
                    <!-- 2 & 1 Star -->
                    <div class="flex items-center gap-2">
                        <span class="w-10 text-zinc-650 font-semibold">2 &lt; Star</span>
                        <div class="flex-1 h-2 bg-zinc-100 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-400"
                                style="width: {{ $stats['total_count'] > 0 ? (($stats['2_star'] + $stats['1_star']) / $stats['total_count']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <span
                            class="w-8 text-right text-zinc-500 font-medium">{{ $stats['2_star'] + $stats['1_star'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Panel -->
        <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm">
            <form action="{{ route('admin.ratings') }}" method="GET" class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h4 class="font-bold text-zinc-900 text-sm">Rating Filters</h4>
                    <a href="{{ route('admin.ratings.create') }}"
                        class="px-4 py-2 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black rounded-xl text-xs font-bold flex items-center gap-2 transition-all shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Manual Rating</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Search</label>
                        <div class="relative">
                            <i data-lucide="search"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search reviews or orders..."
                                class="w-full pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Seller Profile -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Seller /
                            Supplier</label>
                        <div class="relative">
                            <select name="seller_id"
                                class="w-full p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all appearance-none cursor-pointer">
                                <option value="">All Sellers</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->shop_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Buyer User</label>
                        <div class="relative">
                            <select name="buyer_id"
                                class="w-full p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all appearance-none cursor-pointer">
                                <option value="">All Buyers</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->id }}" {{ request('buyer_id') == $buyer->id ? 'selected' : '' }}>
                                        {{ $buyer->name }} ({{ $buyer->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Type -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Rating
                            Type</label>
                        <div class="relative">
                            <select name="rating_type"
                                class="w-full p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all appearance-none cursor-pointer">
                                <option value="">All Types</option>
                                <option value="manual" {{ request('rating_type') === 'manual' ? 'selected' : '' }}>Manual
                                    (Admin/Override)</option>
                                <option value="auto" {{ request('rating_type') === 'auto' ? 'selected' : '' }}>Automatic
                                    (System default)</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Star Scores -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Weighted Total
                            Stars</label>
                        <div class="relative">
                            <select name="score"
                                class="w-full p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all appearance-none cursor-pointer">
                                <option value="">All Scores</option>
                                <option value="5" {{ request('score') == '5' ? 'selected' : '' }}>5 Stars</option>
                                <option value="4" {{ request('score') == '4' ? 'selected' : '' }}>4 Stars & above</option>
                                <option value="3" {{ request('score') == '3' ? 'selected' : '' }}>3 Stars & above</option>
                                <option value="2" {{ request('score') == '2' ? 'selected' : '' }}>2 Stars & above</option>
                                <option value="1" {{ request('score') == '1' ? 'selected' : '' }}>1 Star & above</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                    @if(request()->anyFilled(['search', 'seller_id', 'buyer_id', 'rating_type', 'score']))
                        <a href="{{ route('admin.ratings') }}"
                            class="px-4 py-2 border border-zinc-200 hover:bg-zinc-50 text-zinc-650 rounded-xl text-xs font-semibold transition-all">
                            Reset Filters
                        </a>
                    @endif
                    <button type="submit"
                        class="px-5 py-2 bg-zinc-900 hover:bg-black text-white rounded-xl text-xs font-semibold transition-all">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Collapsible Trash Bin -->
        <div x-data="{ openTrash: false }"
            class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 transition-all shadow-sm">
            <button @click="openTrash = !openTrash" type="button"
                class="flex items-center justify-between w-full text-zinc-700 hover:text-black focus:outline-none">
                <div class="flex items-center space-x-2 font-bold text-xs uppercase tracking-wider">
                    <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
                    <span>Trash Bin ({{ $trashedRatings->count() }})</span>
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                    :class="openTrash ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="openTrash" x-cloak class="mt-4 border-t border-zinc-200 pt-4">
                @if($trashedRatings->isEmpty())
                    <p class="text-xs text-zinc-450 italic text-center py-4">No deleted ratings in trash.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="bg-zinc-100/80 uppercase font-bold text-zinc-500 border-b border-zinc-200 text-[10px]">
                                    <th class="px-4 py-2.5">Order</th>
                                    <th class="px-4 py-2.5">Seller</th>
                                    <th class="px-4 py-2.5">Buyer</th>
                                    <th class="px-4 py-2.5">Rating</th>
                                    <th class="px-4 py-2.5">Deleted At</th>
                                    <th class="px-4 py-2.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach($trashedRatings as $tRating)
                                    <tr class="hover:bg-zinc-100/30">
                                        <td class="px-4 py-3 font-semibold text-zinc-800">
                                            {{ $tRating->order->order_number ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-zinc-650">{{ $tRating->seller->shop_name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-zinc-650">{{ $tRating->buyer->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 font-bold text-zinc-900">
                                            {{ number_format($tRating->weighted_total, 2) }}</td>
                                        <td class="px-4 py-3 text-zinc-400">{{ $tRating->deleted_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-3 text-right flex justify-end space-x-2">
                                            <form
                                                action="{{ route('admin.trash.restore', ['model' => 'ratings', 'id' => $tRating->id]) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2 py-1 bg-emerald-50 text-emerald-700 border border-emerald-250 hover:bg-emerald-100 rounded-lg font-bold text-[10px] uppercase flex items-center space-x-1">
                                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                    <span>Restore</span>
                                                </button>
                                            </form>
                                            @if(auth()->user()->isSuperAdmin())
                                                <form
                                                    action="{{ route('admin.trash.force', ['model' => 'ratings', 'id' => $tRating->id]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to permanently delete this rating? This action is irreversible!')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="px-2 py-1 bg-red-50 text-red-700 border border-red-250 hover:bg-red-100 rounded-lg font-bold text-[10px] uppercase flex items-center space-x-1">
                                                        <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                                        <span>Delete Forever</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Ratings List Card -->
        <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-zinc-50 border-b border-zinc-200 text-[10px] font-bold text-zinc-450 uppercase tracking-wider">
                            <th class="py-4 px-6">Rating Type</th>
                            <th class="py-4 px-6">Order</th>
                            <th class="py-4 px-6">Supplier (Seller)</th>
                            <th class="py-4 px-6">Buyer</th>
                            <th class="py-4 px-6">Star Rating</th>
                            <th class="py-4 px-6">Detailed Scores</th>
                            <th class="py-4 px-6">Review text</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 text-xs">
                        @forelse($ratings as $rating)
                            <tr class="hover:bg-zinc-50/40 transition-colors">
                                <td class="py-4 px-6">
                                    @if($rating->rating_type === 'manual')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <i data-lucide="edit-3" class="w-3 h-3"></i>
                                            Manual
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <i data-lucide="cpu" class="w-3 h-3"></i>
                                            Auto
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 font-semibold text-zinc-850">
                                    @if($rating->order)
                                        <a href="{{ route('admin.orders.show', $rating->order->id) }}"
                                            class="hover:text-zinc-650 hover:underline">
                                            {{ $rating->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-zinc-400">Order ID: {{ $rating->order_id }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($rating->seller)
                                        <a href="{{ route('admin.sellers.show', $rating->seller->id) }}"
                                            class="font-bold text-zinc-900 hover:text-zinc-650 hover:underline block">
                                            {{ $rating->seller->shop_name }}
                                        </a>
                                        <span class="text-[10px] text-zinc-400">{{ $rating->seller->user->email ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-zinc-400">N/A</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($rating->buyer)
                                        <div class="font-semibold text-zinc-850">{{ $rating->buyer->name }}</div>
                                        <span class="text-[10px] text-zinc-400 block">{{ $rating->buyer->email }}</span>
                                    @else
                                        <span class="text-zinc-400">N/A</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-bold text-zinc-900 text-sm">{{ number_format($rating->weighted_total, 2) }}</span>
                                        <div class="flex items-center text-yellow-400">
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-[10px] text-zinc-500 font-medium">
                                        <div>Item: <span class="font-bold text-zinc-800">{{ $rating->item_accuracy }}</span>
                                        </div>
                                        <div>Package: <span class="font-bold text-zinc-800">{{ $rating->packaging }}</span>
                                        </div>
                                        <div>Shipping: <span
                                                class="font-bold text-zinc-800">{{ $rating->shipping_speed }}</span></div>
                                        <div>Comm: <span class="font-bold text-zinc-800">{{ $rating->communication }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 max-w-xs">
                                    <p class="truncate text-zinc-650" title="{{ $rating->review_text }}">
                                        {{ $rating->review_text ?: '— No review description —' }}
                                    </p>
                                </td>
                                <td class="py-4 px-6 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('admin.ratings.edit', $rating->id) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 text-zinc-600 hover:text-black hover:bg-zinc-50 transition-all"
                                        title="Edit rating metrics">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.ratings.destroy', $rating->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this rating? Seller statistics will be updated immediately.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-100 hover:border-red-200 text-red-650 hover:bg-red-50 transition-all"
                                            title="Delete rating">
                                            <i data-lucide="trash" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-16 text-center">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-zinc-50 border border-zinc-200/60 flex items-center justify-center mx-auto mb-3 text-zinc-400">
                                        <i data-lucide="star-off" class="w-5 h-5"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-zinc-800">No Ratings Found</h4>
                                    <p class="text-xs text-zinc-450 mt-1 max-w-xs mx-auto">There are no customer/system ratings
                                        registered matching your search filter parameters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ratings->hasPages())
                <div class="px-6 py-4 border-t border-zinc-200 bg-zinc-50/50">
                    {{ $ratings->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection